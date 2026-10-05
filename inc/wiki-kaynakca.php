<?php
/**
 * Otomatik Kaynakça ve Referans Motoru
 * Metin içine eklenen [ref]...[/ref] etiketlerini yakalar,
 * aynı kaynakları tek bir numara altında birleştirir ve sayfa sonuna Wikipedia standardında bağlantılı kaynakça basar.
 */

function malatya_sozluk_kaynakca_olusturucu($content) {
    if (!is_single() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    // [ref]...[/ref], [ref name="..."]...[/ref] ve [ref name="..."/] etiketlerini yakalar
    $pattern = '/(?:<span[^>]*class=["\'][^"\']*wiki-editor-ref[^"\']*["\'][^>]*>)?\s*\[ref(?:\s+name=["\']?([^"\'\]\/]+)["\']?)?(?:\s*\/\s*\]|\](.*?)\[\/ref\])\s*(?:<\/span>)?/is';

    preg_match_all($pattern, $content, $all_matches, PREG_SET_ORDER);

    if (empty($all_matches)) {
        return $content;
    }

    // 1. ADIM: Kaynakları gruplandır ve tekilleştir
    $unique_sources = array();
    $next_no = 1;

    foreach ($all_matches as $match) {
        $raw_name = !empty($match[1]) ? trim($match[1]) : '';
        $raw_text = isset($match[2]) ? trim($match[2]) : '';

        // Eşleştirme anahtarı oluştur (name parametresi varsa name'e göre, yoksa normalize edilmiş metne göre)
        if (!empty($raw_name)) {
            $key = 'name:' . mb_strtolower($raw_name, 'UTF-8');
        } else {
            $clean_text = preg_replace('/\s+/', ' ', strip_tags($raw_text));
            $key = 'text:' . mb_strtolower($clean_text, 'UTF-8');
        }

        if (empty($key) || $key === 'text:') {
            continue;
        }

        if (!isset($unique_sources[$key])) {
            $unique_sources[$key] = array(
                'no'          => $next_no++,
                'text'        => $raw_text,
                'occurrences' => 0,
            );
        } else {
            if (empty($unique_sources[$key]['text']) && !empty($raw_text)) {
                $unique_sources[$key]['text'] = $raw_text;
            }
        }
        $unique_sources[$key]['occurrences']++;
    }

    if (empty($unique_sources)) {
        return $content;
    }

    // 2. ADIM: Metin içindeki [ref] etiketlerini numaralı süperior linklerle değiştir ([1], [2], ...)
    $occ_counters = array();

    $content = preg_replace_callback($pattern, function($matches) use (&$unique_sources, &$occ_counters) {
        $raw_name = !empty($matches[1]) ? trim($matches[1]) : '';
        $raw_text = isset($matches[2]) ? trim($matches[2]) : '';

        if (!empty($raw_name)) {
            $key = 'name:' . mb_strtolower($raw_name, 'UTF-8');
        } else {
            $clean_text = preg_replace('/\s+/', ' ', strip_tags($raw_text));
            $key = 'text:' . mb_strtolower($clean_text, 'UTF-8');
        }

        if (!isset($unique_sources[$key])) {
            return '';
        }

        $no = $unique_sources[$key]['no'];
        if (!isset($occ_counters[$key])) {
            $occ_counters[$key] = 0;
        }
        $occ_index = $occ_counters[$key]++;
        $occ_id = 'ref-' . $no . '-' . $occ_index;

        return '<sup id="' . esc_attr($occ_id) . '" class="wiki-ref-no"><a href="#cite-' . esc_attr($no) . '" class="wiki-ref-link" title="Kaynak ' . esc_attr($no) . '">[' . esc_html($no) . ']</a></sup>';
    }, $content);

    // 3. ADIM: Sayfa sonuna tekilleştirilmiş ve geri bağlantılı Kaynakça listesini ekle
    $kaynakca_listesi = '<div class="wiki-kaynakca">';
    $kaynakca_listesi .= '<h3 class="wiki-kaynakca-baslik"><span>📚</span> Kaynakça</h3>';
    $kaynakca_listesi .= '<ol class="wiki-kaynakca-listesi">';

    $letters = range('a', 'z');

    foreach ($unique_sources as $key => $source) {
        $no = $source['no'];
        $text = !empty($source['text']) ? $source['text'] : 'Kaynak belirtilmedi';
        $occ_count = $source['occurrences'];

        $kaynakca_listesi .= '<li id="cite-' . esc_attr($no) . '" class="wiki-cite-item">';

        if ($occ_count <= 1) {
            // Tek bir kez kullanılmışsa standart tek geri dönüş oku
            $kaynakca_listesi .= '<a href="#ref-' . esc_attr($no) . '-0" class="wiki-cite-backlink" title="Metindeki yerine geri dön">↑</a> ';
        } else {
            // Birden fazla yerde kullanılmışsa Wikipedia stili: ↑ a b c ...
            $kaynakca_listesi .= '<span class="wiki-cite-backlinks-wrapper">';
            $kaynakca_listesi .= '<span class="wiki-cite-arrow">↑</span> ';
            
            for ($i = 0; $i < $occ_count; $i++) {
                $letter = isset($letters[$i]) ? $letters[$i] : ($i + 1);
                $kaynakca_listesi .= '<a href="#ref-' . esc_attr($no) . '-' . $i . '" class="wiki-cite-backlink wiki-cite-backlink-multi" title="Metindeki ' . ($i + 1) . '. kullanıma geri dön"><sup>' . esc_html($letter) . '</sup></a> ';
            }
            $kaynakca_listesi .= '</span> ';
        }

        $kaynakca_listesi .= '<span class="wiki-cite-text">' . $text . '</span>';
        $kaynakca_listesi .= '</li>';
    }

    $kaynakca_listesi .= '</ol></div>';

    $content .= $kaynakca_listesi;

    return $content;
}

add_filter('the_content', 'malatya_sozluk_kaynakca_olusturucu', 20);