(function() {
    tinymce.create('tinymce.plugins.malatya_kelime_sistemi', {
        init : function(ed, url) {
            ed.addButton('wiki_kelime_kutusu', {
                title : 'Yerel Kelime Şablonu Ekle',
                cmd : 'wiki_kelime_ekle',
                icon: 'paste' 
            });

            ed.addCommand('wiki_kelime_ekle', function() {
                var kelime = ed.selection.getContent() || "Kelime Adı";
                var taslak = '[kelime]\n' +
                '<table>\n' +
                '    <tr><th colspan="2">' + kelime + '</th></tr>\n' +
                '    <tr><td><strong>Anlamı</strong></td><td>...</td></tr>\n' +
                '    <tr><td><strong>Kökeni</strong></td><td>...</td></tr>\n' +
                '    <tr><td><strong>Örnek</strong></td><td><span class="ornek-cumle">"..."</span></td></tr>\n' +
                '</table>\n' +
                '[/kelime]\n';
                ed.execCommand('mceInsertContent', 0, taslak);
            });
        },
    });
    tinymce.PluginManager.add('malatya_kelime_butonu', tinymce.plugins.malatya_kelime_sistemi);
})();