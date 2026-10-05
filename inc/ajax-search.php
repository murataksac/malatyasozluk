<?php
/**
 * Malatya Sözlük - Canlı Arama (AJAX) Motoru
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Doğrudan erişimi engelle
}

add_action('wp_ajax_wiki_canli_arama', 'malatya_canli_arama_islevi');
add_action('wp_ajax_nopriv_wiki_canli_arama', 'malatya_canli_arama_islevi');

function malatya_canli_arama_islevi() {
    // Güvenlik: Nonce doğrulama
    check_ajax_referer('malatya_ajax_nonce', 'nonce');

    if ( empty($_POST['kelime']) ) {
        wp_send_json_error();
    }

    $aranan = sanitize_text_field(wp_unslash($_POST['kelime']));
    
    // Optimizasyon: no_found_rows => true ile veritabanı toplam satır hesabı pas geçilir (2x-3x daha hızlı)
    $sorgu = new WP_Query(array(
        's'                  => $aranan,
        'post_type'          => 'post',
        'post_status'        => 'publish',
        'posts_per_page'     => 6,
        'no_found_rows'      => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    if ($sorgu->have_posts()) {
        echo '<ul class="wiki-ajax-sonuclar">';
        while ($sorgu->have_posts()) {
            $sorgu->the_post();
            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<div class="wiki-ajax-yok">Bu kelimeyle ilgili madde bulunamadı. Belki de ilk sen yazmalısın!</div>';
    }
    wp_die();
}