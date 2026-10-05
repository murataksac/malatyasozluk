<?php get_header(); ?>

<?php get_sidebar(); ?>

<main class="site-main">
    <header class="page-header" style="margin-bottom: 30px; border-bottom: 1px solid #a2a9b1; padding-bottom: 10px;">
        <h1 class="page-title" style="font-family: 'Linux Libertine', Georgia, serif; font-size: 2.2em; margin:0;">
            Arama Sonuçları: <em><?php echo get_search_query(); ?></em>
        </h1>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="wiki-arama-listesi" style="padding-left: 20px;">
            <ul style="list-style-type: square; line-height: 1.8;">
                <?php
                // Arama sonuçlarını liste halinde göster (content-archive'i kullanıyoruz)
                while ( have_posts() ) : the_post();
                    get_template_part( 'template-parts/content', 'archive' );
                endwhile;
                ?>
            </ul>
        </div>

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