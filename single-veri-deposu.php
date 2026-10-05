<?php
/**
 * Veri Deposu Özel Şablonu
 * Malatya Sözlük - Merkezi Tablo & Veri Şablonu
 */
get_header();
get_sidebar();
?>

<main class="site-main">
    <?php while ( have_posts() ) : the_post(); 
        $post_id    = get_the_ID();
        $excel_json = get_post_meta($post_id, '_excel_verisi', true);
        $rows       = $excel_json ? json_decode($excel_json) : array();
        $satir_sayisi = !empty($rows) ? count($rows) - 1 : 0;
        $duzenle_link = current_user_can('edit_post', $post_id) ? get_edit_post_link($post_id) : '';
    ?>
    <article id="post-<?php the_ID(); ?>" class="wiki-madde-tam wiki-veri-deposu-sayfasi">

        <!-- ÜST BAŞLIK VE META ALANI -->
        <header class="entry-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #bfdbfe;">
                        📊 Veri Deposu Tablosu
                    </span>
                    <span style="font-size: 12.5px; color: #64748b;">
                        <?php echo max(0, $satir_sayisi); ?> Veri Satırı
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if ( $duzenle_link ) : ?>
                        <a href="<?php echo esc_url($duzenle_link); ?>" class="btn-aksiyon-kucuk" style="background: #2563eb; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12.5px; font-weight: 700; text-decoration: none;">
                            ✎ Tabloyu Düzenle
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <h1 class="entry-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.85rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; border: none; padding: 0;">
                <?php the_title(); ?>
            </h1>
            <p style="color: #64748b; font-size: 13.5px; margin: 0;">
                Son güncelleme: <?php echo get_the_modified_date('d F Y, H:i'); ?> • Bu veriler sözlük maddeleri tarafından dinamik olarak kullanılabilir.
            </p>
        </header>

        <!-- VERİ ARAÇ ÇUBUĞU & FİLTRE -->
        <div class="wiki-veri-arac-cubugu" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 6px rgba(15,23,42,0.02);">
            <div style="display: flex; align-items: center; gap: 8px; flex: 1; max-width: 380px;">
                <span style="font-size: 14px; color: #64748b;">🔍</span>
                <input type="text" id="veri-tablo-arama" placeholder="Tabloda ara veya filtrele..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px;" onkeyup="tablodaAra(this.value)">
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" onclick="wikiSekmeDegistir('veri-tablosu')" id="tab-btn-tablo" class="wiki-veri-tab-btn aktif" style="background: #2563eb; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer;">
                    📊 Tablo
                </button>
                <button type="button" onclick="wikiSekmeDegistir('tartisma-icerigi')" id="tab-btn-tartisma" class="wiki-veri-tab-btn" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer;">
                    💬 Tartışma (<?php echo get_comments_number(); ?>)
                </button>
            </div>
        </div>

        <!-- TABLO ALANI -->
        <div id="veri-tablosu" class="wiki-veri-sekme" style="display: block;">
            <?php if ( ! empty($rows) && is_array($rows) ) : 
                $first_row = array_shift($rows);
            ?>
                <div class="wiki-tablo-wrapper" style="overflow-x: auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04); margin-bottom: 24px;">
                    <table id="wikiVeriTablosu" class="wiki-tablo-modern" style="width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <?php foreach ($first_row as $col_index => $cell) : ?>
                                    <th style="padding: 12px 16px; font-weight: 700; color: #1e293b; border-right: 1px solid #f1f5f9; cursor: pointer; user-select: none;" onclick="tabloyuSirala(this, <?php echo $col_index; ?>)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                            <span><?php echo esc_html($cell); ?></span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 12px;">⇅</span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody id="veriTablosuBody">
                            <?php foreach ($rows as $row_index => $row) : ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" class="veri-satiri">
                                    <?php foreach ($row as $cell) : ?>
                                        <td style="padding: 10px 16px; border-right: 1px solid #f8fafc; color: #334155;">
                                            <?php echo esc_html($cell); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div style="padding: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; text-align: center; color: #64748b; margin-bottom: 24px;">
                    Bu veri deposunda henüz kayıtlı veri tablosu bulunmamaktadır.
                </div>
            <?php endif; ?>

            <!-- KULLANIM KODU VE BİLGİ KUTUSU -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                
                <!-- Kod Entegrasyonu -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px;">
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <span>🔗 Maddelerde Kullanım Kodu:</span>
                    </div>
                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 8px;">
                        Bu tablodaki hücreleri herhangi bir maddede dinamik göstermek için:
                    </p>
                    <code style="display: block; background: #ffffff; border: 1px solid #93c5fd; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 700; color: #1e40af;">
                        [veri ad="<?php echo esc_attr(get_the_title()); ?>" hucre="A1"]
                    </code>
                </div>

                <!-- Notlar ve Açıklama -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px;">
                    <div style="font-weight: 700; font-size: 13px; color: #1e40af; margin-bottom: 6px;">
                        ℹ️ Tablo Hakkında Notlar
                    </div>
                    <div style="font-size: 12.5px; color: #334155; line-height: 1.55;">
                        <?php 
                        if ( has_excerpt() ) {
                            the_excerpt();
                        } else {
                            echo 'Bu veri seti Malatya Sözlük açık veri projesi kapsamında merkezi olarak güncellenmektedir.';
                        }
                        ?>
                    </div>
            </div> <!-- Notlar kutusu sonu -->
            </div> <!-- Grid kutusu sonu -->
            
            <!-- SOSYAL MEDYA PAYLAŞIM ALANI -->
            <?php get_template_part( 'template-parts/share-box' ); ?>
        </div>

        <!-- TARTIŞMA ALANI -->
        <div id="tartisma-icerigi" class="wiki-veri-sekme" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; box-shadow: 0 4px 15px -3px rgba(15,23,42,0.04);">
            <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
        </div>

    </article>
    <?php endwhile; ?>
</main>

<style>
.wiki-tablo-modern tbody tr:nth-child(even) { background-color: #f8fafc; }
.wiki-tablo-modern tbody tr:hover { background-color: #eff6ff !important; }
.wiki-tablo-modern th:hover { background-color: #f1f5f9; }
@media screen and (max-width: 768px) {
    .wiki-veri-deposu-sayfasi div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function wikiSekmeDegistir(sekmeId) {
    document.getElementById('veri-tablosu').style.display = (sekmeId === 'veri-tablosu') ? 'block' : 'none';
    document.getElementById('tartisma-icerigi').style.display = (sekmeId === 'tartisma-icerigi') ? 'block' : 'none';
    
    var btnTablo = document.getElementById('tab-btn-tablo');
    var btnTartisma = document.getElementById('tab-btn-tartisma');
    
    if (sekmeId === 'veri-tablosu') {
        btnTablo.style.background = '#2563eb';
        btnTablo.style.color = '#ffffff';
        btnTablo.style.border = 'none';
        btnTartisma.style.background = '#f1f5f9';
        btnTartisma.style.color = '#475569';
        btnTartisma.style.border = '1px solid #cbd5e1';
    } else {
        btnTartisma.style.background = '#2563eb';
        btnTartisma.style.color = '#ffffff';
        btnTartisma.style.border = 'none';
        btnTablo.style.background = '#f1f5f9';
        btnTablo.style.color = '#475569';
        btnTablo.style.border = '1px solid #cbd5e1';
    }
}

function tablodaAra(kelime) {
    var filter = kelime.toLowerCase().trim();
    var satirlar = document.querySelectorAll('#veriTablosuBody tr');
    
    satirlar.forEach(function(satir) {
        var metin = satir.textContent.toLowerCase();
        if (metin.indexOf(filter) > -1) {
            satir.style.display = '';
        } else {
            satir.style.display = 'none';
        }
    });
}

function tabloyuSirala(th, colIndex) {
    var table = document.getElementById('wikiVeriTablosu');
    var tbody = document.getElementById('veriTablosuBody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var isAscending = th.getAttribute('data-order') === 'asc';
    
    // Tüm ikonları sıfırla
    table.querySelectorAll('th').forEach(function(h) {
        var icon = h.querySelector('.sort-icon');
        if (icon) icon.textContent = '⇅';
        h.removeAttribute('data-order');
    });

    rows.sort(function(a, b) {
        var valA = a.children[colIndex] ? a.children[colIndex].textContent.trim() : '';
        var valB = b.children[colIndex] ? b.children[colIndex].textContent.trim() : '';
        
        var numA = parseFloat(valA.replace(/,/g, ''));
        var numB = parseFloat(valB.replace(/,/g, ''));

        if (!isNaN(numA) && !isNaN(numB)) {
            return isAscending ? numB - numA : numA - numB;
        } else {
            return isAscending ? valB.localeCompare(valA, 'tr') : valA.localeCompare(valB, 'tr');
        }
    });

    var icon = th.querySelector('.sort-icon');
    if (isAscending) {
        th.setAttribute('data-order', 'desc');
        if (icon) icon.textContent = '▼';
    } else {
        th.setAttribute('data-order', 'asc');
        if (icon) icon.textContent = '▲';
    }

    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}
</script>

<?php get_footer(); ?>