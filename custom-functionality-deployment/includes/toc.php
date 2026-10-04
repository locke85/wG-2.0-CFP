<?php
/**
 * Konfliktfreier TOC-Startpfad.
 *
 * Theme-Code wird nach Plugins geladen. Deshalb faellt die Entscheidung, ob
 * CFP den gemeinsamen Shortcode und Content-Filter registriert, erst auf
 * after_setup_theme.
 */

if ( ! function_exists( 'wg_cfp_toc_debug' ) ) {
    function wg_cfp_toc_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[TOC DEBUG] ' . $message );
        }
    }
}

if ( ! function_exists( 'wg_cfp_toc_theme_owns_feature' ) ) {
    function wg_cfp_toc_theme_owns_feature() {
        $has_shortcode     = shortcode_exists( 'toc' );
        $has_anchor_filter = false !== has_filter( 'the_content', 'wp_h2_add_anchors' );

        return $has_shortcode || $has_anchor_filter;
    }
}

if ( ! function_exists( 'wg_cfp_toc_shortcode' ) ) {
    function wg_cfp_toc_shortcode() {
        if ( ! is_singular() ) {
            wg_cfp_toc_debug( 'Nicht singular.' );
            return '';
        }

        global $post;
        if ( ! $post ) {
            wg_cfp_toc_debug( 'Kein Post gefunden.' );
            return '';
        }

        preg_match_all( '/<h2\\b[^>]*>(.*?)<\\/h2>/is', $post->post_content, $matches );

        if ( empty( $matches[1] ) ) {
            wg_cfp_toc_debug( 'Keine H2 gefunden.' );
            return '';
        }

        $toc = '<div style="text-align:center;"><ul style="margin-left:20px; text-align:left;">';

        foreach ( $matches[1] as $heading ) {
            $heading_text = wp_strip_all_tags( $heading );
            $slug         = sanitize_title( $heading_text );
            $toc         .= '<li><a href="#' . esc_attr( $slug ) . '">' . esc_html( $heading_text ) . '</a></li>';
        }

        $toc .= '</ul></div>';

        wg_cfp_toc_debug( 'TOC erfolgreich generiert.' );

        return $toc;
    }
}

if ( ! function_exists( 'wg_cfp_toc_add_anchors' ) ) {
    function wg_cfp_toc_add_anchors( $content ) {
        if ( ! is_singular() ) {
            wg_cfp_toc_debug( 'Anchor: Nicht singular.' );
            return $content;
        }

        return preg_replace_callback(
            '/<h2\\b([^>]*)>(.*?)<\\/h2>/is',
            'wg_cfp_toc_add_anchor_to_heading',
            $content
        );
    }
}

if ( ! function_exists( 'wg_cfp_toc_add_anchor_to_heading' ) ) {
    function wg_cfp_toc_add_anchor_to_heading( $matches ) {
        $attributes = $matches[1];
        $heading    = $matches[2];

        if ( preg_match( '/\\bid\\s*=\\s*(["\']).*?\\1/i', $attributes ) ) {
            return $matches[0];
        }

        $slug = sanitize_title( wp_strip_all_tags( $heading ) );

        if ( '' === $slug ) {
            return $matches[0];
        }

        wg_cfp_toc_debug( 'Anchor hinzugefuegt: ' . $slug );

        return '<h2' . $attributes . '><a id="' . esc_attr( $slug ) . '"></a>' . $heading . '</h2>';
    }
}

if ( ! function_exists( 'wg_cfp_toc_bootstrap' ) ) {
    function wg_cfp_toc_bootstrap() {
        static $registered = false;

        if ( $registered || wg_cfp_toc_theme_owns_feature() ) {
            return;
        }

        if ( ! shortcode_exists( 'toc' ) ) {
            add_shortcode( 'toc', 'wg_cfp_toc_shortcode' );
        }

        if ( false === has_filter( 'the_content', 'wg_cfp_toc_add_anchors' ) ) {
            add_filter( 'the_content', 'wg_cfp_toc_add_anchors' );
        }

        $registered = true;
    }
}

add_action( 'after_setup_theme', 'wg_cfp_toc_bootstrap', 100 );
