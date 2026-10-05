<?php
/**
 * Malatya Sözlük - Ana Sayfa Şablonu
 * Versiyon: 1.4.0 (Profesyonel Portal & Optimize Performans)
 */
get_header();
get_sidebar();

// İstatistikler (WordPress Transient ile 6 saat önbellekli)
$istatistikler = get_transient('malatya_portal_istatistikler');
if ( false === $istatistikler ) {
    $yazi_sayisi = wp_count_posts('post')->publish;
    $yazar_sayisi = count_users()['total_users'];
    $kategori_sayisi = wp_count_terms(array('taxonomy' => 'category', 'hide_empty' => true));
    
    $istatistikler = array(
        'madde'    => $yazi_sayisi,
        'yazar'    => $yazar_sayisi,
        'kategori' => $kategori_sayisi
    );
    set_transient('malatya_portal_istatistikler', $istatistikler, 6 * HOUR_IN_SECONDS);
}

// Günün Maddesi (24 saatlik transient ile sabit veya rastgele - "Günler" kategorisi hariç tutulur)
$gunun_maddesi_id = get_transient('malatya_gunun_maddesi_id');
if ( ! $gunun_maddesi_id || ! get_post($gunun_maddesi_id) ) {
    // "Günler" kategorisini ve alt kategorilerini bul
    $gunler_kat = get_term_by('slug', 'gunler', 'category');
    if ( ! $gunler_kat ) {
        $gunler_kat = get_term_by('name', 'Günler', 'category');
    }
    
    $haric_katlar = array();
    if ( $gunler_kat ) {
        $haric_katlar[] = $gunler_kat->term_id;
        $alt_katlar = get_term_children($gunler_kat->term_id, 'category');
        if ( ! is_wp_error($alt_katlar) && ! empty($alt_katlar) ) {
            $haric_katlar = array_merge($haric_katlar, $alt_katlar);
        }
    }

    $gunun_args = array(
        'posts_per_page' => 1,
        'orderby'        => 'rand',
        'post_status'    => 'publish',
        'post_type'      => 'post'
    );
    if ( ! empty($haric_katlar) ) {
        $gunun_args['category__not_in'] = $haric_katlar;
    }

    $rastgele = get_posts($gunun_args);
    if ( ! empty($rastgele) ) {
        $gunun_maddesi_id = $rastgele[0]->ID;
        // Gece yarısına kadar veya 12 saat geçerli
        set_transient('malatya_gunun_maddesi_id', $gunun_maddesi_id, 12 * HOUR_IN_SECONDS);
    }
}
?>

<main class="site-main wiki-portal-container">

    <!-- 1. HERO PORTAL VE KARŞILAMA ALANI -->
    <section class="wiki-portal-hero">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span>Özgür & Bağımsız Malatya Ansiklopedisi</span>
            </div>
            <h1 class="hero-title">Malatya'nın Dijital Hafızasına Hoş Geldiniz</h1>
            <p class="hero-description">
                Tarihinden mutfağına, kadim şahsiyetlerinden coğrafyasına ve yerel ağzına kadar Malatya'ya dair her bilginin bir araya getirildiği ortak hafıza projesi.
            </p>

            <!-- Sayaçlar ve İstatistik Rozetleri -->
            <div class="wiki-hero-stats">
                <div class="hero-stat-card">
                    <span class="stat-number"><?php echo number_format_i18n($istatistikler['madde']); ?></span>
                    <span class="stat-label">Ansiklopedik Madde</span>
                </div>
                <div class="hero-stat-card">
                    <span class="stat-number"><?php echo number_format_i18n($istatistikler['yazar']); ?></span>
                    <span class="stat-label">Gönüllü Katkıcı</span>
                </div>
                <div class="hero-stat-card">
                    <span class="stat-number"><?php echo number_format_i18n($istatistikler['kategori']); ?></span>
                    <span class="stat-label">Tematik Kategori</span>
                </div>
            </div>

            <!-- Hızlı Aksiyon Butonları -->
            <div class="hero-actions">
                <a href="<?php echo home_url('/madde-olustur'); ?>" class="btn-hero btn-hero-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Yeni Madde Yaz</span>
                </a>
                <a href="<?php echo home_url('/kategoriler'); ?>" class="btn-hero btn-hero-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    <span>Kategorileri Keşfet</span>
                </a>
                <a href="<?php echo home_url('/eksik-maddeler'); ?>" class="btn-hero btn-hero-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>İhtiyaç Duyulan Maddeler</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 2. GÜNÜN MADDESİ / SEÇKİN İÇERİK KARTI -->
    <?php if ( $gunun_maddesi_id && ( $gunun_post = get_post($gunun_maddesi_id) ) ) : 
        $gunun_excerpt = ! empty($gunun_post->post_excerpt) ? $gunun_post->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes($gunun_post->post_content) ), 45, '...' );
        $gunun_thumb   = get_the_post_thumbnail_url($gunun_post->ID, 'medium_large');
        $word_count    = str_word_count( wp_strip_all_tags($gunun_post->post_content) );
        $read_time     = max( 1, ceil( $word_count / 180 ) );
    ?>
    <section class="wiki-gunun-maddesi-bolumu">
        <div class="gunun-maddesi-header">
            <span class="gunun-rozet">✨ Günün Seçkin Maddesi</span>
            <span class="okuma-suresi">⏱ Yaklaşık <?php echo $read_time; ?> dk okuma</span>
        </div>
        <div class="gunun-maddesi-kart <?php echo $gunun_thumb ? 'has-thumb' : 'no-thumb'; ?>">
            <?php if ( $gunun_thumb ) : ?>
                <div class="gunun-gorsel-alani">
                    <a href="<?php echo esc_url(get_permalink($gunun_post->ID)); ?>">
                        <img src="<?php echo esc_url($gunun_thumb); ?>" alt="<?php echo esc_attr(get_the_title($gunun_post->ID)); ?>" loading="lazy">
                    </a>
                </div>
            <?php endif; ?>
            <div class="gunun-metin-alani">
                <h2 class="gunun-baslik">
                    <a href="<?php echo esc_url(get_permalink($gunun_post->ID)); ?>">
                        <?php echo esc_html(get_the_title($gunun_post->ID)); ?>
                    </a>
                </h2>
                <div class="gunun-ozet">
                    <?php echo esc_html($gunun_excerpt); ?>
                </div>
                <div class="gunun-alt-aksiyon">
                    <a href="<?php echo esc_url(get_permalink($gunun_post->ID)); ?>" class="btn-devamini-oku">
                        Maddenin Tamamını Oku →
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 3. TEMATİK PORTALLAR VE KONU KARTLARI -->
    <section class="wiki-tematik-portallar">
        <div class="bolum-baslik-alani">
            <h2 class="bolum-basligi">Tematik Konu Portalları</h2>
            <p class="bolum-aciklama">İlginizi çeken alanda Malatya'nın zengin birikimini keşfedin:</p>
        </div>

        <div class="tematik-grid">
            <!-- Portal 1: Tarih & Arkeoloji -->
            <a href="<?php echo esc_url(home_url('/kategori/tarih/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-tarih">🏛️</div>
                <div class="kart-icerik">
                    <h3>Tarih & Arkeoloji</h3>
                    <p>UNESCO Mirası Arslantepe, Eski Malatya surları, beylikler ve kadim medeniyetler.</p>
                </div>
            </a>

            <!-- Portal 2: Coğrafya & İlçeler -->
            <a href="<?php echo esc_url(home_url('/kategori/cografya/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-cografya">🗺️</div>
                <div class="kart-icerik">
                    <h3>Coğrafya & 13 İlçe</h3>
                    <p>Battalgazi, Yeşilyurt, Darende, Akçadağ kanyonları ve Beydağları'nın tabiatı.</p>
                </div>
            </a>

            <!-- Portal 3: Mutfak & Kayısı -->
            <a href="<?php echo esc_url(home_url('/kategori/mutfak/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-mutfak">🍑</div>
                <div class="kart-icerik">
                    <h3>Malatya Mutfağı & Kayısı</h3>
                    <p>Coğrafi işaretli kayısı, analı kızlı, kiraz yaprağı köftesi ve asırlık lezzetler.</p>
                </div>
            </a>

            <!-- Portal 4: Malatya Ağzı & Sözlük -->
            <a href="<?php echo esc_url(home_url('/kategori/sozluk/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-sozluk">📖</div>
                <div class="kart-icerik">
                    <h3>Malatya Ağzı & Kelimeler</h3>
                    <p>Unutulmaya yüz tutmuş yöresel kelimeler, deyimler, atasözleri ve sözlü kültür.</p>
                </div>
            </a>

            <!-- Portal 5: Şahsiyetler & Biyografi -->
            <a href="<?php echo esc_url(home_url('/kategori/sahsiyetler/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-biyografi">👤</div>
                <div class="kart-icerik">
                    <h3>Malatyalı Şahsiyetler</h3>
                    <p>Cumhurbaşkanları, devlet adamları, edebiyatçılar, şairler ve kanaat önderleri.</p>
                </div>
            </a>

            <!-- Portal 6: Kültür, Sanat & Folklor -->
            <a href="<?php echo esc_url(home_url('/kategori/kultur/')); ?>" class="tematik-kart">
                <div class="kart-ikon-kutusu ikon-kultur">🎭</div>
                <div class="kart-icerik">
                    <h3>Kültür, Sanat & Folklor</h3>
                    <p>Malatya türküleri, halaylar, el sanatları, maniler ve geleneksel yaşam tarzı.</p>
                </div>
            </a>
        </div>
    </section>

    <!-- 4. İKİLİ KOLON: SON GÜNCELLENENLER & YAZILMAYI BEKLEYENLER -->
    <div class="wiki-ana-grid">
        
        <!-- SOL KOLON: SON GÜNCELLENEN MADDELER -->
        <div class="ana-kolon ana-kolon-guncel">
            <div class="kolon-baslik-satir">
                <div class="kolon-baslik-sol">
                    <span class="kolon-ikon">🕒</span>
                    <h3 class="kolon-baslik">Son Güncellenen Maddeler</h3>
                </div>
                <a href="<?php echo home_url('/?orderby=modified'); ?>" class="kolon-link-tumu">Tümü →</a>
            </div>
            
            <ul class="wiki-liste-modern">
                <?php 
                $sonlar = new WP_Query(array(
                    'posts_per_page'      => 8,
                    'orderby'             => 'modified',
                    'order'               => 'DESC',
                    'post_status'         => 'publish',
                    'no_found_rows'       => true,
                    'ignore_sticky_posts' => true
                ));
                if ( $sonlar->have_posts() ) :
                    while($sonlar->have_posts()) : $sonlar->the_post(); 
                        $zaman_farki = human_time_diff( get_the_modified_time('U'), current_time('timestamp') ) . ' önce';
                        $yazar_id    = get_the_author_meta('ID');
                        $yazar_adi   = get_the_author_meta('display_name') ?: get_the_author_meta('user_login');
                ?>
                    <li class="wiki-liste-ogesi">
                        <div class="madde-bilgi-satiri">
                            <a href="<?php the_permalink(); ?>" class="madde-baslik-link">
                                <?php the_title(); ?>
                            </a>
                            <span class="madde-zaman"><?php echo esc_html($zaman_farki); ?></span>
                        </div>
                        <div class="madde-alt-bilgi">
                            <span class="madde-yazar">Düzenleyen: <strong><?php echo esc_html($yazar_adi); ?></strong></span>
                        </div>
                    </li>
                <?php 
                    endwhile; 
                    wp_reset_postdata(); 
                else :
                    echo '<li class="wiki-bos-durum">Henüz yayınlanmış madde bulunmuyor.</li>';
                endif; 
                ?>
            </ul>
        </div>

        <!-- SAĞ KOLON: YAZILMAYI BEKLEYENLER (YÜKSEK PERFORMANSLI TRANSIENT ÖNBELLEK) -->
        <div class="ana-kolon ana-kolon-eksik">
            <div class="kolon-baslik-satir">
                <div class="kolon-baslik-sol">
                    <span class="kolon-ikon">✍️</span>
                    <h3 class="kolon-baslik">Yazılmayı Bekleyen Maddeler</h3>
                </div>
                <a href="<?php echo home_url('/eksik-maddeler'); ?>" class="kolon-link-tumu">Tüm Liste →</a>
            </div>
            <p class="kolon-aciklama">Diğer maddelerde atıfta bulunulan ancak henüz içeriği yazılmamış başlıklar:</p>

            <ul class="wiki-liste-modern wiki-kirmizi-liste">
                <?php
                // Transient önbellekten eksik maddeleri çek
                $eksik_maddeler = get_transient('malatya_eksik_maddeler_anasayfa');
                
                if ( false === $eksik_maddeler ) {
                    global $wpdb;
                    $eksik_maddeler = array();
                    
                    // Hafif SQL sorgusu: Sadece ID ve post_content
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
                        $eklenen_sayisi = 0;
                        foreach ( $link_sayaci as $madde_adi => $sayisi ) {
                            // Madde gerçekten yok mu kontrol et
                            $var_mi = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_status = 'publish' AND post_type = 'post' LIMIT 1", $madde_adi ) );
                            if ( ! $var_mi ) {
                                $eksik_maddeler[$madde_adi] = $sayisi;
                                $eklenen_sayisi++;
                                if ( $eklenen_sayisi >= 8 ) {
                                    break;
                                }
                            }
                        }
                    }

                    // 12 saat boyunca transient önbellekte sakla
                    set_transient('malatya_eksik_maddeler_anasayfa', $eksik_maddeler, 12 * HOUR_IN_SECONDS);
                }

                if ( ! empty($eksik_maddeler) ) :
                    foreach ( $eksik_maddeler as $madde_adi => $sayi ) :
                        $olustur_url = home_url('/madde-olustur/?baslik=' . urlencode($madde_adi));
                ?>
                    <li class="wiki-liste-ogesi eksik-madde-ogesi">
                        <div class="eksik-madde-sol">
                            <a href="<?php echo esc_url($olustur_url); ?>" class="wiki-kirmizi-baslik" title="<?php echo esc_attr($madde_adi); ?> maddesini siz başlatın">
                                <?php echo esc_html($madde_adi); ?>
                            </a>
                            <span class="atif-sayisi-badge"><?php echo $sayi; ?> atıf</span>
                        </div>
                        <a href="<?php echo esc_url($olustur_url); ?>" class="btn-madde-baslat" title="Maddeyi Yaz">+ Yaz</a>
                    </li>
                <?php 
                    endforeach;
                else :
                    echo '<li class="wiki-bos-durum">Harika! Şu anda yazılmayı bekleyen eksik kırmızı bağlantı bulunmuyor.</li>';
                endif;
                ?>
            </ul>
        </div>

    </div>

    <!-- 5. TOPLULUK VE KATKIDA BULUNMA ÇAĞRISI (CTA) -->
    <section class="wiki-katki-cta-alani">
        <div class="cta-kutu">
            <div class="cta-ikon">✍️</div>
            <div class="cta-metin">
                <h3>Malatya Sözlük Herkesin Katkısıyla Büyüyor</h3>
                <p>Bildiğiniz bir köyü, unutulmuş bir kelimeyi, yerel bir yemeği veya tarihi bir olayı ansiklopediye ekleyerek şehrimizin dijital mirasını birlikte inşa edelim.</p>
            </div>
            <div class="cta-buton-alani">
                <a href="<?php echo home_url('/madde-olustur'); ?>" class="btn-cta-katil">
                    Hemen Madde Yaz
                </a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>