-- ============================================================
--  DATABASE SCHEMA - Website Bán Mỹ Phẩm
--  Tạo bởi: Senior Full-Stack Developer
--  Phiên bản: 1.0
-- ============================================================

-- CREATE DATABASE IF NOT EXISTS manguonmo_db
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE manguonmo_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────
--  1. BẢNG DANH MỤC (categories)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    slug        VARCHAR(120)  NOT NULL UNIQUE,
    parent_id   INT UNSIGNED  NULL DEFAULT NULL,  -- danh mục cha (Skincare > Serum)
    image       VARCHAR(255)  NULL,
    description TEXT          NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  2. BẢNG SẢN PHẨM (products)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED  NOT NULL,
    name            VARCHAR(200)  NOT NULL,
    slug            VARCHAR(220)  NOT NULL UNIQUE,
    brand           VARCHAR(100)  NOT NULL,
    price           DECIMAL(12,2) NOT NULL,
    sale_price      DECIMAL(12,2) NULL,            -- giá khuyến mãi
    stock           INT UNSIGNED  DEFAULT 0,
    image           VARCHAR(255)  NULL,
    images_gallery  JSON          NULL,             -- danh sách ảnh phụ
    description     TEXT          NULL,
    ingredients     TEXT          NULL,             -- thành phần (quan trọng cho AI)
    usage_guide     TEXT          NULL,             -- hướng dẫn sử dụng
    skin_types      JSON          NULL,             -- ["oily","combination"] → AI input
    featured        TINYINT(1)    DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    view_count      INT UNSIGNED  DEFAULT 0,
    sold_count      INT UNSIGNED  DEFAULT 0,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_category   (category_id),
    INDEX idx_brand      (brand),
    INDEX idx_price      (price),
    INDEX idx_status     (status),
    FULLTEXT idx_ft_search (name, brand, ingredients, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  3. BẢNG NGƯỜI DÙNG (users)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,             -- bcrypt hash
    phone       VARCHAR(15)   NULL,
    address     TEXT          NULL,
    avatar      VARCHAR(255)  NULL,
    skin_type   ENUM('oily','dry','combination','sensitive','normal') NULL, -- AI input
    role        ENUM('user','admin') DEFAULT 'user',
    is_active   TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  4. BẢNG GIỎ HÀNG (carts)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS carts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    quantity    INT UNSIGNED  DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cart_item (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  4.5. BẢNG MÃ GIẢM GIÁ (coupons)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50)    NOT NULL UNIQUE,
    type            ENUM('percent','fixed') DEFAULT 'percent',
    value           DECIMAL(10,2)  NOT NULL,
    min_order       DECIMAL(12,2)  DEFAULT 0,
    max_uses        INT UNSIGNED   DEFAULT 100,
    used_count      INT UNSIGNED   DEFAULT 0,
    expires_at      DATE           NULL,
    is_active       TINYINT(1)     DEFAULT 1,
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_code   (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  5. BẢNG ĐƠN HÀNG (orders)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED   NOT NULL,
    coupon_id       INT UNSIGNED   NULL,
    total_amount    DECIMAL(12,2)  NOT NULL,
    discount_amount DECIMAL(12,2)  DEFAULT 0,
    final_amount    DECIMAL(12,2)  NOT NULL,
    payment_method  ENUM('cod','online') DEFAULT 'cod',
    payment_status  ENUM('unpaid','paid') DEFAULT 'unpaid',
    status          ENUM('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
    shipping_name   VARCHAR(100)   NOT NULL,
    shipping_phone  VARCHAR(15)    NOT NULL,
    shipping_address TEXT          NOT NULL,
    note            TEXT           NULL,
    ordered_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE RESTRICT,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_user   (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  6. BẢNG CHI TIẾT ĐƠN HÀNG (order_details)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_details (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED   NOT NULL,
    product_id  INT UNSIGNED   NOT NULL,
    quantity    INT UNSIGNED   NOT NULL,
    unit_price  DECIMAL(12,2)  NOT NULL,  -- lưu giá tại thời điểm mua
    subtotal    DECIMAL(12,2)  NOT NULL,

    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order   (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  7. BẢNG ĐÁNH GIÁ (reviews)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED  NOT NULL,
    user_id     INT UNSIGNED  NOT NULL,
    rating      TINYINT       NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title       VARCHAR(200)  NULL,
    comment     TEXT          NULL,
    is_verified TINYINT(1)    DEFAULT 0,  -- đã mua hàng thật
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    UNIQUE KEY uq_review (product_id, user_id),
    INDEX idx_product (product_id),
    INDEX idx_rating  (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  8. BẢNG HÀNH VI NGƯỜI DÙNG (user_behavior) ← AI Input
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS user_behavior (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    action      ENUM('view','purchase','wishlist') NOT NULL,
    weight      DECIMAL(4,2)  GENERATED ALWAYS AS (
                    CASE action
                        WHEN 'purchase' THEN 1.00
                        WHEN 'wishlist' THEN 0.60
                        WHEN 'view'     THEN 0.30
                    END
                ) STORED,
    session_id  VARCHAR(64)   NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_product (user_id, product_id),
    INDEX idx_action       (action),
    INDEX idx_created      (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  9. BẢNG WISHLIST (wishlists)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS wishlists (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  DỮ LIỆU MẪU
-- ─────────────────────────────────────────────────────────────

-- Danh mục
INSERT IGNORE INTO categories (name, slug, description) VALUES
('Skincare', 'skincare', 'Các sản phẩm chăm sóc da'),
('Makeup', 'makeup', 'Trang điểm'),
('Chăm Sóc Cá Nhân', 'personal-care', 'Dầu gội, sữa tắm...');

INSERT IGNORE INTO categories (name, slug, parent_id) VALUES
('Sữa Rửa Mặt', 'sua-rua-mat', 1),
('Toner', 'toner', 1),
('Serum', 'serum', 1),
('Kem Dưỡng', 'kem-duong', 1),
('Son Môi', 'son-moi', 2),
('Phấn Nền', 'phan-nen', 2),
('Mascara', 'mascara', 2);

-- Admin
INSERT IGNORE INTO users (name, email, password, role, skin_type) VALUES
('Admin Hasami', 'admin@hasami.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'normal'),
('Nguyễn Thị Lan', 'lan@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'oily'),
('Trần Minh Châu', 'chau@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'dry'),
('Lê Thị Hoa', 'hoa@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'combination');
-- Mật khẩu mẫu: "password"

-- Sản phẩm mẫu
INSERT IGNORE INTO products (category_id, name, slug, brand, price, sale_price, stock, image, description, ingredients, skin_types, featured) VALUES
(4, 'Sữa Rửa Mặt CeraVe Hydrating', 'cerave-hydrating-cleanser', 'CeraVe', 320000, 289000, 150,
 'cerave_cleanser.jpg',
 'Sữa rửa mặt dịu nhẹ không gây khô da, duy trì hàng rào bảo vệ da tự nhiên.',
 'Ceramide NP, Ceramide AP, Ceramide EOP, Hyaluronic Acid, Niacinamide',
 '["dry","sensitive","normal"]', 1),

(4, 'Sữa Rửa Mặt La Roche-Posay Effaclar', 'la-roche-posay-effaclar-gel', 'La Roche-Posay', 380000, NULL, 200,
 'lrp_effaclar.jpg',
 'Gel rửa mặt kiểm soát dầu thừa, làm sạch sâu lỗ chân lông.',
 'Zinc Pidolate, Niacinamide, Thermal Spring Water',
 '["oily","combination"]', 1),

(5, 'Toner Paula\'s Choice BHA 2%', 'paulas-choice-bha-toner', 'Paula\'s Choice', 750000, 680000, 80,
 'paulas_bha.jpg',
 'Toner chứa BHA 2% giúp thông thoáng lỗ chân lông, giảm mụn.',
 'Salicylic Acid 2%, Green Tea Extract, Niacinamide',
 '["oily","combination"]', 1),

(6, 'Serum Vitamin C Skinceuticals C E Ferulic', 'skinceuticals-ce-ferulic', 'SkinCeuticals', 3200000, NULL, 30,
 'sce_ce_ferulic.jpg',
 'Serum chống oxy hoá hàng đầu với Vitamin C 15%, Vitamin E và Ferulic Acid.',
 'L-Ascorbic Acid 15%, Alpha Tocopherol, Ferulic Acid',
 '["normal","dry","combination"]', 1),

(7, 'Kem Dưỡng Ẩm Neutrogena Hydro Boost', 'neutrogena-hydro-boost', 'Neutrogena', 285000, 259000, 120,
 'neutrogena_hydro.jpg',
 'Gel kem dưỡng ẩm siêu nhẹ, không gây nhờn rít.',
 'Hyaluronic Acid, Dimethicone, Glycerin',
 '["oily","combination","normal"]', 0),

(8, 'Son MAC Ruby Woo', 'mac-ruby-woo-lipstick', 'MAC', 580000, NULL, 60,
 'mac_ruby_woo.jpg',
 'Son lì kinh điển màu đỏ thuần, lâu trôi cả ngày.',
 'Octyldodecanol, Lanolin, Carmine',
 '["normal","dry","oily","combination","sensitive"]', 1),

(9, 'Phấn Nền Maybelline Fit Me', 'maybelline-fit-me-foundation', 'Maybelline', 195000, 175000, 200,
 'maybelline_fitme.jpg',
 'Phấn nền kiểm soát bóng, mịn màng tự nhiên.',
 'Water, Cyclopentasiloxane, Titanium Dioxide, Niacinamide',
 '["oily","combination"]', 0),

(6, 'Serum The Ordinary Niacinamide 10%', 'the-ordinary-niacinamide-10', 'The Ordinary', 320000, 280000, 300,
 'to_niacinamide.jpg',
 'Serum Niacinamide 10% + Zinc 1% thu nhỏ lỗ chân lông, giảm thâm.',
 'Niacinamide 10%, Zinc PCA 1%, Aqua, Glycerin',
 '["oily","combination","sensitive"]', 1);

-- Mã giảm giá mẫu
INSERT INTO coupons (code, type, value, min_order, max_uses) VALUES
('GLOW10', 'percent', 10, 200000, 500),
('NEWUSER', 'fixed', 50000, 150000, 200),
('SALE20', 'percent', 20, 500000, 100);

-- Hành vi mẫu (cho AI training)
INSERT INTO user_behavior (user_id, product_id, action) VALUES
(2, 1, 'purchase'), (2, 3, 'purchase'), (2, 5, 'purchase'),
(2, 2, 'view'),     (2, 4, 'view'),
(3, 2, 'purchase'), (3, 5, 'purchase'), (3, 7, 'purchase'),
(3, 1, 'view'),     (3, 6, 'wishlist'),
(4, 1, 'purchase'), (4, 3, 'purchase'), (4, 8, 'purchase'),
(4, 6, 'view'),     (4, 4, 'view');

-- Đánh giá mẫu
INSERT INTO reviews (product_id, user_id, rating, title, comment, is_verified) VALUES
(1, 2, 5, 'Rất dịu nhẹ', 'Da mình khô hay bị rát sau rửa mặt nhưng dùng cái này không bị nữa!', 1),
(2, 3, 4, 'Mùi hơi nặng', 'Kiểm soát dầu tốt nhưng mùi không thích.', 1),
(3, 4, 5, 'BHA tốt nhất', 'Da thông thoáng hơn rõ rệt sau 2 tuần.', 1),
(8, 4, 5, 'Serum giá rẻ chất lượng tốt', 'Dùng 1 tháng lỗ chân lông nhỏ hơn nhiều.', 1);

-- ─────────────────────────────────────────────────────────────
--  10. BẢNG LỊCH SỬ CHAT AI (ai_chat_history)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ai_chat_history (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,                  -- NULL nếu là khách vãng lai
    session_id  VARCHAR(64) NULL,                   -- Dành cho việc lưu session của khách vãng lai
    sender      ENUM('user', 'bot') NOT NULL,       -- Người gửi (user hoặc bot)
    message     TEXT NOT NULL,                      -- Nội dung tin nhắn
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

