<?php
/**
 * Arama sonuçlarında veya kırık (kırmızı) linklerde gösterilecek şablon.
 */
// Aranan veya tıklanan kelimeyi yakala
$aranan_kelime = get_search_query(); 
?>

<section class="no-results not-found">
    <div class="page-content" style="font-size: 1.1em; line-height: 1.8;">
        
        <p>Aradığınız "<strong><?php echo esc_html($aranan_kelime); ?></strong>" başlığına ait bir madde Malatya Sözlük'te henüz bulunmuyor.</p>
        
        <div style="background-color: #eaf3ff; border-left: 4px solid #3366cc; padding: 15px; margin: 20px 0;">
            <p style="margin-top:0;">Bu maddeyi başlatarak ansiklopediye katkıda bulunmak ister misiniz?</p>
            
            <a href="<?php echo esc_url(home_url('/madde-olustur/?baslik=' . urlencode($aranan_kelime))); ?>" style="display: inline-block; background-color: #3366cc; color: #fff; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 2px;">
                + "<?php echo esc_html($aranan_kelime); ?>" Maddesini Oluştur
            </a>
        </div>
        
        <p>Alternatif olarak farklı kelimelerle arama yapabilirsiniz:</p>
        <div style="margin-top:20px;">
            <?php get_search_form(); ?>
        </div>
        
    </div>
</section>