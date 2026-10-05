<?php
/**
 * Malatya SÃƒÂ¶zlÃƒÂ¼k TemasÃ„Â± FonksiyonlarÃ„Â±
 * Versiyon: 1.3.0 (Performans, Temizlik ve GÃƒÂ¼venlik GÃƒÂ¼ncellemesi)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // DoÃ„Å¸rudan eriÃ…Å¸im korumasÃ„Â±
}

// =========================================================================
// 1. TEMA DESTEKLERÃ„Â° VE TEMEL KURULUM
// =========================================================================
function malatya_sozluk_kurulum() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('automatic-feed-links');
    
    // Klasik EditÃƒÂ¶r (TinyMCE) iÃƒÂ§in ÃƒÂ¶zel stil desteÃ„Å¸i
    add_editor_style('assets/css/editor-style.css');
    
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 250,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    register_nav_menus(array(
        'ust-menu'    => __('ÃƒÅ“st Ana MenÃƒÂ¼', 'malatya-sozluk'),
        'sol-menu'    => __('Sol Navigasyon MenÃƒÂ¼sÃƒÂ¼', 'malatya-sozluk'),
        'footer_menu' => __('Alt MenÃƒÂ¼ (Footer)', 'malatya-sozluk')
    ));
}
add_action('after_setup_theme', 'malatya_sozluk_kurulum');

// =========================================================================
// 2. PERFORMANS VE SÃ„Â°TE TEMÃ„Â°ZLÃ„Â°K KANCALARI (KONSOLÃ„Â°DE EDÃ„Â°LMÃ„Â°Ã…Â)
// =========================================================================
// Emoji ve gereksiz head etiketlerini kaldÃ„Â±r
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
add_filter('wp_img_tag_add_auto_sizes_attribute', '__return_false');
add_filter('use_block_editor_for_post', '__return_false');
add_filter('show_admin_bar', '__return_false');

// Gutenberg ve gereksiz stil kÃƒÂ¼tÃƒÂ¼phanelerini tek elden temizle
function malatya_sozluk_stil_temizligi() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-block-style');
    wp_dequeue_style('classic-theme-styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}
add_action('wp_enqueue_scripts', 'malatya_sozluk_stil_temizligi', 100);

// =========================================================================
// 3. CSS VE JS DOSYALARINI YÃƒÅ“KLEME & SCRÃ„Â°PT LOKALÃ„Â°ZASYONU
// =========================================================================
function malatya_sozluk_dosyalari_yukle() {
    wp_enqueue_style('malatya-ana-stil', get_stylesheet_uri(), array(), '1.6.0');
    wp_enqueue_style('malatya-wiki-core', get_template_directory_uri() . '/assets/css/wiki-core.css', array(), '1.6.0');
    wp_enqueue_style('sayfa-ici-tasarim', get_template_directory_uri() . '/assets/css/sayfa-ici.css', array(), '1.6.0');
    
    // GiriÃ…Å¸ yapmÃ„Â±Ã…Å¸ kullanÃ„Â±cÃ„Â±lara veya Madde OluÃ…Å¸turma sayfasÃ„Â±nda GÃƒÂ¶rsel EditÃƒÂ¶r bileÃ…Å¸enlerini yÃƒÂ¼kle
    if ( (is_singular() && is_user_logged_in()) || is_page_template('template-madde-olustur.php') ) {
        wp_enqueue_editor();
        wp_enqueue_style('dashicons');
        wp_enqueue_style('editor-buttons');
    }

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

    wp_enqueue_script('malatya-wiki-interact', get_template_directory_uri() . '/assets/js/wiki-interact.js', array('jquery'), '1.6.0', true);
    
    // GÃƒÂ¼venlik ve AJAX URL bilgilerini JS'ye aktar
    wp_localize_script('malatya-wiki-interact', 'malatya_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('malatya_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'malatya_sozluk_dosyalari_yukle');

// =========================================================================
// 4. SIDEBAR VE BÃ„Â°LEÃ…ÂEN ALANLARI
// =========================================================================
function malatya_sozluk_bilesen_alanlari() {
    register_sidebar(array(
        'name'          => __('Sol Panel (Sidebar)', 'malatya-sozluk'),
        'id'            => 'sol-sidebar',
        'description'   => __('Buraya eklenen bileÃ…Å¸enler sol menÃƒÂ¼de gÃƒÂ¶rÃƒÂ¼nÃƒÂ¼r.', 'malatya-sozluk'),
        'before_widget' => '<section class="wiki-widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'malatya_sozluk_bilesen_alanlari');

// =========================================================================
// 5. INC KLASÃƒâ€“RÃƒÅ“NDEKÃ„Â° MODÃƒÅ“LLERÃ„Â° Ãƒâ€¡AÃ„ÂIRMA
// =========================================================================
require_once get_template_directory() . '/inc/wiki-links.php';
require_once get_template_directory() . '/inc/wiki-toc.php';
require_once get_template_directory() . '/inc/wiki-kaynakca.php';
require_once get_template_directory() . '/inc/ajax-search.php';
require_once get_template_directory() . '/inc/wiki-onay.php';
require_once get_template_directory() . '/inc/wiki-gecmis.php';
require_once get_template_directory() . '/inc/seo.php';

// YazÃ„Â± gÃƒÂ¼ncellendiÃ„Å¸inde veya silindiÃ„Å¸inde ana sayfa transient ÃƒÂ¶nbelleklerini temizle
function malatya_eksik_madde_onbellek_temizle($post_id) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    delete_transient('malatya_eksik_maddeler_anasayfa');
    delete_transient('malatya_eksik_maddeler_tam_liste');
    delete_transient('malatya_gunun_maddesi_id');
    delete_transient('malatya_portal_istatistikler');
    delete_transient('malatya_en_maddeler_raporu_v2');
    delete_transient('malatya_en_maddeler_raporu_v3');
    delete_transient('malatya_en_maddeler_raporu_v4');
    delete_transient('malatya_en_maddeler_raporu_v5');
}
add_action('save_post', 'malatya_eksik_madde_onbellek_temizle');
add_action('delete_post', 'malatya_eksik_madde_onbellek_temizle');
add_action('trash_post', 'malatya_eksik_madde_onbellek_temizle');

// =========================================================================
// 5.1. KULLANICI PROFÃ„Â°L URL YAPISI (/author/ -> /profil/), DOSYA/MEDYA & RASTGELE REWRITE
// =========================================================================
// WordPress 6.4+ Ek SayfalarÃ„Â±nÃ„Â± (Attachment Pages) AktifleÃ…Å¸tir
add_filter('wp_attachment_pages_enabled', '__return_true');

function malatya_profil_url_yapilandirmasi() {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'profil';

    // Profil SayfalarÃ„Â±
    add_rewrite_rule('^profil/([^/]+)/?$', 'index.php?author_name=$matches[1]', 'top');
    add_rewrite_rule('^profil/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&paged=$matches[2]', 'top');
    
    // Rastgele Madde
    add_rewrite_rule('^rastgele/?$', 'index.php?rastgele=1', 'top');

    // Dosya / Medya Ek SayfasÃ„Â± Wikipedia Stili KurallarÃ„Â± (/dosya/slug/ ve /medya/slug/)
    add_rewrite_rule('^dosya/([0-9]+)/?$', 'index.php?attachment_id=$matches[1]', 'top');
    add_rewrite_rule('^dosya/([^/]+)/?$', 'index.php?attachment=$matches[1]', 'top');
    add_rewrite_rule('^medya/([0-9]+)/?$', 'index.php?attachment_id=$matches[1]', 'top');
    add_rewrite_rule('^medya/([^/]+)/?$', 'index.php?attachment=$matches[1]', 'top');
}
add_action('init', 'malatya_profil_url_yapilandirmasi');

function malatya_ek_sayfalari_etkinlestir() {
    if (get_option('wp_attachment_pages_enabled') !== '1') {
        update_option('wp_attachment_pages_enabled', '1');
    }
    // WordPress Core'un ek sayfalarÃ„Â±nÃ„Â± ana yazÃ„Â±ya veya dosyaya yÃƒÂ¶nlendirmesini engelle
    remove_action('template_redirect', 'wp_redirect_attachment_to_post');
}
add_action('init', 'malatya_ek_sayfalari_etkinlestir');

add_filter('query_vars', function($vars) {
    $vars[] = 'rastgele';
    return $vars;
});

add_filter('author_link', function($link, $author_id, $author_nicename) {
    return home_url('/profil/' . $author_nicename . '/');
}, 10, 3);

// =========================================================================
// 6. HELPER: YARDIMCI BAÃ…ÂLIK Ã„Â°LE SAYFA/YAZI BULMA (Modern WP StandardÃ„Â±)
// =========================================================================
function malatya_get_post_by_title($title, $post_type = 'post') {
    $query = new WP_Query(array(
        'title'                  => $title,
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));
    return $query->have_posts() ? $query->posts[0] : null;
}

// =========================================================================
// 7. KLASÃ„Â°K DÃƒÅ“ZENLEYÃ„Â°CÃ„Â°YE (TINYMCE) KUSTOM BUTONLAR VE STÃ„Â°LLER
// =========================================================================
function malatya_sozluk_tinymce_eklentileri_kaydet($plugin_array) {
    $v = '1.4.0';
    $plugin_array['wiki_ref_butonu']       = get_template_directory_uri() . '/assets/js/wiki-kaynak-buton.js?v=' . $v;
    $plugin_array['malatya_bilgi_butonu']  = get_template_directory_uri() . '/assets/js/wiki-bilgi-butonu.js?v=' . $v;
    $plugin_array['malatya_kelime_butonu'] = get_template_directory_uri() . '/assets/js/wiki-kelime-butonu.js?v=' . $v;
    return $plugin_array;
}

function malatya_sozluk_tinymce_butonlari_kaydet($buttons) {
    array_push($buttons, '|', 'wiki_h2_butonu', 'wiki_h3_butonu', 'wiki_ref_butonu', 'wiki_link_butonu', 'wiki_piped_link', 'wiki_sablon_butonu', 'wiki_bilgi_kutusu', 'wiki_kelime_kutusu');
    return $buttons;
}

function malatya_sozluk_editor_kurulumu() {
    add_filter('mce_external_plugins', 'malatya_sozluk_tinymce_eklentileri_kaydet');
    add_filter('mce_buttons', 'malatya_sozluk_tinymce_butonlari_kaydet');
}
add_action('admin_init', 'malatya_sozluk_editor_kurulumu');
add_action('init', 'malatya_sozluk_editor_kurulumu');

// EditÃƒÂ¶r CSS dosyasÃ„Â±nÃ„Â± TinyMCE iframe iÃƒÂ§ine baÃ„Å¸la
add_filter('mce_css', function($mce_css) {
    if (!empty($mce_css)) {
        $mce_css .= ',';
    }
    $mce_css .= get_template_directory_uri() . '/assets/css/editor-style.css?v=1.4.0';
    return $mce_css;
});

// Admin genelinde ve blok editÃƒÂ¶rde [ref] etiketlerini gÃƒÂ¶rselleÃ…Å¸tir
function malatya_admin_editor_stilleri() {
    ?>
    <style>
    .wiki-editor-ref {
        display: inline !important;
        font-size: 0.88em !important;
        font-style: italic !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-radius: 3px !important;
        padding: 1px 4px !important;
        margin: 0 2px !important;
        border: none !important;
        box-shadow: none !important;
        line-height: inherit !important;
    }
    .wiki-editor-ref::before {
        content: none !important;
        display: none !important;
    }
    #qt_content_wiki_qt_ref {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
        font-weight: 600 !important;
    }
    #qt_content_wiki_qt_link {
        background: #f0fdf4 !important;
        color: #166534 !important;
        border-color: #bbf7d0 !important;
        font-weight: 600 !important;
    }
    </style>
    <?php
}
add_action('admin_head', 'malatya_admin_editor_stilleri');

// Metin (HTML) sekmesi iÃƒÂ§in HÃ„Â±zlÃ„Â± Etiket ButonlarÃ„Â± (Quicktags)
function malatya_sozluk_quicktags_ekle() {
    if (wp_script_is('quicktags')) {
        ?>
        <script type="text/javascript">
        if (typeof QTags !== 'undefined') {
            QTags.addButton('wiki_qt_ref', '[ref] Kaynak', '[ref]', '[/ref]', 'r', 'KaynakÃƒÂ§a [ref] etiketi ekle', 101);
            QTags.addButton('wiki_qt_link', '[[ Ã„Â°ÃƒÂ§ Link ]]', '[[', ']]', 'w', 'Ã„Â°ÃƒÂ§ Link [[Madde]] etiketi ekle', 102);
        }
        </script>
        <?php
    }
}
add_action('admin_print_footer_scripts', 'malatya_sozluk_quicktags_ekle');

// =========================================================================
// 8. KATEGORÃ„Â°, ETÃ„Â°KET VE ARÃ…ÂÃ„Â°VLERDE ALFABETÃ„Â°K SIRALAMA VE SAYFA LÃ„Â°MÃ„Â°TÃ„Â° (50 Madde)
// =========================================================================
function malatya_sozluk_arsiv_sorgu_ayarlari($query) {
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_category() || $query->is_tag() || $query->is_tax() || $query->is_archive()) {
            $query->set('posts_per_page', 50); // Sayfa baÃ…Å¸Ã„Â±na 50 madde
            $query->set('orderby', 'title');   // A'dan Z'ye alfabetik sÃ„Â±ra
            $query->set('order', 'ASC');
        }
    }
}
add_action('pre_get_posts', 'malatya_sozluk_arsiv_sorgu_ayarlari');

// =========================================================================
// 9. MADDE HIZLI DÃƒÅ“ZENLEME (QUICK EDIT) AJAX MOTORU
// =========================================================================
function malatya_hizli_duzenle_ajax_handler() {
    check_ajax_referer('malatya_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Bu iÃ…Å¸lemi yapmak iÃƒÂ§in lÃƒÂ¼tfen ÃƒÂ¶nce giriÃ…Å¸ yapÃ„Â±n.'));
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id || get_post_type($post_id) !== 'post') {
        wp_send_json_error(array('message' => 'GeÃƒÂ§ersiz madde IDsi.'));
    }

    $content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
    $summary = isset($_POST['summary']) ? sanitize_text_field($_POST['summary']) : '';

    if (empty(trim($content))) {
        wp_send_json_error(array('message' => 'Madde iÃƒÂ§eriÃ„Å¸i boÃ…Å¸ bÃ„Â±rakÃ„Â±lamaz.'));
    }

    // Yetki kontrolÃƒÂ¼ (YalnÃ„Â±zca YÃƒÂ¶neticiler doÃ„Å¸rudan yayÃ„Â±mlayabilir)
    if (current_user_can('manage_options')) {
        $update_data = array(
            'ID'           => $post_id,
            'post_content' => $content,
        );
        $updated_id = wp_update_post($update_data);

        if (is_wp_error($updated_id)) {
            wp_send_json_error(array('message' => 'GÃƒÂ¼ncelleme sÃ„Â±rasÃ„Â±nda bir hata oluÃ…Å¸tu: ' . $updated_id->get_error_message()));
        }

        // DeÃ„Å¸iÃ…Å¸iklik ÃƒÂ¶zeti ve yazar bilgisini kaydet
        if (!empty($summary)) {
            update_post_meta($post_id, '_wiki_son_duzenleme_ozeti', $summary);
        }
        update_post_meta($post_id, '_wiki_son_duzenleyen', get_current_user_id());

        // KatkÃ„Â±yÃ„Â± Madde GeÃƒÂ§miÃ…Å¸ine ve YÃƒÂ¶netici Profiline Kaydet
        if (function_exists('malatya_wiki_katki_kaydet')) {
            malatya_wiki_katki_kaydet($post_id, get_current_user_id(), 'HÃ„Â±zlÃ„Â± DÃƒÂ¼zenleme', $summary);
        }
        
        // Ãƒâ€“nbellekleri temizle
        clean_post_cache($post_id);
        delete_transient('malatya_en_maddeler_raporu_v2');

        wp_send_json_success(array(
            'message' => 'Madde baÃ…Å¸arÃ„Â±yla gÃƒÂ¼ncellendi ve doÃ„Å¸rudan yayÃ„Â±mlandÃ„Â±!',
            'direct'  => true
        ));
    } else {
        // YÃƒÂ¶netici olmayan kullanÃ„Â±cÃ„Â±lar iÃƒÂ§in: DÃƒÂ¼zenleme Ãƒâ€“nerisi olarak wiki_revizyon ÃƒÂ¶zel tipinde kaydet
        $current_user = wp_get_current_user();
        $author_name = $current_user->display_name ?: $current_user->user_login;
        $madde_basligi = get_the_title($post_id);

        $oneri_id = wp_insert_post(array(
            'post_title'   => $madde_basligi . ' (Ãƒâ€“neri: ' . esc_attr($author_name) . ')',
            'post_content' => $content,
            'post_status'  => 'pending', // YÃƒÂ¶netici onayÃ„Â± bekliyor
            'post_parent'  => $post_id,
            'post_author'  => get_current_user_id(),
            'post_type'    => 'wiki_revizyon'
        ));

        if ($oneri_id && !is_wp_error($oneri_id)) {
            if (!empty($summary)) {
                update_post_meta($oneri_id, '_wiki_duzenleme_ozeti', $summary);
            }
            update_post_meta($oneri_id, '_wiki_kaynak_madde_id', $post_id);
            update_post_meta($oneri_id, '_wiki_oneri_yapan_kullanici', $author_name);

            wp_send_json_success(array(
                'message' => 'DÃƒÂ¼zenleme ÃƒÂ¶neriniz baÃ…Å¸arÃ„Â±yla yÃƒÂ¶netici onayÃ„Â±na gÃƒÂ¶nderildi. YÃƒÂ¶netici onayladÃ„Â±Ã„Å¸Ã„Â±nda ana madde gÃƒÂ¼ncellenecektir. KatkÃ„Â±nÃ„Â±z iÃƒÂ§in teÃ…Å¸ekkÃƒÂ¼r ederiz!',
                'direct'  => false
            ));
        } else {
            wp_send_json_error(array('message' => 'Ãƒâ€“neri kaydedilirken bir sorun oluÃ…Å¸tu.'));
        }
    }
}
add_action('wp_ajax_malatya_hizli_duzenle', 'malatya_hizli_duzenle_ajax_handler');

// HÃ„Â±zlÃ„Â± Ãƒâ€“nizleme AJAX Handler
function malatya_hizli_onizleme_ajax_handler() {
    check_ajax_referer('malatya_ajax_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Oturum aÃƒÂ§manÃ„Â±z gerekiyor.'));
    }

    $raw_content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';

    // Filtrelerden geÃƒÂ§ir (Wiki Linkleri, KaynakÃƒÂ§a, Navbox Ã…Å¸ablonlarÃ„Â± vb.)
    $parsed_content = wpautop($raw_content);
    if (function_exists('malatya_sozluk_wiki_link_filtresi')) {
        $parsed_content = malatya_sozluk_wiki_link_filtresi($parsed_content);
    }
    if (function_exists('malatya_cift_suslu_sablon_motoru')) {
        $parsed_content = malatya_cift_suslu_sablon_motoru($parsed_content);
    }
    if (function_exists('malatya_sozluk_kaynakca_olusturucu')) {
        $parsed_content = malatya_sozluk_kaynakca_olusturucu($parsed_content);
    }
    $parsed_content = do_shortcode($parsed_content);

    wp_send_json_success(array('html' => $parsed_content));
}
add_action('wp_ajax_malatya_hizli_onizleme', 'malatya_hizli_onizleme_ajax_handler');

// =========================================================================
// 8. RASTGELE MADDE YÃƒâ€“NLENDÃ„Â°RME MOTORU (Ãƒâ€“NBELLEKSÃ„Â°Z)
// =========================================================================
function malatya_rastgele_madde_yonlendirme() {
    if ( isset($_GET['random']) || isset($_GET['rastgele']) || get_query_var('rastgele') ) {
        global $wpdb;
        $gecerli_id = is_singular('post') ? get_queried_object_id() : 0;

        $haric_sql = "";
        if ( $gecerli_id > 0 ) {
            $haric_sql = " AND ID != " . intval($gecerli_id);
        }

        // TÃƒÂ¼m yayÃ„Â±nlanmÃ„Â±Ã…Å¸ post ID'lerini ÃƒÂ§ek ve PHP'de array_rand ile rastgele seÃƒÂ§
        $post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' {$haric_sql}" );

        if ( ! empty($post_ids) ) {
            $random_key = array_rand($post_ids);
            $hedef_id   = $post_ids[$random_key];
            $hedef_url  = get_permalink($hedef_id);

            // TarayÃ„Â±cÃ„Â±nÃ„Â±n 302 yÃƒÂ¶nlendirmesini hafÃ„Â±zaya almasÃ„Â±nÃ„Â± (cache) kesin olarak engelle
            nocache_headers();
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Pragma: no-cache");
            header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

            wp_safe_redirect($hedef_url, 302);
            exit;
        } else {
            wp_safe_redirect(home_url('/'), 302);
            exit;
        }
    }
}
add_action('template_redirect', 'malatya_rastgele_madde_yonlendirme', 1);

// =========================================================================
// 9. GÃƒâ€“RSEL OPTÃ„Â°MÃ„Â°ZASYONU: TEK DOSYA, MAX 1200PX, DÃ„Â°Ã„ÂER KOPYALARI SÃ„Â°LME
// =========================================================================
// TÃƒÂ¼m otomatik ara boyut (thumbnail, medium, large vb.) ÃƒÂ¼retimini devre dÃ„Â±Ã…Å¸Ã„Â± bÃ„Â±rak
add_filter('intermediate_image_sizes', '__return_empty_array', 9999);
add_filter('intermediate_image_sizes_advanced', '__return_empty_array', 9999);
add_filter('fallback_intermediate_image_sizes', '__return_empty_array', 9999);
add_filter('big_image_size_threshold', '__return_false', 9999);

// YÃƒÂ¼klenen gÃƒÂ¶rseli maksimum 1200px geniÃ…Å¸lik/yÃƒÂ¼kseklikte tek dosya olarak kaydet
add_filter('wp_handle_upload', function($upload) {
    if ( empty($upload['file']) || empty($upload['type']) ) {
        return $upload;
    }

    if ( strpos($upload['type'], 'image') === false ) {
        return $upload;
    }

    $file_path = $upload['file'];
    $editor = wp_get_image_editor($file_path);

    if ( ! is_wp_error($editor) ) {
        $size = $editor->get_size();
        $max_w = 1200;
        $max_h = 1200;

        // 1200px'den bÃƒÂ¼yÃƒÂ¼kse orantÃ„Â±lÃ„Â± olarak kÃƒÂ¼ÃƒÂ§ÃƒÂ¼lt ve ÃƒÂ¼zerine kaydet
        if ( $size['width'] > $max_w || $size['height'] > $max_h ) {
            $editor->resize($max_w, $max_h, false);
            $editor->set_quality(85);
            $editor->save($file_path);
        }
    }

    return $upload;
}, 999);

// Ekstra oluÃ…Å¸turulan alt boyut dosyalarÃ„Â±nÃ„Â± diskten temizle ve tek dosya tut
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    if ( isset($metadata['sizes']) ) {
        $upload_dir = wp_upload_dir();
        $base_dir = isset($metadata['file']) ? dirname($upload_dir['basedir'] . '/' . $metadata['file']) : '';

        if ( ! empty($base_dir) && is_dir($base_dir) && ! empty($metadata['sizes']) ) {
            foreach ($metadata['sizes'] as $size_info) {
                if ( ! empty($size_info['file']) ) {
                    $sub_file = $base_dir . '/' . $size_info['file'];
                    if ( file_exists($sub_file) && is_file($sub_file) ) {
                        @unlink($sub_file);
                    }
                }
            }
        }
        $metadata['sizes'] = array();
    }
    return $metadata;
}, 999, 2);

add_action('init', function() {
    if (get_option('thumbnail_size_w') != 0) {
        update_option('thumbnail_size_w', 0);
        update_option('thumbnail_size_h', 0);
        update_option('medium_size_w', 0);
        update_option('medium_size_h', 0);
        update_option('large_size_w', 0);
        update_option('large_size_h', 0);
        update_option('medium_large_size_w', 0);
        update_option('medium_large_size_h', 0);
    }
});

// =========================================================================
// 10. Ã…ÂABLON {{Ã…Å¸ablon}} MOTORU
// =========================================================================
function malatya_cift_suslu_sablon_motoru($content) {
    if ( is_admin() ) return $content;

    return preg_replace_callback('/\{\{(.*?)\}\}/', function($matches) {
        $sablon_adi = trim($matches[1]); 
        $tam_kod = $matches[0];         

        $sayfa = malatya_get_post_by_title($sablon_adi, 'page');

        if ($sayfa && $sayfa->post_status === 'publish') {
            $sablon_icerigi = do_shortcode(wpautop($sayfa->post_content));
            
            if (function_exists('malatya_sozluk_wiki_link_filtresi')) {
                $sablon_icerigi = malatya_sozluk_wiki_link_filtresi($sablon_icerigi);
            }
            
            $sablon_basligi = get_the_title($sayfa->ID);
            if ( empty($sablon_basligi) ) {
                $sablon_basligi = $sablon_adi;
            }

            if ($sablon_adi === 'Anlam AyrÃ„Â±mÃ„Â±') {
                return '<div class="wiki-anlam-ayrimi-kapsayici">' . $sablon_icerigi . '</div>';
            }

            // Sadece giriÃ…Å¸ yapmÃ„Â±Ã…Å¸ kullanÃ„Â±cÃ„Â±lara sayfaya git & dÃƒÂ¼zenle ikonlarÃ„Â±nÃ„Â± gÃƒÂ¶ster (yazÃ„Â±sÃ„Â±z sadece ikon)
            $action_icons = '';
            if ( is_user_logged_in() ) {
                $sayfa_link   = get_permalink($sayfa->ID);
                $duzenle_link = current_user_can('edit_pages') ? admin_url('post.php?post=' . $sayfa->ID . '&action=edit') : $sayfa_link;

                $action_icons = '<span class="navbox-actions" style="font-size: 13px; font-weight: 700; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px 7px; display: inline-flex; gap: 6px; align-items: center; margin-right: 10px;" onclick="event.stopPropagation();">';
                $action_icons .= '<a href="' . esc_url($sayfa_link) . '" title="Ã…Âablon SayfasÃ„Â±na Git" style="color: #2563eb; text-decoration: none; font-size: 13px;">Ã¢â€ â€”</a>';
                $action_icons .= '<span style="color: #cbd5e1;">|</span>';
                $action_icons .= '<a href="' . esc_url($duzenle_link) . '" title="Ã…Âablonu DÃƒÂ¼zenle" style="color: #475569; text-decoration: none; font-size: 13px;">Ã¢Å“Â</a>';
                $action_icons .= '</span>';
            }

            $out = '<div class="wiki-navbox" style="border: 1px solid #cbd5e1; margin: 24px 0; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden;">';
            $out .= '<div class="navbox-header" style="background: #f1f5f9; padding: 10px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; user-select: none;" onclick="wikiNavboxAc(this, event)">';
            
            // Sol Alan: Ã„Â°konlar ve Ã…Âablon BaÃ…Å¸lÃ„Â±Ã„Å¸Ã„Â±
            $out .= '<div style="display: flex; align-items: center;">';
            $out .= $action_icons;
            $out .= '<span class="navbox-baslik" style="font-size: 14px; font-weight: 700; color: #0f172a;">' . esc_html($sablon_basligi) . '</span>';
            $out .= '</div>';
            
            // SaÃ„Å¸: AÃƒÂ§/Kapa Butonu
            $out .= '<span class="toggle-icon" style="font-size: 12px; color: #64748b; font-weight: 600;">Ã¢â€“Â² Gizle</span>';
            $out .= '</div>';
            
            // Ã„Â°ÃƒÂ§erik
            $out .= '<div class="navbox-content" style="display: block; padding: 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 13.5px; line-height: 1.6;">' . $sablon_icerigi . '</div>';
            $out .= '</div>';
            
            return $out;
        }

        return $tam_kod;
    }, $content);
}
add_filter('the_content', 'malatya_cift_suslu_sablon_motoru', 8);

// =========================================================================
// 11. EN Ãƒâ€¡OK BAÃ„ÂLANTI VERÃ„Â°LEN / Ãƒâ€“KSÃƒÅ“Z MADDELER RAPORU (TRANSIENT Ãƒâ€“NBELLEKLÃ„Â°)
// =========================================================================
function malatya_en_maddeler_raporu() {
    $cache_key = 'malatya_en_maddeler_raporu_v5';
    $output = get_transient($cache_key);

    if (false !== $output) {
        return $output;
    }

    $tum_maddeler = get_posts(array(
        'posts_per_page'         => -1,
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));
    
    $baglanti_skorlari = array();
    $madde_id_haritasi = array();
    $baslik_lower_map  = array();

    foreach ($tum_maddeler as $m) {
        $baslik = trim($m->post_title);
        $madde_id_haritasi[$baslik] = $m->ID;
        $baglanti_skorlari[$baslik] = 0;
        $baslik_lower_map[mb_strtolower($baslik, 'UTF-8')] = $baslik;
    }

    foreach ($tum_maddeler as $m) {
        preg_match_all('/\[\[(.*?)\]\]/', $m->post_content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $baglanan_raw) {
                $baglanan_raw = trim($baglanan_raw);
                // Piped link ayrÃ„Â±Ã…Å¸tÃ„Â±rma: [[Malatya|Malatya'da]] -> "Malatya"
                if (strpos($baglanan_raw, '|') !== false) {
                    $parts = explode('|', $baglanan_raw);
                    $baglanan_raw = trim($parts[0]);
                }

                $baglanan_lower = mb_strtolower($baglanan_raw, 'UTF-8');
                if (isset($baslik_lower_map[$baglanan_lower])) {
                    $gercek_baslik = $baslik_lower_map[$baglanan_lower];
                    $baglanti_skorlari[$gercek_baslik]++;
                }
            }
        }
    }

    arsort($baglanti_skorlari);
    $en_cok_baglanan = array_slice($baglanti_skorlari, 0, 10, true);
    $oksuzler = array_filter($baglanti_skorlari, function($skor) { return $skor === 0; });

    $output = '<div class="wiki-en-maddeler-raporu" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 24px 0;">';
    
    // 1. En Ãƒâ€¡ok BaÃ„Å¸lantÃ„Â± Alanlar
    $output .= '<div class="rapor-kolon" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04);">';
    $output .= '<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 14px;">';
    $output .= '<h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; border: none; padding: 0; display: flex; align-items: center; gap: 8px;"><span>Ã¢Â­Â</span> En Ãƒâ€¡ok BaÃ„Å¸lantÃ„Â± Verilenler</h3>';
    $output .= '<span style="font-size: 11.5px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 12px;">Merkezi Maddeler</span>';
    $output .= '</div>';
    $output .= '<p style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">DiÃ„Å¸er ansiklopedi maddelerinde en fazla atÃ„Â±fta bulunulan ana baÃ…Å¸lÃ„Â±klar:</p>';
    
    $output .= '<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">';
    $sira = 1;
    foreach ($en_cok_baglanan as $baslik => $sayi) {
        if ($sayi > 0 && isset($madde_id_haritasi[$baslik])) {
            $badge_style = 'background: #f1f5f9; color: #475569;';
            if ($sira === 1) $badge_style = 'background: #fef08a; color: #854d0e; font-weight: 800;';
            elseif ($sira === 2) $badge_style = 'background: #e2e8f0; color: #334155; font-weight: 800;';
            elseif ($sira === 3) $badge_style = 'background: #fed7aa; color: #9a3412; font-weight: 800;';

            $output .= '<li style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">';
            $output .= '<div style="display: flex; align-items: center; gap: 10px;">';
            $output .= '<span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; font-size: 11px; ' . $badge_style . '">#' . $sira . '</span>';
            $output .= '<a href="' . esc_url(get_permalink($madde_id_haritasi[$baslik])) . '" style="font-weight: 700; font-size: 13.5px; color: #0f172a; text-decoration: none;">' . esc_html($baslik) . '</a>';
            $output .= '</div>';
            $output .= '<span style="font-size: 11.5px; font-weight: 700; background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 12px;">' . intval($sayi) . ' baÃ„Å¸lantÃ„Â±</span>';
            $output .= '</li>';
            $sira++;
        }
    }
    $output .= '</ul></div>';

    // 2. Ãƒâ€“ksÃƒÂ¼z (BaÃ„Å¸lantÃ„Â±sÃ„Â±z) Maddeler
    $output .= '<div class="rapor-kolon" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04);">';
    $output .= '<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 14px;">';
    $output .= '<h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; border: none; padding: 0; display: flex; align-items: center; gap: 8px;"><span>Ã¢Å¡Â Ã¯Â¸Â</span> Ãƒâ€“ksÃƒÂ¼z (BaÃ„Å¸lantÃ„Â±sÃ„Â±z) Maddeler</h3>';
    $output .= '<span style="font-size: 11.5px; font-weight: 700; background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px;">KÃƒÂ¶prÃƒÂ¼ Bekleyen</span>';
    $output .= '</div>';
    $output .= '<p style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">HenÃƒÂ¼z baÃ…Å¸ka hiÃƒÂ§bir maddeden iÃƒÂ§ baÃ„Å¸lantÃ„Â± (link) almamÃ„Â±Ã…Å¸ maddeler:</p>';
    
    $output .= '<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">';
    $limit = 0;
    foreach ($oksuzler as $baslik => $sayi) {
        if ($limit >= 10) break;
        if (isset($madde_id_haritasi[$baslik])) {
            $output .= '<li style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">';
            $output .= '<a href="' . esc_url(get_permalink($madde_id_haritasi[$baslik])) . '" style="font-weight: 600; font-size: 13.5px; color: #0f172a; text-decoration: none;">' . esc_html($baslik) . '</a>';
            $output .= '<a href="' . esc_url(get_permalink($madde_id_haritasi[$baslik])) . '" style="font-size: 11.5px; font-weight: 700; color: #2563eb; text-decoration: none; background: #ffffff; border: 1px solid #cbd5e1; padding: 2px 8px; border-radius: 4px;">Maddeye Git Ã¢â€ â€™</a>';
            $output .= '</li>';
            $limit++;
        }
    }
    if ($limit === 0) {
        $output .= '<li style="padding: 12px; color: #166534; background: #f0fdf4; border-radius: 8px; font-size: 13px;">Harika! Sitedeki tÃƒÂ¼m maddeler birbirine baÃ„Å¸lanmÃ„Â±Ã…Å¸ durumda.</li>';
    }
    $output .= '</ul></div></div>';

    set_transient($cache_key, $output, 6 * HOUR_IN_SECONDS);
    return $output;
}
add_shortcode('en_maddeler', 'malatya_en_maddeler_raporu');

// =========================================================================
// 12. YORUM FORMU URL ALANI VE BOT KORUMA (HONEYPOT + GÃƒÅ“VENLÃ„Â°K)
// =========================================================================
function malatya_sozluk_url_alanini_kaldir( $fields ) {
    if ( isset( $fields['url'] ) ) {
        unset( $fields['url'] );
    }
    return $fields;
}
add_filter( 'comment_form_default_fields', 'malatya_sozluk_url_alanini_kaldir' );

function malatya_yorum_dogrulama_ekle() {
    echo '<p style="margin-top:10px;"><label>GÃƒÂ¼venlik: Malatya plaka kodu? (44)</label><br>';
    echo '<input type="text" name="mly_plaka" required style="width:100px; padding:5px; border:1px solid #a2a9b1;"></p>';
}
add_action('comment_form_after_fields', 'malatya_yorum_dogrulama_ekle');

function malatya_yorum_honeypot_alani_ekle() {
    echo '<div style="display:none !important; visibility:hidden !important; position:absolute; left:-9999px;">';
    echo '<label>Bu alanÃ„Â± boÃ…Å¸ bÃ„Â±rakÃ„Â±n:</label>';
    echo '<input type="text" name="mly_ikinci_ad" tabindex="-1" autocomplete="off">';
    echo '</div>';
}
add_action('comment_form_after_fields', 'malatya_yorum_honeypot_alani_ekle');
add_action('comment_form_logged_in_after', 'malatya_yorum_honeypot_alani_ekle');

function malatya_yorum_dogrula($commentdata) {
    if ( ! is_admin() ) {
        if ( ! empty($_POST['mly_ikinci_ad']) ) {
            wp_die("Spam botu tespit edildi! Yorumunuz gÃƒÂ¼venlik gerekÃƒÂ§esiyle reddedildi.");
        }
        if ( empty($_POST['mly_plaka']) || trim($_POST['mly_plaka']) !== '44' ) {
            wp_die("Hata: LÃƒÂ¼tfen Malatya'nÃ„Â±n plaka kodunu (44) doÃ„Å¸ru giriniz.");
        }
    }
    return $commentdata;
}
add_filter('preprocess_comment', 'malatya_yorum_dogrula');

// =========================================================================
// 13. AKILLI ETÃ„Â°KET VE Ã„Â°Ãƒâ€¡ BAÃ„ÂLANTI ASÃ„Â°STANI (ADMIN METABOX)
// =========================================================================
function malatya_sozluk_akilli_metaboxlar() {
    add_meta_box('malatya_dinamik_etiketler', 'Malatya SÃƒÂ¶zlÃƒÂ¼k - Ã„Â°ÃƒÂ§erikten Etiket Ãƒâ€“nerileri', 'malatya_sozluk_akilli_etiket_render', 'post', 'side', 'low');
    add_meta_box('malatya_dinamik_ic_linkler', 'Malatya SÃƒÂ¶zlÃƒÂ¼k - Ã„Â°ÃƒÂ§ BaÃ„Å¸lantÃ„Â± AsistanÃ„Â±', 'malatya_sozluk_akilli_link_render', 'post', 'side', 'high');
}
add_action('add_meta_boxes', 'malatya_sozluk_akilli_metaboxlar');

function malatya_sozluk_akilli_etiket_render($post) {
    $tum_etiketler = get_tags(array('hide_empty' => false));
    $etiket_listesi = array();
    foreach ($tum_etiketler as $etiket) {
        $etiket_listesi[] = $etiket->name;
    }
    echo '<p style="font-size:11px; color:#666; margin-bottom:10px;">YazÃ„Â±nÃ„Â±zda geÃƒÂ§en anahtar kelimeler burada belirecek:</p>';
    echo '<div id="dinamik-etiket-onerileri" style="display:flex; flex-wrap:wrap; gap:5px; min-height:30px; padding:5px; border:1px dashed #ccc; border-radius:4px;">';
    echo '<span style="color:#999; font-style:italic; font-size:11px;">Taramak iÃƒÂ§in yazmaya devam edin...</span>';
    echo '</div>';
    ?>
    <script>
    jQuery(document).ready(function($) {
        var sistemEtiketleri = <?php echo json_encode($etiket_listesi); ?>;
        function etiketleriTara() {
            var icerik = '';
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                icerik = wp.data.select('core/editor').getEditedPostContent();
            } else if ($('#wp-content-wrap').hasClass('tmce-active') && typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                icerik = tinyMCE.get('content').getContent({format: 'text'});
            } else if ($('#content').length) {
                icerik = $('#content').val();
            }
            if (!icerik) return;

            var bulunanlar = [];
            var mevcutEtiketler = $('#new-tag-post_tag').val() ? $('#new-tag-post_tag').val().toLowerCase() : '';
            sistemEtiketleri.forEach(function(etiket) {
                var regex = new RegExp('(^|\\s|[.,!?"\'])' + etiket.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '(\\s|[.,!?"\']|$)', 'gi');
                if (regex.test(icerik) && mevcutEtiketler.indexOf(etiket.toLowerCase()) === -1) {
                    bulunanlar.push(etiket);
                }
            });
            var oneriKutusu = $('#dinamik-etiket-onerileri');
            if (bulunanlar.length > 0) {
                oneriKutusu.empty();
                bulunanlar.forEach(function(etiket) {
                    oneriKutusu.append('<button type="button" class="button button-small dinamik-tag-btn" style="border-color:#3366cc; color:#3366cc; margin:2px;" data-tag="'+etiket+'">+ '+etiket+'</button>');
                });
            } else {
                oneriKutusu.html('<span style="color:#999; font-style:italic; font-size:11px;">EÃ…Å¸leÃ…Å¸en etiket bulunamadÃ„Â±.</span>');
            }
        }
        setInterval(etiketleriTara, 2500);
        $(document).on('keyup', '#content', etiketleriTara);
        
        $(document).on('click', '.dinamik-tag-btn', function() {
            var etiket = $(this).data('tag');
            $('.newtag').val(etiket);
            $('.tagadd').click();
            $(this).fadeOut(200);
        });
    });
    </script>
    <?php
}

function malatya_sozluk_akilli_link_render($post) {
    $maddeler = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'no_found_rows'  => true,
        'post__not_in'   => array($post->ID)
    ));
    $madde_basliklari = array();
    foreach ($maddeler as $madde) {
        $madde_basliklari[] = $madde->post_title;
    }
    echo '<p style="font-size:11px; color:#666; margin-bottom:10px;">Metinde geÃƒÂ§en ve maddesi bulunan kelimeler:</p>';
    echo '<div id="dinamik-link-onerileri" style="display:flex; flex-wrap:wrap; gap:5px; min-height:40px; padding:8px; border:1px solid #e2e4e7; background:#f0f0f1; border-radius:4px;">';
    echo '<span style="color:#999; font-style:italic; font-size:11px;">Yazmaya baÃ…Å¸layÃ„Â±nca ÃƒÂ¶neriler gelecek...</span>';
    echo '</div>';
    ?>
    <script>
    jQuery(document).ready(function($) {
        var mevcutMaddeler = <?php echo json_encode($madde_basliklari); ?>;
        function linkleriTara() {
            var icerik = '';
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                icerik = wp.data.select('core/editor').getEditedPostContent();
            } else if ($('#wp-content-wrap').hasClass('tmce-active') && typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                icerik = tinyMCE.get('content').getContent({format: 'text'});
            } else if ($('#content').length) {
                icerik = $('#content').val();
            }
            if (!icerik) return;

            var bulunanlar = [];
            mevcutMaddeler.forEach(function(madde) {
                var regex = new RegExp('(^|\\s|[.,!?"\'])' + madde.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '(\\s|[.,!?"\']|$)', 'gi');
                if (regex.test(icerik) && icerik.indexOf('[[' + madde + ']]') === -1) {
                    bulunanlar.push(madde);
                }
            });
            var oneriKutusu = $('#dinamik-link-onerileri');
            if (bulunanlar.length > 0) {
                oneriKutusu.empty();
                bulunanlar.forEach(function(madde) {
                    oneriKutusu.append('<button type="button" class="button button-small dinamik-link-btn" style="border-color:#0645ad; color:#0645ad; margin:2px;" data-link="'+madde+'">[[ '+madde+' ]]</button>');
                });
            } else {
                oneriKutusu.html('<span style="color:#999; font-style:italic; font-size:11px;">BaÃ„Å¸lantÃ„Â± ÃƒÂ¶nerisi yok.</span>');
            }
        }
        setInterval(linkleriTara, 2500);
        $(document).on('keyup', '#content', linkleriTara);
        
        $(document).on('click', '.dinamik-link-btn', function() {
            alert('LÃƒÂ¼tfen metin iÃƒÂ§inde bu kelimeyi seÃƒÂ§ip [[ ]] iÃƒÂ§ine alÃ„Â±n.');
        });
    });
    </script>
    <?php
}

// Bilgi Kutusu KÃ„Â±sa Kodu [bilgi]...[/bilgi]
function malatya_sozluk_bilgi_kutusu($atts, $content = null) {
    return '<aside class="wiki-bilgi-kutusu">' . do_shortcode($content) . '</aside>';
}
add_shortcode('bilgi', 'malatya_sozluk_bilgi_kutusu');

// =========================================================================
// 14. FACEBOOK RASTGELE MADDE PAYLAÃ…ÂIM BOTU
// =========================================================================
if ( ! wp_next_scheduled( 'malatya_sozluk_facebook_paylas_event' ) ) {
    wp_schedule_event( time(), 'daily', 'malatya_sozluk_facebook_paylas_event' );
}
add_action( 'malatya_sozluk_facebook_paylas_event', 'malatya_facebook_rastgele_paylas' );

function malatya_facebook_rastgele_paylas() {
    $access_token = 'BURAYA_UZUN_SURELI_PAGE_TOKEN_GELECEK';
    if ($access_token === 'BURAYA_UZUN_SURELI_PAGE_TOKEN_GELECEK') {
        return; // Token girilmediyse boÃ…Å¸una ÃƒÂ§alÃ„Â±Ã…Å¸tÃ„Â±rma
    }

    $rastgele_maddeler = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ));

    if ( ! empty( $rastgele_maddeler ) ) {
        $madde = $rastgele_maddeler[0]; 
        $link = get_permalink( $madde->ID );
        $baslik = get_the_title( $madde->ID );
        $mesaj = "Malatya SÃƒÂ¶zlÃƒÂ¼k'te GÃƒÂ¼nÃƒÂ¼n Maddesi: " . $baslik;
        $page_id = 'malatyasozluk';

        wp_remote_post( "https://graph.facebook.com/v18.0/{$page_id}/feed", array(
            'method'    => 'POST',
            'timeout'   => 15,
            'body'      => array(
                'message'      => $mesaj,
                'link'         => $link,
                'access_token' => $access_token,
            ),
        ) );
    }
}

// =========================================================================
// 15. ANSÃ„Â°KLOPEDÃ„Â°K YÃƒâ€“NLENDÃ„Â°RME SÃ„Â°STEMÃ„Â° (#YÃƒâ€“NLENDÃ„Â°R [[Hedef]])
// =========================================================================
function malatya_sozluk_yonlendirme_motoru() {
    if ( is_page() || is_single() ) {
        global $post;
        if ( ! $post ) return;
        $content = trim($post->post_content);

        if ( preg_match('/^#YÃƒâ€“NLENDÃ„Â°R\s*\[\[(.*?)\]\]/i', $content, $matches) ) {
            $hedef_adi = trim($matches[1]);
            $kaynak_adi = get_the_title($post->ID);

            $hedef = malatya_get_post_by_title($hedef_adi, 'post');
            if (!$hedef) {
                $hedef = malatya_get_post_by_title($hedef_adi, 'page');
            }

            if ( $hedef ) {
                $redirect_url = add_query_arg('redirected_from', urlencode($kaynak_adi), get_permalink($hedef->ID));
                wp_redirect( $redirect_url, 301 );
                exit;
            }
        }
    }
}
add_action('template_redirect', 'malatya_sozluk_yonlendirme_motoru', 1);

// =========================================================================
// 16. Ã„Â°LGÃ„Â° PUANI HESAPLAMA VE Ãƒâ€“N BELLEKLEME
// =========================================================================
function malatya_sozluk_puanli_maddeleri_getir($post_id) {
    $cache_key = 'wiki_ilgili_puan_' . $post_id . '_v4';
    $sonuclar = get_transient($cache_key);
    
    if (false === $sonuclar) {
        $categories = wp_get_post_categories($post_id);
        $tags = wp_get_post_tags($post_id, array('fields' => 'ids'));

        $maddeler = get_posts(array(
            'category__in'   => $categories,
            'post__not_in'   => array($post_id),
            'posts_per_page' => 12,
            'no_found_rows'  => true,
        ));

        $puanli_liste = array();
        foreach ($maddeler as $madde) {
            $puan = 0;
            $puan += count(array_intersect($categories, wp_get_post_categories($madde->ID))) * 3;
            $madde_tags = wp_get_post_tags($madde->ID, array('fields' => 'ids'));

            if (!empty($tags) && !empty($madde_tags)) {
                $puan += count(array_intersect($tags, $madde_tags)) * 5;
            }

            if ($puan >= 5) {
                $puanli_liste[$madde->ID] = $puan;
            }
        }

        arsort($puanli_liste);
        $sonuclar = array_slice($puanli_liste, 0, 3, true);
        set_transient($cache_key, $sonuclar, 12 * HOUR_IN_SECONDS);
    }
    return $sonuclar;
}

// =========================================================================
// 17. VERÃ„Â° DEPOSU (CUSTOM POST TYPE & EXCEL) SÃ„Â°STEMÃ„Â°
// =========================================================================
add_action('init', function() {
    register_post_type('veri-deposu', array(
        'labels'      => array('name' => 'Veri Deposu', 'singular_name' => 'Veri'),
        'public'      => true,
        'menu_icon'   => 'dashicons-table-col-after',
        'supports'    => array('title', 'comments', 'excerpt'),
        'has_archive' => true,
    ));
});

add_action('admin_enqueue_scripts', function($hook) {
    if (get_post_type() !== 'veri-deposu') return;
    wp_enqueue_script('jspreadsheet', 'https://cdn.jsdelivr.net/npm/jspreadsheet-ce/dist/index.min.js', array(), '1.0');
    wp_enqueue_style('jspreadsheet-css', 'https://cdn.jsdelivr.net/npm/jspreadsheet-ce/dist/jspreadsheet.min.css', array(), '1.0');
    wp_enqueue_script('jsuites', 'https://cdn.jsdelivr.net/npm/jsuites/dist/jsuites.min.js', array(), '1.0');
    wp_enqueue_style('jsuites-css', 'https://cdn.jsdelivr.net/npm/jsuites/dist/jsuites.min.css', array(), '1.0');
});

add_action('add_meta_boxes', function() {
    add_meta_box('excel_editor', 'Veri Tablosu (Excel)', function($post) {
        $data = get_post_meta($post->ID, '_excel_verisi', true) ?: '[["","",""]]';
        echo '<div id="spreadsheet"></div>';
        echo '<input type="hidden" id="excel_input" name="excel_verisi" value="' . esc_attr($data) . '">';
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('spreadsheet');
            const inputField = document.getElementById('excel_input');
            if (!container || !inputField) return;

            const spreadsheet = jspreadsheet(container, {
                worksheets: [{
                    data: JSON.parse(inputField.value),
                    minDimensions: [1, 2],
                }],
                onchange: function() {
                    veriyiKutuyaYaz(this);
                }
            });

            function veriyiKutuyaYaz(instance) {
                if (instance && typeof instance.getData === 'function') {
                    const rawData = instance.getData();
                    const cleanData = rawData.filter(row => row.some(cell => cell !== null && cell.toString().trim() !== ""));
                    const hiddenInput = document.getElementById('excel_input');
                    if (hiddenInput) {
                        hiddenInput.value = JSON.stringify(cleanData.length > 0 ? cleanData : [[""]]);
                    }
                }
            }

            const form = document.getElementById('post');
            if (form) {
                form.addEventListener('submit', function() {
                    if (spreadsheet) veriyiKutuyaYaz(spreadsheet);
                });
            }
        });
        </script>
        <?php
    }, 'veri-deposu');
});

add_action('save_post', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['excel_verisi'])) {
        update_post_meta($post_id, '_excel_verisi', sanitize_text_field($_POST['excel_verisi']));
    }
});

// [veri ad="BaÃ…Å¸lÃ„Â±k" hucre="A1"]
add_shortcode('veri', function($atts) {
    $a = shortcode_atts(array(
        'id'    => '',
        'ad'    => '',
        'hucre' => ''
    ), $atts);

    $post_id = $a['id'];

    if (!empty($a['ad'])) {
        $sayfa = malatya_get_post_by_title($a['ad'], array('post', 'page', 'veri-deposu'));
        if ($sayfa) {
            $post_id = $sayfa->ID;
        }
    }

    if (empty($post_id)) return '---';

    $meta = get_post_meta($post_id, '_excel_verisi', true);
    if (!$meta) return '---';
    
    $data = json_decode($meta);
    if (!$data) return '---';

    $sutun = ord(strtoupper(substr($a['hucre'], 0, 1))) - 65;
    $satir = intval(substr($a['hucre'], 1)) - 1;
    
    return isset($data[$satir][$sutun]) ? esc_html($data[$satir][$sutun]) : '---';
});

// [veri_listesi] kÃ„Â±sa kodu
add_shortcode('veri_listesi', function() {
    $sorgu = new WP_Query(array(
        'post_type'      => 'veri-deposu',
        'posts_per_page' => 100,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ));

    if (!$sorgu->have_posts()) return '<div class="wiki-bos-durum" style="padding: 16px; background: #f8fafc; border-radius: 8px; color: #64748b;">HenÃƒÂ¼z kayÃ„Â±tlÃ„Â± veri deposu tablosu bulunmamaktadÃ„Â±r.</div>';

    $cikti = '<div class="wiki-veri-listesi-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; margin: 20px 0;">';
    while ($sorgu->have_posts()) {
        $sorgu->the_post();
        $excel_data = get_post_meta(get_the_ID(), '_excel_verisi', true);
        $rows = $excel_data ? json_decode($excel_data) : array();
        $count = !empty($rows) ? max(0, count($rows) - 1) : 0;
        
        $cikti .= '<a href="' . esc_url(get_permalink()) . '" class="wiki-veri-kart" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-decoration: none; display: flex; align-items: flex-start; gap: 12px; transition: all 0.2s ease; box-shadow: 0 2px 6px rgba(15,23,42,0.02);">';
        $cikti .= '<div style="font-size: 24px; background: #eff6ff; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">ÄŸÅ¸â€œÅ </div>';
        $cikti .= '<div>';
        $cikti .= '<strong style="display: block; font-size: 14.5px; color: #0f172a; margin-bottom: 4px;">' . esc_html(get_the_title()) . '</strong>';
        $cikti .= '<span style="font-size: 12px; color: #64748b;">' . $count . ' Veri SatÃ„Â±rÃ„Â± Ã¢â‚¬Â¢ ' . get_the_modified_date('d M Y') . '</span>';
        $cikti .= '</div>';
        $cikti .= '</a>';
    }
    $cikti .= '</div>';

    wp_reset_postdata();
    return $cikti;
});

// YazdÃ„Â±rma sayfasÃ„Â±nda kaynaÃ„Å¸Ã„Â± gÃƒÂ¶sterme
add_filter('the_content', function($content) {
    if (is_single()) {
        $link = get_permalink();
        $content .= '<style>@media print {.wiki-print-source { display: block !important; }}</style>';
        $content .= '<div class="wiki-print-source" style="display:none; margin-top:30px; padding-top:10px; border-top:1px solid #aaa; font-size:10pt; color:#333;">';
        $content .= '<strong>Kaynak:</strong> ' . esc_url($link);
        $content .= '</div>';
    }
    return $content;
}, 99);

// =========================================================================
// 18. ENDPOINTS (Sayfaya BaÃ„Å¸lantÃ„Â±lar & GeÃƒÂ§miÃ…Å¸)
// =========================================================================
add_action( 'init', function() {
    add_rewrite_endpoint( 'sayfaya-baglantilar', EP_PERMALINK | EP_PAGES );
    add_rewrite_endpoint( 'gecmis', EP_PERMALINK );
});

add_filter( 'template_include', function( $template ) {
    global $wp_query, $post;

    if ( isset( $wp_query->query_vars['sayfaya-baglantilar'] ) && is_single() && $post ) {
        $madde_basligi = get_the_title($post->ID);
        $madde_url = get_permalink($post->ID);
        $baglanti_sorgusu = new WP_Query( array(
            'post_type'      => 'post',
            'posts_per_page' => 100,
            's'              => '[[' . $madde_basligi . ']]',
            'no_found_rows'  => true,
        ));

        add_filter( 'the_content', function() use ( $baglanti_sorgusu, $madde_basligi, $madde_url ) {
            $toplam = $baglanti_sorgusu->post_count;
            $output = '<div class="wiki-gecmis-panel-wrapper">';
            $output .= '<div class="wiki-gecmis-ust-bar">';
            $output .= '<a href="' . esc_url($madde_url) . '" class="wiki-gecmis-don-btn">Ã¢â€ Â <strong>' . esc_html($madde_basligi) . '</strong> Maddesine DÃƒÂ¶n</a>';
            $output .= '<span class="wiki-gecmis-toplam-rozet">' . intval($toplam) . ' BaÃ„Å¸lantÃ„Â±</span>';
            $output .= '</div>';
            
            $output .= '<div class="wiki-gecmis-baslik-alani">';
            $output .= '<h2>Ã¢Å¡â„¢Ã¯Â¸Â "' . esc_html($madde_basligi) . '" Maddesine BaÃ„Å¸lantÃ„Â±sÃ„Â± Olan Sayfalar</h2>';
            $output .= '<p class="wiki-gecmis-aciklama">Bu maddeye ansiklopedi iÃƒÂ§erisinden iÃƒÂ§ link (baÃ„Å¸lantÃ„Â±) veren tÃƒÂ¼m diÃ„Å¸er maddeler listelenmektedir:</p>';
            $output .= '</div>';
            
            if ( $baglanti_sorgusu->have_posts() ) {
                $output .= '<ul class="wiki-liste-sade" style="line-height:2; font-size:14.5px; padding-left:10px;">';
                while ( $baglanti_sorgusu->have_posts() ) {
                    $baglanti_sorgusu->the_post();
                    $output .= '<li style="margin-bottom:8px;">ÄŸÅ¸â€â€” <a href="' . esc_url(get_permalink()) . '" style="color:#0284c7; font-weight:700; text-decoration:none;">' . esc_html(get_the_title()) . '</a></li>';
                }
                $output .= '</ul>';
                wp_reset_postdata();
            } else {
                $output .= '<div class="wiki-bos-liste-mesaji"><p>Bu maddeye henÃƒÂ¼z hiÃƒÂ§bir sayfadan baÃ„Å¸lantÃ„Â± verilmemiÃ…Å¸.</p></div>';
            }
            
            $output .= '</div>';
            return $output;
        });

        add_filter( 'the_title', function( $title, $id ) use ( $post ) {
            if ( $id == $post->ID && in_the_loop() ) {
                return 'Sayfaya baÃ„Å¸lantÃ„Â±lar: ' . $title;
            }
            return $title;
        }, 10, 2);
    }

    if ( isset( $wp_query->query_vars['gecmis'] ) && is_single() && $post ) {
        add_filter( 'the_content', function( $content ) use ( $post ) {
            if ( function_exists('malatya_wiki_gecmis_sayfasi_icerigi') ) {
                return malatya_wiki_gecmis_sayfasi_icerigi( $post->ID );
            }
            $revisions = wp_get_post_revisions( $post->ID );
            $output = '<div class="wiki-gecmis-panel">';
            $output .= '<p style="margin-bottom:15px;"><a href="' . esc_url(get_permalink($post->ID)) . '" style="color:#0645ad; font-weight:bold;">Ã¢â€ Â Maddeye dÃƒÂ¶n</a></p>';
            $output .= '<h2>"' . esc_html(get_the_title($post->ID)) . '" DeÃ„Å¸iÃ…Å¸iklik GeÃƒÂ§miÃ…Å¸i</h2>';
            if ( !empty($revisions) ) {
                $output .= '<ul class="wiki-liste-sade" style="line-height:1.8; list-style:square; padding-left:20px;">';
                foreach ( $revisions as $revision ) {
                    $tarih = date_i18n( 'j F Y, H:i', strtotime( $revision->post_modified ) );
                    $yazar = get_the_author_meta( 'display_name', $revision->post_author );
                    $output .= "<li><strong>" . esc_html($tarih) . "</strong> Ã¢â‚¬â€ " . esc_html($yazar) . " tarafÃ„Â±ndan gÃƒÂ¼ncellendi.</li>";
                }
                $output .= '</ul>';
            } else {
                $output .= '<p>Bu madde henÃƒÂ¼z hiÃƒÂ§ dÃƒÂ¼zenlenmemiÃ…Å¸.</p>';
            }
            return $output . '</div>';
        });

        add_filter( 'the_title', function( $title, $id ) use ( $post ) {
            if ( $id == $post->ID && in_the_loop() ) {
                return 'Sayfa GeÃƒÂ§miÃ…Å¸i: ' . $title;
            }
            return $title;
        }, 10, 2);
    }

    return $template;
});

// =========================================================================
// 17. OYUNLAÅTIRMA (GAMIFICATION) / ROZET SÄ°STEMÄ°
// =========================================================================
function malatya_sozluk_kullanici_rozet_getir($user_id) {
    $toplam_katki = (int) get_user_meta($user_id, '_wiki_toplam_katki_sayisi', true);
    $madde_sayisi = count_user_posts($user_id, 'post', true);
    $toplam = $toplam_katki + $madde_sayisi;

    if ($toplam >= 100) {
        return array('isim' => 'ğŸ† Yerel TarihÃ§i', 'renk' => '#b45309', 'bg' => '#fef3c7');
    } elseif ($toplam >= 50) {
        return array('isim' => 'â­ KÄ±demli Yazar', 'renk' => '#4338ca', 'bg' => '#e0e7ff');
    } elseif ($toplam >= 10) {
        return array('isim' => 'âœï¸ KatkÄ± SaÄŸlayan', 'renk' => '#047857', 'bg' => '#d1fae5');
    } elseif ($toplam >= 1) {
        return array('isim' => 'ğŸŒ± Acemi Ã‡Ä±rak', 'renk' => '#334155', 'bg' => '#f1f5f9');
    } else {
        return array('isim' => 'ğŸ“– Okur', 'renk' => '#64748b', 'bg' => '#f8fafc');
    }
}

