<?php
/**
 * Malatya Sözlük - Katkı ve Madde Geçmişi (Revision & Contribution History) Sistemi
 * 
 * Maddelere yapılan tüm düzenlemeleri, kullanıcı katkılarını ve sayfa geçmişini
 * veritabanında loglar; madde geçmişinde ve kullanıcı profilinde listeler.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// 1. KATKI VE GEÇMİŞ KAYIT MOTORU
// =========================================================================
/**
 * Maddeye ve kullanıcıya katkı kaydı ekler.
 *
 * @param int    $post_id    Madde ID'si
 * @param int    $user_id    Katkıyı yapan kullanıcı ID'si
 * @param string $islem_turu 'Madde Oluşturuldu', 'Hızlı Düzenleme', 'Onaylanan Katkı', 'Panel Düzenlemesi' vb.
 * @param string $ozet       Değişiklik özeti açıklaması
 * @return bool
 */
function malatya_wiki_katki_kaydet( $post_id, $user_id, $islem_turu = 'Hızlı Düzenleme', $ozet = '' ) {
    $post_id = intval($post_id);
    $user_id = intval($user_id);

    if ( ! $post_id || get_post_type($post_id) !== 'post' ) {
        return false;
    }

    if ( ! $user_id ) {
        $user_id = 1; // Anonim veya varsayılan admin
    }

    $user_info = get_userdata($user_id);
    $user_display_name = $user_info ? ($user_info->display_name ?: $user_info->user_login) : 'Kullanıcı';
    $user_login = $user_info ? $user_info->user_login : 'user';

    $suan = current_time('mysql');
    $kayit_id = uniqid('rev_');

    // A) Madde Geçmişi Kaydı
    $madde_gecmisi = get_post_meta($post_id, '_wiki_madde_gecmisi', true);
    if ( ! is_array($madde_gecmisi) ) {
        $madde_gecmisi = array();
    }

    $yeni_madde_kaydi = array(
        'id'          => $kayit_id,
        'tarih'       => $suan,
        'user_id'     => $user_id,
        'user_name'   => $user_display_name,
        'user_login'  => $user_login,
        'islem'       => sanitize_text_field($islem_turu),
        'ozet'        => !empty($ozet) ? sanitize_text_field($ozet) : 'Madde içeriği güncellendi.',
        'ip'          => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '')
    );

    array_unshift($madde_gecmisi, $yeni_madde_kaydi);
    // En fazla son 100 revizyon kaydı tutulur
    $madde_gecmisi = array_slice($madde_gecmisi, 0, 100);
    update_post_meta($post_id, '_wiki_madde_gecmisi', $madde_gecmisi);

    // B) Kullanıcı Profil Katkı Kaydı
    if ( $user_id > 0 ) {
        $kullanici_katkilari = get_user_meta($user_id, '_wiki_kullanici_katkilari', true);
        if ( ! is_array($kullanici_katkilari) ) {
            $kullanici_katkilari = array();
        }

        $yeni_user_kaydi = array(
            'id'          => $kayit_id,
            'post_id'     => $post_id,
            'post_title'  => get_the_title($post_id),
            'post_url'    => get_permalink($post_id),
            'tarih'       => $suan,
            'islem'       => sanitize_text_field($islem_turu),
            'ozet'        => !empty($ozet) ? sanitize_text_field($ozet) : 'Madde içeriği güncellendi.'
        );

        array_unshift($kullanici_katkilari, $yeni_user_kaydi);
        // Kullanıcı başına en son 200 katkı saklanır
        $kullanici_katkilari = array_slice($kullanici_katkilari, 0, 200);
        update_user_meta($user_id, '_wiki_kullanici_katkilari', $kullanici_katkilari);
        
        // Katkı sayacını artır
        $toplam_katki = (int) get_user_meta($user_id, '_wiki_toplam_katki_sayisi', true);
        update_user_meta($user_id, '_wiki_toplam_katki_sayisi', $toplam_katki + 1);
    }

    return true;
}

// =========================================================================
// 2. MADDE GEÇMİŞİ GETİRİCİSİ (FALLBACK DESTEKLİ)
// =========================================================================
function malatya_wiki_madde_gecmisi_getir( $post_id ) {
    $gecmis = get_post_meta($post_id, '_wiki_madde_gecmisi', true);

    if ( ! empty($gecmis) && is_array($gecmis) ) {
        return $gecmis;
    }

    // Özel geçmiş yoksa WordPress yerel revizyonlarından ve yazı yazarından sentezle
    $gecmis = array();
    $post = get_post($post_id);

    if ( ! $post ) return $gecmis;

    // WP Revizyonları
    $revisions = wp_get_post_revisions($post_id);
    if ( ! empty($revisions) ) {
        foreach ( $revisions as $rev ) {
            $author_id = $rev->post_author;
            $user_info = get_userdata($author_id);
            $gecmis[] = array(
                'id'         => 'wp_rev_' . $rev->ID,
                'tarih'      => $rev->post_modified,
                'user_id'    => $author_id,
                'user_name'  => $user_info ? ($user_info->display_name ?: $user_info->user_login) : 'Yazar',
                'user_login' => $user_info ? $user_info->user_login : '',
                'islem'      => 'Düzenleme',
                'ozet'       => 'Madde güncellendi.'
            );
        }
    } else {
        // En azından ilk oluşturulma kaydını ekle
        $author_id = $post->post_author;
        $user_info = get_userdata($author_id);
        $gecmis[] = array(
            'id'         => 'post_init_' . $post->ID,
            'tarih'      => $post->post_date,
            'user_id'    => $author_id,
            'user_name'  => $user_info ? ($user_info->display_name ?: $user_info->user_login) : 'Yazar',
            'user_login' => $user_info ? $user_info->user_login : '',
            'islem'      => 'Madde Oluşturuldu',
            'ozet'       => 'Madde sözlüğe eklendi.'
        );
    }

    return $gecmis;
}

// =========================================================================
// 3. KULLANICI KATKILARI GETİRİCİSİ
// =========================================================================
function malatya_wiki_kullanici_katkilari_getir( $user_id ) {
    $katkilar = get_user_meta($user_id, '_wiki_kullanici_katkilari', true);

    if ( ! empty($katkilar) && is_array($katkilar) ) {
        return $katkilar;
    }

    // Özel katkı logu yoksa kullanıcının yazdığı yazılardan üret
    $katkilar = array();
    $posts = get_posts(array(
        'author'         => $user_id,
        'post_type'      => 'post',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));

    foreach ( $posts as $p ) {
        $katkilar[] = array(
            'id'         => 'post_' . $p->ID,
            'post_id'    => $p->ID,
            'post_title' => $p->post_title,
            'post_url'   => get_permalink($p->ID),
            'tarih'      => $p->post_date,
            'islem'      => 'Madde Oluşturuldu',
            'ozet'       => 'Madde oluşturuldu ve yayımlandı.'
        );
    }

    return $katkilar;
}

// =========================================================================
// 4. SAYFA GEÇMİŞİ (/gecmis) ARAYÜZ OLUŞTURUCU
// =========================================================================
function malatya_wiki_gecmis_sayfasi_icerigi( $post_id ) {
    $gecmis = malatya_wiki_madde_gecmisi_getir($post_id);
    $madde_baslik = get_the_title($post_id);
    $madde_url = get_permalink($post_id);

    ob_start();
    ?>
    <div class="wiki-gecmis-panel-wrapper">
        <div class="wiki-gecmis-ust-bar">
            <a href="<?php echo esc_url($madde_url); ?>" class="wiki-gecmis-don-btn">
                ← <strong><?php echo esc_html($madde_baslik); ?></strong> Maddesine Dön
            </a>
            <span class="wiki-gecmis-toplam-rozet"><?php echo count($gecmis); ?> Değişiklik Kaydı</span>
        </div>

        <div class="wiki-gecmis-baslik-alani">
            <h2>📜 "<?php echo esc_html($madde_baslik); ?>" Sayfa Geçmişi</h2>
            <p class="wiki-gecmis-aciklama">Bu maddenin dijital hafızasındaki tüm oluşturma, düzenleme ve katkı geçmişi kronolojik olarak aşağıda listelenmektedir.</p>
        </div>

        <?php if ( ! empty($gecmis) ) : ?>
            <div class="wiki-gecmis-timeline">
                <?php foreach ( $gecmis as $kayit ) : 
                    $tarih_str = date_i18n('j F Y, H:i', strtotime($kayit['tarih']));
                    $user_id = !empty($kayit['user_id']) ? intval($kayit['user_id']) : 0;
                    $user_name = !empty($kayit['user_name']) ? $kayit['user_name'] : 'Katkıcı';
                    $user_url = $user_id > 0 ? get_author_posts_url($user_id) : '#';
                    $islem = !empty($kayit['islem']) ? $kayit['islem'] : 'Düzenleme';
                    $ozet = !empty($kayit['ozet']) ? $kayit['ozet'] : 'Açıklama belirtilmemiş.';
                ?>
                    <div class="wiki-gecmis-kayit-karti">
                        <div class="kayit-sol-tarih">
                            <span class="tarih-ikon">🕒</span>
                            <span class="tarih-metin"><?php echo esc_html($tarih_str); ?></span>
                        </div>

                        <div class="kayit-orta-kullanici">
                            <?php if ( $user_id > 0 ) : ?>
                                <a href="<?php echo esc_url($user_url); ?>" class="katkici-profil-linki" title="<?php echo esc_attr($user_name); ?> Profilini İncele">
                                    <?php echo get_avatar($user_id, 24); ?>
                                    <span class="kullanici-adi"><?php echo esc_html($user_name); ?></span>
                                </a>
                            <?php else : ?>
                                <span class="katkici-anonim">👤 <?php echo esc_html($user_name); ?></span>
                            <?php endif; ?>
                            
                            <span class="islem-turu-rozet islem-<?php echo sanitize_html_class(strtolower($islem)); ?>">
                                <?php echo esc_html($islem); ?>
                            </span>
                        </div>

                        <div class="kayit-sag-ozet">
                            <span class="ozet-etiket">Özet:</span>
                            <span class="ozet-metin">"<?php echo esc_html($ozet); ?>"</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="wiki-bos-gecmis">
                <p>Bu maddeye ait henüz kayıtlı bir değişiklik geçmişi bulunmamaktadır.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// =========================================================================
// 5. ÖN YÜZ PROFİL GÜNCELLEME AJAX İŞLEYİCİSİ
// =========================================================================
function malatya_profil_guncelle_ajax_handler() {
    check_ajax_referer('malatya_ajax_nonce', 'nonce');

    if ( ! is_user_logged_in() ) {
        wp_send_json_error(array('message' => 'Lütfen önce giriş yapınız.'));
    }

    $current_user_id = get_current_user_id();
    $display_name    = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';
    $bio             = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $user_url        = isset($_POST['user_url']) ? esc_url_raw($_POST['user_url']) : '';

    if ( empty($display_name) ) {
        wp_send_json_error(array('message' => 'Görünen isim boş bırakılamaz.'));
    }

    $update_data = array(
        'ID'           => $current_user_id,
        'display_name' => $display_name,
        'user_url'     => $user_url,
        'description'  => $bio
    );

    $updated = wp_update_user($update_data);

    if ( is_wp_error($updated) ) {
        wp_send_json_error(array('message' => 'Profil güncellenirken bir sorun oluştu: ' . $updated->get_error_message()));
    }

    wp_send_json_success(array('message' => 'Profil bilgileriniz başarıyla güncellendi!'));
}
add_action('wp_ajax_malatya_profil_guncelle', 'malatya_profil_guncelle_ajax_handler');
