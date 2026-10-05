</div> <!-- .main-container sonu [cite: 10, 31] -->

<style>
    .site-footer {
        background: #f8f9fa;
        border-top: 1px solid #a2a9b1;
        padding: 30px 20px;
        margin-top: 50px;
        text-align: center;
        color: #54595d;
        font-size: 13px;
    }
    .footer-container { max-width: 1200px; margin: 0 auto; }
    
    .footer-links {
        display: flex;
        justify-content: center;
        gap: 25px;
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
        flex-wrap: wrap;
    }
    .footer-links a { 
        color: #0645ad; 
        font-weight: 500; 
        text-decoration: none; 
    }
    .footer-links a:hover { text-decoration: underline; }

    .footer-copy { margin-bottom: 5px; color: #202122; }
    .lisans-uyari { font-size: 11px; opacity: 0.8; }
</style>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Footer Menüsü [cite: 10] -->
        <?php if ( has_nav_menu( 'footer_menu' ) ) :
            wp_nav_menu( array(
                'theme_location' => 'footer_menu',
                'container'      => 'nav',
                'menu_class'     => 'footer-links',
                'depth'          => 1,
                'fallback_cb'    => false
            ) );
        endif; ?>

        <div class="footer-info">
            <p class="footer-copy">
                &copy; <?php echo date('Y'); ?> 
                <strong><a href="<?php echo home_url('/'); ?>" style="color: inherit; text-decoration: none;">Malatya Sözlük</a></strong> 
                — Malatya'nın Dijital Hafızası.
            </p>
            <p class="lisans-uyari">
                İçerikler aksi belirtilmedikçe kopyalanabilir ve geliştirilebilir.
            </p>
        </div>
    </div>
</footer>

<div class="wiki-print-footer-fixed" style="display:none;">
    <strong>Kaynak:</strong> <?php 
        global $wp;
        $current_url = home_url( add_query_arg( array(), $wp->request ) );
        echo esc_url( $current_url ); 
    ?>
</div>

<!-- MOBİL ALT NAVİGASYON (HIZLI MENÜ) -->
<nav class="mobile-bottom-nav">
    <a href="<?php echo home_url('/'); ?>" class="nav-item">
        <span class="icon">🏠</span>
        <span class="text">Ana Sayfa</span>
    </a>
    <a href="<?php echo home_url('/?rastgele=1'); ?>" class="nav-item">
        <span class="icon">🎲</span>
        <span class="text">Rastgele</span>
    </a>
    <a href="#" onclick="document.getElementById('wiki-canli-arama-kutusu').style.display = 'block'; document.getElementById('wiki-arama-input').focus(); return false;" class="nav-item">
        <span class="icon">🔍</span>
        <span class="text">Ara</span>
    </a>
    <?php if ( is_user_logged_in() ) : 
        $current_user = wp_get_current_user();
        $profil_link = get_author_posts_url($current_user->ID);
    ?>
    <a href="<?php echo esc_url($profil_link); ?>" class="nav-item">
        <span class="icon">👤</span>
        <span class="text">Profil</span>
    </a>
    <?php else : ?>
    <a href="<?php echo wp_login_url(); ?>" class="nav-item">
        <span class="icon">🔑</span>
        <span class="text">Giriş</span>
    </a>
    <?php endif; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>