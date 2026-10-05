<?php
/*
Template Name: XML Sitemap
*/
// XML formatı olduğunu tarayıcıya bildir
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Yazıları (Maddeleri) çek
$posts = get_posts(array(
    'numberposts' => -1,
    'post_type'   => 'post',
    'post_status' => 'publish'
));

foreach ($posts as $post) {
    echo '<url>';
    echo '<loc>' . get_permalink($post->ID) . '</loc>';
    echo '<lastmod>' . get_the_modified_date('c', $post->ID) . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// Sayfaları çek
$pages = get_pages();
foreach ($pages as $page) {
    echo '<url>';
    echo '<loc>' . get_permalink($page->ID) . '</loc>';
    echo '<lastmod>' . get_the_modified_date('c', $page->ID) . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.5</priority>';
    echo '</url>';
}

echo '</urlset>';
?>