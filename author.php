<?php
/**
 * Malatya Sözlük - Kullanıcı Profil Sayfası (User Profile & Contributions)
 * 
 * Kayıtlı kullanıcıların profil bilgilerini, başlattığı maddeleri,
 * yaptığı düzenleme katkılarını ve tartışma yorumlarını gösterir.
 */

get_header(); 
get_sidebar(); 

$author = get_queried_object();
if ( ! $author || ! isset($author->ID) ) {
    $author_id = get_query_var('author');
    $author = get_userdata($author_id);
}

if ( ! $author ) {
    echo '<main class="site-main"><p>Kullanıcı bulunamadı.</p></main>';
    get_footer();
    exit;
}

$author_id       = $author->ID;
$current_user_id = get_current_user_id();
$is_own_profile  = ( $current_user_id === $author_id );

// Rol / Unvan / Rozet Belirleme (Oyunlaştırma)
$rozet = function_exists('malatya_sozluk_kullanici_rozet_getir') ? malatya_sozluk_kullanici_rozet_getir($author_id) : array('isim' => '👤 Sözlük Üyesi', 'renk' => '#0f172a', 'bg' => '#f1f5f9');
$rol_etiket = $rozet['isim'];
$rozet_renk = $rozet['renk'];
$rozet_bg   = $rozet['bg'];

// Eğer yönetici veya editörse üstüne yaz
$user_roles = (array) $author->roles;
if ( in_array('administrator', $user_roles) ) {
    $rol_etiket = '🛡️ YÖNETİCİ (' . $rozet['isim'] . ')';
    $rozet_renk = '#b91c1c';
    $rozet_bg   = '#fee2e2';
} elseif ( in_array('editor', $user_roles) ) {
    $rol_etiket = '⭐ BAŞ EDİTÖR (' . $rozet['isim'] . ')';
    $rozet_renk = '#4338ca';
    $rozet_bg   = '#e0e7ff';
}

// İstatistikler
$madde_sayisi = count_user_posts($author_id, 'post', true);
$katkilar     = function_exists('malatya_wiki_kullanici_katkilari_getir') ? malatya_wiki_kullanici_katkilari_getir($author_id) : array();
$katki_sayisi = count($katkilar);

$yorum_sayisi_args = array(
    'user_id' => $author_id,
    'count'   => true,
    'status'  => 'approve'
);
$yorum_sayisi = get_comments($yorum_sayisi_args);

$kayit_tarihi = date_i18n('j F Y', strtotime($author->user_registered));
$bio = $author->description;
?>

<main class="site-main">
    <div class="wiki-profil-wrapper">
        
        <!-- PROFİL ÜST KART -->
        <div class="wiki-profil-kart">
            <div class="profil-sol-alan">
                <div class="profil-avatar-cerceve">
                    <?php echo get_avatar($author_id, 96, '', esc_attr($author->display_name), array('class' => 'wiki-profil-avatar-img')); ?>
                </div>
                <div class="profil-kimlik-bilgi">
                    <div class="profil-isim-satir">
                        <h1 class="profil-gorunen-isim"><?php echo esc_html($author->display_name ?: $author->user_login); ?></h1>
                        <span class="profil-rol-rozet" style="background-color: <?php echo esc_attr($rozet_bg); ?>; color: <?php echo esc_attr($rozet_renk); ?>; border: 1px solid <?php echo esc_attr($rozet_renk); ?>; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; margin-top: 5px;"><?php echo esc_html($rol_etiket); ?></span>
                    </div>
                    <div class="profil-meta-satir">
                        <span class="profil-kullanici-adi">@<?php echo esc_html($author->user_login); ?></span>
                        <span class="meta-nokta">•</span>
                        <span class="profil-kayit-tarihi">📅 Üyelik: <?php echo esc_html($kayit_tarihi); ?></span>
                        <?php if ( ! empty($author->user_url) ) : ?>
                            <span class="meta-nokta">•</span>
                            <span class="profil-web"><a href="<?php echo esc_url($author->user_url); ?>" target="_blank" rel="nofollow">🌐 Web Sitesi</a></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( ! empty($bio) ) : ?>
                        <div class="profil-biyografi">
                            <p><?php echo nl2br(esc_html($bio)); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="profil-biyografi-bos">
                            <em>Bu kullanıcı henüz hakkında yazısı eklememiş.</em>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- İSTATİSTİK KUTULARI -->
            <div class="profil-istatistik-kutulari">
                <div class="stat-kutu">
                    <span class="stat-sayi"><?php echo number_format_i18n($madde_sayisi); ?></span>
                    <span class="stat-etiket">📝 Başlatılan Madde</span>
                </div>
                <div class="stat-kutu">
                    <span class="stat-sayi"><?php echo number_format_i18n($katki_sayisi); ?></span>
                    <span class="stat-etiket">✏️ Düzenleme Katkısı</span>
                </div>
                <div class="stat-kutu">
                    <span class="stat-sayi"><?php echo number_format_i18n($yorum_sayisi); ?></span>
                    <span class="stat-etiket">💬 Tartışma Mesajı</span>
                </div>
            </div>
        </div>

        <!-- PROFİL SEKME BUTONLARI -->
        <div class="wiki-profil-sekme-bar">
            <button type="button" class="wiki-profil-tab-btn aktif" onclick="wikiProfilSekmeDegistir(event, 'tab-katkilar')">
                <span class="tab-ikon">✏️</span> Düzenleme Katkıları (<?php echo $katki_sayisi; ?>)
            </button>
            <button type="button" class="wiki-profil-tab-btn" onclick="wikiProfilSekmeDegistir(event, 'tab-maddeler')">
                <span class="tab-ikon">📰</span> Başlattığı Maddeler (<?php echo $madde_sayisi; ?>)
            </button>
            <button type="button" class="wiki-profil-tab-btn" onclick="wikiProfilSekmeDegistir(event, 'tab-yorumlar')">
                <span class="tab-ikon">💬</span> Tartışma Yorumları (<?php echo $yorum_sayisi; ?>)
            </button>
            <?php if ( $is_own_profile ) : ?>
                <button type="button" class="wiki-profil-tab-btn tab-btn-ayar" onclick="wikiProfilSekmeDegistir(event, 'tab-ayarlar')">
                    <span class="tab-ikon">⚙️</span> Profili Düzenle
                </button>
            <?php endif; ?>
        </div>

        <!-- SEKME İÇERİKLERİ -->
        <div class="wiki-profil-sekme-icerikleri">
            
            <!-- SEKME 1: DÜZENLEME KATKILARI -->
            <div id="tab-katkilar" class="wiki-profil-tab-pane" style="display:block;">
                <div class="pane-baslik-kutusu">
                    <h3>📜 Madde Düzenleme ve Revizyon Katkıları</h3>
                    <p>Kullanıcının sözlük maddelerine yaptığı doğrudan veya onaylanmış tüm düzenlemeler:</p>
                </div>

                <?php if ( ! empty($katkilar) ) : ?>
                    <div class="wiki-katki-tablo-wrapper">
                        <table class="wiki-katki-tablosu">
                            <thead>
                                <tr>
                                    <th style="width:170px;">Tarih</th>
                                    <th>Madde Adı</th>
                                    <th style="width:150px;">İşlem Türü</th>
                                    <th>Değişiklik Özeti</th>
                                    <th style="width:100px;">Geçmiş</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $katkilar as $katki ) : 
                                    $tarih_str = date_i18n('j F Y, H:i', strtotime($katki['tarih']));
                                    $post_title = !empty($katki['post_title']) ? $katki['post_title'] : 'Madde';
                                    $post_url = !empty($katki['post_url']) ? $katki['post_url'] : get_permalink($katki['post_id']);
                                    $gecmis_url = user_trailingslashit($post_url . 'gecmis');
                                    $islem = !empty($katki['islem']) ? $katki['islem'] : 'Düzenleme';
                                    $ozet = !empty($katki['ozet']) ? $katki['ozet'] : 'Açıklama belirtilmemiş.';
                                ?>
                                    <tr>
                                        <td class="td-tarih">🕒 <?php echo esc_html($tarih_str); ?></td>
                                        <td class="td-madde">
                                            <strong><a href="<?php echo esc_url($post_url); ?>"><?php echo esc_html($post_title); ?></a></strong>
                                        </td>
                                        <td class="td-islem">
                                            <span class="katki-islem-rozet"><?php echo esc_html($islem); ?></span>
                                        </td>
                                        <td class="td-ozet">
                                            <em>"<?php echo esc_html($ozet); ?>"</em>
                                        </td>
                                        <td class="td-aksiyon">
                                            <a href="<?php echo esc_url($gecmis_url); ?>" class="wiki-mini-link" title="Madde geçmişini görüntüle">Geçmiş ↗</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="wiki-bos-liste-mesaji">
                        <p>Henüz kayıtlı bir düzenleme katkısı bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SEKME 2: BAŞLATTIĞI MADDELER -->
            <div id="tab-maddeler" class="wiki-profil-tab-pane" style="display:none;">
                <div class="pane-baslik-kutusu">
                    <h3>📝 Başlatılan Sözlük Maddeleri</h3>
                    <p>Bu yazar tarafından sıfırdan oluşturulan ve yayımlanan maddeler:</p>
                </div>

                <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $yazar_maddeleri = new WP_Query(array(
                    'author'         => $author_id,
                    'post_type'      => 'post',
                    'posts_per_page' => 20,
                    'post_status'    => 'publish',
                    'paged'          => $paged
                ));

                if ( $yazar_maddeleri->have_posts() ) : ?>
                    <div class="wiki-yazar-madde-grid">
                        <?php while ( $yazar_maddeleri->have_posts() ) : $yazar_maddeleri->the_post(); ?>
                            <div class="wiki-yazar-madde-karti">
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <div class="madde-ozet">
                                    <?php echo wp_trim_words(get_the_excerpt(), 18); ?>
                                </div>
                                <div class="madde-kart-alt">
                                    <span>📅 <?php echo get_the_date('j F Y'); ?></span>
                                    <span>📂 <?php the_category(', '); ?></span>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php else : ?>
                    <div class="wiki-bos-liste-mesaji">
                        <p>Bu kullanıcı henüz kendi adına bir madde başlatmamış.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SEKME 3: TARTIŞMA YORUMLARI -->
            <div id="tab-yorumlar" class="wiki-profil-tab-pane" style="display:none;">
                <div class="pane-baslik-kutusu">
                    <h3>💬 Madde Tartışma Katılımları</h3>
                    <p>Kullanıcının madde tartışma panolarına yazdığı mesajlar ve görüşler:</p>
                </div>

                <?php
                $user_comments = get_comments(array(
                    'user_id' => $author_id,
                    'status'  => 'approve',
                    'number'  => 30
                ));

                if ( ! empty($user_comments) ) : ?>
                    <div class="wiki-profil-yorum-listesi">
                        <?php foreach ( $user_comments as $c ) : 
                            $madde = get_post($c->comment_post_ID);
                            if ( ! $madde ) continue;
                            $c_url = get_permalink($madde->ID) . '#comment-' . $c->comment_ID;
                            $c_tarih = date_i18n('j F Y, H:i', strtotime($c->comment_date));
                        ?>
                            <div class="wiki-profil-yorum-karti">
                                <div class="yorum-kart-ust">
                                    <span class="yorum-madde-baslik">
                                        📰 <a href="<?php echo esc_url(get_permalink($madde->ID)); ?>"><strong><?php echo esc_html($madde->post_title); ?></strong></a> maddesinde:
                                    </span>
                                    <span class="yorum-tarih">🕒 <?php echo esc_html($c_tarih); ?></span>
                                </div>
                                <div class="yorum-kart-metin">
                                    <?php echo wpautop(esc_html($c->comment_content)); ?>
                                </div>
                                <div class="yorum-kart-alt">
                                    <a href="<?php echo esc_url($c_url); ?>" class="wiki-mini-link">Tartışmaya Git ↗</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="wiki-bos-liste-mesaji">
                        <p>Henüz tartışma panolarına yazılmış bir mesaj bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SEKME 4: PROFİL DÜZENLE (Sadece kendi profili ise) -->
            <?php if ( $is_own_profile ) : ?>
                <div id="tab-ayarlar" class="wiki-profil-tab-pane" style="display:none;">
                    <div class="pane-baslik-kutusu">
                        <h3>⚙️ Profil Bilgilerini Düzenle</h3>
                        <p>Sözlük profilinizde görünen bilgilerinizi güncelleyin:</p>
                    </div>

                    <form id="wiki-profil-form" class="wiki-profil-duzenle-form" onsubmit="wikiProfilGuncelle(event)">
                        <div id="wiki-profil-mesaj" class="wiki-alert-box" style="display:none; margin-bottom:15px;"></div>

                        <div class="form-satir">
                            <label for="prof_display_name"><strong>Görünen İsim:</strong></label>
                            <input type="text" id="prof_display_name" name="display_name" value="<?php echo esc_attr($author->display_name); ?>" required class="wiki-form-input">
                        </div>

                        <div class="form-satir">
                            <label for="prof_user_url"><strong>Web Sitesi / Sosyal Link:</strong></label>
                            <input type="url" id="prof_user_url" name="user_url" value="<?php echo esc_attr($author->user_url); ?>" placeholder="https://..." class="wiki-form-input">
                        </div>

                        <div class="form-satir">
                            <label for="prof_description"><strong>Biyografi / Hakkımda:</strong></label>
                            <textarea id="prof_description" name="description" rows="4" placeholder="Kendinizden ve ilgi alanlarınızdan bahsedin..." class="wiki-form-textarea"><?php echo esc_textarea($author->description); ?></textarea>
                        </div>

                        <div class="form-alt-buton">
                            <button type="submit" id="btn-profil-kaydet" class="wiki-btn-primary">
                                💾 Bilgilerimi Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script>
// Profil Sekme Değiştirme
function wikiProfilSekmeDegistir(event, tabId) {
    event.preventDefault();
    document.querySelectorAll('.wiki-profil-tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.wiki-profil-tab-btn').forEach(btn => btn.classList.remove('aktif'));
    
    const hedef = document.getElementById(tabId);
    if (hedef) hedef.style.display = 'block';
    event.currentTarget.classList.add('aktif');
}

// Profil Güncelleme AJAX
function wikiProfilGuncelle(event) {
    event.preventDefault();
    const form = document.getElementById('wiki-profil-form');
    const msgBox = document.getElementById('wiki-profil-mesaj');
    const btn = document.getElementById('btn-profil-kaydet');

    if (!form || !btn) return;

    btn.disabled = true;
    btn.innerText = 'Kaydediliyor...';
    if (msgBox) msgBox.style.display = 'none';

    const formData = new FormData(form);
    formData.append('action', 'malatya_profil_guncelle');
    formData.append('nonce', (typeof malatya_ajax !== 'undefined' && malatya_ajax.nonce) ? malatya_ajax.nonce : '');

    const ajaxUrl = (typeof malatya_ajax !== 'undefined' && malatya_ajax.ajax_url) ? malatya_ajax.ajax_url : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = '💾 Bilgilerimi Kaydet';
            if (msgBox) {
                msgBox.style.display = 'block';
                if (data.success) {
                    msgBox.className = 'wiki-alert-box wiki-alert-success';
                    msgBox.innerHTML = '<strong>✓ Başarılı:</strong> ' + data.data.message;
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    msgBox.className = 'wiki-alert-box wiki-alert-error';
                    msgBox.innerHTML = '<strong>Hata:</strong> ' + data.data.message;
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = '💾 Bilgilerimi Kaydet';
            if (msgBox) {
                msgBox.style.display = 'block';
                msgBox.className = 'wiki-alert-box wiki-alert-error';
                msgBox.innerHTML = '<strong>Bağlantı Hatası:</strong> ' + err.message;
            }
        });
}
</script>

<?php get_footer(); ?>
