<?php
declare(strict_types=1);

/**
 * Centralized image configuration.
 * All imagery uses real lichi (lychee) photos hotlinked from free Pexels and
 * Unsplash CDNs. Alt text is provided per image.
 *
 * NOTE: URLs point to images.pexels.com / images.unsplash.com and are
 * hotlinked at runtime. To cache them locally instead, download into
 * assets/images/ and change the URL here.
 */

$images = [
    // Brand / heroes
    'hero' => [
        'url' => 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=1600',
        'alt' => 'Close-up of a bunch of fresh red lichi fruits',
    ],
    'annual_banner' => [
        'url' => 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=1400&q=80',
        'alt' => 'A pile of fresh red lichi fruits on a table',
    ],
    'farm' => [
        'url' => 'https://images.pexels.com/photos/32530089/pexels-photo-32530089.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'alt' => 'Farmers harvesting ripe lichi fruits in an orchard',
    ],
    'farmer' => [
        'url' => 'https://images.pexels.com/photos/32799486/pexels-photo-32799486.jpeg?auto=compress&cs=tinysrgb&w=900',
        'alt' => 'Aerial view of a lichi farmer in the orchard',
    ],
    'packaging' => [
        'url' => 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900',
        'alt' => 'Heap of fresh ripe lichi fruits',
    ],
    'delivery' => [
        'url' => 'https://images.pexels.com/photos/30540388/pexels-photo-30540388.jpeg?auto=compress&cs=tinysrgb&w=900',
        'alt' => 'Fresh lichi fruits hanging on the branch',
    ],
    'season' => [
        'url' => 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Fresh red lichi fruits with green leaves',
    ],
    'lt_gift' => [
        'url' => 'https://images.pexels.com/photos/15221058/pexels-photo-15221058.jpeg?auto=compress&cs=tinysrgb&w=900',
        'alt' => 'Red lichi fruits in close-up shot',
    ],
    'about_hero' => [
        'url' => 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=1400&q=80',
        'alt' => 'A pile of fresh red lichi fruits',
    ],
];

// Product image pool (real lichi photos, shared across seeded products).
$productImages = [
    'lychee_live' => 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900',
    'lychee_board' => 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900',
    'lychee_dry' => 'https://images.pexels.com/photos/15221058/pexels-photo-15221058.jpeg?auto=compress&cs=tinysrgb&w=900',
    'lychee_basket' => 'https://images.pexels.com/photos/30540388/pexels-photo-30540388.jpeg?auto=compress&cs=tinysrgb&w=900',
    'lychee_rambutan' => 'https://images.pexels.com/photos/16820482/pexels-photo-16820482.jpeg?auto=compress&cs=tinysrgb&w=900',
    'lychee_combo' => 'https://images.unsplash.com/photo-1642063412059-faa1c79531ce?auto=format&fit=crop&w=900&q=80',
    'fruit_mixed' => 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?auto=format&fit=crop&w=900&q=80',
    'fruit_table' => 'https://images.pexels.com/photos/32530089/pexels-photo-32530089.jpeg?auto=compress&cs=tinysrgb&w=900',
    'fruit_green' => 'https://images.pexels.com/photos/32799486/pexels-photo-32799486.jpeg?auto=compress&cs=tinysrgb&w=900',
    'fruit_pack' => 'https://images.pexels.com/photos/5097676/pexels-photo-5097676.jpeg?auto=compress&cs=tinysrgb&w=900',
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