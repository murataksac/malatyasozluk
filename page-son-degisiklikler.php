<?php
/**
 * Template Name: Son Değişiklikler Sayfası
 * Malatya Sözlük - Canlı Değişiklikler Akışı
 */
get_header(); 
get_sidebar(); 
?>

<main class="site-main">
    <header class="page-header wiki-son-degisiklikler-header" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
            <div style="display: inline-flex; align-items: center; gap: 8px;">
                <span style="background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span>
                    <span>Canlı İçerik Akışı</span>
                </span>
            </div>
            <a href="<?php echo home_url('/madde-olustur'); ?>" class="btn-madde-yaz-header" style="background: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>+ Yeni Madde Yaz</span>
            </a>
        </div>

        <h1 class="page-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.85rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; border: none; padding: 0;">
            Son Değişiklikler
        </h1>
        <p style="color: #64748b; font-size: 14px; margin: 0;">
            Malatya Sözlük bünyesinde yakın zamanda oluşturulan, güncellenen veya zenginleştirilen ansiklopedi maddeleri:
        </p>
    </header>

    <div class="entry-content">
        <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 25,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'paged'          => $paged
        );
        $son_degisiklikler = new WP_Query($args);

        if ($son_degisiklikler->have_posts()) :
        ?>
            <div class="wiki-son-degisiklikler-liste" style="display: flex; flex-direction: column; gap: 12px;">
                <?php
                while ($son_degisiklikler->have_posts()) : $son_degisiklikler->the_post();
                    $post_id      = get_the_ID();
                    $author_id    = get_the_author_meta('ID');
                    $author_name  = get_the_author_meta('display_name') ?: get_the_author_meta('user_login');
                    $author_url   = get_author_posts_url($author_id);
                    $mod_time     = get_the_modified_time('U');
                    $time_diff    = human_time_diff($mod_time, current_time('timestamp')) . ' önce';
                    $full_date    = get_the_modified_date('d F Y, H:i');
                    $categories   = get_the_category();
                    $gecmis_url   = add_query_arg('gecmis', '1', get_permalink());
                    $baglanti_url = add_query_arg('baglantilar', '1', get_permalink());
                ?>
                    <div class="wiki-degisiklik-karti" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; transition: all 0.2s ease; box-shadow: 0 2px 6px rgba(15,23,42,0.02);">
                        
                        <!-- Sol Bilgi Alanı -->
                        <div style="display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 260px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <a href="<?php the_permalink(); ?>" style="font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none;" class="madde-link-vurgu">
                                    <?php the_title(); ?>
                                </a>
                                <?php if ( ! empty($categories) ) : ?>
                                    <span style="background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 4px;">
                                        <?php echo esc_html($categories[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #64748b; flex-wrap: wrap;">
                                <span title="<?php echo esc_attr($full_date); ?>" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🕒 <?php echo esc_html($time_diff); ?></span>
                                </span>
                                <span style="color: #cbd5e1;">•</span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <?php echo get_avatar($author_id, 18, '', '', array('style' => 'border-radius:50%; vertical-align:middle;')); ?>
                                    <a href="<?php echo esc_url($author_url); ?>" style="color: #334155; text-decoration: none; font-weight: 600;">
                                        <?php echo esc_html($author_name); ?>
                                    </a>
                                </span>
                            </div>
                        </div>

                        <!-- Sağ Aksiyon Butonları -->
                        <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                            <a href="<?php the_permalink(); ?>" class="btn-aksiyon-kucuk" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #2563eb; font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 6px; text-decoration: none;" title="Maddeyi Oku">
                                Oku
                            </a>
                            <a href="<?php echo esc_url($gecmis_url); ?>" class="btn-aksiyon-kucuk" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; font-size: 12px; font-weight: 600; padding: 5px 10px; border-radius: 6px; text-decoration: none;" title="Tüm Değişiklik Geçmişi">
                                Geçmiş
                            </a>
                            <a href="<?php echo esc_url($baglanti_url); ?>" class="btn-aksiyon-kucuk" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; font-size: 12px; font-weight: 600; padding: 5px 8px; border-radius: 6px; text-decoration: none;" title="Sayfaya Verilen Bağlantılar">
                                🔗
                            </a>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($son_degisiklikler->max_num_pages > 1) : ?>
                <div class="wiki-sayfalama-alani" style="margin-top: 30px; text-align: center;">
                    <div class="navigation pagination">
                        <div class="nav-links">
                            <?php
                            echo paginate_links(array(
                                'total'     => $son_degisiklikler->max_num_pages,
                                'current'   => $paged,
                                'mid_size'  => 2,
                                'prev_text' => '← Önceki',
                                'next_text' => 'Sonraki →',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php 
            wp_reset_postdata();
        else : 
        ?>
            <div style="padding: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; text-align: center; color: #64748b;">
                Henüz kayıtlı bir değişiklik bulunmuyor.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>