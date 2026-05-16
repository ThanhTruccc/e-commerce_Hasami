<?php
require_once CORE_PATH . '/Model.php';

// ============================================================
//  MODEL: Product.php
// ============================================================

class Product extends Model {

    protected string $table = 'products';

    /** Lấy sản phẩm kèm tên danh mục */
    public function getWithCategory(int $id): array|false {
        return $this->query("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(r.id) AS review_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE p.id = :id AND p.status = 'active'
            GROUP BY p.id
        ", [':id' => $id])->fetch();
    }

    /** Lọc và phân trang */
    public function filter(array $params, int $page = 1): array {
        $where  = ['p.status = "active"'];
        $binds  = [];

        if (!empty($params['category_id'])) {
            $where[] = 'p.category_id = :cat_id';
            $binds[':cat_id'] = (int)$params['category_id'];
        }
        if (!empty($params['brand'])) {
            $where[] = 'p.brand = :brand';
            $binds[':brand'] = $params['brand'];
        }
        if (!empty($params['skin_type'])) {
            $where[] = "JSON_CONTAINS(p.skin_types, :skin, '$')";
            $binds[':skin'] = json_encode($params['skin_type']);
        }
        if (!empty($params['price_min'])) {
            $where[] = 'COALESCE(p.sale_price, p.price) >= :pmin';
            $binds[':pmin'] = (float)$params['price_min'];
        }
        if (!empty($params['price_max'])) {
            $where[] = 'COALESCE(p.sale_price, p.price) <= :pmax';
            $binds[':pmax'] = (float)$params['price_max'];
        }
        if (!empty($params['search'])) {
            $where[] = 'MATCH(p.name, p.brand, p.ingredients, p.description) AGAINST(:q IN BOOLEAN MODE)';
            $binds[':q'] = $params['search'] . '*';
        }

        $whereClause = implode(' AND ', $where);
        $offset      = ($page - 1) * PRODUCTS_PER_PAGE;

        $sortMap = [
            'newest'     => 'p.created_at DESC',
            'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
            'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
            'bestseller' => 'p.sold_count DESC',
            'rating'     => 'avg_rating DESC',
        ];
        $orderBy = $sortMap[$params['sort'] ?? 'newest'] ?? 'p.created_at DESC';

        $sql = "
            SELECT p.*, c.name AS category_name,
                   COALESCE(AVG(r.rating), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE {$whereClause}
            GROUP BY p.id
            ORDER BY {$orderBy}
            LIMIT " . PRODUCTS_PER_PAGE . " OFFSET {$offset}
        ";

        $data     = $this->query($sql, $binds)->fetchAll();
        $total    = $this->countFilter($whereClause, $binds);
        $pages    = ceil($total / PRODUCTS_PER_PAGE);

        return compact('data', 'total', 'pages', 'page');
    }

    private function countFilter(string $where, array $binds): int {
        return (int)$this->query(
            "SELECT COUNT(DISTINCT p.id) FROM products p
             JOIN categories c ON p.category_id = c.id
             LEFT JOIN reviews r ON r.product_id = p.id
             WHERE {$where}",
            $binds
        )->fetchColumn();
    }

    public function getFeatured(int $limit = 8): array {
        return $this->query("
            SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating
            FROM products p
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE p.featured = 1 AND p.status = 'active'
            GROUP BY p.id ORDER BY p.sold_count DESC LIMIT :lim
        ", [':lim' => $limit])->fetchAll();
    }

    public function getBrands(): array {
        return $this->query(
            "SELECT DISTINCT brand FROM products WHERE status='active' ORDER BY brand"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateStock(int $id, int $quantity): void {
        $this->query(
            "UPDATE products SET stock = stock - :qty, sold_count = sold_count + :sold WHERE id = :id",
            [':qty' => $quantity, ':sold' => $quantity, ':id' => $id]
        );
    }

    public function getDashboardStats(): array {
        return $this->query("
            SELECT
                COUNT(*) AS total_products,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                SUM(CASE WHEN sale_price IS NOT NULL THEN 1 ELSE 0 END) AS on_sale
            FROM products WHERE status = 'active'
        ")->fetch();
    }
}
