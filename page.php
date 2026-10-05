<?php
/**
 * Standart Sayfa Şablonu (Page Template)
 * Malatya Sözlük yerel ansiklopedi projesi için optimize edilmiştir.
 */
get_header(); 
get_sidebar(); // Sol taraftaki dinamik menüyü buraya da çekiyoruz
?>

<main class="site-main">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php body_class('wiki-article wiki-page-content'); ?>>
            
            <header class="entry-header" style="border-bottom: 1px solid #a2a9b1; margin-bottom: 20px; padding-bottom: 8px;">
                <h1 class="entry-title" style="font-family: 'Linux Libertine', 'Georgia', 'Times', serif; font-size: 2em; font-weight: normal; margin: 0; color: #000;">
                    <?php the_title(); ?>
                </h1>
            </header>

            <div class="entry-content" style="line-height: 1.6; color: #202122;">
                <?php 
                // Sayfa içeriğini basar, shortcode'ları ve [[iç linkleri]] çalıştırır
                the_content(); 
                ?>
            </div>

            <?php 
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        </article>

    <?php endwhile; else : ?>
        <p>Aradığınız sayfa bulunamadı.</p>
    <?php endif; ?>
</main>

<?php 
get_footer(); 
?>