(function() {
    tinymce.create('tinymce.plugins.malatya_bilgi_sistemi', {
        init : function(ed, url) {
            ed.addButton('wiki_bilgi_kutusu', {
                text : '📋 [bilgi] Kutusu',
                tooltip : 'Bilgi Kutusu Taslağı Ekle',
                cmd : 'wiki_bilgi_ekle'
            });

            ed.addCommand('wiki_bilgi_ekle', function() {
                var baslik = ed.selection.getContent({format: 'text'}) || "Madde Adı";
                var taslak = '\n[bilgi]\n' +
                    '<table>\n' +
                    '    <tr><th colspan="2">' + baslik + '</th></tr>\n' +
                    '    <tr><td colspan="2">[[Dosya:Gorsel.jpg]]</td></tr>\n' +
                    '    <tr><td><strong>Konum</strong></td><td>Malatya</td></tr>\n' +
                    '    <tr><td><strong>Önemli Özellik</strong></td><td>...</td></tr>\n' +
                    '    <tr><td><strong>Tarih</strong></td><td>...</td></tr>\n' +
                    '</table>\n' +
                    '[/bilgi]\n';
                ed.execCommand('mceInsertContent', false, taslak);
            });
        },
    });
    tinymce.PluginManager.add('malatya_bilgi_butonu', tinymce.plugins.malatya_bilgi_sistemi);
})();