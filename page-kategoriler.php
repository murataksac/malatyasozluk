<?php
/*
Template Name: Kategoriler Sayfası
*/
get_header(); // [cite: 16]
get_sidebar(); // [cite: 25]
?>

<main class="site-main">
    <header class="page-header" style="margin-bottom: 30px; border-bottom: 1px solid #a2a9b1; padding-bottom: 10px;">
        <h1 class="page-title" style="font-family: 'Linux Libertine', Georgia, serif; font-size: 2.2em; margin:0;">
            <?php the_title(); ?>
        </h1>
        <p style="margin-top: 10px; font-size: 1.1em; color: #54595d;">Malatya Sözlük bünyesindeki tüm ana konular ve alt kategoriler:</p>
    </header>

    <div class="entry-content">
        <?php
        // Sadece en üst seviye (parent olmayan) ana kategorileri çekelim
        $ana_kategoriler = get_categories(array(
            'parent'     => 0,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'hide_empty' => 0
        ));

        if (!empty($ana_kategoriler)) :
        ?>
        
        <!-- Kategorilere Özel Gelişmiş Görünüm Stilleri -->
        <style>
            .wiki-kategori-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-top: 20px;
            }
            @media screen and (max-width: 768px) {
                .wiki-kategori-grid {
                    grid-template-columns: 1fr;
                }
            }
            .wiki-kategori-kart {
                background: #f8f9fa;
                border: 1px solid #a2a9b1;
                border-radius: 4px;
                padding: 18px;
                transition: all 0.2s ease-in-out;
            }
            .wiki-kategori-kart:hover {
                background: #fff;
                border-color: #3366cc; /* Kurumsal mavi vurgu */
                box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            }
            .wiki-kategori-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #c8ccd1;
                padding-bottom: 8px;
                margin-bottom: 12px;
            }
            .wiki-kategori-header h3 {
                margin: 0;
                font-family: 'Linux Libertine', Georgia, serif;
                font-size: 1.25em;
                border-bottom: none;
                padding-bottom: 0;
            }
            .wiki-kategori-header h3 a {
                color: #0645ad; /* [cite: 28] */
                text-decoration: none;
            }
            .wiki-kategori-header h3 a:hover {
                text-decoration: underline;
            }
            .wiki-madde-sayisi {
                background: #3366cc;
                color: #fff;
                font-size: 0.8em;
                font-weight: bold;
                padding: 3px 10px;
                border-radius: 12px;
            }
            .wiki-kategori-aciklama {
                font-size: 0.9em;
                color: #54595d;
                line-height: 1.5;
                margin-bottom: 15px;
            }
            /* ALT KATEGORİ STİLLERİ */
            .wiki-alt-kategoriler-baslik {
                font-size: 0.8em;
                font-weight: bold;
                color: #202122; /* [cite: 27] */
                margin-top: 15px;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .wiki-alt-kategori-listesi {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-bottom: 15px;
            }
            .wiki-alt-kategori-link {
                background: #eaf3ff;
                color: #0645ad;
                font-size: 0.8em;
                font-weight: 500;
                padding: 4px 10px;
                border-radius: 15px;
                border: 1px solid #a2a9b1;
                display: inline-block;
                text-decoration: none;
                transition: all 0.15s ease;
            }
            .wiki-alt-kategori-link:hover {
                background: #3366cc;
                color: #fff;
                border-color: #3366cc;
                text-decoration: none;
            }
            .wiki-son-maddeler-baslik {
                font-size: 0.8em;
                font-weight: bold;
                color: #202122;
                margin-top: 15px;
                margin-bottom: 6px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .wiki-kategori-son-maddeler {
                list-style: square;
                padding-left: 18px;
                margin: 0;
                font-size: 0.9em;
            }
            .wiki-kategori-son-maddeler li {
                margin-bottom: 5px;
            }
            .wiki-kategori-son-maddeler li a {
                color: #0645ad;
            }
        </style>

        <div class="wiki-kategori-grid">
            <?php foreach ($ana_kategoriler as $kat) : 
                // Bu ana kategoriye bağlı ALT kategorileri çekelim
                $alt_kategoriler = get_categories(array(
                    'parent'     => $kat->term_id,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => 0
                ));

                // Ana kategoriye ait en yeni 3 maddeyi çekelim [cite: 13]
                $son_maddeler = get_posts(array(
                    'category'       => $kat->term_id,
                    'posts_per_page' => 3,
                    'post_status'    => 'publish'
                ));
            ?>
                <div class="wiki-kategori-kart">
                    <div class="wiki-kategori-header">
                        <h3>
                            <a href="<?php echo esc_url(get_category_link($kat->term_id)); ?>">
                                📂 <?php echo esc_html($kat->name); ?>
                            </a>
                        </h3>
                        <span class="wiki-madde-sayisi"><?php echo $kat->count; ?> Madde</span>
                    </div>
                    
                    <?php if ($kat->description) : ?>
                        <p class="wiki-kategori-aciklama"><?php echo esc_html($kat->description); ?></p>
                    <?php endif; ?>

                    <!-- ALT KATEGORİ LİSTELEME -->
                    <?php if (!empty($alt_kategoriler)) : ?>
                        <div class="wiki-alt-kategoriler-baslik">Alt Kategoriler</div>
                        <div class="wiki-alt-kategori-listesi">
                            <?php foreach ($alt_kategoriler as $alt_kat) : ?>
                                <a class="wiki-alt-kategori-link" href="<?php echo esc_url(get_category_link($alt_kat->term_id)); ?>">
                                    📁 <?php echo esc_html($alt_kat->name); ?> (<?php echo $alt_kat->count; ?>)
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($son_maddeler)) : ?>
                        <div class="wiki-son-maddeler-baslik">Son Eklenen Maddeler</div>
                        <ul class="wiki-kategori-son-maddeler">
                            <?php foreach ($son_maddeler as $post) : setup_postdata($post); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </li>
                            <?php endforeach; wp_reset_postdata(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php else : ?>
            <p>Sitede henüz tanımlanmış bir kategori bulunmuyor.</p>
        <?php endif; ?>
    </div>
</main>

<?php 
get_footer(); // [cite: 10]
?>