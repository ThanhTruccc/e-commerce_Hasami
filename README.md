# GlowViet — Website Bán Mỹ Phẩm + AI Gợi Ý Sản Phẩm

> **Đồ án đại học** | PHP MVC thuần + MySQL + AI Recommendation Engine (không dùng API trả phí)

---

## 🚀 Cài Đặt Nhanh

### Yêu Cầu Hệ Thống
- PHP ≥ 8.1
- MySQL ≥ 8.0
- Apache với `mod_rewrite` bật (XAMPP/WAMP/Laragon)

### Bước 1: Đặt project vào web root
```
htdocs/manguonmo/   (XAMPP)
www/manguonmo/      (WAMP)
```

### Bước 2: Tạo database
```sql
-- Mở phpMyAdmin hoặc MySQL CLI
mysql -u root -p < database/schema.sql
```

### Bước 3: Cấu hình
Mở `config/config.php` và sửa:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'manguonmo_db');
define('DB_USER', 'root');
define('DB_PASS', 'thanhtruc');   // mật khẩu MySQL của bạn
define('APP_URL',  'http://localhost/manguonmo/public');
```

### Bước 4: Tạo thư mục upload
```
public/images/products/   (tạo thủ công, cấp quyền ghi)
public/images/uploads/
```

### Bước 5: Chạy
Truy cập: `http://localhost/manguonmo/public`

---

## 🔐 Tài Khoản Demo

| Vai trò | Email | Mật khẩu |
|---------|-------|-----------|
| Admin   | admin@glowviet.vn | password |
| User 1  | lan@gmail.com | password |
| User 2  | chau@gmail.com | password |

---

## 📁 Cấu Trúc Thư Mục

```
manguonmo/
├── app/
│   ├── controllers/          # 8 Controllers
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   ├── AuthController.php
│   │   ├── CartController.php   (+ OrderController)
│   │   ├── AdminController.php  (+ ReviewController + WishlistController)
│   ├── models/               # 10 Models
│   │   ├── Product.php
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Cart.php
│   │   ├── Order.php          (+ OrderDetail)
│   │   └── Review.php         (+ Wishlist + Coupon)
│   └── views/
│       ├── layouts/           # main_layout.php + admin_layout.php
│       ├── home/index.php
│       ├── product/           # list.php + detail.php
│       ├── cart/index.php
│       ├── order/             # checkout.php + history.php
│       ├── auth/              # login.php + register.php
│       ├── admin/             # dashboard.php
│       └── partials/          # product_card.php
│
├── core/
│   ├── App.php                # Router
│   ├── Controller.php         # Base Controller
│   ├── Model.php              # Base Model (PDO)
│   └── AI/
│       ├── RecommendationEngine.php   # Hybrid fusion
│       ├── ContentBasedFilter.php     # CBF algorithm
│       └── CollaborativeFilter.php    # Item-based CF
│
├── config/config.php          # Cấu hình hệ thống
├── database/schema.sql        # DDL + dữ liệu mẫu
└── public/
    ├── index.php              # Entry point
    ├── .htaccess              # URL rewriting
    ├── css/style.css          # Custom CSS
    └── js/main.js             # Frontend JS
```

---

## 🤖 Giải Thích Thuật Toán AI

### 1. Content-Based Filtering (CBF)
Dựa trên đặc trưng sản phẩm và profile người dùng.

**Công thức:**
```
score(p) = 0.35 × skin_type_match
         + 0.25 × category_match
         + 0.20 × price_proximity
         + 0.20 × ingredient_jaccard
```

- **skin_type_match**: 1.0 nếu da khớp, 0.5 nếu phù hợp mọi da, 0.0 nếu không phù hợp
- **category_match**: 1.0 nếu đúng danh mục ưa thích, 0.5 nếu danh mục cha
- **price_proximity**: Gaussian proximity so với khoảng giá mong muốn
- **ingredient_jaccard**: |A ∩ B| / |A ∪ B| với tập thành phần

### 2. Collaborative Filtering (Item-Based CF)
```sql
-- "Khách mua A thường mua B" — Cosine Similarity
SELECT p2.*, COUNT(*) / SQRT(count_A * count_B) AS cosine_sim
FROM order_details od1
JOIN order_details od2 ON od1.order_id = od2.order_id AND od2.product_id != :pid
JOIN products p2 ON od2.product_id = p2.id
WHERE od1.product_id = :pid
GROUP BY od2.product_id
ORDER BY cosine_sim DESC
LIMIT 6;
```

### 3. Hybrid Fusion
```
hybrid_score = cbf_score × 0.45 + colf_score × 0.55
```
Sản phẩm xuất hiện trong cả hai → nhận bonus ×1.1

### 4. Behavior Tracking
| Hành vi | Trọng số |
|---------|---------|
| Mua hàng | 1.00 |
| Wishlist | 0.60 |
| Xem      | 0.30 |

---

## 🗄️ Sơ Đồ Quan Hệ (ERD)

```
users ─────────────── orders ── order_details ── products
  │                     │                           │
  │                   coupons                   categories
  │                                                 │
  ├── carts ─────────── products              (parent_id)
  ├── reviews ────────── products
  ├── wishlists ──────── products
  ├── user_behavior ─── products ← AI INPUT
  └── ai_chat_history ── (Lịch sử tư vấn)
```

---

## 🌊 Luồng Xử Lý (Data Flow)

```
User mở trang chủ
    └─→ HomeController::index()
        ├─→ ProductModel::getFeatured()   → DB
        ├─→ CategoryModel::getTree()      → DB
        └─→ RecommendationEngine::getRecommendations(userId)
            ├─→ buildUserProfile(userId)  → DB (skin_type, behavior)
            ├─→ ContentBasedFilter        → score mỗi sản phẩm
            ├─→ CollaborativeFilter       → item-based CF, user-based CF
            └─→ hybridFusion()            → merge + sort
                └─→ View: home/index.php  ← hiển thị gợi ý AI

User xem sản phẩm
    └─→ ProductController::detail($id)
        ├─→ trackBehavior(userId, id, 'view')   → user_behavior
        ├─→ getSimilarProducts($id, userId)      → AI
        └─→ View: product/detail.php

User đặt hàng
    └─→ OrderController::placeOrder()
        ├─→ Order::createOrder()
        ├─→ Product::updateStock()
        ├─→ trackBehavior(userId, id, 'purchase')  → user_behavior
        └─→ Cart::clearCart()
```

---

## 🔗 URL Routes

| URL | Controller | Method |
|-----|-----------|--------|
| `/` | HomeController | index |
| `/product` | ProductController | index |
| `/product/detail/1` | ProductController | detail(1) |
| `/cart` | CartController | index |
| `/cart/add` | CartController | add |
| `/order/checkout` | OrderController | checkout |
| `/order/history` | OrderController | history |
| `/auth/login` | AuthController | login |
| `/auth/register` | AuthController | register |
| `/admin` | AdminController | index |
| `/admin/products` | AdminController | products |
| `/admin/orders` | AdminController | orders |
| `/wishlist/toggle` | WishlistController | toggle |
| `/ai/chat` | AIController | chat |
| `/ai/history` | AIController | history |

---

## ✨ Tính Năng Đầy Đủ

### Người Dùng
- [x] Đăng ký / Đăng nhập (bcrypt)
- [x] Chọn loại da khi đăng ký → AI cá nhân hóa
- [x] Xem sản phẩm theo danh mục (Skincare, Makeup)
- [x] Tìm kiếm full-text (MySQL FULLTEXT)
- [x] Lọc theo loại da, giá, thương hiệu
- [x] Xem chi tiết sản phẩm (thành phần, đánh giá, tabs)
- [x] Thêm vào giỏ hàng (AJAX)
- [x] Đặt hàng COD
- [x] Áp dụng mã giảm giá
- [x] Xem lịch sử đơn hàng
- [x] Wishlist
- [x] Đánh giá sản phẩm (chỉ ai đã mua)

### Admin
- [x] Dashboard thống kê (Chart.js)
- [x] Doanh thu theo tháng
- [x] Top sản phẩm bán chạy
- [x] CRUD sản phẩm (upload ảnh)
- [x] Quản lý đơn hàng (cập nhật trạng thái)
- [x] Quản lý người dùng (khoá/mở)

### AI
- [x] Content-Based Filtering (weighted score)
- [x] Collaborative Filtering (cosine similarity SQL)
- [x] Behavior Tracking (view, purchase, wishlist)
- [x] Hybrid Fusion
- [x] Explainable AI (hiển thị lý do gợi ý)
- [x] AJAX filter real-time
- [x] AI Chatbot tư vấn sản phẩm thông minh
- [x] Lưu trữ và hiển thị lịch sử trò chuyện AI (Database)

---

## 📚 Công Nghệ Sử Dụng

| Layer | Công nghệ |
|-------|-----------|
| Backend | PHP 8.1 (MVC thuần, không framework) |
| Database | MySQL 8.0 + PDO |
| Frontend | Bootstrap 5.3 + Vanilla JS + CSS3 |
| Charts | Chart.js 4 |
| Icons | Bootstrap Icons |
| Fonts | Google Fonts (Outfit + Playfair Display) |
| AI | Custom PHP algorithms + pure SQL |


