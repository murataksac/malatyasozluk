<?php
/*
Template Name: Canlı Düzenleme Sayfası
*/
get_header(); // [cite: 16]
get_sidebar(); // [cite: 25]

$istenen_baslik = isset($_GET['baslik']) ? sanitize_text_field($_GET['baslik']) : ''; // 
?>

<main class="site-main">
    <header class="entry-header" style="border-bottom: 1px solid #a2a9b1; margin-bottom: 20px; padding-bottom: 10px;">
        <h1 class="entry-title">Canlı Madde Düzenleyici & Önizleme</h1>
        <p style="color:#54595d; font-size:0.95em;">Sol tarafta içeriğinizi yazarken sağ tarafta anlık ansiklopedik görünümünü takip edebilirsiniz.</p>
    </header>

    <style>
        .wiki-duzenleme-konteyner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media screen and (max-width: 900px) {
            .wiki-duzenleme-konteyner {
                grid-template-columns: 1fr; /* Mobilde üst üste gelir */
            }
        }
        .wiki-editor-alani, .wiki-onizleme-alani {
            background: #fff;
            border: 1px solid #a2a9b1;
            padding: 18px;
            border-radius: 2px;
        }
        .wiki-onizleme-alani {
            background: #f8f9fa;
            min-height: 420px;
        }
        .wiki-onizleme-baslik {
            font-family: 'Linux Libertine', Georgia, serif;
            font-size: 2em;
            border-bottom: 1px solid #a2a9b1;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #000;
        }
        .wiki-onizleme-rozet {
            display: inline-block;
            background: #3366cc;
            color: #fff;
            font-size: 0.75em;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            margin-bottom: 10px;
        }
    </style>

    <div class="entry-content">
        <form method="post" action="<?php echo esc_url(home_url('/madde-olustur/')); ?>">
            <div class="wiki-duzenleme-konteyner">
                
                <!-- SOL SÜTUN: YAZIM ALANI -->
                <div class="wiki-editor-alani">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Madde Başlığı *</label>
                        <input type="text" id="live-baslik" name="wiki_baslik" value="<?php echo esc_attr($ [cite: 36]istenen_baslik); ?>" required placeholder="Örn: Malatya Kalesi" style="width:100%; padding:10px; border:1px solid #ccc; font-size:1.1em;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Ansiklopedik İçerik *</label>
                        <textarea id="live-icerik" name="wiki_icerik" rows="16" required placeholder="İçeriğinizi girin... [[Wiki Link]], == Başlık == ve [ref]Kaynak[/ref] kullanabilirsiniz." style="width:100%; padding:10px; border:1px solid #ccc; line-height:1.6; font-family: monospace;"></textarea>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Adınız / Mahlasınız</label>
                        <input type="text" name="wiki_yazar" placeholder="Kimin katkısı olarak görünsün?" style="width:100%; padding:10px; border:1px solid #ccc;">
                    </div>

                    <button type="submit" style="background-color: #3366cc; color: white; padding: 12px 25px; border: none; font-size: 1.1em; font-weight: bold; cursor: pointer; width: 100%;">Maddeyi Onaya Gönder</button>
                </div>

                <!-- SAĞ SÜTUN: CANLI ÖNİZLEME -->
                <div class="wiki-onizleme-alani">
                    <span class="wiki-onizleme-rozet">👁️ CANLI ÖNİZLEME</span>
                    <h1 id="pv-baslik" class="wiki-onizleme-baslik"><?php echo $istenen_baslik ? esc_html($istenen_baslik) : 'Madde Başlığı'; ?></h1>
                    <div id="pv-icerik" style="line-height:1.6; color:#202122;">
                        <em style="color:#888;">Yazmaya başladığınızda içeriğiniz burada anlık olarak şekillenecektir...</em>
                    </div>
                </div>

            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const baslikInput = document.getElementById("live-baslik");
    const icerikTextarea = document.getElementById("live-icerik");
    const pvBaslik = document.getElementById("pv-baslik");
    const pvIcerik = document.getElementById("pv-icerik");

    function renderPreview() {
        // 1. Başlık Güncelleme
        const bVal = baslikInput.value.trim();
        pvBaslik.innerText = bVal ? bVal : "Madde Başlığı";

        // 2. İçerik Güncelleme
        let text = icerikTextarea.value;
        if (!text.trim()) {
            pvIcerik.innerHTML = '<em style="color:#888;">Yazmaya başladığınızda içeriğiniz burada anlık olarak şekillenecektir...</em>';
            return;
        }

        // HTML Kaçış (Güvenlik)
        let html = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        // H2 ve H3 Başlıklar (== Başlık ==)
        html = html.replace(/^==\s*(.*?)\s*==$/gm, '<h2 style="font-family:serif; border-bottom:1px solid #a2a9b1; margin-top:15px;">$1</h2>');
        html = html.replace(/^===\s*(.*?)\s*===$'gm, '<h3 style="font-family:serif; margin-top:10px;">$1</h3>');

        // Wiki Linkler ([[Madde|Metin]] ve [[Madde]])
        html = html.replace(/\\[\[(.*?)\|(.*?)\\]\]/g, '<a class="wiki-link wiki-mavi" style="color:#0645ad;" href="#">$2</a>');
        html = html.replace(/\\[\[(.*?)\\]\]/g, '<a class="wiki-link wiki-mavi" style="color:#0645ad;" href="#">$1</a>');

        // Referanslar ([ref]Kaynak[/ref])
        let refCount = 0;
        html = html.replace(/\\[ref\\](.*?)\\[\/ref\\]/g, function(match, p1) {
            refCount++;
            return '<sup style="color:#0645ad; font-weight:bold; margin-left:2px;">[' + refCount + ']</sup>';
        });

        // Alt satırlar (\n -> <br>)
        html = html.replace(/\n/g, '<br>');

        pvIcerik.innerHTML = html;
    }

    baslikInput.addEventListener("input", renderPreview);
    icerikTextarea.addEventListener("input", renderPreview);
});
</script>

<?php get_footer(); // [cite: 10] ?>