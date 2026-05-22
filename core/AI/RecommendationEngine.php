<?php
// ============================================================
//  CORE/AI/RecommendationEngine.php
//  Tổng hợp điểm từ Content-Based + Collaborative Filtering
//  Hybrid Recommendation System
// ============================================================

require_once CORE_PATH . '/AI/ContentBasedFilter.php';
require_once CORE_PATH . '/AI/CollaborativeFilter.php';

class RecommendationEngine {

    private ContentBasedFilter $cbf;
    private CollaborativeFilter $colf;
    private PDO $db;

    // Trọng số kết hợp hai thuật toán
    private float $cbfWeight  = 0.45;  // Content-based
    private float $colfWeight = 0.55;  // Collaborative

    public function __construct(PDO $db) {
        $this->db   = $db;
        $this->cbf  = new ContentBasedFilter($db);
        $this->colf = new CollaborativeFilter($db);
    }

    /**
     * API chính: Gợi ý sản phẩm cho user
     *
     * @param int   $userId             0 nếu là guest
     * @param int   $currentProductId   Sản phẩm đang xem (0 nếu trang home)
     * @param array $filters            Bộ lọc thêm từ user
     */
    public function getRecommendations(int $userId, int $currentProductId = 0, array $filters = []): array {
        // ── Lấy profile người dùng ─────────────────────────────
        $userProfile = $this->buildUserProfile($userId, $filters);

        // ── Chạy hai thuật toán song song ─────────────────────
        // Lấy nhiều kết quả hơn (ví dụ: 50 sản phẩm) để có đủ tập mẫu cho việc lọc nghiêm ngặt
        $cbResults   = $this->cbf->recommend($userProfile, $currentProductId, 50);
        $colfResults = $this->colf->recommend($userId, $currentProductId, 50);

        // ── Áp dụng bộ lọc nghiêm ngặt (Strict Filtering) ──
        if (!empty($filters)) {
            $strictFilter = function($p) use ($filters) {
                // 1. Lọc theo Loại da
                if (!empty($filters['skin_type'])) {
                    $skinTypes = json_decode($p['skin_types'] ?? '[]', true);
                    if (!is_array($skinTypes)) $skinTypes = [];
                    if (!in_array($filters['skin_type'], $skinTypes) && !in_array('normal', $skinTypes)) {
                        return false;
                    }
                }

                // 2. Lọc theo Giá thực tế (sale_price nếu có, không thì price)
                $price = (float)($p['sale_price'] ?: $p['price']);
                if (isset($filters['price_min']) && $price < (float)$filters['price_min']) {
                    return false;
                }
                if (isset($filters['price_max']) && $price > (float)$filters['price_max']) {
                    return false;
                }

                // 3. Lọc theo Danh mục (khớp danh mục trực tiếp hoặc danh mục cha)
                if (!empty($filters['category_id'])) {
                    $prodCatId = (int)$p['category_id'];
                    $prodParentCatId = (int)($p['parent_category_id'] ?? 0);
                    $filterCatId = (int)$filters['category_id'];
                    if ($prodCatId !== $filterCatId && $prodParentCatId !== $filterCatId) {
                        return false;
                    }
                }

                return true;
            };

            $cbResults   = array_filter($cbResults, $strictFilter);
            $colfResults = array_filter($colfResults, $strictFilter);
        }

        // ── Kết hợp kết quả (Hybrid Fusion) ───────────────────
        $merged = $this->hybridFusion($cbResults, $colfResults);

        // ── Loại trừ sản phẩm user đã mua & sản phẩm đang xem ──
        $purchased = $userId > 0 ? $this->getPurchasedProductIds($userId) : [];
        
        $merged = array_filter($merged, function($p) use ($purchased, $currentProductId) {
            $pid = (int)$p['id'];
            if ($currentProductId > 0 && $pid === $currentProductId) return false;
            if (in_array($pid, $purchased)) return false;
            return true;
        });

        return array_values(array_slice($merged, 0, AI_MAX_RECOMMENDATIONS));
    }

    /**
     * Hybrid Fusion: Kết hợp điểm từ CBF và CollF
     * Dùng linear combination + normalize
     */
    private function hybridFusion(array $cbResults, array $colfResults): array {
        $combined = [];

        // Normalize CBF scores về [0,1]
        $maxCbf = max(array_column($cbResults, 'ai_score') ?: [0]);
        if ($maxCbf <= 0) $maxCbf = 1;

            foreach ($cbResults as $p) {
                $pid = (int)$p['id'];
                $combined[$pid] = $p;
                $combined[$pid]['hybrid_score'] = ($p['ai_score'] / $maxCbf) * $this->cbfWeight;
                $combined[$pid]['sources'] = ['cbf'];
                $combined[$pid]['ai_reason'] = "Phù hợp với loại da";
            }

            // Normalize CollF scores về [0,1]
            $colfScores = array_column($colfResults, 'cf_score');
            $maxColf    = max($colfScores ?: [0]);
            if ($maxColf <= 0) $maxColf = 1;

            foreach ($colfResults as $p) {
                $pid = (int)$p['id'];
                $normalizedScore = ($p['cf_score'] / $maxColf) * $this->colfWeight;

                if (isset($combined[$pid])) {
                    $combined[$pid]['hybrid_score'] += $normalizedScore * 1.1;
                    $combined[$pid]['sources'][] = 'colf';
                    $combined[$pid]['ai_reason'] = "Được nhiều người mua cùng";
                } else {
                    $combined[$pid] = $p;
                    $combined[$pid]['hybrid_score'] = $normalizedScore;
                    $combined[$pid]['sources'] = ['colf'];
                    $combined[$pid]['ai_reason'] = "Gợi ý cho bạn";
                }
            }

        // Sắp xếp theo hybrid score
        uasort($combined, fn($a, $b) => $b['hybrid_score'] <=> $a['hybrid_score']);

        return array_values($combined);
    }

    /**
     * Xây dựng profile người dùng từ:
     *   - Dữ liệu profile (skin_type)
     *   - Session filters
     *   - Lịch sử hành vi (preferred category, ingredients)
     */
    private function buildUserProfile(int $userId, array $filters): array {
        $profile = [
            'skin_type'               => null,
            'preferred_category_id'   => null,
            'price_min'               => 0,
            'price_max'               => PHP_FLOAT_MAX,
            'preferred_ingredients'   => [],
        ];

        // Override từ session filters (user đang lọc)
        if (!empty($filters['skin_type']))    $profile['skin_type']    = $filters['skin_type'];
        if (!empty($filters['price_max']))    $profile['price_max']    = (float)$filters['price_max'];
        if (!empty($filters['price_min']))    $profile['price_min']    = (float)$filters['price_min'];
        if (!empty($filters['category_id']))  $profile['preferred_category_id'] = (int)$filters['category_id'];

        if ($userId <= 0) return $profile;

        // Lấy skin_type và info từ DB
        $stmt = $this->db->prepare("SELECT skin_type FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && !$profile['skin_type']) {
            $profile['skin_type'] = $user['skin_type'];
        }

        // Suy luận preferred category từ lịch sử hành vi
        $stmt = $this->db->prepare("
            SELECT p.category_id, SUM(ub.weight) AS pref_score
            FROM user_behavior ub
            JOIN products p ON ub.product_id = p.id
            WHERE ub.user_id = :uid
            GROUP BY p.category_id
            ORDER BY pref_score DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $prefCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($prefCategory && !$profile['preferred_category_id']) {
            $profile['preferred_category_id'] = (int)$prefCategory['category_id'];
        }

        // Suy luận preferred ingredients từ sản phẩm đã mua/xem
        $stmt = $this->db->prepare("
            SELECT p.ingredients
            FROM user_behavior ub
            JOIN products p ON ub.product_id = p.id
            WHERE ub.user_id = :uid AND ub.action IN ('purchase', 'wishlist')
            ORDER BY ub.weight DESC
            LIMIT 5
        ");
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $ings = array_map(
                fn($i) => strtolower(trim($i)),
                explode(',', $row['ingredients'] ?? '')
            );
            $profile['preferred_ingredients'] = array_unique(
                array_merge($profile['preferred_ingredients'], array_filter($ings))
            );
        }

        return $profile;
    }

    /**
     * Lấy danh sách product_id mà user đã mua
     */
    private function getPurchasedProductIds(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT od.product_id
            FROM orders o
            JOIN order_details od ON o.id = od.order_id
            WHERE o.user_id = :uid AND o.status = 'delivered'
        ");
        $stmt->execute([':uid' => $userId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
    }

    /**
     * Ghi nhận hành vi người dùng vào DB
     *
     * @param int    $userId
     * @param int    $productId
     * @param string $action  'view' | 'purchase' | 'wishlist'
     */
    public function trackBehavior(int $userId, int $productId, string $action): void {
        if ($userId <= 0 || $productId <= 0) return;

        // Nếu là 'view' → chỉ insert nếu chưa view trong 1 giờ (tránh spam)
        if ($action === 'view') {
            $stmt = $this->db->prepare("
                SELECT id FROM user_behavior
                WHERE user_id = :uid AND product_id = :pid AND action = 'view'
                  AND created_at > NOW() - INTERVAL 1 HOUR
                LIMIT 1
            ");
            $stmt->execute([':uid' => $userId, ':pid' => $productId]);
            if ($stmt->fetch()) return; // Đã view trong 1 giờ → bỏ qua
        }

        $stmt = $this->db->prepare("
            INSERT INTO user_behavior (user_id, product_id, action, session_id)
            VALUES (:uid, :pid, :action, :sid)
        ");
        $stmt->execute([
            ':uid'    => $userId,
            ':pid'    => $productId,
            ':action' => $action,
            ':sid'    => session_id(),
        ]);

        // Tăng view_count nếu là view
        if ($action === 'view') {
            $this->db->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = :pid")
                     ->execute([':pid' => $productId]);
        }
    }

    /**
     * Gợi ý sản phẩm "Bạn có thể thích" trên trang chi tiết
     * (kết hợp cả hai nguồn nhưng ưu tiên CF)
     */
    public function getSimilarProducts(int $productId, int $userId = 0, int $limit = 4): array {
        // Lấy thông tin sản phẩm hiện tại để làm query profile
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) return [];

        $filters = [
            'skin_type'   => json_decode($product['skin_types'] ?? '[]', true)[0] ?? null,
            'category_id' => $product['category_id'],
            'price_min'   => (float)$product['price'] * 0.4,
            'price_max'   => (float)$product['price'] * 2.5,
        ];

        return $this->getRecommendations($userId, $productId, $filters);
    }
}
