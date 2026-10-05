<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
	<!-- Android (Chrome ve diğerleri) için -->
	<meta name="theme-color" content="#f97316">
	<!-- iOS (Safari) için -->
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- Google Tag -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VQBDJYJFCK"></script>
    <script>
      window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date()); gtag('config', 'G-VQBDJYJFCK');
    </script>
</head>
<body <?php body_class(); ?>>

<header class="site-header">
    <div class="header-container">
        
        <!-- MOBİL TOGGLE & MARKA LOGO -->
        <div class="header-branding-wrapper">
            <button id="mobile-menu-toggle" class="mobile-menu-toggle" type="button" aria-label="Menüyü Aç/Kapat">
                <span class="toggle-icon">☰</span>
                <span class="toggle-text">Menü</span>
            </button>

            <div class="site-branding">
                <?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
                    <a href="<?php echo home_url('/'); ?>" class="brand-logo-link">
                        <span class="brand-badge">MS</span>
                        <span class="brand-title"><?php bloginfo('name'); ?></span>
                    </a>
                <?php endif; ?>
                <span class="site-slogan-badge"><?php bloginfo('description'); ?></span>
            </div>
        </div>

        <!-- MODERN CANLI ARAMA ÇUBUĞU -->
        <div class="header-search-wrapper">
            <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="header-search-form">
                <span class="search-input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <input type="search" placeholder="Maddelerde veya kelimelerde ara..." name="s" id="header-search-input" autocomplete="off">
                <span class="search-kbd-badge"><kbd>Ctrl</kbd> <kbd>K</kbd></span>
            </form>
            <div id="wiki-canli-arama-kutusu" class="floating-search-results"></div>
        </div>

        <!-- NAVİGASYON VE KULLANICI AKSİYONLARI -->
        <div class="header-actions-wrapper">
            <nav class="header-nav">
                <?php if ( has_nav_menu('ust-menu') ) {
                    wp_nav_menu(array('theme_location' => 'ust-menu', 'container' => false, 'menu_class' => 'ust-ana-menu', 'depth' => 2));
                } ?>
            </nav>

            <div class="header-user-actions">
                <button type="button" class="wiki-dark-mode-toggle" onclick="wikiToggleDarkMode()" title="Gece/Gündüz Modu" style="margin-right: 5px;">🌓</button>
                <a href="<?php echo home_url('/madde-olustur'); ?>" class="btn-create-entry" title="Yeni Madde Ekle">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Madde Yaz</span>
                </a>

                <?php if ( is_user_logged_in() ) : 
                    $current_user = wp_get_current_user();
                    $profil_url = get_author_posts_url($current_user->ID);
                ?>
                    <a href="<?php echo esc_url($profil_url); ?>" class="header-profile-link" title="Profilimi ve Katkılarımı Görüntüle">
                        <span class="profile-avatar-thumb"><?php echo get_avatar($current_user->ID, 24); ?></span>
                        <span class="profile-username"><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></span>
                    </a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="header-auth-btn auth-logout" title="Oturumu Kapat">Çıkış</a>
                <?php else : ?>
                    <a href="<?php echo home_url('/ms/giris-kayit'); ?>" class="header-auth-btn auth-login">Giriş Yap</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</header>
<div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>
<div class="main-container"> <!-- Sayfa içeriği burada başlar -->
