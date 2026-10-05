<?php
/**
 * Kategori ve arşiv sayfalarındaki her bir maddenin "Liste" görünümü.
 * Sadece liste elemanı (li) ve tıklanabilir madde adını barındırır.
 */
?>
<li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <a href="<?php the_permalink(); ?>" class="wiki-mavi" style="font-size: 1.1em;">
        <?php the_title(); ?>
    </a>
</li>