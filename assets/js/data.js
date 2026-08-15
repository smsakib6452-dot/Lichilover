/* ============================================================
   Lichi Lover — Seed Data (converted from database/seed.sql)
   All store content is stored here and hydrated into
   localStorage on first load. Admin changes persist in
   localStorage and override these defaults.
   ============================================================ */

window.LL_SEED = {

  settings: {
    shop_name: 'Lichi Lover',
    shop_tagline: 'Freshness You Can Taste.',
    announcement: 'Fresh Lichi Delivered to Your Door 🍒',
    shop_email: 'hello@lichilover.example',
    shop_phone: '',
    shop_address: '',
    whatsapp_number: '',
    facebook_url: '',
    instagram_url: '',
    hero_headline: 'Fresh Lichi, Straight to Your Door',
    hero_subheadline: 'Enjoy naturally sweet, juicy and freshly selected lichi delivered across Bangladesh.',
    free_delivery_threshold: '0',
    about_text: 'Lichi Lover is a fresh fruit e-commerce brand focused on bringing carefully selected lichi to customers with convenient online ordering and doorstep delivery.'
  },

  categories: [
    { id: 1, name: 'Fresh Lichi', slug: 'fresh-lichi', description: 'Freshly harvested lichi selected from trusted farms.', image: 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', sort_order: 1, is_active: 1 },
    { id: 2, name: 'Boxes & Gift Packs', slug: 'boxes-gift-packs', description: 'Beautifully packed lichi boxes for family and gifting.', image: 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80', sort_order: 2, is_active: 1 },
    { id: 3, name: 'Combos & Deals', slug: 'combos-deals', description: 'Great value combos for the whole family.', image: 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80', sort_order: 3, is_active: 1 }
  ],

  products: [
    { id: 1, category_id: 1, name: 'Premium Rajshahi Lichi', slug: 'premium-rajshahi-lichi', short_description: 'Naturally sweet, juicy lichi harvested fresh from Rajshahi orchards.', description: '<p>Our Premium Rajshahi Lichi is hand-picked at the peak of ripeness from trusted orchards in the Rajshahi region, famous across Bangladesh for the sweetest, juiciest lichi.</p><p>Each fruit is carefully selected, gently packed and delivered fresh to your door within hours of harvest.</p>', base_price: 350.00, compare_price: 400.00, image: 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80'], stock_qty: 150, sold_count: 320, rating_avg: 4.80, rating_count: 214, is_featured: 1, is_active: 1 },
    { id: 2, category_id: 1, name: 'Fresh Lichi Family Pack', slug: 'fresh-lichi-family-pack', short_description: 'A generous family pack of fresh, sweet lichi for every day of the season.', description: '<p>Enjoy a bountiful supply of fresh lichi with the Family Pack. Perfect for sharing at home, this pack brings the taste of the season straight from the orchard.</p>', base_price: 900.00, compare_price: 1000.00, image: 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80'], stock_qty: 80, sold_count: 150, rating_avg: 4.60, rating_count: 98, is_featured: 0, is_active: 1 },
    { id: 3, category_id: 2, name: 'Premium Lichi Box', slug: 'premium-lichi-box', short_description: 'A premium presentation box of hand-selected, best-grade lichi.', description: '<p>The Premium Lichi Box is our best-selling gift presentation. Only the finest fruits make it into this box, packed with care and ready to impress.</p>', base_price: 1500.00, compare_price: 1700.00, image: 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80'], stock_qty: 60, sold_count: 210, rating_avg: 4.90, rating_count: 187, is_featured: 1, is_active: 1 },
    { id: 4, category_id: 2, name: 'Lichi Lover Special Box', slug: 'lichi-lover-special-box', short_description: 'Our biggest, most special lichi box for celebrations and large families.', description: '<p>Make any celebration special with the Lichi Lover Special Box. A generous selection of premium lichi, beautifully arranged and delivered with love.</p>', base_price: 2800.00, compare_price: 3200.00, image: 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80'], stock_qty: 40, sold_count: 95, rating_avg: 4.70, rating_count: 76, is_featured: 1, is_active: 1 },
    { id: 5, category_id: 1, name: 'Fresh Lichi Mini Pack', slug: 'fresh-lichi-mini-pack', short_description: 'A perfect small pack to try the taste of fresh Rajshahi lichi.', description: '<p>Just right for a first taste or a small treat, the Mini Pack delivers the same orchard-fresh quality in a smaller portion.</p>', base_price: 180.00, compare_price: 200.00, image: 'https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80'], stock_qty: 200, sold_count: 180, rating_avg: 4.50, rating_count: 63, is_featured: 0, is_active: 1 },
    { id: 6, category_id: 1, name: 'Handpicked Lichi Bunch', slug: 'handpicked-lichi-bunch', short_description: 'Lichi still on the bunch for maximum freshness and appeal.', description: '<p>Experience lichi as it is picked — still attached to the bunch. Handpicked for freshness, ideal for those who want the very best of the season.</p>', base_price: 380.00, compare_price: 420.00, image: 'https://images.unsplash.com/photo-1528821128474-27f963b062bf?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1528821128474-27f963b062bf?auto=format&fit=crop&w=900&q=80'], stock_qty: 90, sold_count: 120, rating_avg: 4.70, rating_count: 84, is_featured: 0, is_active: 1 },
    { id: 7, category_id: 2, name: 'Lichi Gift Hamper', slug: 'lichi-gift-hamper', short_description: 'A thoughtful gift hamper filled with premium lichi and a greeting note.', description: '<p>Send love with our Lichi Gift Hamper — premium lichi beautifully arranged in a keepsake box with a hand-written greeting card. Perfect for Eid, birthdays and more.</p>', base_price: 1100.00, compare_price: 1300.00, image: 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=900&q=80'], stock_qty: 45, sold_count: 70, rating_avg: 4.80, rating_count: 52, is_featured: 0, is_active: 1 },
    { id: 8, category_id: 3, name: 'Lichi Combo (Premium + Family)', slug: 'lichi-combo-premium-family', short_description: 'Two best-sellers in one — Premium and Family packs at a great price.', description: '<p>The perfect combo for big families: enjoy our Premium Rajshahi Lichi together with the Family Pack at a special combo price.</p>', base_price: 2400.00, compare_price: 2500.00, image: 'https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?auto=format&fit=crop&w=900&q=80'], stock_qty: 35, sold_count: 45, rating_avg: 4.90, rating_count: 31, is_featured: 0, is_active: 1 },
    { id: 9, category_id: 1, name: 'Premium Grade A Lichi', slug: 'premium-grade-a-lichi', short_description: 'Top-grade, extra-large lichi selected fruit by fruit.', description: '<p>Our Grade A selection is the cream of the harvest — extra-large, perfectly ripe and irresistibly sweet. Reserved for the most discerning lichi lovers.</p>', base_price: 700.00, compare_price: 750.00, image: 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=80'], stock_qty: 75, sold_count: 130, rating_avg: 4.90, rating_count: 141, is_featured: 1, is_active: 1 },
    { id: 10, category_id: 3, name: 'Lichi Lover Family Bundle', slug: 'lichi-lover-family-bundle', short_description: 'A value bundle of fresh lichi for the whole season.', description: '<p>Stock up for the season with the Family Bundle — a smart value selection that keeps the whole family enjoying fresh lichi for longer.</p>', base_price: 1700.00, compare_price: 1900.00, image: 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80', gallery: ['https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80'], stock_qty: 55, sold_count: 88, rating_avg: 4.60, rating_count: 59, is_featured: 0, is_active: 1 }
  ],

  variants: [
    { id: 1, product_id: 1, name: '1 KG', weight: 1.00, price: 350.00, compare_price: 400.00, stock_qty: 60, is_default: 1, is_active: 1 },
    { id: 2, product_id: 1, name: '2 KG', weight: 2.00, price: 650.00, compare_price: 720.00, stock_qty: 50, is_default: 0, is_active: 1 },
    { id: 3, product_id: 1, name: '5 KG', weight: 5.00, price: 1500.00, compare_price: 1700.00, stock_qty: 40, is_default: 0, is_active: 1 },
    { id: 4, product_id: 2, name: '3 KG', weight: 3.00, price: 900.00, compare_price: 1000.00, stock_qty: 45, is_default: 1, is_active: 1 },
    { id: 5, product_id: 3, name: '5 KG', weight: 5.00, price: 1500.00, compare_price: 1700.00, stock_qty: 60, is_default: 1, is_active: 1 },
    { id: 6, product_id: 4, name: '10 KG', weight: 10.00, price: 2800.00, compare_price: 3200.00, stock_qty: 40, is_default: 1, is_active: 1 },
    { id: 7, product_id: 5, name: '500 GM', weight: 0.50, price: 180.00, compare_price: 200.00, stock_qty: 200, is_default: 1, is_active: 1 },
    { id: 8, product_id: 6, name: '1 KG', weight: 1.00, price: 380.00, compare_price: 420.00, stock_qty: 90, is_default: 1, is_active: 1 },
    { id: 9, product_id: 7, name: '3 KG', weight: 3.00, price: 1100.00, compare_price: 1300.00, stock_qty: 45, is_default: 1, is_active: 1 },
    { id: 10, product_id: 8, name: '8 KG', weight: 8.00, price: 2400.00, compare_price: 2500.00, stock_qty: 35, is_default: 1, is_active: 1 },
    { id: 11, product_id: 9, name: '2 KG', weight: 2.00, price: 700.00, compare_price: 750.00, stock_qty: 75, is_default: 1, is_active: 1 },
    { id: 12, product_id: 10, name: '6 KG', weight: 6.00, price: 1700.00, compare_price: 1900.00, stock_qty: 55, is_default: 1, is_active: 1 }
  ],

  zones: [
    { id: 1, district: 'Chattogram', division: 'Chattogram', delivery_fee: 60.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 1 },
    { id: 2, district: "Cox's Bazar", division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 2 },
    { id: 3, district: 'Cumilla', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 3 },
    { id: 4, district: 'Brahmanbaria', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 4 },
    { id: 5, district: 'Chandpur', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 5 },
    { id: 6, district: 'Lakshmipur', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 6 },
    { id: 7, district: 'Noakhali', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 7 },
    { id: 8, district: 'Feni', division: 'Chattogram', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 8 },
    { id: 9, district: 'Bandarban', division: 'Chattogram', delivery_fee: 150.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 9 },
    { id: 10, district: 'Khagrachhari', division: 'Chattogram', delivery_fee: 150.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 10 },
    { id: 11, district: 'Rangamati', division: 'Chattogram', delivery_fee: 150.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 11 },
    { id: 12, district: 'Dhaka', division: 'Dhaka', delivery_fee: 100.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 12 },
    { id: 13, district: 'Gazipur', division: 'Dhaka', delivery_fee: 100.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 13 },
    { id: 14, district: 'Narayanganj', division: 'Dhaka', delivery_fee: 100.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 14 },
    { id: 15, district: 'Tangail', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 15 },
    { id: 16, district: 'Faridpur', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 16 },
    { id: 17, district: 'Gopalganj', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 17 },
    { id: 18, district: 'Kishoreganj', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 18 },
    { id: 19, district: 'Madaripur', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 19 },
    { id: 20, district: 'Manikganj', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 20 },
    { id: 21, district: 'Munshiganj', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 21 },
    { id: 22, district: 'Narsingdi', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 22 },
    { id: 23, district: 'Rajbari', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 23 },
    { id: 24, district: 'Shariatpur', division: 'Dhaka', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 24 },
    { id: 25, district: 'Sylhet', division: 'Sylhet', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 25 },
    { id: 26, district: 'Moulvibazar', division: 'Sylhet', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 26 },
    { id: 27, district: 'Habiganj', division: 'Sylhet', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 27 },
    { id: 28, district: 'Sunamganj', division: 'Sylhet', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 28 },
    { id: 29, district: 'Rajshahi', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 29 },
    { id: 30, district: 'Pabna', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 30 },
    { id: 31, district: 'Sirajganj', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 31 },
    { id: 32, district: 'Bogura', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 32 },
    { id: 33, district: 'Joypurhat', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 33 },
    { id: 34, district: 'Naogaon', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 34 },
    { id: 35, district: 'Natore', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 35 },
    { id: 36, district: 'Chapainawabganj', division: 'Rajshahi', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 36 },
    { id: 37, district: 'Khulna', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 37 },
    { id: 38, district: 'Jashore', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 38 },
    { id: 39, district: 'Satkhira', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 39 },
    { id: 40, district: 'Bagerhat', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 40 },
    { id: 41, district: 'Chuadanga', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 41 },
    { id: 42, district: 'Jhenaidah', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 42 },
    { id: 43, district: 'Kushtia', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 43 },
    { id: 44, district: 'Magura', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 44 },
    { id: 45, district: 'Meherpur', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 45 },
    { id: 46, district: 'Narail', division: 'Khulna', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 46 },
    { id: 47, district: 'Barishal', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 47 },
    { id: 48, district: 'Bhola', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 48 },
    { id: 49, district: 'Patuakhali', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 49 },
    { id: 50, district: 'Barguna', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 50 },
    { id: 51, district: 'Pirojpur', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 51 },
    { id: 52, district: 'Jhalokati', division: 'Barishal', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 52 },
    { id: 53, district: 'Rangpur', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 53 },
    { id: 54, district: 'Dinajpur', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 54 },
    { id: 55, district: 'Kurigram', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 55 },
    { id: 56, district: 'Gaibandha', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 56 },
    { id: 57, district: 'Lalmonirhat', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 57 },
    { id: 58, district: 'Nilphamari', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 58 },
    { id: 59, district: 'Panchagarh', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 59 },
    { id: 60, district: 'Thakurgaon', division: 'Rangpur', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 60 },
    { id: 61, district: 'Mymensingh', division: 'Mymensingh', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 61 },
    { id: 62, district: 'Jamalpur', division: 'Mymensingh', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 62 },
    { id: 63, district: 'Netrokona', division: 'Mymensingh', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 63 },
    { id: 64, district: 'Sherpur', division: 'Mymensingh', delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 64 },
    { id: 65, district: 'Other Districts', division: null, delivery_fee: 120.00, free_delivery_threshold: 0.00, is_active: 1, sort_order: 99 }
  ],

  coupons: [
    { id: 1, code: 'WELCOME10', discount_type: 'percent', discount_value: 10.00, min_order: 500.00, max_discount: 200.00, expires_at: '2030-12-31', usage_limit: 1000, used_count: 1, is_active: 1 },
    { id: 2, code: 'SAVE50', discount_type: 'fixed', discount_value: 50.00, min_order: 1000.00, max_discount: null, expires_at: '2030-12-31', usage_limit: 500, used_count: 0, is_active: 1 },
    { id: 3, code: 'FRESH15', discount_type: 'percent', discount_value: 15.00, min_order: 2000.00, max_discount: 500.00, expires_at: '2030-12-31', usage_limit: 300, used_count: 0, is_active: 1 }
  ],

  // DEMO ACCOUNTS — DEVELOPMENT ONLY (passwords stored in plain text for the static demo)
  admins: [
    { id: 1, name: 'Lichi Lover Admin', email: 'admin@lichilover.com', phone: '01700000000', password: 'admin123', role: 'super_admin', must_change_password: 1, is_active: 1 }
  ],

  users: [
    { id: 1, name: 'Demo Customer', email: 'customer@lichilover.com', phone: '01712345678', password: 'customer123', is_active: 1 },
    { id: 2, name: 'Rahim Uddin', email: 'rahim@example.com', phone: '01812345678', password: 'customer123', is_active: 1 }
  ],

  // DEMO ORDERS — so the admin panel shows orders from the demo customer in
  // any browser. `_days_ago` and missing order_number/created_at are filled
  // in by store.js at seed time (order_number = LL-<year>-00000X, created_at
  // spread over the last week so the dashboard chart has data).
  orders: [
    {
      id: 1,
      user_id: 1,
      full_name: 'Demo Customer',
      phone: '01712345678',
      email: 'customer@lichilover.com',
      division: 'Chattogram',
      district: 'Chattogram',
      upazila: 'Panchlaish',
      address: 'House 12, Road 5, Block C, Panchlaish',
      delivery_note: 'Please call me when the delivery rider is near.',
      delivery_zone_id: 1,
      subtotal: 1300.00,
      delivery_fee: 60.00,
      discount: 0,
      coupon_code: null,
      total: 1360.00,
      status: 'delivered',
      payment_method: 'cod',
      payment_status: 'paid',
      items: [
        { variant_id: 2, product_id: 1, product_name: 'Premium Rajshahi Lichi', slug: 'premium-rajshahi-lichi', image: 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', variant_name: '2 KG', weight: 2.00, unit_price: 650.00, compare_price: 720.00, quantity: 2, line_total: 1300.00 }
      ],
      payment: null,
      _days_ago: 6
    },
    {
      id: 2,
      user_id: 1,
      full_name: 'Demo Customer',
      phone: '01712345678',
      email: 'customer@lichilover.com',
      division: 'Dhaka',
      district: 'Dhaka',
      upazila: 'Dhanmondi',
      address: 'Apartment 4B, House 23, Road 7, Dhanmondi',
      delivery_note: '',
      delivery_zone_id: 12,
      subtotal: 1500.00,
      delivery_fee: 100.00,
      discount: 150.00,
      coupon_code: 'WELCOME10',
      total: 1450.00,
      status: 'processing',
      payment_method: 'cod',
      payment_status: 'pending',
      items: [
        { variant_id: 5, product_id: 3, product_name: 'Premium Lichi Box', slug: 'premium-lichi-box', image: 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80', variant_name: '5 KG', weight: 5.00, unit_price: 1500.00, compare_price: 1700.00, quantity: 1, line_total: 1500.00 }
      ],
      payment: null,
      _days_ago: 3
    },
    {
      id: 3,
      user_id: 2,
      full_name: 'Rahim Uddin',
      phone: '01812345678',
      email: 'rahim@example.com',
      division: 'Chattogram',
      district: "Cox's Bazar",
      upazila: "Cox's Bazar Sadar",
      address: 'House 3, Kolatoli Road',
      delivery_note: 'Leave the parcel at the hotel reception.',
      delivery_zone_id: 2,
      subtotal: 540.00,
      delivery_fee: 120.00,
      discount: 0,
      coupon_code: null,
      total: 660.00,
      status: 'confirmed',
      payment_method: 'bkash',
      payment_status: 'paid',
      items: [
        { variant_id: 7, product_id: 5, product_name: 'Fresh Lichi Mini Pack', slug: 'fresh-lichi-mini-pack', image: 'https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80', variant_name: '500 GM', weight: 0.50, unit_price: 180.00, compare_price: 200.00, quantity: 3, line_total: 540.00 }
      ],
      payment: {
        method: 'bkash',
        payment_id: 'DEMO-BKASH-LL-000003',
        transaction_id: '8G7F2H9J1K',
        amount: 660.00,
        status: 'paid',
        gateway_response: { status: 'paid', transaction_id: '8G7F2H9J1K', demo: true, method: 'bkash' }
      },
      _days_ago: 1
    }
  ],

  reviews: [
    { id: 1, product_id: 1, user_id: 1, rating: 5, review: 'Sample review: The Rajshahi lichi was incredibly sweet and juicy. Arrived fresh the next day!', status: 'approved', is_demo: 1 },
    { id: 2, product_id: 1, user_id: 2, rating: 4, review: 'Sample review: Very fresh and well packed. Delivery took a day but quality was excellent.', status: 'approved', is_demo: 1 },
    { id: 3, product_id: 3, user_id: 1, rating: 5, review: 'Sample review: Beautiful box, perfect for gifting. Fruits were top quality.', status: 'approved', is_demo: 1 },
    { id: 4, product_id: 2, user_id: 2, rating: 4, review: 'Sample review: Family pack was great value. Everyone loved it.', status: 'approved', is_demo: 1 },
    { id: 5, product_id: 9, user_id: 1, rating: 5, review: 'Sample review: The Grade A lichi are the best I have ever tasted. Highly recommended.', status: 'approved', is_demo: 1 }
  ],

  // Image pool (real lichi photos)
  images: {
    hero: { url: 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=1600', alt: 'Close-up of a bunch of fresh red lichi fruits' },
    annual_banner: { url: 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=1400&q=80', alt: 'A pile of fresh red lichi fruits on a table' },
    farm: { url: 'https://images.pexels.com/photos/32530089/pexels-photo-32530089.jpeg?auto=compress&cs=tinysrgb&w=1200', alt: 'Farmers harvesting ripe lichi fruits in an orchard' },
    farmer: { url: 'https://images.pexels.com/photos/32799486/pexels-photo-32799486.jpeg?auto=compress&cs=tinysrgb&w=900', alt: 'Aerial view of a lichi farmer in the orchard' },
    packaging: { url: 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900', alt: 'Heap of fresh ripe lichi fruits' },
    delivery: { url: 'https://images.pexels.com/photos/30540388/pexels-photo-30540388.jpeg?auto=compress&cs=tinysrgb&w=900', alt: 'Fresh lichi fruits hanging on the branch' },
    season: { url: 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80', alt: 'Fresh red lichi fruits with green leaves' },
    lt_gift: { url: 'https://images.pexels.com/photos/15221058/pexels-photo-15221058.jpeg?auto=compress&cs=tinysrgb&w=900', alt: 'Red lichi fruits in close-up shot' },
    about_hero: { url: 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=1400&q=80', alt: 'A pile of fresh red lichi fruits' },
    lychee_live: 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900',
    lychee_board: 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900',
    lychee_dry: 'https://images.pexels.com/photos/15221058/pexels-photo-15221058.jpeg?auto=compress&cs=tinysrgb&w=900',
    lychee_basket: 'https://images.pexels.com/photos/30540388/pexels-photo-30540388.jpeg?auto=compress&cs=tinysrgb&w=900',
    lychee_rambutan: 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900',
    lychee_combo: 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=900&q=80',
    fruit_mixed: 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80',
    fruit_table: 'https://images.pexels.com/photos/32530089/pexels-photo-32530089.jpeg?auto=compress&cs=tinysrgb&w=900',
    fruit_green: 'https://images.pexels.com/photos/32799486/pexels-photo-32799486.jpeg?auto=compress&cs=tinysrgb&w=900',
    fruit_pack: 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900'
  }
};
