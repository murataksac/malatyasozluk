<?php get_header(); ?> 
<?php get_sidebar(); ?> 

<main class="site-main"> 
<?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( in_category('yerel-kelimeler') ? 'wiki-madde-tam sozluk-sayfasi' : 'wiki-madde-tam' ); ?>>

    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>
		<!-- SAĞ SABİT SEKME BUTONLARI -->
		<div class="wiki-floating-rail">
			<button type="button" class="wiki-rail-btn aktif" onclick="wikiSekmeAc(event, 'madde-icerigi')" data-label="Madde">
				📰
			</button>

			<button type="button" class="wiki-rail-btn" onclick="wikiSekmeAc(event, 'tartisma-icerigi')" data-label="Tartışma">
				💬
			</button>

			<?php if ( is_user_logged_in() ) : ?>
			<button type="button" class="wiki-rail-btn wiki-rail-edit-btn" onclick="wikiHizliDuzenleAc()" data-label="Düzenle">
				✏️
			</button>
			<?php endif; ?>
			
		</div>
		<!-- YÖNLENDİRME BİLGİ NOTU -->
		<?php if ( isset($_GET['redirected_from']) ) : ?>
			<div class="wiki-redirect-notice">
				<span class="redirect-icon">↳</span> 
				<em>(<strong><?php echo esc_html($_GET['redirected_from']); ?></strong> sayfasından yönlendirildi)</em>
			</div>
		<?php endif; ?>

        <div id="madde-icerigi" class="wiki-sekme-alani" style="display:block;">
            <div class="entry-content">
                <?php
                // Maddenin TAM içeriğini buraya basar
                the_content();
                ?>
            </div>
			
			<!-- kategori -->
            <footer class="entry-footer wiki-madde-alti">
                <div class="wiki-kategoriler">
                    <strong>Kategoriler: </strong> <?php the_category(' | '); ?>
                </div>
                <div class="wiki-madde-bilgi">
                    <em>Bu madde en son <?php the_modified_date('j F Y'); ?> tarihinde güncellenmiştir.</em>
                </div>
				
			<!-- Madde Altı Araçlar Menüsü -->
			<div class="wiki-madde-araclari">
				<ul>
					<li><a href="<?php echo user_trailingslashit( get_permalink() . 'sayfaya-baglantilar' ); ?>">⚙️ Sayfaya bağlantılar</a></li>
					<li><a href="javascript:window.print()">🖨️ Yazdırılabilir sürüm</a></li>

					<li><a href="<?php echo user_trailingslashit( get_permalink() . 'gecmis' ); ?>">📜 Sayfa geçmişi</a></li>
					<li><a href="<?php echo esc_url( home_url( '/son-degisiklikler' ) ); ?>">🕒 Son değişiklikler</a></li>
					
					<?php if ( is_user_logged_in() ) : ?>
					<li>
						<a href="javascript:void(0)" onclick="wikiHizliDuzenleAc()" data-label="Hızlı Düzenle">
							✏️ Hızlı Düzenle
						</a>
					</li>
					<?php endif; ?>
					
					<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
					<li>
						<a href="<?php echo get_edit_post_link(); ?>" data-label="Gelişmiş Düzenle">
							⚙️ Panelden Düzenle
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</div>
            </footer>
            
            <!-- SOSYAL MEDYA PAYLAŞIM ALANI -->
            <?php get_template_part( 'template-parts/share-box' ); ?>
            
			<!-- İlgili maddeler -->
			<style>
				.wiki-ilgili-maddeler-modern { margin-top: 0px; padding-top: 20px; border-top: 1px solid #a2a9b1; }
				/* 250px yaparak 3 sütunun sığmasını garanti altına alıyoruz [cite: 31] */
				
				.wiki-grid-3 { 
					display: grid; 
				
					/* Ekran genişliğine bakmaksızın alanı 3 eşit parçaya böler */
					grid-template-columns: repeat(3, 1fr); 
					gap: 15px; 
					margin-top: 15px; 
				}

				/* Mobilde (900px altı) düzgün görünmesi için tek sütuna düşürelim */
				@media screen and (max-width: 900px) {
					.wiki-grid-3 { grid-template-columns: 1fr; }
				}
				.wiki-kart-modern { 
					background: #fff; border: 1px solid #c8ccd1; padding: 15px; border-radius: 4px;
					transition: all 0.3s ease;
				}
				.wiki-kart-modern:hover { border-color: #3366cc; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
				.wiki-kart-modern h4 { margin: 0 0 8px 0; font-size: 1.1em; border: none; }
				.wiki-kart-modern p { font-size: 0.85em; color: #54595d; margin: 0; }
			</style>

			<div class="wiki-ilgili-maddeler-modern">
				<!-- <h3 style="border:none; font-size: 1.3em;">İlginizi Çekebilir</h3> -->
				<div class="wiki-grid-3">
					<?php
					$ilgili_maddeler = malatya_sozluk_puanli_maddeleri_getir(get_the_ID());
					foreach ($ilgili_maddeler as $m_id => $puan) : ?>
						<div class="wiki-kart-modern">
							<h4><a href="<?php echo get_permalink($m_id); ?>" style="color:#0645ad; text-decoration:none;"><?php echo get_the_title($m_id); ?></a></h4>
							<p><?php echo wp_trim_words(get_the_excerpt($m_id), 10); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
        </div>
	
		<!-- tartışma -->
        <div id="tartisma-icerigi" class="wiki-sekme-alani" style="display:none;">
            <?php
            // Tartışma şablonunu (comments.php) buraya çağırıyoruz
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>
        </div>

</article>
<?php endwhile; ?>

<?php 
// Giriş yapmış kullanıcılar için Wikipedia stili hızlı düzenleme penceresi
if ( is_user_logged_in() ) {
    get_template_part( 'template-parts/quick-edit-modal' );
}
?>

</main>
<?php get_footer(); ?>