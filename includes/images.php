<?php
declare(strict_types=1);

/**
 * Centralized image configuration.
 * All imagery uses free Unsplash photo URLs. Alt text is provided per image.
 * If a dedicated lichi photo were unavailable we use suitable tropical/fresh fruit imagery.
 *
 * NOTE: URLs point to images.unsplash.com and are hotlinked at runtime.
 * To cache them locally instead, download into assets/images/ and change the URL here.
 */

$images = [
    // Brand / heroes
    'hero' => [
        'url' => 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=1600&q=80',
        'alt' => 'Fresh red lychee fruits with green leaves',
    ],
    'annual_banner' => [
        'url' => 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=1400&q=80',
        'alt' => 'Fresh berries arranged in a basket',
    ],
    'farm' => [
        'url' => 'https://images.unsplash.com/photo-1620706857370-e1b9770e8bb1?auto=format&fit=crop&w=1200&q=80',
        'alt' => 'Fruit orchard in sunlight',
    ],
    'farmer' => [
        'url' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Farmer working in a green field',
    ],
    'packaging' => [
        'url' => 'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Fruit packed in clean boxes',
    ],
    'delivery' => [
        'url' => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Delivery box being handed over',
    ],
    'season' => [
        'url' => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Fresh fruit on a rustic table',
    ],
    'lt_gift' => [
        'url' => 'https://images.unsplash.com/photo-1517282009859-f000ec3b26fe?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Bright tropical fruits as a gift',
    ],
    'about_hero' => [
        'url' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1400&q=80',
        'alt' => 'Fresh red seasonal fruits',
    ],
];

// Product image pool (shared across seeded products so SQL imports are simple).
$productImages = [
    'lychee_live' => 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80',
    'lychee_board' => 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=80',
    'lychee_dry' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=900&q=80',
    'lychee_basket' => 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?auto=format&fit=crop&w=900&q=80',
    'lychee_rambutan' => 'https://images.unsplash.com/photo-1528821128474-27f963b062bf?auto=format&fit=crop&w=900&q=80',
    'lychee_combo' => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80',
    'fruit_mixed' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=900&q=80',
    'fruit_table' => 'https://images.unsplash.com/photo-1595855759920-86582396756a?auto=format&fit=crop&w=900&q=80',
    'fruit_green' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=80',
    'fruit_pack' => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=900&q=80',
];

/**
 * Get an image URL by key, falling back to a safe default.
 */
function image_url(string $key, string $defaultKey = 'hero'): string
{
    global $images, $productImages;
    $all = array_merge($images, $productImages);
    if (isset($all[$key]['url'])) {
        return $all[$key]['url'];
    }
    if (isset($all[$key])) {
        return is_string($all[$key]) ? $all[$key] : $all[$key]['url'];
    }
    if (isset($all[$defaultKey]['url'])) {
        return $all[$defaultKey]['url'];
    }
    if (isset($all[$defaultKey])) {
        return is_string($all[$defaultKey]) ? $all[$defaultKey] : $all[$defaultKey]['url'];
    }
    return 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=80';
}

/**
 * Get alt text for an image key.
 */
function image_alt(string $key): string
{
    global $images;
    return $images[$key]['alt'] ?? 'Lichi Lover fruit';
}