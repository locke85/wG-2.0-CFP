<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) || ! wg_cfp_is_main_site_context() ) {
    return;
}

if ( ! function_exists( 'wg_cfp_allow_user_description_html' ) ) {
    function wg_cfp_allow_user_description_html() {
        return (bool) apply_filters( 'wg_cfp_allow_user_description_html', false );
    }
}

if ( ! function_exists( 'wg_cfp_enable_user_description_html' ) ) {
    function wg_cfp_enable_user_description_html() {
        if ( ! wg_cfp_allow_user_description_html() ) {
            return;
        }

        remove_filter( 'pre_user_description', 'wp_filter_kses' );
        add_filter( 'pre_user_description', 'wp_kses_post' );
        remove_filter( 'edit_user_description', 'wp_filter_kses' );
        add_filter( 'edit_user_description', 'wp_kses_post' );
    }
}
add_action( 'init', 'wg_cfp_enable_user_description_html' );
