CREATE DATABASE IF NOT EXISTS somba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE somba;

CREATE TABLE games (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 slug VARCHAR(120) NOT NULL UNIQUE,
 description TEXT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 game_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(190) NOT NULL,
 slug VARCHAR(190) NOT NULL UNIQUE,
 provider VARCHAR(80) NOT NULL DEFAULT 'topup.dev',
 provider_sku VARCHAR(190) NULL,
 maketou_cart_id VARCHAR(190) NULL,
 price DECIMAL(15,2) NOT NULL DEFAULT 0,
 currency CHAR(3) NOT NULL DEFAULT 'XAF',
 delivery_type VARCHAR(80) NOT NULL DEFAULT 'game_id',
 active TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_number VARCHAR(40) NOT NULL UNIQUE,
 product_id BIGINT UNSIGNED NOT NULL,
 customer_email VARCHAR(190) NULL,
 customer_data JSON NOT NULL,
 amount DECIMAL(15,2) NOT NULL,
 currency CHAR(3) NOT NULL,
 payment_status ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
 delivery_status ENUM('pending','processing','delivered','failed') NOT NULL DEFAULT 'pending',
 maketou_reference VARCHAR(190) NULL,
 provider_order_id VARCHAR(190) NULL,
 provider_response JSON NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE webhook_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 provider VARCHAR(80) NOT NULL,
 event_id VARCHAR(190) NULL,
 payload JSON NOT NULL,
 processed TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_provider_event (provider,event_id)
);

INSERT INTO games (name,slug,description) VALUES
('Free Fire','free-fire','Diamonds Free Fire'),
('PUBG Mobile','pubg-mobile','UC PUBG Mobile'),
('Mobile Legends','mobile-legends','Diamonds Mobile Legends'),
('Genshin Impact','genshin-impact','Genesis Crystals'),
('Razer Gold','razer-gold','PIN / crédit'),
('Steam','steam','Gift Cards Steam'),
('Apple','apple','Gift Cards Apple'),
('Google Play','google-play','Gift Cards Google Play'),
('Telegram','telegram','Produits/crédits selon catalogue');
