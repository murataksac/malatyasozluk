<?php
/**
 * Gelişmiş Başlık ID Ekleme Motoru (Sayfa İçi Kutu İptal Edildi)
 * Yazı içindeki H2 başlıklarına otomatik benzersiz ID'ler ekler.
 * SADECE 3 veya daha fazla başlık varsa aktif olur.
 * Yalnızca Sidebar'daki menünün sayfada zıplama yapması için çalışır.
 */

function malatya_sozluk_h2_id_ekleyici($content) {
    if (!is_single() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    // Esnek regex ile tüm H2 etiketlerini bul
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $content, $matches);

    // 1. KURAL: Başlık Sayısını Kontrol Et (3 Başlık Kuralı)
    $baslik_sayisi = count($matches[0]);

    // Eğer sayfada 3'ten az başlık varsa hiçbir işlem yapma (Sidebar menüsü iptal olur)
    if ($baslik_sayisi < 3) {
        return $content;
    }

    $bulunanlar = array();
    $degisecekler = array();

    if (!empty($matches[1])) {
        foreach ($matches[1] as $index => $baslik) {
            $temiz_baslik = strip_tags($baslik);
            $slug = sanitize_title($temiz_baslik);
            if (empty($slug)) {
                $slug = 'baslik-' . $index;
            }

            $eski_h2 = $matches[0][$index];
            
            // Sadece Sidebar için class ve id'leri ekliyoruz
            $yeni_h2 = '<h2 id="' . $slug . '" class="wiki-article-heading">' . $baslik . '</h2>';
            
            $bulunanlar[] = $eski_h2;
            $degisecekler[] = $yeni_h2;
        }
    }

    // 2. KURAL: Metindeki başlıkları, ID eklenmiş (Sidebar uyumlu) versiyonlarıyla değiştir
    $content = str_replace($bulunanlar, $degisecekler, $content);

    return $content;
}
add_filter('the_content', 'malatya_sozluk_h2_id_ekleyici', 15);
?>