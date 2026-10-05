<?php
/**
 * Template Name: Şablon
 * Malatya Sözlük - Ansiklopedik Şablon Sayfası
 */
get_header(); 
get_sidebar();
?>

<main class="site-main">
    <?php while ( have_posts() ) : the_post(); 
        $post_id      = get_the_ID();
        $sayfa_link   = get_permalink($post_id);
        $duzenle_link = current_user_can('edit_pages') ? admin_url('post.php?post=' . $post_id . '&action=edit') : $sayfa_link;
        $sablon_kodu  = '{{' . get_the_title() . '}}';
    ?>
    <article id="post-<?php the_ID(); ?>" class="wiki-sablon-sayfasi">
        
        <!-- ŞABLON ÜST BAŞLIK ALANI -->
        <header class="page-header" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #bfdbfe;">
                        🧩 Ansiklopedi Şablonu
                    </span>
                    <span style="font-size: 12px; color: #94a3b8;">ID: #<?php echo $post_id; ?></span>
                </div>

                <!-- HIZLI AKSİYON BUTONLARI -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="<?php echo esc_url($sayfa_link); ?>" class="wiki-btn-aksiyon" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>↗ Sayfaya Git</span>
                    </a>
                    <?php if ( current_user_can('edit_pages') ) : ?>
                        <a href="<?php echo esc_url($duzenle_link); ?>" class="wiki-btn-aksiyon" style="background: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <span>✎ Şablonu Düzenle</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <h1 class="page-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.8rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; border: none; padding: 0;">
                Şablon: <?php the_title(); ?>
            </h1>
            <p style="color: #64748b; font-size: 14px; margin: 0;">
                Bu şablon, ilgili ansiklopedi maddelerinin en altında veya içerisinde ortak bilgi kutusu/navigasyon olarak kullanılır.
            </p>
        </header>

        <!-- KULLANIM REHBERİ KUTUSU -->
        <div class="wiki-sablon-rehber" style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 13.5px; color: #334155;">
                    💡 <strong>Nasıl Kullanılır?</strong> Bu şablonu herhangi bir maddeye yerleştirmek için metin içerisine aşağıdaki kodu ekleyin:
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <code style="background: #ffffff; border: 1.5px solid #93c5fd; padding: 4px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; color: #1e40af;"><?php echo esc_html($sablon_kodu); ?></code>
                </div>
            </div>
        </div>

        <!-- ŞABLON GÖRSEL ÖNİZLEME ALANI -->
        <div class="wiki-sablon-govde" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04);">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                Şablon İçerik Önizlemesi
            </div>
            
            <div class="entry-content" style="font-size: 14.5px; line-height: 1.7; color: #1e293b;">
                <?php the_content(); ?>
            </div>
        </div>

    </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
