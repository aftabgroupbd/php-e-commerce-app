-- ---------------------------------------------------------------------------
-- Demo data. Import AFTER schema.sql:
--     mysql -u root -p hw < database/seed.sql
--
-- Demo accounts
--   admin@shop.test    / admin123      (role: admin)
--   customer@shop.test / password123   (role: customer)
--
-- Change these passwords before putting anything online.
-- ---------------------------------------------------------------------------

INSERT INTO `customers` (`name`, `email`, `password`, `role`) VALUES
    ('Shop Admin', 'admin@shop.test',    '$2y$10$p5WFpcUsCiORKt10lYy3YuIgTzd/Q1FE8okI4TJtQnkhkN0WK.UKO', 'admin'),
    ('Demo Customer', 'customer@shop.test', '$2y$10$lRvQBAOyp/lZ8ac6c0ctXOEsPcij0wtZvaDSPeGi8TS9wnYj5It9i', 'customer');

INSERT INTO `products` (`name`, `description`, `price`, `stock`, `image`) VALUES
    ('Classic T-Shirt',  'Soft cotton t-shirt, available in every size.',      19.99, 25, '5c76dcab302962019-02-27-19-53-31270586.jpg'),
    ('Denim Jacket',     'Washed denim jacket with a button front.',           59.50, 10, '5c76dcc1557792019-02-27-19-53-53596875.jpg'),
    ('Casual Shirt',     'Everyday shirt that works at the office too.',       34.00, 15, '5c76dcd2ad27d2019-02-27-19-54-10354008.jpg'),
    ('Hooded Sweater',   'Warm hoodie with a front pocket.',                   44.25, 12, '5c7790229512b2019-02-28-08-39-14835138.jpg'),
    ('Summer Polo',      'Lightweight polo shirt for warm days.',              24.75, 30, '5c779034707dc2019-02-28-08-39-32630749.jpg'),
    ('Slim Fit Jeans',   'Stretch denim, slim through the leg.',               49.00,  8, '5c77904143f0e2019-02-28-08-39-45178855.jpg'),
    ('Winter Coat',      'Padded coat that keeps the wind out.',               99.90,  5, '5c779053570a22019-02-28-08-40-03161981.jpg'),
    ('Knit Pullover',    'Fine knit pullover for layering.',                   39.99,  0, '5c779078606c22019-02-28-08-40-40168525.jpg');
