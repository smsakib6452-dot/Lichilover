-- =====================================================================
-- Lichi Lover — Sample Data (DEVELOPMENT ONLY)
-- Run this AFTER database.sql. Import into the `lichi_lover` database.
--
-- Includes: categories, products, variants, delivery zones, coupons,
--           demo reviews (marked is_demo=1), demo accounts and settings.
--
-- DEMO ACCOUNTS (DEVELOPMENT ONLY — change before going live):
--   Admin:    admin@lichilover.com  / admin123  (must change password)
--   Customer: customer@lichilover.com / customer123
-- =====================================================================

USE `lichi_lover`;

-- ---------------------------------------------------------------------
-- Categories
-- ---------------------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `sort_order`, `is_active`) VALUES
(1, 'Fresh Lichi', 'fresh-lichi', 'Freshly harvested lichi selected from trusted farms.', 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', 1, 1),
(2, 'Boxes & Gift Packs', 'boxes-gift-packs', 'Beautifully packed lichi boxes for family and gifting.', 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80', 2, 1),
(3, 'Combos & Deals', 'combos-deals', 'Great value combos for the whole family.', 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80', 3, 1);

-- ---------------------------------------------------------------------
-- Products
-- ---------------------------------------------------------------------
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `base_price`, `compare_price`, `image`, `gallery`, `stock_qty`, `sold_count`, `rating_avg`, `rating_count`, `is_featured`, `is_active`) VALUES
(1, 1, 'Premium Rajshahi Lichi', 'premium-rajshahi-lichi', 'Naturally sweet, juicy lichi harvested fresh from Rajshahi orchards.', '<p>Our Premium Rajshahi Lichi is hand-picked at the peak of ripeness from trusted orchards in the Rajshahi region, famous across Bangladesh for the sweetest, juiciest lichi.</p><p>Each fruit is carefully selected, gently packed and delivered fresh to your door within hours of harvest.</p>', 350.00, 400.00, 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80","https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80"]', 150, 320, 4.80, 214, 1, 1),
(2, 1, 'Fresh Lichi Family Pack', 'fresh-lichi-family-pack', 'A generous family pack of fresh, sweet lichi for every day of the season.', '<p>Enjoy a bountiful supply of fresh lichi with the Family Pack. Perfect for sharing at home, this pack brings the taste of the season straight from the orchard.</p>', 900.00, 1000.00, 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80"]', 80, 150, 4.60, 98, 0, 1),
(3, 2, 'Premium Lichi Box', 'premium-lichi-box', 'A premium presentation box of hand-selected, best-grade lichi.', '<p>The Premium Lichi Box is our best-selling gift presentation. Only the finest fruits make it into this box, packed with care and ready to impress.</p>', 1500.00, 1700.00, 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80"]', 60, 210, 4.90, 187, 1, 1),
(4, 2, 'Lichi Lover Special Box', 'lichi-lover-special-box', 'Our biggest, most special lichi box for celebrations and large families.', '<p>Make any celebration special with the Lichi Lover Special Box. A generous selection of premium lichi, beautifully arranged and delivered with love.</p>', 2800.00, 3200.00, 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80"]', 40, 95, 4.70, 76, 1, 1),
(5, 1, 'Fresh Lichi Mini Pack', 'fresh-lichi-mini-pack', 'A perfect small pack to try the taste of fresh Rajshahi lichi.', '<p>Just right for a first taste or a small treat, the Mini Pack delivers the same orchard-fresh quality in a smaller portion.</p>', 180.00, 200.00, 'https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80"]', 200, 180, 4.50, 63, 0, 1),
(6, 1, 'Handpicked Lichi Bunch', 'handpicked-lichi-bunch', 'Lichi still on the bunch for maximum freshness and appeal.', '<p>Experience lichi as it is picked — still attached to the bunch. Handpicked for freshness, ideal for those who want the very best of the season.</p>', 380.00, 420.00, 'https://images.unsplash.com/photo-1528821128474-27f963b062bf?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1528821128474-27f963b062bf?auto=format&fit=crop&w=900&q=80"]', 90, 120, 4.70, 84, 0, 1),
(7, 2, 'Lichi Gift Hamper', 'lichi-gift-hamper', 'A thoughtful gift hamper filled with premium lichi and a greeting note.', '<p>Send love with our Lichi Gift Hamper — premium lichi beautifully arranged in a keepsake box with a hand-written greeting card. Perfect for Eid, birthdays and more.</p>', 1100.00, 1300.00, 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=900&q=80"]', 45, 70, 4.80, 52, 0, 1),
(8, 3, 'Lichi Combo (Premium + Family)', 'lichi-combo-premium-family', 'Two best-sellers in one — Premium and Family packs at a great price.', '<p>The perfect combo for big families: enjoy our Premium Rajshahi Lichi together with the Family Pack at a special combo price.</p>', 2400.00, 2500.00, 'https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?auto=format&fit=crop&w=900&q=80"]', 35, 45, 4.90, 31, 0, 1),
(9, 1, 'Premium Grade A Lichi', 'premium-grade-a-lichi', 'Top-grade, extra-large lichi selected fruit by fruit.', '<p>Our Grade A selection is the cream of the harvest — extra-large, perfectly ripe and irresistibly sweet. Reserved for the most discerning lichi lovers.</p>', 700.00, 750.00, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=80"]', 75, 130, 4.90, 141, 1, 1),
(10, 3, 'Lichi Lover Family Bundle', 'lichi-lover-family-bundle', 'A value bundle of fresh lichi for the whole season.', '<p>Stock up for the season with the Family Bundle — a smart value selection that keeps the whole family enjoying fresh lichi for longer.</p>', 1700.00, 1900.00, 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80', '["https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80"]', 55, 88, 4.60, 59, 0, 1);

-- ---------------------------------------------------------------------
-- Product variants (1 KG / 2 KG / 5 KG etc.)
-- ---------------------------------------------------------------------
INSERT INTO `product_variants` (`id`, `product_id`, `name`, `weight`, `price`, `compare_price`, `stock_qty`, `is_default`, `is_active`) VALUES
(1,  1, '1 KG', 1.00, 350.00, 400.00, 60, 1, 1),
(2,  1, '2 KG', 2.00, 650.00, 720.00, 50, 0, 1),
(3,  1, '5 KG', 5.00, 1500.00, 1700.00, 40, 0, 1),
(4,  2, '3 KG', 3.00, 900.00, 1000.00, 45, 1, 1),
(5,  3, '5 KG', 5.00, 1500.00, 1700.00, 60, 1, 1),
(6,  4, '10 KG', 10.00, 2800.00, 3200.00, 40, 1, 1),
(7,  5, '500 GM', 0.50, 180.00, 200.00, 200, 1, 1),
(8,  6, '1 KG', 1.00, 380.00, 420.00, 90, 1, 1),
(9,  7, '3 KG', 3.00, 1100.00, 1300.00, 45, 1, 1),
(10, 8, '8 KG', 8.00, 2400.00, 2500.00, 35, 1, 1),
(11, 9, '2 KG', 2.00, 700.00, 750.00, 75, 1, 1),
(12, 10, '6 KG', 6.00, 1700.00, 1900.00, 55, 1, 1);

-- ---------------------------------------------------------------------
-- Delivery zones — all 64 districts of Bangladesh.
-- Demo fees: Chattogram ৳60, Dhaka ৳100, everything else ৳120.
-- Admin can change these from Settings → Delivery.
-- ---------------------------------------------------------------------
INSERT INTO `delivery_zones` (`district`, `division`, `delivery_fee`, `free_delivery_threshold`, `is_active`, `sort_order`) VALUES
('Chattogram', 'Chattogram', 60.00, 0.00, 1, 1),
('Cox''s Bazar', 'Chattogram', 120.00, 0.00, 1, 2),
('Cumilla', 'Chattogram', 120.00, 0.00, 1, 3),
('Brahmanbaria', 'Chattogram', 120.00, 0.00, 1, 4),
('Chandpur', 'Chattogram', 120.00, 0.00, 1, 5),
('Lakshmipur', 'Chattogram', 120.00, 0.00, 1, 6),
('Noakhali', 'Chattogram', 120.00, 0.00, 1, 7),
('Feni', 'Chattogram', 120.00, 0.00, 1, 8),
('Bandarban', 'Chattogram', 150.00, 0.00, 1, 9),
('Khagrachhari', 'Chattogram', 150.00, 0.00, 1, 10),
('Rangamati', 'Chattogram', 150.00, 0.00, 1, 11),
('Dhaka', 'Dhaka', 100.00, 0.00, 1, 12),
('Gazipur', 'Dhaka', 100.00, 0.00, 1, 13),
('Narayanganj', 'Dhaka', 100.00, 0.00, 1, 14),
('Tangail', 'Dhaka', 120.00, 0.00, 1, 15),
('Faridpur', 'Dhaka', 120.00, 0.00, 1, 16),
('Gopalganj', 'Dhaka', 120.00, 0.00, 1, 17),
('Kishoreganj', 'Dhaka', 120.00, 0.00, 1, 18),
('Madaripur', 'Dhaka', 120.00, 0.00, 1, 19),
('Manikganj', 'Dhaka', 120.00, 0.00, 1, 20),
('Munshiganj', 'Dhaka', 120.00, 0.00, 1, 21),
('Narsingdi', 'Dhaka', 120.00, 0.00, 1, 22),
('Rajbari', 'Dhaka', 120.00, 0.00, 1, 23),
('Shariatpur', 'Dhaka', 120.00, 0.00, 1, 24),
('Sylhet', 'Sylhet', 120.00, 0.00, 1, 25),
('Moulvibazar', 'Sylhet', 120.00, 0.00, 1, 26),
('Habiganj', 'Sylhet', 120.00, 0.00, 1, 27),
('Sunamganj', 'Sylhet', 120.00, 0.00, 1, 28),
('Rajshahi', 'Rajshahi', 120.00, 0.00, 1, 29),
('Pabna', 'Rajshahi', 120.00, 0.00, 1, 30),
('Sirajganj', 'Rajshahi', 120.00, 0.00, 1, 31),
('Bogura', 'Rajshahi', 120.00, 0.00, 1, 32),
('Joypurhat', 'Rajshahi', 120.00, 0.00, 1, 33),
('Naogaon', 'Rajshahi', 120.00, 0.00, 1, 34),
('Natore', 'Rajshahi', 120.00, 0.00, 1, 35),
('Chapainawabganj', 'Rajshahi', 120.00, 0.00, 1, 36),
('Khulna', 'Khulna', 120.00, 0.00, 1, 37),
('Jashore', 'Khulna', 120.00, 0.00, 1, 38),
('Satkhira', 'Khulna', 120.00, 0.00, 1, 39),
('Bagerhat', 'Khulna', 120.00, 0.00, 1, 40),
('Chuadanga', 'Khulna', 120.00, 0.00, 1, 41),
('Jhenaidah', 'Khulna', 120.00, 0.00, 1, 42),
('Kushtia', 'Khulna', 120.00, 0.00, 1, 43),
('Magura', 'Khulna', 120.00, 0.00, 1, 44),
('Meherpur', 'Khulna', 120.00, 0.00, 1, 45),
('Narail', 'Khulna', 120.00, 0.00, 1, 46),
('Barishal', 'Barishal', 120.00, 0.00, 1, 47),
('Bhola', 'Barishal', 120.00, 0.00, 1, 48),
('Patuakhali', 'Barishal', 120.00, 0.00, 1, 49),
('Barguna', 'Barishal', 120.00, 0.00, 1, 50),
('Pirojpur', 'Barishal', 120.00, 0.00, 1, 51),
('Jhalokati', 'Barishal', 120.00, 0.00, 1, 52),
('Rangpur', 'Rangpur', 120.00, 0.00, 1, 53),
('Dinajpur', 'Rangpur', 120.00, 0.00, 1, 54),
('Kurigram', 'Rangpur', 120.00, 0.00, 1, 55),
('Gaibandha', 'Rangpur', 120.00, 0.00, 1, 56),
('Lalmonirhat', 'Rangpur', 120.00, 0.00, 1, 57),
('Nilphamari', 'Rangpur', 120.00, 0.00, 1, 58),
('Panchagarh', 'Rangpur', 120.00, 0.00, 1, 59),
('Thakurgaon', 'Rangpur', 120.00, 0.00, 1, 60),
('Mymensingh', 'Mymensingh', 120.00, 0.00, 1, 61),
('Jamalpur', 'Mymensingh', 120.00, 0.00, 1, 62),
('Netrokona', 'Mymensingh', 120.00, 0.00, 1, 63),
('Sherpur', 'Mymensingh', 120.00, 0.00, 1, 64),
('Other Districts', NULL, 120.00, 0.00, 1, 99);

-- ---------------------------------------------------------------------
-- Coupons (demo)
-- ---------------------------------------------------------------------
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_order`, `max_discount`, `expires_at`, `usage_limit`, `used_count`, `is_active`) VALUES
('WELCOME10', 'percent', 10.00, 500.00, 200.00, '2030-12-31', 1000, 0, 1),
('SAVE50', 'fixed', 50.00, 1000.00, NULL, '2030-12-31', 500, 0, 1),
('FRESH15', 'percent', 15.00, 2000.00, 500.00, '2030-12-31', 300, 0, 1);

-- ---------------------------------------------------------------------
-- Demo accounts — DEVELOPMENT ONLY
-- ---------------------------------------------------------------------
INSERT INTO `admins` (`name`, `email`, `phone`, `password`, `role`, `must_change_password`, `is_active`) VALUES
('Lichi Lover Admin', 'admin@lichilover.com', '01700000000', '$2y$10$0JNgWEGpPE1IO.BBzJp94.w4LU1yO.J1nDhz3njIjLqUfykzGuJK.', 'super_admin', 1, 1);

INSERT INTO `users` (`name`, `email`, `phone`, `password`, `is_active`) VALUES
('Demo Customer', 'customer@lichilover.com', '01712345678', '$2y$10$zUCNG36VrhryvkViLoCrPOGYzUlTc0J9d7ZOsTzZdkgtnpUzUWUAa', 1),
('Rahim Uddin', 'rahim@example.com', '01812345678', '$2y$10$zUCNG36VrhryvkViLoCrPOGYzUlTc0J9d7ZOsTzZdkgtnpUzUWUAa', 1);

-- ---------------------------------------------------------------------
-- Demo reviews (clearly marked is_demo=1 — sample data only)
-- ---------------------------------------------------------------------
INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `review`, `status`, `is_demo`) VALUES
(1, 1, 5, 'Sample review: The Rajshahi lichi was incredibly sweet and juicy. Arrived fresh the next day!', 'approved', 1),
(1, 2, 4, 'Sample review: Very fresh and well packed. Delivery took a day but quality was excellent.', 'approved', 1),
(3, 1, 5, 'Sample review: Beautiful box, perfect for gifting. Fruits were top quality.', 'approved', 1),
(2, 2, 4, 'Sample review: Family pack was great value. Everyone loved it.', 'approved', 1),
(9, 1, 5, 'Sample review: The Grade A lichi are the best I have ever tasted. Highly recommended.', 'approved', 1);

-- ---------------------------------------------------------------------
-- Settings
-- ---------------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('shop_name', 'Lichi Lover'),
('shop_tagline', 'Freshness You Can Taste.'),
('announcement', 'Fresh Lichi Delivered to Your Door 🍒'),
('shop_email', 'hello@lichilover.example'),
('shop_phone', ''),
('shop_address', ''),
('whatsapp_number', ''),
('facebook_url', ''),
('instagram_url', ''),
('hero_headline', 'Fresh Lichi, Straight to Your Door'),
('hero_subheadline', 'Enjoy naturally sweet, juicy and freshly selected lichi delivered across Bangladesh.'),
('free_delivery_threshold', '0'),
('about_text', 'Lichi Lover is a fresh fruit e-commerce brand focused on bringing carefully selected lichi to customers with convenient online ordering and doorstep delivery.');
