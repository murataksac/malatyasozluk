<?php
/**
 * Malatya Sözlük - Sosyal Medya Paylaşım Kutusu Bileşeni
 */
$paylas_url    = urlencode( get_permalink() );
$paylas_ham    = get_permalink();
$sayfa_basligi = get_the_title();

if ( is_attachment() ) {
    $paylas_metin = urlencode( 'Dosya: ' . $sayfa_basligi . ' - Malatya Sözlük' );
} elseif ( is_singular('veri-deposu') ) {
    $paylas_metin = urlencode( 'Veri Tablosu: ' . $sayfa_basligi . ' - Malatya Sözlük' );
} else {
    $paylas_metin = urlencode( $sayfa_basligi . ' - Malatya Sözlük' );
}
?>
<div class="wiki-paylas-kutusu">
    <div class="paylas-sol-bilgi">
        <span class="paylas-ana-ikon">📤</span>
        <span class="paylas-etiket">Bu Sayfayı Paylaş:</span>
    </div>
    <div class="paylas-buton-grubu">
        <a href="https://api.whatsapp.com/send?text=<?php echo $paylas_metin; ?>%20<?php echo $paylas_url; ?>" target="_blank" rel="noopener noreferrer" class="btn-paylas btn-paylas-whatsapp" title="WhatsApp ile Paylaş">
            <span class="paylas-simge">💬</span>
            <span>WhatsApp</span>
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?php echo $paylas_metin; ?>&url=<?php echo $paylas_url; ?>" target="_blank" rel="noopener noreferrer" class="btn-paylas btn-paylas-x" title="X'te (Twitter) Paylaş">
            <span class="paylas-simge">𝕏</span>
            <span>X (Twitter)</span>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $paylas_url; ?>" target="_blank" rel="noopener noreferrer" class="btn-paylas btn-paylas-facebook" title="Facebook'ta Paylaş">
            <span class="paylas-simge">📘</span>
            <span>Facebook</span>
        </a>
        <a href="https://t.me/share/url?url=<?php echo $paylas_url; ?>&text=<?php echo $paylas_metin; ?>" target="_blank" rel="noopener noreferrer" class="btn-paylas btn-paylas-telegram" title="Telegram'da Paylaş">
            <span class="paylas-simge">✈️</span>
            <span>Telegram</span>
        </a>
        <button type="button" class="btn-paylas btn-paylas-kopyala" onclick="malatyaBaglantiKopyala('<?php echo esc_js($paylas_ham); ?>', this)" title="Bağlantıyı Panoya Kopyala">
            <span class="paylas-simge">🔗</span>
            <span class="kopyala-yazi">Bağlantıyı Kopyala</span>
        </button>
    </div>
</div>
