<aside class="site-sidebar">
    
    <!-- MOBİL BAŞLIK VE KAPATMA BUTONU -->
    <div class="sidebar-mobile-header">
        <span>Malatya Sözlük Menü</span>
        <button type="button" id="sidebar-close-btn" class="sidebar-close-btn" aria-label="Menüyü Kapat">✕</button>
    </div>

    <!-- 1. İÇİNDEKİLER TABLOSU (TOC) -->
    <?php if ( is_single() ) : ?>
    <section class="wiki-widget wiki-sidebar-toc-container" style="display: none;">
        <h3 class="widget-title">
            <span>İçindekiler</span>
            <span style="font-size:0.8em;">[<a href="#" id="wiki-toc-toggle">gizle</a>]</span>
        </h3>
        <div id="wiki-sidebar-toc"></div>
    </section>
    <?php endif; ?>
	
    <!-- 2. YAZI ETİKETLERİ BULUTU -->	
	<?php if ( is_single() ) : ?>
		<?php 
		$post_tags = get_the_tags();
		if ( $post_tags ) : ?>
			<section class="wiki-widget wiki-sidebar-tags-container">
				<h3 class="widget-title">İlgili Etiketler</h3>
				<div class="wiki-tag-cloud">
					<?php foreach ( $post_tags as $tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="wiki-tag-item">
							#<?php echo esc_html( $tag->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>
	
    <!-- 3. SABİT ANA MENÜ -->
    <section class="wiki-widget wiki-sidebar-tags-container">
        <h3 class="widget-title">Menü</h3>
        <?php
        if ( has_nav_menu( 'sol-menu' ) ) { 
            wp_nav_menu( array(
                'theme_location' => 'sol-menu',
                'menu_class'     => 'wiki-dinamik-menu', 
                'container'      => false, 
                'fallback_cb'    => false 
            ) );
        } else {
            echo '<p style="font-size: 0.85em; color: #666;">Lütfen panelden (Görünüm > Menüler) bir menü oluşturup "Sol Menü" olarak işaretleyin.</p>';
        }
        ?>
    </section>

    <!-- 4. PANEL BİLEŞENLERİ (WIDGETS) -->
    <?php 
    if ( is_active_sidebar( 'sol-sidebar' ) ) : ?>
        <div class="wiki-custom-widgets">
            <?php dynamic_sidebar( 'sol-sidebar' ); ?>
        </div>
    <?php endif; ?>

</aside>