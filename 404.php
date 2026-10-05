<?php get_header(); ?>
<?php get_sidebar(); ?>

<main class="site-main">
    <section class="error-404 not-found">
        <header class="page-header" style="margin-bottom: 20px; border-bottom: 1px solid #a2a9b1; padding-bottom: 10px;">
            <h1 class="page-title" style="font-family: 'Linux Libertine', Georgia, serif; font-size: 2.2em; margin:0; color:#dd3333;">
                Madde Bulunamadı (404)
            </h1>
        </header>

        <div class="page-content" style="font-size:1.1em; line-height:1.8;">
            <p>Malatya Sözlük'te aradığınız başlığa ait bir sayfa veya madde henüz oluşturulmamış.</p>
            
            <?php 
            // URL'den aranmak istenen kelimeyi tahmin edip butona yazdıralım
            $url_yolu = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $tahmini_baslik = ucwords(str_replace(array('-', '_'), ' ', $url_yolu));
            if(empty($tahmini_baslik)) { $tahmini_baslik = "Yeni"; }
            ?>

            <div style="background-color: #eaf3ff; border-left: 4px solid #3366cc; padding: 15px; margin: 20px 0;">
                <p style="margin-top:0;">Bu maddeyi başlatarak ansiklopediye katkıda bulunmak ister misiniz?</p>
                <a href="<?php echo esc_url(home_url('/madde-olustur/?baslik=' . urlencode($tahmini_baslik))); ?>" style="display: inline-block; background-color: #3366cc; color: #fff; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 2px;">
                    + "<?php echo esc_html($tahmini_baslik); ?>" Maddesini Oluştur
                </a>
            </div>

            <div class="widget_search" style="margin-top:20px;">
                <?php get_search_form(); ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>