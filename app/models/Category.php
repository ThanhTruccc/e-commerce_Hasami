<?php
require_once CORE_PATH . '/Model.php';

// ============================================================
//  MODEL: Category.php
// ============================================================

class Category extends Model {

    protected string $table = 'categories';

    public function getTree(): array {
        $all    = $this->findAll('name ASC');
        $tree   = [];
        $lookup = [];

        foreach ($all as &$cat) {
            $cat['children'] = [];
            $lookup[$cat['id']] = &$cat;
        }
        foreach ($all as &$cat) {
            if ($cat['parent_id'] && isset($lookup[$cat['parent_id']])) {
                $lookup[$cat['parent_id']]['children'][] = &$cat;
            } else {
                $tree[] = &$cat;
            }
        }
        return $tree;
    }

    public function findBySlug(string $slug): array|false {
        return $this->query(
            "SELECT * FROM categories WHERE slug = :slug LIMIT 1",
            [':slug' => $slug]
        )->fetch();
    }

    public function getWithProductCount(): array {
        return $this->query("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
            GROUP BY c.id ORDER BY c.name
        ")->fetchAll();
    }
}
