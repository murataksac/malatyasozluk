(function() {
    tinymce.PluginManager.add('wiki_ref_butonu', function(editor, url) {
        
        var refCss = 
            '.wiki-editor-ref { display: inline !important; font-size: 0.88em !important; font-style: italic !important; background-color: #f1f5f9 !important; color: #475569 !important; border-radius: 3px !important; padding: 1px 4px !important; margin: 0 2px !important; border: none !important; box-shadow: none !important; line-height: inherit !important; } ' +
            '.wiki-editor-ref::before { content: none !important; display: none !important; }';

        // 1. Editör içine stilleri enjekte et
        function applyStyles() {
            if (editor.dom) {
                editor.dom.addStyle(refCss);
            }
        }
        editor.on('init', applyStyles);
        editor.on('PreInit', applyStyles);

        // 2. [ref] etiketlerini span rozetine dönüştürme fonksiyonu
        function formatRefs(content) {
            if (!content || typeof content !== 'string') return content;
            // Zaten sarılmış olanları tekrar sarmadan, tüm [ref]...[/ref] bloklarını rozet içine al
            return content.replace(/(<span[^>]*class=["'][^"']*wiki-editor-ref[^"']*["'][^>]*>[\s\S]*?<\/span>)|(\[ref\]([\s\S]*?)\[\/ref\])/gi, function(match, alreadyWrapped, fullRef, refInner) {
                if (alreadyWrapped) {
                    return alreadyWrapped;
                }
                return '<span class="wiki-editor-ref" data-mce-ref="1">[ref]' + refInner + '[/ref]</span>';
            });
        }

        // 3. Editörden veri alınırken (Kaydederken veya HTML sekmesine geçerken) span etiketini temizle
        function cleanRefs(content) {
            if (!content || typeof content !== 'string') return content;
            return content.replace(/<span[^>]*class=["'][^"']*wiki-editor-ref[^"']*["'][^>]*>([\s\S]*?)<\/span>/gi, '$1');
        }

        // Editöre veri girerken biçimlendir
        editor.on('BeforeSetContent', function(e) {
            if (e.content) {
                e.content = formatRefs(e.content);
            }
        });

        // Editör yüklendiğinde ve içerik değiştiğinde kontrol et
        editor.on('SetContent LoadContent', function() {
            applyStyles();
        });

        // Editörden veri kaydedilirken span'ları temizle
        editor.on('GetContent', function(e) {
            if (e.content && e.format !== 'raw') {
                e.content = cleanRefs(e.content);
            }
        });

        // Klavye ile yazarken veya odak değiştiğinde otomatik rozetleştirme
        var typingTimer;
        editor.on('keyup change blur', function(e) {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                var body = editor.getBody();
                if (body && body.innerHTML && body.innerHTML.indexOf('[ref]') !== -1) {
                    var currentHtml = body.innerHTML;
                    var formatted = formatRefs(currentHtml);
                    if (formatted !== currentHtml) {
                        // Seçim konumunu korumaya çalışarak güncelle
                        var bookmark = editor.selection.getBookmark(2, true);
                        body.innerHTML = formatted;
                        try {
                            editor.selection.moveToBookmark(bookmark);
                        } catch(err) {}
                    }
                }
            }, 1200);
        });

        // =========================================================================
        // 4. ANSİKLOPEDİ & BAŞLIK ARAÇ ÇUBUĞU BUTONLARI
        // =========================================================================
        
        // A) H2 Başlık Butonu
        editor.addButton('wiki_h2_butonu', {
            text: 'H2 Başlık',
            tooltip: 'H2 Alt Başlık Yap',
            onclick: function() {
                editor.execCommand('FormatBlock', false, 'h2');
            }
        });

        // B) H3 Başlık Butonu
        editor.addButton('wiki_h3_butonu', {
            text: 'H3 Başlık',
            tooltip: 'H3 Küçük Başlık Yap',
            onclick: function() {
                editor.execCommand('FormatBlock', false, 'h3');
            }
        });

        // C) [ref] Kaynakça Butonu
        editor.addButton('wiki_ref_butonu', {
            text: '📚 [ref] Kaynak',
            tooltip: 'Ansiklopedi Kaynağı Ekle ([ref])',
            onclick: function() {
                var seciliMetin = editor.selection.getContent({format: 'text'});
                if (seciliMetin && seciliMetin.trim() !== '') {
                    editor.insertContent('<span class="wiki-editor-ref" data-mce-ref="1">[ref]' + seciliMetin.trim() + '[/ref]</span>&nbsp;');
                } else {
                    var kaynakMetni = window.prompt("Kaynağı veya linki girin:\n(Örn: Kemal Deniz, Malatya Tarihi, s.45 veya https://malatyasozluk.com/Malatya)");
                    if (kaynakMetni !== null && kaynakMetni.trim() !== '') {
                        editor.insertContent('<span class="wiki-editor-ref" data-mce-ref="1">[ref]' + kaynakMetni.trim() + '[/ref]</span>&nbsp;');
                    }
                }
            }
        });

        // D) [[ ]] İç Link Butonu
        editor.addButton('wiki_link_butonu', {
            text: '🔗 [[İç Link]]',
            tooltip: 'İç Link Ekle (Maddeye Bağla)',
            onclick: function() {
                var seciliMetin = editor.selection.getContent({format: 'text'});
                if (seciliMetin && seciliMetin.trim() !== '') {
                    editor.insertContent('[[' + seciliMetin.trim() + ']]');
                } else {
                    var linkMetni = window.prompt("Bağlanacak maddenin adını girin:\n(Örn: Battalgazi)");
                    if (linkMetni !== null && linkMetni.trim() !== '') {
                        editor.insertContent('[[' + linkMetni.trim() + ']]');
                    }
                }
            }
        });

        // E) [[Madde|Metin]] Gelişmiş İç Link Butonu
        editor.addButton('wiki_piped_link', {
            text: '🔀 [[Madde|Metin]]',
            tooltip: 'Farklı Metinle Link Ver ([[Madde|Metin]])',
            onclick: function() {
                var madde = window.prompt("Bağlanacak asıl madde adı nedir?\n(Örn: Battalgazi)");
                if (madde && madde.trim() !== '') {
                    var metin = window.prompt("Sayfada hangi metin görünsün?\n(Örn: Eski Malatya)");
                    if (metin && metin.trim() !== '') {
                        editor.insertContent('[[' + madde.trim() + '|' + metin.trim() + ']]');
                    }
                }
            }
        });

        // F) {{Şablon}} Butonu
        editor.addButton('wiki_sablon_butonu', {
            text: '📑 {{Şablon}}',
            tooltip: 'Şablon / Navbox Ekle ({{Şablon Adı}})',
            onclick: function() {
                var val = window.prompt("Eklenecek şablon sayfasının adı:\n(Örn: Malatya Tarihi)");
                if (val && val.trim() !== '') {
                    editor.insertContent('{{' + val.trim() + '}}');
                }
            }
        });

    });
})();