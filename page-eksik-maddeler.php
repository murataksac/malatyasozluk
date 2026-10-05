<?php
/**
 * Template Name: Eksik Maddeler Sayfası
 * Malatya Sözlük - Yazılmayı Bekleyen (İstenen) Maddeler
 */
get_header(); 
get_sidebar(); 

// Eksik maddeleri transient önbellekten çek
$cache_key = 'malatya_eksik_maddeler_tam_liste';
$eksik_maddeler = get_transient($cache_key);

if ( false === $eksik_maddeler ) {
    global $wpdb;
    $eksik_maddeler = array();

    // Hafif SQL sorgusu
    $yazilar = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'", OBJECT );
    $link_sayaci = array();

    if ( ! empty($yazilar) ) {
        foreach ( $yazilar as $yazi ) {
            if ( empty($yazi->post_content) ) continue;
            if ( preg_match_all( '/\[\[([^\|\]]+)(?:\|[^\]]+)?\]\]/', $yazi->post_content, $matches ) ) {
                foreach ( $matches[1] as $madde ) {
                    $madde = trim( $madde );
                    if ( mb_strlen($madde, 'UTF-8') < 2 ) continue;
                    if ( ! isset($link_sayaci[$madde]) ) {
                        $link_sayaci[$madde] = 0;
                    }
                    $link_sayaci[$madde]++;
                }
            }
        }
    }

    if ( ! empty($link_sayaci) ) {
        arsort($link_sayaci);
        foreach ( $link_sayaci as $madde_adi => $sayisi ) {
            $var_mi = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_status = 'publish' AND post_type = 'post' LIMIT 1", $madde_adi ) );
            if ( ! $var_mi ) {
                $eksik_maddeler[$madde_adi] = $sayisi;
            }
        }
    }

    set_transient($cache_key, $eksik_maddeler, 12 * HOUR_IN_SECONDS);
}

$toplam_eksik = count($eksik_maddeler);
?>

<main class="site-main">
    <header class="page-header wiki-eksik-header" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
            <div style="display: inline-flex; align-items: center; gap: 8px;">
                <span style="background: #fee2e2; color: #991b1b; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #fca5a5;">
                    ✍️ <?php echo $toplam_eksik; ?> Yazılmayı Bekleyen Madde
                </span>
            </div>
            <a href="<?php echo home_url('/madde-olustur'); ?>" class="btn-madde-yaz-header" style="background: #dc2626; color: #ffffff; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>+ Yeni Madde Başlat</span>
            </a>
        </div>

        <h1 class="page-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.85rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; border: none; padding: 0;">
            Yazılmayı Bekleyen (İstenen) Maddeler
        </h1>
        <p style="color: #64748b; font-size: 14px; margin: 0;">
            Sözlükte diğer maddelerden iç bağlantı (kırmızı link) verilen ancak henüz içeriği oluşturulmamış başlıklar. Kırmızı başlıklara tıklayarak maddeyi ilk siz başlatabilirsiniz.
        </p>
    </header>

    <div class="entry-content">
        
        <!-- Canlı Arama Kutusu -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 6px rgba(15,23,42,0.02);">
            <span style="color: #64748b; font-size: 14px;">🔍</span>
            <input type="text" id="eksik-madde-filtre" placeholder="Eksik maddeler arasında ara..." style="width: 100%; border: none; outline: none; font-size: 14px; font-family: inherit;" onkeyup="eksikMaddeFiltrele(this.value)">
        </div>

        <?php if ( ! empty($eksik_maddeler) ) : ?>
            <div id="eksik-maddeler-grid" class="eksik-maddeler-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
                <?php foreach ($eksik_maddeler as $madde => $sayi) : 
                    $url = home_url('/madde-olustur/?baslik=' . urlencode($madde));
                ?>
                    <div class="eksik-madde-kart" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; transition: all 0.2s ease; box-shadow: 0 2px 6px rgba(15,23,42,0.02);" data-baslik="<?php echo esc_attr(mb_strtolower($madde, 'UTF-8')); ?>">
                        <div class="eksik-madde-bilgi" style="display: flex; flex-direction: column; gap: 4px; overflow: hidden;">
                            <a href="<?php echo esc_url($url); ?>" class="wiki-kirmizi-link" style="color: #dc2626; font-weight: 700; font-size: 14.5px; text-decoration: none; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="<?php echo esc_attr($madde); ?> maddesini siz başlatın">
                                <?php echo esc_html($madde); ?>
                            </a> 
                            <span class="gecis-sayisi" style="font-size: 11.5px; color: #94a3b8;">
                                🔗 <?php echo $sayi; ?> maddede atıf var
                            </span>
                        </div>
                        <a href="<?php echo esc_url($url); ?>" class="olustur-buton" style="background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; text-decoration: none; white-space: nowrap; transition: all 0.2s ease;">
                            + Oluştur
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="wiki-basari-kutusu" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 20px; border-radius: 10px; text-align: center; font-weight: 600;">
                🎉 Tebrikler! Malatya Sözlük bünyesinde şu an için yazılmamış kırmızı bağlantı kalmamıştır.
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function eksikMaddeFiltrele(deger) {
    var filter = deger.toLowerCase().trim();
    var kartlar = document.querySelectorAll('#eksik-maddeler-grid .eksik-madde-kart');
    
    kartlar.forEach(function(kart) {
        var baslik = kart.getAttribute('data-baslik');
        if (baslik && baslik.indexOf(filter) > -1) {
            kart.style.display = 'flex';
        } else {
            kart.style.display = 'none';
        }
    });
}
</script>

<?php get_footer(); ?>