<?php
// ============================================================
//  CORE/AI/CollaborativeFilter.php
//  Thuật toán: Item-Based Collaborative Filtering
//
//  "Khách hàng mua sản phẩm A thường cũng mua sản phẩm B"
//
//  Công thức:
//    similarity(A, B) = số lần A và B cùng xuất hiện trong 1 đơn hàng
//                       -----------------------------------------
//                       sqrt(số đơn có A) * sqrt(số đơn có B)
//
//  Đây là Cosine Similarity trên không gian đơn hàng (item-item matrix)
// ============================================================

class CollaborativeFilter {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Gợi ý sản phẩm dựa trên lịch sử mua hàng.
     * Kết hợp 3 nguồn:
     *   1. Item-based CF từ order_details (co-purchase)
     *   2. User-based CF từ user_behavior (người cùng hành vi)
     *   3. Trending (sản phẩm bán chạy)
     *
     * @param int $userId
     * @param int $currentProductId  (0 nếu ở trang home)
     * @param int $limit
     */
    public function recommend(int $userId, int $currentProductId = 0, int $limit = 6): array {
        $results = [];

        // ── 1. Co-purchase (Item-based CF) ────────────────────
        if ($currentProductId > 0) {
            $coPurchase = $this->getCoPurchaseRecommendations($currentProductId, $limit);
            foreach ($coPurchase as $p) {
                $pid = $p['id'];
                if (!isset($results[$pid])) {
                    $results[$pid] = $p;
                    $results[$pid]['cf_score'] = 0;
                }
                $results[$pid]['cf_score'] += (float)$p['cosine_sim'] * 0.6;
                $results[$pid]['cf_reason'] = 'Thường mua cùng nhau';
            }
        }

        // ── 2. User-based CF (Similar users' purchases) ───────
        if ($userId > 0) {
            $similarUsers = $this->getSimilarUserRecommendations($userId, $limit);
            foreach ($similarUsers as $p) {
                $pid = $p['id'];
                if (!isset($results[$pid])) {
                    $results[$pid] = $p;
                    $results[$pid]['cf_score'] = 0;
                }
                $results[$pid]['cf_score'] += (float)$p['similarity_score'] * 0.4;
                if (empty($results[$pid]['cf_reason'])) {
                    $results[$pid]['cf_reason'] = 'Người dùng tương tự yêu thích';
                }
            }
        }

        // ── 3. Trending fallback nếu không đủ ─────────────────
        if (count($results) < $limit) {
            $trending = $this->getTrending($limit - count($results));
            foreach ($trending as $p) {
                $pid = $p['id'];
                if (!isset($results[$pid])) {
                    $results[$pid] = $p;
                    $results[$pid]['cf_score'] = (float)$p['sold_count'] / 100;
                    $results[$pid]['cf_reason'] = 'Đang bán chạy';
                }
            }
        }

        // Sắp xếp theo score
        uasort($results, fn($a, $b) => $b['cf_score'] <=> $a['cf_score']);

        return array_values(array_slice($results, 0, $limit));
    }

    /**
     * Item-Based Collaborative Filtering bằng SQL thuần.
     *
     * Ý tưởng:
     *   - Tìm tất cả đơn hàng chứa $productId
     *   - Trong các đơn đó, tìm sản phẩm nào cùng xuất hiện
     *   - Tính cosine similarity:
     *       sim(A,B) = co_count / sqrt(count_A * count_B)
     */
    private function getCoPurchaseRecommendations(int $productId, int $limit): array {
        $sql = "
            SELECT
                p.*,
                c.name AS category_name,
                c.parent_id AS parent_category_id,
                COUNT(*)                    AS co_count,
                (
                    SELECT COUNT(DISTINCT order_id)
                    FROM order_details
                    WHERE product_id = :pid1
                )                           AS count_a,
                (
                    SELECT COUNT(DISTINCT order_id)
                    FROM order_details
                    WHERE product_id = od2.product_id
                )                           AS count_b,
                COUNT(*) / SQRT(
                    (SELECT COUNT(DISTINCT order_id) FROM order_details WHERE product_id = :pid2) *
                    (SELECT COUNT(DISTINCT order_id) FROM order_details WHERE product_id = od2.product_id)
                )                           AS cosine_sim
            FROM order_details od1
            JOIN order_details od2
                ON od1.order_id   = od2.order_id
                AND od2.product_id != :pid3
            JOIN products p  ON od2.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE od1.product_id = :pid4
              AND p.status = 'active'
              AND p.stock > 0
            GROUP BY od2.product_id
            ORDER BY cosine_sim DESC
            LIMIT :lim
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid1', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':pid2', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':pid3', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':pid4', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit,     PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * User-Based CF: Tìm người dùng có hành vi tương tự
     * → Gợi ý sản phẩm họ đã mua/xem nhưng user hiện tại chưa có
     *
     * Tương đồng = dot product của vector hành vi (weighted)
     */
    private function getSimilarUserRecommendations(int $userId, int $limit): array {
        $sql = "
            SELECT
                p.*,
                c.name AS category_name,
                c.parent_id AS parent_category_id,
                SUM(ub_other.weight)  AS raw_score,
                COUNT(DISTINCT ub_similar.user_id) AS user_count,
                SUM(ub_other.weight) * COUNT(DISTINCT ub_similar.user_id) AS similarity_score
            FROM user_behavior ub_me
            -- Tìm user khác cũng tương tác với sản phẩm user hiện tại đã xem
            JOIN user_behavior ub_similar
                ON  ub_similar.product_id = ub_me.product_id
                AND ub_similar.user_id   != :uid1
            -- Lấy sản phẩm user tương tự đã tương tác
            JOIN user_behavior ub_other
                ON  ub_other.user_id = ub_similar.user_id
                AND ub_other.product_id NOT IN (
                    SELECT product_id FROM user_behavior WHERE user_id = :uid2
                )
            JOIN products p    ON ub_other.product_id = p.id
            JOIN categories c  ON p.category_id = c.id
            WHERE ub_me.user_id = :uid3
              AND p.status = 'active'
              AND p.stock > 0
            GROUP BY ub_other.product_id
            ORDER BY similarity_score DESC
            LIMIT :lim
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid1', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid3', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit,  PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fallback: Trending products (bán chạy + xem nhiều)
     */
    private function getTrending(int $limit): array {
        $sql = "
            SELECT p.*, c.name AS category_name, c.parent_id AS parent_category_id,
                   (p.sold_count * 3 + p.view_count) AS trend_score
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active' AND p.stock > 0
            ORDER BY trend_score DESC
            LIMIT :lim
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
