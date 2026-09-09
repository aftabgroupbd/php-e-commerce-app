-- ---------------------------------------------------------------------------
-- Mini Shop - database schema
--
-- Import with:  mysql -u root -p hw < database/schema.sql
--
-- Naming rules used here:
--   * table names are plural           (customers, products, orders)
--   * primary key is always "id"
--   * foreign keys are "<table>_id"    (customer_id, order_id, product_id)
--   * every table has created_at / updated_at timestamps
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `customers`;

-- ---------------------------------------------------------------------------
-- customers: both shop customers and administrators live here.
-- The "role" column decides what a user is allowed to do.
-- ---------------------------------------------------------------------------
CREATE TABLE `customers` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(190) NOT NULL,
    -- password_hash() output is 60 chars today, 255 leaves room for future algorithms
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    -- two accounts can never share an email address
    UNIQUE KEY `uniq_customers_email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------------
CREATE TABLE `products` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    -- DECIMAL, never FLOAT: money must not lose precision
    `price`       DECIMAL(10, 2) UNSIGNED NOT NULL,
    `stock`       INT UNSIGNED NOT NULL DEFAULT 0,
    -- only the file name, e.g. "a1b2c3.jpg"; the folder comes from config
    `image`       VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_products_name` (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- orders: one row per placed order
-- ---------------------------------------------------------------------------
CREATE TABLE `orders` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id`  INT UNSIGNED NOT NULL,
    `total_amount` DECIMAL(10, 2) UNSIGNED NOT NULL,
    `status`       ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    -- shipping details are copied into the order: the customer may edit their
    -- profile later, but the address this parcel was sent to must not change
    `shipping_name`    VARCHAR(100) NOT NULL,
    `shipping_phone`   VARCHAR(30)  NOT NULL,
    `shipping_address` VARCHAR(255) NOT NULL,
    `shipping_city`    VARCHAR(100) NOT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_orders_customer` (`customer_id`),
    KEY `idx_orders_status` (`status`),
    -- deleting an account also deletes that account's orders
    CONSTRAINT `fk_orders_customer`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- order_items: one row per product inside an order.
--
-- product_name / price are COPIED into this table on purpose. If the shop
-- later renames the product or changes its price, an old invoice must still
-- show what the customer actually bought and paid.
-- ---------------------------------------------------------------------------
CREATE TABLE `order_items` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`     INT UNSIGNED NOT NULL,
    -- NULL-able: the product may be deleted from the catalogue later
    `product_id`   INT UNSIGNED NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `price`        DECIMAL(10, 2) UNSIGNED NOT NULL,
    `quantity`     INT UNSIGNED NOT NULL,
    `subtotal`     DECIMAL(10, 2) UNSIGNED NOT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order` (`order_id`),
    KEY `idx_order_items_product` (`product_id`),
    CONSTRAINT `fk_order_items_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
