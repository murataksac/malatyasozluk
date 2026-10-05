<?php
/* Template Name: Giriş ve Kayıt Sayfası */
get_header();

if ( is_user_logged_in() ) { 
    wp_redirect( home_url() ); 
    exit; 
}

$hata = ''; 
$basari = '';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    // Giriş Yap İşlemi
    if ( isset($_POST['mly_giris_yap']) ) {
        if (!isset($_POST['mly_login_nonce']) || !wp_verify_nonce($_POST['mly_login_nonce'], 'mly_login_action')) {
            $hata = "Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.";
        } else {
            $user_login = sanitize_user($_POST['log']);
            $user_pass  = $_POST['pwd']; // esc_sql kaldırıldı: özel karakterli şifrelerin bozulmasını önler
            
            $creds = array(
                'user_login'    => $user_login,
                'user_password' => $user_pass,
                'remember'      => true
            );
            $user = wp_signon( $creds, is_ssl() );
            if ( is_wp_error($user) ) { 
                $hata = "Kullanıcı adı veya şifre hatalı."; 
            } else { 
                wp_redirect( home_url() ); 
                exit; 
            }
        }
    }
    
    // Kayıt Ol İşlemi
    if ( isset($_POST['mly_kayit_ol']) ) {
        if (!isset($_POST['mly_register_nonce']) || !wp_verify_nonce($_POST['mly_register_nonce'], 'mly_register_action')) {
            $hata = "Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.";
        } else {
            $user_login = sanitize_user($_POST['user_login']);
            $user_email = sanitize_email($_POST['user_email']);
            $user_pass  = $_POST['user_pass'];
            
            if ( empty($user_login) || empty($user_email) || empty($user_pass) ) {
                $hata = "Lütfen tüm zorunlu alanları doldurun.";
            } elseif ( username_exists($user_login) ) { 
                $hata = "Bu kullanıcı adı zaten alınmış."; 
            } elseif ( email_exists($user_email) ) { 
                $hata = "Bu e-posta adresi zaten kayıtlı."; 
            } else {
                $user_id = wp_create_user( $user_login, $user_pass, $user_email );
                if ( !is_wp_error($user_id) ) { 
                    $basari = "Kaydınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz."; 
                } else { 
                    $hata = "Kayıt sırasında bir hata oluştu: " . esc_html($user_id->get_error_message()); 
                }
            }
        }
    }
}
?>

<style>
    .auth-card {
        max-width: 420px; margin: 60px auto; background: #fff;
        border: 1px solid #a2a9b1; border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;
    }
    .auth-header { padding: 30px 20px; text-align: center; background: #f8f9fa; border-bottom: 1px solid #eaecf0; }
    .auth-header h1 { font-size: 1.6em; margin: 0; color: #000; }
    
    .wiki-sekme-menusu { display: flex; border-bottom: 1px solid #eaecf0; }
    .wiki-sekme-buton {
        flex: 1; padding: 15px; border: none; background: #fff; cursor: pointer;
        font-weight: bold; color: #54595d; transition: all 0.2s;
    }
    .wiki-sekme-buton.aktif { color: #3366cc; border-bottom: 3px solid #3366cc; background: #f0f7ff; }
    
    .auth-body { padding: 30px; }
    .wiki-form-yapisi label { display: block; font-size: 0.9em; font-weight: 600; margin-bottom: 8px; color: #202122; }
    .wiki-form-yapisi input {
        width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #a2a9b1;
        border-radius: 4px; font-size: 1em; transition: border-color 0.2s;
    }
    .wiki-form-yapisi input:focus { border-color: #3366cc; outline: none; box-shadow: 0 0 0 3px rgba(51,102,204,0.1); }
    
    .wiki-submit-button {
        width: 100%; padding: 12px; background: #3366cc; color: #fff; border: none;
        border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1.1em;
    }
    .wiki-submit-button:hover { background: #2a4b8d; }
    .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; text-align: center; }
    .alert-error { background: #fee7e6; color: #b32424; border: 1px solid #f8c2c2; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
</style>

<main class="site-main">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Malatya Sözlük</h1>
            <p style="font-size: 0.9em; color: #666; margin-top: 5px;">Dijital hafızaya katkıda bulunmaya başla.</p>
        </div>

        <div class="wiki-sekme-menusu">
            <button class="wiki-sekme-buton aktif" onclick="wikiSekmeAc(event, 'giris-formu')">Giriş Yap</button>
            <button class="wiki-sekme-buton" onclick="wikiSekmeAc(event, 'kayit-formu')">Kayıt Ol</button>
        </div>

        <div class="auth-body">
            <?php if($hata) echo '<div class="alert alert-error">'.esc_html($hata).'</div>'; ?>
            <?php if($basari) echo '<div class="alert alert-success">'.esc_html($basari).'</div>'; ?>

            <div id="giris-formu" class="wiki-sekme-alani aktif-sekme">
                <form method="post" class="wiki-form-yapisi">
                    <?php wp_nonce_field('mly_login_action', 'mly_login_nonce'); ?>
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="log" required>
                    <label>Şifre</label>
                    <input type="password" name="pwd" required>
                    <button type="submit" name="mly_giris_yap" class="wiki-submit-button">Oturum Aç</button>
                </form>
            </div>

            <div id="kayit-formu" class="wiki-sekme-alani" style="display:none;">
                <form method="post" class="wiki-form-yapisi">
                    <?php wp_nonce_field('mly_register_action', 'mly_register_nonce'); ?>
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="user_login" required>
                    <label>E-posta Adresi</label>
                    <input type="email" name="user_email" required>
                    <label>Şifre</label>
                    <input type="password" name="user_pass" required>
                    <button type="submit" name="mly_kayit_ol" class="wiki-submit-button">Hesap Oluştur</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>