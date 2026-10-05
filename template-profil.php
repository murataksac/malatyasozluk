<?php
/* Template Name: Kullanıcı Profili */
get_header();
get_sidebar();

// Giriş yapan kullanıcıyı al veya URL'den kullanıcı ara
$current_user = wp_get_current_user();
if ( !is_user_logged_in() ) {
    wp_redirect( home_url('/giris-yap') );
    exit;
}
?>

<main class="site-main">
    <header class="entry-header" style="border-bottom: 1px solid #a2a9b1; margin-bottom: 20px;">
        <h1 class="entry-title">Kullanıcı: <?php echo esc_html($current_user->display_name); ?></h1>
    </header>

    <!-- PROFİL SEKME BUTONLARI -->
    <div class="wiki-sekme-menusu">
        <button class="wiki-sekme-buton aktif" onclick="wikiSekmeAc(event, 'profil-ozet')">Özet</button>
        <button class="wiki-sekme-buton" onclick="wikiSekmeAc(event, 'katkilar')">Katkılar</button>
    </div>

    <div id="profil-ozet" class="wiki-sekme-alani aktif-sekme" style="display:block;">
        <div class="wiki-article-container" style="display: flex; gap: 20px; background: #f8f9fa; padding: 20px; border: 1px solid #a2a9b1;">
            <div class="profil-avatar">
                <?php echo get_avatar($current_user->ID, 120); ?>
            </div>
            <div class="profil-detay">
                <p><strong>Kullanıcı Adı:</strong> <?php echo $current_user->user_login; ?></p>
                <p><strong>E-posta:</strong> <?php echo $current_user->user_email; ?></p>
                <p><strong>Kayıt Tarihi:</strong> <?php echo date('j F Y', strtotime($current_user->user_registered)); ?></p>
            </div>
        </div>
    </div>

    <div id="katkilar" class="wiki-sekme-alani" style="display:none;">
        <h3>Son Yazdığınız Maddeler</h3>
        <ul style="list-style: square; padding-left: 20px;">
            <?php
            $user_posts = get_posts(array('author' => $current_user->ID, 'posts_per_page' => 10));
            if ($user_posts) :
                foreach ($user_posts as $post) : setup_postdata($post); ?>
                    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> (<?php echo get_the_date(); ?>)</li>
                <?php endforeach; wp_reset_postdata();
            else :
                echo "Henüz bir madde oluşturmamışsınız.";
            endif; ?>
        </ul>
    </div>
</main>

<?php get_footer(); ?>