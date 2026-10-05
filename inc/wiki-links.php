<?php
/**
 * Otomatik Wiki Link Dönüştürücü - Kararlı Sürüm (v3)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function malatya_sozluk_wiki_link_filtresi($content) {
    if ( is_admin() || ! is_main_query() ) { return $content; }

    return preg_replace_callback('/\[\[(.*?)\]\]/', function($matches) {
        // $matches[1] parantez içindeki ham metni alır
        $ham_metin = $matches[1];
        
        if (empty($ham_metin)) return $matches;

        // Piped link ayrıştırma (Madde|Metin)
        if (strpos($ham_metin, '|') !== false) {
            list($target, $label) = explode('|', $ham_metin, 2);
        } else {
            $target = $ham_metin;
            $label = $ham_metin;
        }

        $target = trim($target);
        $label = trim($label);

        // Maddenin varlığını kontrol et
        $sayfa = malatya_get_post_by_title($target, array('post', 'page'));

        if ($sayfa && $sayfa->post_status == 'publish') {
            $link = get_permalink($sayfa->ID);
            return '<a class="wiki-link wiki-mavi" href="' . esc_url($link) . '">' . esc_html($label) . '</a>';
        } else {
            $olusturma_linki = home_url('/ms/madde-olustur/?baslik=' . rawurlencode($target));
            return '<a class="wiki-link wiki-kirmizi" href="' . esc_url($olusturma_linki) . '" title="' . esc_attr($target) . ' maddesini oluştur">' . esc_html($label) . '</a>';
        }
    }, $content);
}

add_filter('the_content', 'malatya_sozluk_wiki_link_filtresi', 10);