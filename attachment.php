<?php
/**
 * Malatya Sözlük - Tekil Medya & Dosya Sayfası (Attachment / Media Details)
 * Wikipedia Dosya Bilgi Kartı Standartlarında
 */
get_header();
get_sidebar();
?>

<main class="site-main wiki-medya-sayfasi">
    <?php while ( have_posts() ) : the_post(); 
        $post_id       = get_the_ID();
        $is_image      = wp_attachment_is_image( $post_id );
        $full_img_url  = wp_get_attachment_url( $post_id );
        $meta_data     = wp_get_attachment_metadata( $post_id );
        $file_path     = get_attached_file( $post_id );
        $file_size_str = ( $file_path && file_exists($file_path) ) ? size_format( filesize($file_path), 2 ) : '---';
        $mime_type     = get_post_mime_type( $post_id );
        $width         = ! empty($meta_data['width']) ? $meta_data['width'] : '---';
        $height        = ! empty($meta_data['height']) ? $meta_data['height'] : '---';
        $alt_text      = get_post_meta( $post_id, '_wp_attachment_image_alt', true );
        $caption       = wp_get_attachment_caption( $post_id );
        $description   = get_the_content();
        
        $author_id     = get_the_author_meta('ID');
        $author_name   = get_the_author_meta('display_name') ?: get_the_author_meta('user_login');
        $author_url    = get_author_posts_url( $author_id );
        $parent_id     = wp_get_post_parent_id( $post_id );
    ?>
    <article id="post-<?php the_ID(); ?>" class="wiki-madde-tam wiki-dosya-detay-kapsayici">

        <!-- ÜST BAŞLIK VE EK BİLGİLER -->
        <header class="page-header" style="margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #bfdbfe;">
                        🖼️ Medya & Dosya
                    </span>
                    <span style="font-size: 12px; color: #94a3b8;">
                        <?php echo esc_html( $mime_type ); ?>
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="<?php echo esc_url($full_img_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-aksiyon-kucuk" style="background: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🔍 Tam Boyutta Aç</span>
                    </a>
                </div>
            </div>

            <h1 class="entry-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.8rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; border: none; padding: 0;">
                Dosya: <?php the_title(); ?>
            </h1>
            
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; flex-wrap: wrap;">
                <span>📅 Yüklenme: <?php echo get_the_date('d F Y, H:i'); ?></span>
                <span>•</span>
                <span>Yükleyen: <a href="<?php echo esc_url($author_url); ?>" style="color: #2563eb; font-weight: 600; text-decoration: none;"><?php echo esc_html($author_name); ?></a></span>
            </div>
        </header>

        <!-- MEDYA GÖRSEL GÖSTERİM ALANI -->
        <div class="wiki-medya-onizleme-kutu" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 24px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04);">
            <?php if ( $is_image ) : ?>
                <div style="max-width: 100%; display: inline-block;">
                    <a href="<?php echo esc_url($full_img_url); ?>" target="_blank" title="Orijinal çözünürlükte görüntüle">
                        <img src="<?php echo esc_url($full_img_url); ?>" alt="<?php echo esc_attr($alt_text ?: get_the_title()); ?>" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); max-height: 600px; object-fit: contain;">
                    </a>
                </div>
                <?php if ( ! empty($caption) ) : ?>
                    <p class="medya-altyazi" style="font-size: 13.5px; color: #475569; font-style: italic; margin-top: 12px; margin-bottom: 0;">
                        <?php echo esc_html($caption); ?>
                    </p>
                <?php endif; ?>
            <?php else : ?>
                <div style="padding: 30px; color: #64748b;">
                    <p style="font-size: 40px; margin-bottom: 10px;">📁</p>
                    <p>Bu dosya türü doğrudan önizlenemiyor.</p>
                    <a href="<?php echo esc_url($full_img_url); ?>" class="wiki-btn-primary" download>Dosyayı İndir</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- MEDYA DETAY & METADATA BİLGİ KARTLARI -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            
            <!-- 1. Teknik Özellikler -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <span>📊</span> Dosya Bilgileri & Metadata
                </h3>
                <table style="width: 100%; font-size: 13px; line-height: 1.8; border-collapse: collapse;">
                    <tr>
                        <td style="color: #64748b; width: 130px; font-weight: 600;">Çözünürlük:</td>
                        <td style="color: #0f172a; font-weight: 700;"><?php echo esc_html($width . ' × ' . $height . ' px'); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600;">Dosya Boyutu:</td>
                        <td style="color: #0f172a;"><?php echo esc_html($file_size_str); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600;">MIME Türü:</td>
                        <td style="color: #0f172a;"><?php echo esc_html($mime_type); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600;">Dosya URL:</td>
                        <td>
                            <button type="button" onclick="malatyaBaglantiKopyala('<?php echo esc_js($full_img_url); ?>', this)" style="background: #ffffff; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 4px; cursor: pointer;">
                                📋 URL Kopyala
                            </button>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 2. Açıklama ve Kullanım Bilgisi -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <span>📝</span> Açıklama & Notlar
                </h3>
                
                <?php if ( ! empty($description) ) : ?>
                    <div style="font-size: 13px; color: #334155; line-height: 1.6; margin-bottom: 12px;">
                        <?php echo wpautop(esc_html($description)); ?>
                    </div>
                <?php else : ?>
                    <p style="font-size: 13px; color: #94a3b8; font-style: italic; margin-bottom: 12px;">
                        Bu medya için henüz detaylı bir açıklama girilmemiş.
                    </p>
                <?php endif; ?>

                <?php if ( $parent_id && ( $parent_post = get_post($parent_id) ) ) : ?>
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 12px; font-size: 12.5px; color: #1e40af;">
                        🔗 <strong>Kullanıldığı Madde:</strong> <a href="<?php echo esc_url(get_permalink($parent_id)); ?>" style="color: #1d4ed8; font-weight: 700; text-decoration: underline;"><?php echo esc_html(get_the_title($parent_id)); ?></a>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- SOSYAL PAYLAŞIM ALANI -->
        <?php get_template_part( 'template-parts/share-box' ); ?>

    </article>
    <?php endwhile; ?>
</main>

<style>
@media screen and (max-width: 768px) {
    .wiki-dosya-detay-kapsayici div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php get_footer(); ?>
