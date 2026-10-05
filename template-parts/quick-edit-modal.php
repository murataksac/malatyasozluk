<?php
/**
 * Malatya Sözlük - Madde Hızlı Düzenleme Ekranı (Wikipedia Stili)
 * Yalnızca giriş yapmış kullanıcılar için görüntülenir.
 */
if ( ! is_user_logged_in() ) {
    return;
}
global $post;
?>

<!-- HIZLI DÜZENLEME MODAL PENCERESİ -->
<div id="wiki-quick-edit-modal" class="wiki-modal-overlay" aria-hidden="true" role="dialog">
    <div class="wiki-modal-dialog">
        
        <!-- MODAL BAŞLIK -->
        <div class="wiki-modal-header">
            <div class="wiki-modal-title">
                <span class="modal-icon">✏️</span>
                <h3>Hızlı Düzenle: <span class="madde-adi"><?php the_title(); ?></span></h3>
            </div>
            <button type="button" class="wiki-modal-close" onclick="wikiHizliDuzenleKapat()" aria-label="Kapat">✕</button>
        </div>

        <!-- MODAL GÖVDE -->
        <div class="wiki-modal-body">
            
            <!-- GÖRSEL WP / WIKIPEDIA DÜZENLEYİCİ -->
            <div class="wiki-editor-container-wrapper">
                <?php
                $editor_id = 'wikihizlieditor';
                $editor_settings = array(
                    'textarea_name' => 'wiki_hizli_icerik',
                    'textarea_rows' => 16,
                    'media_buttons' => false, // Ortam Ekle butonunu kaldır
                    'teeny'         => false,
                    'quicktags'     => false, // Karışıklığı önlemek için sadece görsel editör modu
                    'tinymce'       => array(
                        'wpautop'             => true,
                        'verify_html'         => false,
                        'toolbar1'            => 'bold,italic,wiki_h2_butonu,wiki_h3_butonu,bullist,numlist,blockquote,|,wiki_ref_butonu,wiki_link_butonu,wiki_piped_link,wiki_sablon_butonu,wiki_bilgi_kutusu,|,removeformat,undo,redo',
                        'toolbar2'            => '',
                    ),
                );
                wp_editor( $post->post_content, $editor_id, $editor_settings );
                ?>
            </div>

            <!-- DEĞİŞİKLİK ÖZETİ -->
            <div class="wiki-edit-summary-wrapper">
                <label for="wiki-edit-summary">
                    <span>Değişiklik Özeti:</span>
                    <small style="color:#64748b; font-weight:normal;">(Yaptığınız değişikliği kısaca açıklayın)</small>
                </label>
                <input type="text" id="wiki-edit-summary" class="wiki-summary-input" placeholder="Örn: İmla hatası düzeltildi, yeni kaynak eklendi, bilgi güncellendi...">
            </div>

            <!-- CANLI ÖNİZLEME ALANI -->
            <div id="wiki-quick-preview-box" class="wiki-quick-preview-container" style="display:none;">
                <div class="preview-header-bar">
                    <span>👁️ Canlı Sayfa Önizlemesi</span>
                    <small style="color:#64748b;">(Değişikliklerin sayfada nasıl görüneceği)</small>
                </div>
                <div id="wiki-preview-content" class="preview-content-body entry-content">
                    <div style="color:#94a3b8; font-style:italic; padding:20px; text-align:center;">Önizleme yükleniyor...</div>
                </div>
            </div>

            <!-- BİLDİRİM / MESAJ KUTUSU -->
            <div id="wiki-quick-edit-alert" class="wiki-alert-box" style="display:none;"></div>

        </div>

        <!-- MODAL ALT BUTONLAR -->
        <div class="wiki-modal-footer">
            <div class="footer-left">
                <button type="button" id="wiki-btn-toggle-preview" class="wiki-btn-secondary" onclick="wikiHizliOnizleme()">
                    <span class="btn-icon">👁️</span> <span class="btn-text">Önizleme Göster</span>
                </button>
                <?php if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
                    <a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>" class="wiki-btn-admin-link" target="_blank">
                        Gelişmiş Panel Editörü ↗
                    </a>
                <?php endif; ?>
            </div>
            <div class="footer-right">
                <?php $is_yonetici = current_user_can('manage_options'); ?>
                <button type="button" class="wiki-btn-cancel" onclick="wikiHizliDuzenleKapat()">Vazgeç</button>
                <button type="button" id="wiki-btn-save-edit" class="wiki-btn-primary" onclick="wikiHizliKaydet(<?php echo $post->ID; ?>)">
                    <span class="btn-icon"><?php echo $is_yonetici ? '💾' : '📤'; ?></span> 
                    <span class="btn-text"><?php echo $is_yonetici ? 'Değişiklikleri Yayımla' : 'Onaya Gönder'; ?></span>
                </button>
            </div>
        </div>

    </div>
</div>
