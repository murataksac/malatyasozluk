<?php get_header(); ?>
<?php get_sidebar(); ?>

<main class="site-main">
    <?php if ( have_posts() ) : ?>

    <header class="archive-header">
        <?php 
        /* 1. EKMEK KIRINTISI (BREADCRUMB) 
           Alt kategorideyseniz üst kategoriyi gösterir */
        if ( is_category() ) {
            $this_cat = get_queried_object();
            if ( $this_cat->parent != 0 ) {
                $parent_cat = get_category($this_cat->parent);
                echo '<nav class="wiki-breadcrumb">';
                echo '<a href="' . get_category_link($parent_cat->term_id) . '">' . $parent_cat->name . '</a> &gt; ';
                echo '<span>' . $this_cat->name . '</span>';
                echo '</nav>';
            }
        }

        /* 2. ANSİKLOPEDİK BAŞLIK [cite: 3] */
        the_archive_title( '<h1 class="archive-title">', '</h1>' );

        /* 3. KATEGORİ AÇIKLAMASI [cite: 3] */
        the_archive_description( '<div class="archive-description">', '</div>' );
        ?>
    </header>

    <?php 
    /* 4. ALT KATEGORİLER LİSTESİ
       Eğer bu kategorinin altında başka kategoriler varsa onları listeler */
    $current_cat = get_queried_object();
    if ( is_category() ) {
        $sub_categories = get_categories( array('parent' => $current_cat->term_id) );
        if ( !empty($sub_categories) ) : ?>
            <div class="wiki-sub-categories">
                <strong>Alt Kategoriler:</strong>
                <ul>
                    <?php foreach ( $sub_categories as $sub_cat ) : ?>
                        <li>
                            <a href="<?php echo get_category_link($sub_cat->term_id); ?>" class="wiki-mavi">
                                <?php echo $sub_cat->name; ?> (<?php echo $sub_cat->count; ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; 
    } ?>

    <!-- 5. MADDE LİSTESİ (MODERN GRID) [cite: 3, 5] -->
    <div class="wiki-kategori-konteynir">
        <div class="wiki-liste-bilgi">
            Bu alanda toplam <strong><?php echo $wp_query->found_posts; ?></strong> madde listeleniyor.
        </div>
        
        <ul class="wiki-archive-grid">
            <?php
            while ( have_posts() ) : the_post();
                // template-parts/content-archive.php dosyasını çağırır [cite: 5]
                get_template_part( 'template-parts/content', 'archive' );
            endwhile;
            ?>
        </ul>
    </div>

    <!-- 6. SAYFALAMA -->
    <div class="wiki-sayfalama-alani">
        <?php the_posts_pagination( array( 
            'mid_size'  => 2, 
            'prev_text' => '← Önceki', 
            'next_text' => 'Sonraki →' 
        ) ); ?>
    </div>

    <?php else : ?>
        <?php get_template_part( 'template-parts/content', 'none' ); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>