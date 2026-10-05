<?php
/**
 * Malatya Sözlük - Düzenleme Önerileri & Yönetici Onay Sistemi
 * 
 * Yönetici olmayan kullanıcıların hızlı düzenleme ile gönderdiği içerikler
 * 'wiki_revizyon' özel tipinde kaydedilir. Yönetici onayladığında ana madde
 * güncellenir ve öneri temizlenir; böylece mükerrer yazı oluşması engellenir.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// 1. ÖZEL TİP (CPT): DÜZENLEME ÖNERİLERİ
// =========================================================================
function malatya_wiki_revizyon_cpt_kaydet() {
    register_post_type('wiki_revizyon', array(
        'labels' => array(
            'name'               => 'Düzenleme Önerileri',
            'singular_name'      => 'Düzenleme Önerisi',
            'menu_name'          => 'Düzenleme Önerileri',
            'all_items'          => 'Tüm Öneriler',
            'edit_item'          => 'Öneriyi İncele & Düzenle',
            'view_item'          => 'Öneriyi Görüntüle',
            'search_items'       => 'Öneri Ara',
            'not_found'          => 'Bekleyen düzenleme önerisi bulunamadı.',
            'not_found_in_trash' => 'Çöp kutusunda öneri yok.'
        ),
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => 'edit.php', // Yazılar menüsünün altına ekle
        'capability_type'     => 'post',
        'capabilities'        => array(
            'create_posts' => 'do_not_allow', // Panelden doğrudan yeni öneri açılmasın
        ),
        'map_meta_cap'        => true,
        'hierarchical'        => false,
        'supports'            => array('title', 'editor', 'author'),
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
    ));
}
add_action('init', 'malatya_wiki_revizyon_cpt_kaydet');

// =========================================================================
// 2. ADMİN MENÜSÜNDE BEKLEYEN ÖNERİ SAYACI
// =========================================================================
function malatya_wiki_oneri_menu_sayaci() {
    global $submenu;
    if ( ! current_user_can('manage_options') ) return;

    $count = wp_count_posts('wiki_revizyon')->pending;
    if ( $count > 0 && isset($submenu['edit.php']) ) {
        foreach ( $submenu['edit.php'] as $key => $item ) {
            if ( isset($item[2]) && $item[2] === 'edit.php?post_type=wiki_revizyon' ) {
                $submenu['edit.php'][$key][0] .= ' <span class="awaiting-mod update-plugins count-' . $count . '"><span class="pending-count">' . $count . '</span></span>';
                break;
            }
        }
    }
}
add_action('admin_menu', 'malatya_wiki_oneri_menu_sayaci', 999);

// =========================================================================
// 3. ÖNERİ LİSTE TABLOSU SÜTUNLARI
// =========================================================================
function malatya_wiki_revizyon_sutunlar($columns) {
    $yeni_sutunlar = array(
        'cb'           => $columns['cb'],
        'title'        => 'Öneri Başlığı',
        'hedef_madde'  => 'Hedef Ana Madde',
        'onerici'      => 'Öneren Kullanıcı',
        'ozet'         => 'Değişiklik Özeti',
        'date'         => 'Tarih',
        'islemler'     => 'Onay İşlemleri'
    );
    return $yeni_sutunlar;
}
add_filter('manage_wiki_revizyon_posts_columns', 'malatya_wiki_revizyon_sutunlar');

function malatya_wiki_revizyon_sutun_icerikleri($column, $post_id) {
    $hedef_id = get_post_meta($post_id, '_wiki_kaynak_madde_id', true) ?: wp_get_post_parent_id($post_id);
    
    switch ($column) {
        case 'hedef_madde':
            if ($hedef_id && get_post($hedef_id)) {
                echo '<strong><a href="' . esc_url(get_permalink($hedef_id)) . '" target="_blank">' . esc_html(get_the_title($hedef_id)) . ' ↗</a></strong>';
            } else {
                echo '<span style="color:#94a3b8;">Bilinmiyor</span>';
            }
            break;

        case 'onerici':
            $author_id = get_post_field('post_author', $post_id);
            $user_info = get_userdata($author_id);
            $onerici_adi = $user_info ? ($user_info->display_name ?: $user_info->user_login) : 'Bilinmeyen Üye';
            echo esc_html($onerici_adi);
            break;

        case 'ozet':
            $ozet = get_post_meta($post_id, '_wiki_duzenleme_ozeti', true);
            echo !empty($ozet) ? '<em>' . esc_html($ozet) . '</em>' : '<span style="color:#94a3b8;">Belirtilmemiş</span>';
            break;

        case 'islemler':
            $onayla_nonce = wp_create_nonce('malatya_oneri_onayla_' . $post_id);
            $reddet_nonce = wp_create_nonce('malatya_oneri_reddet_' . $post_id);

            $onayla_url = admin_url('admin-post.php?action=malatya_oneri_onayla&oneri_id=' . $post_id . '&_wpnonce=' . $onayla_nonce);
            $reddet_url = admin_url('admin-post.php?action=malatya_oneri_reddet&oneri_id=' . $post_id . '&_wpnonce=' . $reddet_nonce);

            echo '<div style="display:flex; gap:8px; align-items:center;">';
            echo '<a href="' . esc_url($onayla_url) . '" class="button button-primary button-small" style="background:#16a34a; border-color:#15803d; color:#fff;" onclick="return confirm(\'Bu düzenlemeyi onaylayıp ana maddeye aktarmak istiyor musunuz?\')">✓ Onayla ve Birleştir</a>';
            echo '<a href="' . esc_url($reddet_url) . '" class="button button-secondary button-small" style="color:#dc2626;" onclick="return confirm(\'Bu öneriyi reddetmek ve silmek istediğinize emin misiniz?\')">✕ Reddet</a>';
            echo '</div>';
            break;
    }
}
add_action('manage_wiki_revizyon_posts_custom_column', 'malatya_wiki_revizyon_sutun_icerikleri', 10, 2);

// =========================================================================
// 4. TEK TIKLA ONAYLA VE ANA MADDEYE BİRLEŞTİR İŞLEYİCİSİ
// =========================================================================
function malatya_oneri_onayla_islevi() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Bu işlemi yapmaya yetkiniz yok.');
    }

    $oneri_id = isset($_GET['oneri_id']) ? intval($_GET['oneri_id']) : 0;
    check_admin_referer('malatya_oneri_onayla_' . $oneri_id);

    $oneri = get_post($oneri_id);
    if ( ! $oneri || $oneri->post_type !== 'wiki_revizyon' ) {
        wp_die('Geçersiz öneri kaydı.');
    }

    $hedef_id = get_post_meta($oneri_id, '_wiki_kaynak_madde_id', true) ?: $oneri->post_parent;
    if ( ! $hedef_id || ! get_post($hedef_id) ) {
        wp_die('Önerinin ait olduğu ana madde bulunamadı.');
    }

    $ozet = get_post_meta($oneri_id, '_wiki_duzenleme_ozeti', true);

    // 1. Ana Maddeyi Yeni İçerikle Güncelle
    wp_update_post(array(
        'ID'           => $hedef_id,
        'post_content' => $oneri->post_content,
    ));

    // 2. Meta ve Revizyon Bilgilerini Ana Maddeye Yaz
    if ( ! empty($ozet) ) {
        update_post_meta($hedef_id, '_wiki_son_duzenleme_ozeti', $ozet);
    }
    update_post_meta($hedef_id, '_wiki_son_duzenleyen', $oneri->post_author);

    // 3. Katkıyı Madde Geçmişine ve Kullanıcı Profiline Kaydet
    if ( function_exists('malatya_wiki_katki_kaydet') ) {
        malatya_wiki_katki_kaydet($hedef_id, $oneri->post_author, 'Onaylanan Katkı', $ozet);
    }

    // 4. Önbellekleri Temizle
    clean_post_cache($hedef_id);
    delete_transient('malatya_en_maddeler_raporu_v2');

    // 5. Öneri Kaydını Sil (Mükerrer yazı kalmasın)
    wp_delete_post($oneri_id, true);

    // 5. Başarı Bildirimi ile Yönlendir
    $hedef_baslik = get_the_title($hedef_id);
    $redirect_url = add_query_arg(array(
        'post_type'       => 'wiki_revizyon',
        'wiki_onay_durum' => 'onaylandi',
        'madde'           => urlencode($hedef_baslik)
    ), admin_url('edit.php'));

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_malatya_oneri_onayla', 'malatya_oneri_onayla_islevi');

// =========================================================================
// 5. ÖNERİYİ REDDET & SİL İŞLEYİCİSİ
// =========================================================================
function malatya_oneri_reddet_islevi() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Bu işlemi yapmaya yetkiniz yok.');
    }

    $oneri_id = isset($_GET['oneri_id']) ? intval($_GET['oneri_id']) : 0;
    check_admin_referer('malatya_oneri_reddet_' . $oneri_id);

    $oneri = get_post($oneri_id);
    if ( $oneri && $oneri->post_type === 'wiki_revizyon' ) {
        wp_delete_post($oneri_id, true);
    }

    $redirect_url = add_query_arg(array(
        'post_type'       => 'wiki_revizyon',
        'wiki_onay_durum' => 'reddedildi'
    ), admin_url('edit.php'));

    wp_safe_redirect($redirect_url);
    exit;
}
add_action('admin_post_malatya_oneri_reddet', 'malatya_oneri_reddet_islevi');

// =========================================================================
// 6. ADMİN BİLDİRİM MESAJLARI (NOTICES)
// =========================================================================
function malatya_wiki_oneri_admin_bildirimleri() {
    if ( isset($_GET['wiki_onay_durum']) ) {
        if ( $_GET['wiki_onay_durum'] === 'onaylandi' ) {
            $madde = isset($_GET['madde']) ? sanitize_text_field($_GET['madde']) : 'Madde';
            echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Başarılı:</strong> "' . esc_html($madde) . '" maddesine ait düzenleme önerisi onaylandı ve ana maddeye başarıyla aktarıldı.</p></div>';
        } elseif ( $_GET['wiki_onay_durum'] === 'reddedildi' ) {
            echo '<div class="notice notice-info is-dismissible"><p>Düzenleme önerisi reddedildi ve silindi.</p></div>';
        }
    }
}
add_action('admin_notices', 'malatya_wiki_oneri_admin_bildirimleri');

// =========================================================================
// 7. ÖNERİ DÜZENLEME SAYFASINA BİLGİ KUTUSU & ONAY BUTONU EKLEME
// =========================================================================
function malatya_wiki_revizyon_meta_box() {
    add_meta_box(
        'wiki_revizyon_detay_kutusu',
        '📌 Düzenleme Önerisi Bilgileri & Onay Paneli',
        'malatya_wiki_revizyon_meta_box_icerik',
        'wiki_revizyon',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'malatya_wiki_revizyon_meta_box');

function malatya_wiki_revizyon_meta_box_icerik($post) {
    $hedef_id = get_post_meta($post->ID, '_wiki_kaynak_madde_id', true) ?: $post->post_parent;
    $ozet     = get_post_meta($post->ID, '_wiki_duzenleme_ozeti', true);
    
    $author_id   = $post->post_author;
    $user_info   = get_userdata($author_id);
    $onerici_adi = $user_info ? ($user_info->display_name ?: $user_info->user_login) : 'Üye';

    $onayla_nonce = wp_create_nonce('malatya_oneri_onayla_' . $post->ID);
    $onayla_url   = admin_url('admin-post.php?action=malatya_oneri_onayla&oneri_id=' . $post->ID . '&_wpnonce=' . $onayla_nonce);
    ?>
    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:15px;">
        <table class="form-table" style="margin:0;">
            <tr>
                <th style="width:160px; padding:6px 0;"><strong>Hedef Ana Madde:</strong></th>
                <td style="padding:6px 0;">
                    <?php if ( $hedef_id && get_post($hedef_id) ) : ?>
                        <strong style="font-size:1.1em;"><a href="<?php echo esc_url(get_permalink($hedef_id)); ?>" target="_blank" style="color:#2563eb; text-decoration:none;"><?php echo esc_html(get_the_title($hedef_id)); ?> ↗</a></strong>
                        <span style="color:#64748b; font-size:0.9em; margin-left:10px;">(ID: <?php echo esc_html($hedef_id); ?>)</span>
                    <?php else : ?>
                        <span style="color:#ef4444;">Ana madde bulunamadı.</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th style="padding:6px 0;"><strong>Öneren Kullanıcı:</strong></th>
                <td style="padding:6px 0;">
                    <strong><?php echo esc_html($onerici_adi); ?></strong>
                    <?php if ( $user_info ) : ?>
                        <span style="color:#64748b; font-size:0.9em;">(<?php echo esc_html($user_info->user_email); ?>)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th style="padding:6px 0;"><strong>Değişiklik Özeti:</strong></th>
                <td style="padding:6px 0;">
                    <?php echo !empty($ozet) ? '<span style="background:#e0e7ff; color:#3730a3; padding:3px 8px; border-radius:4px; font-weight:500;">' . esc_html($ozet) . '</span>' : '<em style="color:#94a3b8;">Açıklama girilmemiş</em>'; ?>
                </td>
            </tr>
        </table>
        
        <div style="margin-top:15px; padding-top:12px; border-top:1px solid #e2e8f0; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <a href="<?php echo esc_url($onayla_url); ?>" class="button button-primary button-large" style="background:#16a34a; border-color:#15803d; font-weight:bold;" onclick="return confirm('Bu düzenlemeyi onaylayıp ana maddeye aktarmak istiyor musunuz?')">
                ✓ Bu Öneriyi Onayla ve Ana Maddeye Aktar
            </a>
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=malatya_oneri_reddet&oneri_id=' . $post->ID . '&_wpnonce=' . wp_create_nonce('malatya_oneri_reddet_' . $post->ID))); ?>" class="button button-secondary button-large" style="color:#dc2626;" onclick="return confirm('Bu öneriyi reddedip silmek istediğinize emin misiniz?')">
                ✕ Öneriyi Reddet
            </a>
            <span style="color:#64748b; font-size:12px;">Onayladığınızda içerik otomatik olarak ana maddeye kopyalanacak ve bu öneri kaydı temizlenecektir.</span>
        </div>

        <?php
        // Görsel Değişiklik Karşılaştırması (Editör Önizlemesi ve Diff)
        if ( $hedef_id && get_post($hedef_id) ) :
            $hedef_post = get_post($hedef_id);
            require_once ABSPATH . WPINC . '/wp-diff.php';

            // İçerikleri görsel editör formatında hazırla
            $mevcut_icerik_render = wpautop( $hedef_post->post_content );
            $oneri_icerik_render  = wpautop( $post->post_content );

            if ( function_exists('malatya_sozluk_wiki_link_filtresi') ) {
                $mevcut_icerik_render = malatya_sozluk_wiki_link_filtresi( $mevcut_icerik_render );
                $oneri_icerik_render  = malatya_sozluk_wiki_link_filtresi( $oneri_icerik_render );
            }

            // Metin bazlı temiz fark karşılaştırması (Render edilmiş HTML üzerinden)
            $diff = wp_text_diff( $mevcut_icerik_render, $oneri_icerik_render, array(
                'title'           => 'Metin Karşılaştırması',
                'title_left'      => 'Mevcut Yayındaki Madde',
                'title_right'     => 'Önerilen Yeni Metin',
                'show_split_view' => true,
            ));
            
            // HTML etiketlerinin kod olarak görünmesi yerine render edilmesi için:
            $diff = str_replace( array( '&lt;', '&gt;', '&amp;nbsp;' ), array( '<', '>', '&nbsp;' ), $diff );
            ?>
            <div style="margin-top:20px; background:#ffffff; border:1px solid #cbd5e1; border-radius:10px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                
                <!-- SEKME BAŞLIKLARI -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px;">
                    <div style="display:flex; gap:10px;">
                        <button type="button" id="btn-tab-gorsel" onclick="malatyaDiffSekme('gorsel')" style="background:#2563eb; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:700; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                            <span>👁️ Görsel Editör Görünümü (Yan Yana)</span>
                        </button>
                        <button type="button" id="btn-tab-diff" onclick="malatyaDiffSekme('diff')" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; padding:8px 16px; border-radius:6px; font-weight:700; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                            <span>🔍 Vurgulu Metin Farkları (Diff)</span>
                        </button>
                    </div>
                    <span style="color:#64748b; font-size:12.5px;">Önerilen değişiklikleri inceleyip yukarıdan onaylayabilirsiniz.</span>
                </div>

                <!-- 1. SEKME: GÖRSEL EDİTÖR GÖRÜNÜMÜ (YAN YANA) -->
                <div id="panel-gorsel-onizleme" style="display:block;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        
                        <!-- Sol: Mevcut Madde -->
                        <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:8px; padding:16px; display:flex; flex-direction:column;">
                            <div style="font-weight:800; font-size:13.5px; color:#475569; padding-bottom:8px; margin-bottom:12px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between;">
                                <span>📄 Mevcut Yayındaki İçerik</span>
                                <span style="font-size:11px; font-weight:600; background:#e2e8f0; color:#475569; padding:2px 8px; border-radius:12px;">Canlı Sürüm</span>
                            </div>
                            <div class="wiki-editor-preview-box" style="font-family:'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; font-size:14px; line-height:1.7; color:#1e293b; max-height:500px; overflow-y:auto; padding-right:8px;">
                                <?php echo $mevcut_icerik_render; ?>
                            </div>
                        </div>

                        <!-- Sağ: Önerilen Yeni İçerik -->
                        <div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:8px; padding:16px; display:flex; flex-direction:column;">
                            <div style="font-weight:800; font-size:13.5px; color:#166534; padding-bottom:8px; margin-bottom:12px; border-bottom:1px solid #bbf7d0; display:flex; align-items:center; justify-content:space-between;">
                                <span>✨ Önerilen Yeni İçerik (Düzenleme)</span>
                                <span style="font-size:11px; font-weight:700; background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:12px;">Onay Bekleyen</span>
                            </div>
                            <div class="wiki-editor-preview-box" style="font-family:'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; font-size:14px; line-height:1.7; color:#0f172a; max-height:500px; overflow-y:auto; padding-right:8px;">
                                <?php echo $oneri_icerik_render; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 2. SEKME: VURGULU METİN FARKLARI (DIFF) -->
                <div id="panel-diff-gorunum" style="display:none;">
                    <?php if ( ! empty($diff) ) : ?>
                        <style>
                            .wiki-diff-wrapper table.diff { width: 100%; border-collapse: collapse; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 13.5px; line-height: 1.6; }
                            .wiki-diff-wrapper table.diff th { background: #f8fafc; padding: 10px; border: 1px solid #cbd5e1; text-align: left; font-weight: 700; color: #334155; }
                            .wiki-diff-wrapper table.diff td { padding: 8px 12px; border: 1px solid #e2e8f0; vertical-align: top; word-break: break-word; }
                            .wiki-diff-wrapper .diff-deletedline { background-color: #fef2f2; color: #991b1b; }
                            .wiki-diff-wrapper .diff-deletedline del { background-color: #fecaca; text-decoration: none; font-weight: 700; padding: 1px 4px; border-radius: 3px; }
                            .wiki-diff-wrapper .diff-addedline { background-color: #f0fdf4; color: #166534; }
                            .wiki-diff-wrapper .diff-addedline ins { background-color: #bbf7d0; text-decoration: none; font-weight: 700; padding: 1px 4px; border-radius: 3px; }
                            .wiki-diff-wrapper .diff-context { background-color: #ffffff; color: #475569; }
                            .wiki-editor-preview-box h2 { font-size: 1.3rem; margin-top: 15px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; font-weight: 700; color: #0f172a; }
                            .wiki-editor-preview-box h3 { font-size: 1.15rem; margin-top: 12px; margin-bottom: 6px; font-weight: 600; color: #1e293b; }
                            .wiki-editor-preview-box p { margin-bottom: 12px; }
                            .wiki-editor-preview-box ul, .wiki-editor-preview-box ol { margin-left: 20px; margin-bottom: 12px; }
                            .wiki-editor-preview-box blockquote { border-left: 4px solid #cbd5e1; padding-left: 12px; color: #64748b; margin: 12px 0; font-style: italic; }
                        </style>
                        <div class="wiki-diff-wrapper" style="overflow-x:auto; max-height:500px; border:1px solid #cbd5e1; border-radius:8px;">
                            <?php echo $diff; ?>
                        </div>
                    <?php else : ?>
                        <div style="padding:18px; background:#f8fafc; border-radius:8px; color:#64748b; font-style:italic;">
                            İçerikte metin farkı bulunamadı veya içerik birebir aynı.
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                function malatyaDiffSekme(tur) {
                    var panelGorsel = document.getElementById('panel-gorsel-onizleme');
                    var panelDiff   = document.getElementById('panel-diff-gorunum');
                    var btnGorsel   = document.getElementById('btn-tab-gorsel');
                    var btnDiff     = document.getElementById('btn-tab-diff');
                    
                    if (tur === 'gorsel') {
                        panelGorsel.style.display = 'block';
                        panelDiff.style.display   = 'none';
                        btnGorsel.style.background = '#2563eb';
                        btnGorsel.style.color      = '#ffffff';
                        btnGorsel.style.border     = 'none';
                        btnDiff.style.background   = '#f1f5f9';
                        btnDiff.style.color        = '#475569';
                        btnDiff.style.border       = '1px solid #cbd5e1';
                    } else {
                        panelGorsel.style.display = 'none';
                        panelDiff.style.display   = 'block';
                        btnDiff.style.background   = '#2563eb';
                        btnDiff.style.color        = '#ffffff';
                        btnDiff.style.border       = 'none';
                        btnGorsel.style.background = '#f1f5f9';
                        btnGorsel.style.color      = '#475569';
                        btnGorsel.style.border     = '1px solid #cbd5e1';
                    }
                }
                </script>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
