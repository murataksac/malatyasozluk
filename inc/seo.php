<?php
/**
 * Malatya Sözlük - SEO, OpenGraph & Schema.org JSON-LD Modülü
 * Versiyon: 1.5.0
 * 
 * Bu modül; arama motorları (Google, Yandex, Bing) ve sosyal medya platformları 
 * (Facebook, X/Twitter, WhatsApp, Instagram, LinkedIn) için meta etiketleri ve 
 * Schema.org Yapısal Veri (JSON-LD) işaretlemelerini otomatik üretir.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sayfa için temiz ve özgün bir meta açıklama (description) üretir.
 */
function malatya_seo_get_description() {
    global $post;

    if ( is_singular() && ! empty( $post ) ) {
        // Özel özet varsa kullan, yoksa içerikten temizle
        if ( ! empty( $post->post_excerpt ) ) {
            $desc = $post->post_excerpt;
        } else {
            $desc = $post->post_content;
            // Shortcode ve wiki şablon/bağlantı parantezlerini temizle
            $desc = strip_shortcodes( $desc );
            $desc = preg_replace( '/\[\[([^\|\]]+)(?:\|([^\]]+))?\]\]/', '$2$1', $desc );
            $desc = preg_replace( '/\{\{([^\}]+)\}\}/', '', $desc );
            $desc = wp_strip_all_tags( $desc );
            $desc = preg_replace( '/\s+/', ' ', $desc );
        }
        $desc = trim( $desc );
        if ( mb_strlen( $desc, 'UTF-8' ) > 160 ) {
            $desc = mb_substr( $desc, 0, 157, 'UTF-8' ) . '...';
        }
        return $desc;
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( ! empty( $term->description ) ) {
            return wp_strip_all_tags( $term->description );
        }
        return sprintf( '%s ile ilgili Malatya Sözlük ansiklopedi maddeleri ve kaynaklar.', single_term_title( '', false ) );
    } elseif ( is_author() ) {
        $author = get_queried_object();
        if ( ! empty( $author->description ) ) {
            return wp_strip_all_tags( $author->description );
        }
        return sprintf( '%s - Malatya Sözlük yazar ve katkıcı profil sayfası.', $author->display_name ?: $author->user_login );
    } elseif ( is_search() ) {
        return sprintf( '"%s" için Malatya Sözlük ansiklopedi arama sonuçları.', get_search_query() );
    }

    $site_desc = get_bloginfo( 'description' );
    return $site_desc ?: 'Malatya\'nın Dijital Hafızası ve Bağımsız Ansiklopedisi.';
}

/**
 * Sayfa için görsel URL'sini belirler:
 * 1. Öne çıkan görsel (varsa)
 * 2. Madde içeriğindeki ilk görsel (varsa)
 * 3. Ekli medya dosyası (varsa)
 * 4. Varsayılan bg.jpg görseli
 */
function malatya_seo_get_image() {
    global $post;

    // 1. Öne çıkan görsel
    if ( is_singular() && ! empty( $post ) && has_post_thumbnail( $post ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
        if ( $img ) {
            return $img[0];
        }
    }

    // 2. Madde içeriğindeki ilk görsel
    if ( is_singular() && ! empty( $post ) && ! empty( $post->post_content ) ) {
        if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $matches ) ) {
            if ( ! empty( $matches[1] ) ) {
                return esc_url_raw( $matches[1] );
            }
        }
    }

    // 3. Ekli medya (Attachment)
    if ( is_singular() && ! empty( $post ) && $post->post_type === 'attachment' && wp_attachment_is_image( $post->ID ) ) {
        $att_img = wp_get_attachment_image_src( $post->ID, 'full' );
        if ( $att_img ) {
            return $att_img[0];
        }
    }

    // 4. Varsayılan bg.jpg görseli
    return get_template_directory_uri() . '/bg.jpg';
}

/**
 * Canonical URL döner.
 */
function malatya_seo_get_canonical_url() {
    if ( is_singular() ) {
        return get_permalink();
    } elseif ( is_category() || is_tag() || is_tax() ) {
        return get_term_link( get_queried_object() );
    } elseif ( is_author() ) {
        return get_author_posts_url( get_queried_object_id() );
    } elseif ( is_front_page() || is_home() ) {
        return home_url( '/' );
    }
    return home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
}

/**
 * <head> içine Meta, OpenGraph ve Twitter Card etiketlerini ekler.
 */
function malatya_seo_meta_tags() {
    $title       = wp_get_document_title();
    $description = malatya_seo_get_description();
    $image       = malatya_seo_get_image();
    $url         = malatya_seo_get_canonical_url();
    $site_name   = get_bloginfo( 'name' );

    echo "\n<!-- Malatya Sözlük SEO & OpenGraph Başlangıcı -->\n";
    echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
    echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";

    // OpenGraph
    echo '<meta property="og:locale" content="tr_TR" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
    echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
    echo '<meta property="article:publisher" content="https://www.facebook.com/malatyasozluk" />' . "\n";

    if ( is_singular( 'post' ) ) {
        global $post;
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post->ID ) ) . '" />' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c', $post->ID ) ) . '" />' . "\n";
        
        $categories = get_the_category( $post->ID );
        if ( ! empty( $categories ) ) {
            echo '<meta property="article:section" content="' . esc_attr( $categories[0]->name ) . '" />' . "\n";
        }
        
        $tags = get_the_tags( $post->ID );
        if ( ! empty( $tags ) ) {
            foreach ( $tags as $tag ) {
                echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '" />' . "\n";
            }
        }
    } elseif ( is_author() ) {
        echo '<meta property="og:type" content="profile" />' . "\n";
    } else {
        echo '<meta property="og:type" content="website" />' . "\n";
    }

    // Twitter Card & Sosyal Medya Hesapları
    $card_type = 'summary_large_image';
    echo '<meta name="twitter:card" content="' . esc_attr( $card_type ) . '" />' . "\n";
    echo '<meta name="twitter:site" content="@malatyasozluk" />' . "\n";
    echo '<meta name="twitter:creator" content="@malatyasozluk" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
    echo "<!-- Malatya Sözlük SEO & OpenGraph Sonu -->\n\n";
}
add_action( 'wp_head', 'malatya_seo_meta_tags', 1 );

/**
 * Schema.org JSON-LD Yapısal Veri İşaretlemesi
 */
function malatya_seo_schema_jsonld() {
    $site_name   = get_bloginfo( 'name' );
    $site_url    = home_url( '/' );
    $logo_url    = malatya_seo_get_image();
    $graph       = array();

    // Resmi Sosyal Medya Sayfaları
    $social_links = array(
        'https://www.facebook.com/malatyasozluk',
        'https://www.instagram.com/malatya.sozluk',
        'https://x.com/malatyasozluk'
    );

    // 1. Organization Schema
    $organization = array(
        '@type'  => 'Organization',
        '@id'    => $site_url . '#organization',
        'name'   => $site_name,
        'url'    => $site_url,
        'sameAs' => $social_links,
        'logo'   => array(
            '@type' => 'ImageObject',
            'url'   => $logo_url
        )
    );
    $graph[] = $organization;

    // 2. WebSite Schema (Arama Motoru Sitelinks Arama Çubuğu Desteği)
    $website = array(
        '@type'           => 'WebSite',
        '@id'             => $site_url . '#website',
        'url'             => $site_url,
        'name'            => $site_name,
        'description'     => get_bloginfo( 'description' ),
        'publisher'       => array( '@id' => $site_url . '#organization' ),
        'inLanguage'      => 'tr-TR',
        'sameAs'          => $social_links,
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => $site_url . '?s={search_term_string}',
            'query-input' => 'required name=search_term_string'
        )
    );
    $graph[] = $website;

    // 3. BreadcrumbList Schema
    $breadcrumbs = array();
    $breadcrumbs[] = array(
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Ana Sayfa',
        'item'     => $site_url
    );

    if ( is_singular( 'post' ) ) {
        global $post;
        $categories = get_the_category( $post->ID );
        $pos = 2;
        if ( ! empty( $categories ) ) {
            $cat = $categories[0];
            $breadcrumbs[] = array(
                '@type'    => 'ListItem',
                'position' => $pos,
                'name'     => $cat->name,
                'item'     => get_category_link( $cat->term_id )
            );
            $pos++;
        }
        $breadcrumbs[] = array(
            '@type'    => 'ListItem',
            'position' => $pos,
            'name'     => get_the_title( $post->ID ),
            'item'     => get_permalink( $post->ID )
        );
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        $breadcrumbs[] = array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => $term->name,
            'item'     => get_term_link( $term )
        );
    } elseif ( is_author() ) {
        $author = get_queried_object();
        $breadcrumbs[] = array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => $author->display_name ?: $author->user_login,
            'item'     => get_author_posts_url( $author->ID )
        );
    }

    if ( count( $breadcrumbs ) > 1 ) {
        $graph[] = array(
            '@type'           => 'BreadcrumbList',
            '@id'             => malatya_seo_get_canonical_url() . '#breadcrumb',
            'itemListElement' => $breadcrumbs
        );
    }

    // 4. Article / DefinedTerm Schema (Tekil Madde Sayfaları)
    if ( is_singular( 'post' ) ) {
        global $post;
        $author_id   = $post->post_author;
        $author_name = get_the_author_meta( 'display_name', $author_id ) ?: get_the_author_meta( 'user_login', $author_id );
        $author_url  = get_author_posts_url( $author_id );
        
        $article = array(
            '@type'            => 'Article',
            '@id'              => get_permalink( $post->ID ) . '#article',
            'isPartOf'         => array( '@id' => $site_url . '#website' ),
            'headline'         => get_the_title( $post->ID ),
            'description'      => malatya_seo_get_description(),
            'datePublished'    => get_the_date( 'c', $post->ID ),
            'dateModified'     => get_the_modified_date( 'c', $post->ID ),
            'mainEntityOfPage' => get_permalink( $post->ID ),
            'inLanguage'       => 'tr-TR',
            'author'           => array(
                '@type' => 'Person',
                'name'  => $author_name,
                'url'   => $author_url
            ),
            'publisher'        => array( '@id' => $site_url . '#organization' ),
            'image'            => malatya_seo_get_image()
        );

        $tags = get_the_tags( $post->ID );
        if ( ! empty( $tags ) ) {
            $tag_names = wp_list_pluck( $tags, 'name' );
            $article['keywords'] = implode( ', ', $tag_names );
        }

        $cats = get_the_category( $post->ID );
        if ( ! empty( $cats ) ) {
            $article['articleSection'] = $cats[0]->name;
        }

        $graph[] = $article;
    }

    // JSON-LD çıktısını yazdır
    $schema_output = array(
        '@context' => 'https://schema.org',
        '@graph'   => $graph
    );

    echo "\n<!-- Malatya Sözlük Schema.org JSON-LD Başlangıcı -->\n";
    echo '<script type="application/ld+json">' . wp_json_encode( $schema_output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
    echo "<!-- Malatya Sözlük Schema.org JSON-LD Sonu -->\n\n";
}
add_action( 'wp_head', 'malatya_seo_schema_jsonld', 2 );
