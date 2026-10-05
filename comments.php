<?php
// Şifreli yazılarda yorumları gizle
if ( post_password_required() ) {
    return;
}
?>

<div id="tartisma" class="wiki-comments-area">
    <div class="tartisma-baslik">
        <h2>Tartışma Panosu</h2>
        <p class="tartisma-aciklama">Bu maddeyi geliştirmek için bilgi ve kaynak paylaşımında bulunun.</p>
    </div>

    <?php if ( have_comments() ) : ?>
        <ul class="wiki-comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ul',
                'short_ping'  => true,
                'avatar_size' => 40,
                'type'        => 'comment',
            ) );
            ?>
        </ul>

        <?php the_comments_navigation(); ?>

    <?php endif; // Check for have_comments(). ?>

    <?php
    // Yorumlar kapalıysa uyarı ver
    if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
    ?>
        <p class="no-comments">Bu madde için tartışma kapatılmıştır.</p>
    <?php endif; ?>

    <?php
    // Özel Tartışma Formu (Ansiklopedi dilinde)
    $comment_args = array(
        'title_reply'          => 'Tartışmaya Katıl',
        'title_reply_to'       => '%s adlı kullanıcıya yanıt ver',
        'cancel_reply_link'    => 'Yanıtlamayı İptal Et',
        'label_submit'         => 'Tartışmaya Ekle',
        'comment_field'        => '<p class="comment-form-comment"><label for="comment">Görüşünüz veya Kaynağınız</label><textarea id="comment" name="comment" cols="45" rows="5" required="required"></textarea></p>',
        'class_submit'         => 'wiki-submit-button',
    );
    comment_form( $comment_args );
    ?>
</div>