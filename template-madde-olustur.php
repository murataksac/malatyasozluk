<?php
/*
Template Name: Madde Oluşturma Formu
*/
get_header(); 
get_sidebar(); 

$mesaj = '';
$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;

// Form gönderimi kontrolü
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wiki_baslik'])) {
    
    // 1. Güvenlik: Nonce Kontrolü (CSRF Koruması)
    if (!isset($_POST['madde_olustur_nonce']) || !wp_verify_nonce($_POST['madde_olustur_nonce'], 'madde_olustur_action')) {
        wp_die("Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyiniz.");
    }
    
    // 2. Güvenlik: Honeypot kontrolü
    if (!empty($_POST['honeypot_field'])) {
        wp_die("Spam botu tespit edildi.");
    }

    // 3. Güvenlik Sorusu Kontrolü (Giriş yapmamış kullanıcılar için)
    $dogrulama_gecerli = $is_logged_in || (isset($_POST['mly_dogrulama']) && trim($_POST['mly_dogrulama']) === '44');

    if ( ! $dogrulama_gecerli ) {
        $mesaj = '<div class="wiki-alert-box wiki-alert-error" style="margin-bottom:20px;">
        <strong>Hata:</strong> Güvenlik sorusunu hatalı cevapladınız. Malatya\'nın plaka kodu 44\'tür.</div>';
    } else {
        // Gelen verileri temizle
        $baslik = sanitize_text_field($_POST['wiki_baslik']);
        $icerik = isset($_POST['wiki_icerik']) ? wp_unslash($_POST['wiki_icerik']) : '';
        
        if ( $is_logged_in ) {
            $yazar_id = $current_user->ID;
            $yazar_adi = $current_user->display_name ?: $current_user->user_login;
        } else {
            $yazar_id = 1;
            $yazar_adi = !empty($_POST['wiki_yazar']) ? sanitize_text_field($_POST['wiki_yazar']) : "Anonim Katkıcı";
        }

        if ( empty(trim($baslik)) || empty(trim(strip_tags($icerik))) ) {
            $mesaj = '<div class="wiki-alert-box wiki-alert-error" style="margin-bottom:20px;">
            <strong>Hata:</strong> Lütfen madde başlığı ve içeriğini boş bırakmayınız.</div>';
        } else {
            $yeni_madde = array(
                'post_title'    => $baslik,
                'post_content'  => $icerik,
                'post_status'   => 'draft', // Editör onayından sonra yayınlanır
                'post_author'   => $yazar_id,
                'post_type'     => 'post'
            );

            $madde_id = wp_insert_post($yeni_madde);

            if ($madde_id && !is_wp_error($madde_id)) {
                update_post_meta($madde_id, '_wiki_olusturan_yazar', $yazar_adi);
                
                // Katkı kaydını ekle
                if ( function_exists('malatya_wiki_katki_kaydet') ) {
                    malatya_wiki_katki_kaydet($madde_id, $yazar_id, 'Madde Oluşturuldu', 'Yeni madde sözlüğe eklendi.');
                }

                $mesaj = '<div class="wiki-alert-box wiki-alert-success" style="margin-bottom:20px; font-size:1.05em; padding:18px;">
                <strong>✓ Tebrikler!</strong> "<strong>' . esc_html($baslik) . '</strong>" maddesi başarıyla oluşturuldu ve editör onayına gönderildi. İncelemenin ardından Malatya Sözlük hafızasına eklenecektir. Katkılarınız için teşekkür ederiz!</div>';
            } else {
                $mesaj = '<div class="wiki-alert-box wiki-alert-error" style="margin-bottom:20px;">
                <strong>Hata:</strong> Madde kaydedilirken bir sorun oluştu. Lütfen tekrar deneyin.</div>';
            }
        }
    }
}

$istenen_baslik = isset($_GET['baslik']) ? sanitize_text_field($_GET['baslik']) : '';
?>

<main class="site-main">
    <header class="entry-header" style="margin-bottom:25px;">
        <h1 class="entry-title" style="font-size:2rem; font-weight:800; color:#0f172a;">Yeni Madde Oluştur</h1>
        <p style="color:#64748b; font-size:1rem; margin-top:6px; line-height:1.6;">
            Malatya'nın dijital hafızasını zenginleştirin! Bildiğiniz bir tarihi yapıyı, şahsiyeti, coğrafi mekânı veya yerel kelimeyi aşağıdaki ansiklopedi editörü ile yazıp gönderebilirsiniz.
        </p>
    </header>

    <div class="entry-content">
        <?php echo $mesaj; ?>

        <form method="post" action="" class="wiki-madde-olustur-form" style="background:#ffffff; padding:25px; border:1px solid #cbd5e1; border-radius:12px; box-shadow:0 4px 15px -3px rgba(15,23,42,0.04);">
            <?php wp_nonce_field('madde_olustur_action', 'madde_olustur_nonce'); ?>

            <div style="margin-bottom: 20px;">
                <label for="wiki_baslik_input" style="display:block; font-weight:700; margin-bottom:8px; font-size:14px; color:#1e293b;">
                    Madde Başlığı <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" id="wiki_baslik_input" name="wiki_baslik" value="<?php echo esc_attr($istenen_baslik); ?>" required placeholder="Örn: Battalgazi Ulu Camii, Darende Tohma Çayı..." style="width:100%; padding:12px 16px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:15px; font-weight:600; box-sizing:border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:700; margin-bottom:8px; font-size:14px; color:#1e293b;">
                    Ansiklopedik İçerik <span style="color:#ef4444;">*</span>
                </label>
                
                <div class="wiki-editor-container-wrapper">
                    <?php
                    $editor_id = 'wiki_icerik';
                    $editor_settings = array(
                        'textarea_name' => 'wiki_icerik',
                        'textarea_rows' => 18,
                        'media_buttons' => false,
                        'teeny'         => false,
                        'quicktags'     => false,
                        'tinymce'       => array(
                            'wpautop'             => true,
                            'verify_html'         => false,
                            'toolbar1'            => 'bold,italic,wiki_h2_butonu,wiki_h3_butonu,bullist,numlist,blockquote,|,wiki_ref_butonu,wiki_link_butonu,wiki_piped_link,wiki_sablon_butonu,wiki_bilgi_kutusu,|,removeformat,undo,redo',
                            'toolbar2'            => '',
                        ),
                    );
                    wp_editor( '', $editor_id, $editor_settings );
                    ?>
                </div>
                <p style="color:#64748b; font-size:12.5px; margin-top:8px;">
                    💡 <strong>İpucu:</strong> Madde içi bağlantı vermek için <code>[[Madde Adı]]</code>, kaynak belirtmek için araç çubuğundaki <strong>📚 [ref] Kaynak</strong> butonunu kullanabilirsiniz.
                </p>
            </div>

            <div style="display:none !important; visibility:hidden !important; position:absolute; left:-9999px;">
                <label>Lütfen bu alanı boş bırakın:</label>
                <input type="text" name="honeypot_field" tabindex="-1" autocomplete="off">
            </div>

            <?php if ( ! $is_logged_in ) : ?>
                <div style="margin-bottom: 20px; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
                    <div style="margin-bottom: 14px;">
                        <label for="wiki_yazar_input" style="display:block; font-weight:700; margin-bottom:6px; font-size:13.5px; color:#334155;">
                            Adınız / Mahlasınız (İsteğe Bağlı):
                        </label>
                        <input type="text" id="wiki_yazar_input" name="wiki_yazar" placeholder="Örn: Ahmet Yılmaz" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;">
                    </div>
                    
                    <div>
                        <label for="mly_dogrulama_input" style="display:block; font-weight:700; margin-bottom:6px; font-size:13.5px; color:#334155;">
                            Güvenlik Sorusu: Malatya'nın plaka kodu kaçtır? <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" id="mly_dogrulama_input" name="mly_dogrulama" required placeholder="44" style="width:140px; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;">
                    </div>
                </div>
            <?php else : ?>
                <div style="margin-bottom: 20px; background:#eff6ff; padding:12px 16px; border-radius:8px; border:1px solid #bfdbfe; font-size:13.5px; color:#1e40af; display:flex; align-items:center; gap:8px;">
                    <span>👤 Katkı Sahibi:</span>
                    <strong><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></strong>
                    <span style="color:#60a5fa;">(@<?php echo esc_html($current_user->user_login); ?>)</span>
                </div>
            <?php endif; ?>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="wiki-btn-primary" style="padding:12px 28px; font-size:15px; font-weight:700; cursor:pointer;">
                    📤 Maddeyi Onaya Gönder
                </button>
            </div>
        </form>
    </div>
</main>

<?php get_footer(); ?>