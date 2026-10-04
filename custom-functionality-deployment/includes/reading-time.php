<?php
/** Konfliktfreier Startpfad fuer den Lesezeit-Shortcode. */

if ( ! function_exists( 'wg_cfp_reading_time_shortcode' ) ) {
    function wg_cfp_reading_time_shortcode() {
        if ( ! is_singular() ) {
            return '';
        }

        $post = get_post();
        if ( ! $post ) {
            return '';
        }

        $content       = $post->post_content;
        $wpm           = 300;
        $clean_content = strip_shortcodes( $content );
        $clean_content = wp_strip_all_tags( $clean_content );
        $word_count    = str_word_count( $clean_content );
        $time          = ceil( $word_count / $wpm );

        return '<span class="read-time">⏱ ' . esc_html( (string) $time ) . ' min Lesezeit</span>';
    }
}

if ( ! function_exists( 'wg_cfp_reading_time_bootstrap' ) ) {
    function wg_cfp_reading_time_bootstrap() {
        static $registered = false;

        if ( $registered || shortcode_exists( 'lesezeit' ) ) {
            return;
        }

        add_shortcode( 'lesezeit', 'wg_cfp_reading_time_shortcode' );
        $registered = true;
    }
}

add_action( 'after_setup_theme', 'wg_cfp_reading_time_bootstrap', 100 );
