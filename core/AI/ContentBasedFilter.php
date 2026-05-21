<?php
// ============================================================
//  CORE/AI/ContentBasedFilter.php
//  Thuật toán: Content-Based Filtering
//
//  Gợi ý sản phẩm dựa trên đặc trưng nội dung:
//    - Loại da người dùng (weight: 0.35)
//    - Danh mục sản phẩm (weight: 0.25)
//    - Khoảng giá       (weight: 0.20)
//    - Thành phần       (weight: 0.20)
// ============================================================

class ContentBasedFilter {

    private PDO   $db;
    private float $wSkinType;
    private float $wCategory;
    private float $wPrice;
    private float $wIngredient;

    public function __construct(PDO $db) {
        $this->db          = $db;
        $this->wSkinType   = AI_WEIGHT_SKIN_TYPE;
        $this->wCategory   = AI_WEIGHT_CATEGORY;
        $this->wPrice      = AI_WEIGHT_PRICE;
        $this->wIngredient = AI_WEIGHT_INGREDIENT;
    }

    /**
     * Gợi ý sản phẩm cho user dựa trên profile của họ.
     *
     * @param array $userProfile  ['skin_type'=>'oily', 'price_min'=>0, 'price_max'=>500000]
     * @param int   $excludeId    ID sản phẩm đang xem (loại trừ khỏi gợi ý)
     * @param int   $limit
     * @return array  Danh sách sản phẩm có điểm score kèm theo
     */
    public function recommend(array $userProfile, int $excludeId = 0, int $limit = 6): array {
        // Lấy tất cả sản phẩm active
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active'";
        if ($excludeId > 0) {
            $sql .= " AND p.id != :exclude_id";
        }

        $stmt = $this->db->prepare($sql);
        if ($excludeId > 0) $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Tính max_price để chuẩn hoá ──────────────────────
        $maxPrice = max(array_column($products, 'price')) ?: 1;

        // ── Tính score cho từng sản phẩm ─────────────────────
        $scored = [];
        foreach ($products as $product) {
            $score = $this->computeScore($product, $userProfile, $maxPrice);
            if ($score > 0) {
                $product['ai_score']  = round($score, 4);
                $product['ai_reason'] = $this->explainScore($product, $userProfile);
                $scored[] = $product;
            }
        }

        // Sắp xếp theo điểm giảm dần
        usort($scored, fn($a, $b) => $b['ai_score'] <=> $a['ai_score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Tính điểm tổng hợp (Weighted Sum Model)
     *
     *  score = w1*skin_match + w2*cat_match + w3*price_score + w4*ingredient_score
     */
    private function computeScore(array $product, array $userProfile, float $maxPrice): float {
        $score = 0.0;

        // ── 1. Skin Type Match ────────────────────────────────
        if (!empty($userProfile['skin_type'])) {
            $skinTypes = json_decode($product['skin_types'] ?? '[]', true);
            if (in_array($userProfile['skin_type'], $skinTypes)) {// Kiểm tra xem loại da của người dùng hiện tại có 
                                                                    // nằm trong mảng các loại da phù hợp của sản phẩm hay không. 
                $score += $this->wSkinType * 1.0; // Nhận giá trị 1.0 (nhân với trọng số wSkinType)
            } elseif (in_array('normal', $skinTypes)) {// Kiểm tra nếu sản phẩm phù hợp với da thường
                // Sản phẩm phù hợp tất cả loại da → cộng điểm nhỏ hơn
                $score += $this->wSkinType * 0.5; // cộng điểm ít hơn
            }
            // Trường hợp không thuộc cả 2 trên sẽ nhận giá trị 0.0 (không cộng điểm)
        }

        // ── 2. Category Match ─────────────────────────────────
        if (!empty($userProfile['preferred_category_id'])) {
            if ((int)$product['category_id'] === (int)$userProfile['preferred_category_id']) {
                $score += $this->wCategory * 1.0; // Khớp danh mục trực tiếp → Nhận giá trị 1.0
            } elseif ((int)($product['parent_category_id'] ?? 0) === (int)$userProfile['preferred_category_id']) {
                $score += $this->wCategory * 0.5; // Khớp danh mục cha (cùng nhóm lớn) → Nhận giá trị 0.5
            }
        } else {
            // Không có preference (Người dùng mới chưa có dữ liệu danh mục ưa thích) → Không phạt sản phẩm
            $score += $this->wCategory * 0.5; // Tự động nhận hệ số trung bình 0.5
        }

        // ── 3. Price Score (Gaussian proximity) ───────────────
        $price    = (float)$product['sale_price'] ?: (float)$product['price'];// Giá thực tế (sale nếu có)
        // Ngưỡng dưới (0 nếu chưa thiết lập)
        $minPref  = (float)($userProfile['price_min'] ?? 0);
        // Ngưỡng trên (hoặc giá cao nhất trong DB)
        $maxPref  = (float)($userProfile['price_max'] ?? $maxPrice);

        if ($minPref <= $price && $price <= $maxPref) {
            $score += $this->wPrice * 1.0;
        } elseif ($price < $minPref) {
            // Rẻ hơn mong muốn → vẫn cộng điểm
            $diff = ($minPref - $price) / $maxPrice;
            $score += $this->wPrice * max(0, 1 - $diff * 2);
        } else {
            // Đắt hơn mong muốn → trừ điểm mạnh hơn
            $diff = ($price - $maxPref) / $maxPrice;
            $score += $this->wPrice * max(0, 1 - $diff * 3);
        }

        // ── 4. Ingredient Overlap (Jaccard Similarity) ────────
        if (!empty($userProfile['preferred_ingredients'])) {
            $ingScore = $this->ingredientJaccard(
                $product['ingredients'] ?? '',
                $userProfile['preferred_ingredients']
            );
            $score += $this->wIngredient * $ingScore;
        } else {
            $score += $this->wIngredient * 0.5;
        }

        return $score;
    }

    /**
     * Jaccard Similarity giữa danh sách thành phần sản phẩm
     * và danh sách thành phần yêu thích của user.
     *
     * J(A,B) = |A ∩ B| / |A ∪ B|
     */
    private function ingredientJaccard(string $productIngredients, array $preferredIngredients): float {
        // Parse thành phần sản phẩm thành mảng
        $productSet = array_map(
            fn($i) => strtolower(trim($i)),
            explode(',', $productIngredients)
        );
        $productSet   = array_filter($productSet);
        $preferredSet = array_map('strtolower', $preferredIngredients);

        $intersection = array_intersect($productSet, $preferredSet);
        $union        = array_unique(array_merge($productSet, $preferredSet));

        if (empty($union)) return 0.5;
        return count($intersection) / count($union);
    }

    /**
     * Tạo câu giải thích lý do gợi ý (Explainable AI)
     */
    private function explainScore(array $product, array $userProfile): string {
        $reasons = [];

        $skinTypes = json_decode($product['skin_types'] ?? '[]', true);
        if (!empty($userProfile['skin_type']) && in_array($userProfile['skin_type'], $skinTypes)) {
            $skinLabel = SKIN_TYPES[$userProfile['skin_type']] ?? $userProfile['skin_type'];
            $reasons[] = "Phù hợp với {$skinLabel}";
        }

        if (!empty($userProfile['price_max'])) {
            $price = (float)$product['sale_price'] ?: (float)$product['price'];
            if ($price <= $userProfile['price_max']) {
                $reasons[] = 'Trong ngân sách của bạn';
            }
        }

        return !empty($reasons) ? implode(' · ', $reasons) : 'Được nhiều người yêu thích';
    }
}
