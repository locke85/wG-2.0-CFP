<?php
/**
 * Optional renderer for GenerateBlocks Pro forms inserted into static shells.
 * WordPress pages continue to use the native GB-Pro render path unchanged.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wg_cfp_gb_static_form_route( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) { return ''; }
	$path = wp_parse_url( $value, PHP_URL_PATH );
	$path = is_string( $path ) ? $path : $value;
	$path = trim( $path, '/' );
	return '' === $path ? '/' : '/' . $path . '/';
}

/**
 * @return array<int, array{form_id:int,allowed_routes:array<int,string>}>
 */
function wg_cfp_gb_static_forms() {
	$forms = apply_filters( 'wg_cfp_generateblocks_static_forms', array() );
	$normalized = array();
	foreach ( is_array( $forms ) ? $forms : array() as $form ) {
		if ( ! is_array( $form ) || ! absint( $form['form_id'] ?? 0 ) ) { continue; }
		$routes = array_values( array_filter( array_map( 'wg_cfp_gb_static_form_route', (array) ( $form['allowed_routes'] ?? array() ) ) ) );
		if ( empty( $routes ) ) { continue; }
		$normalized[ absint( $form['form_id'] ) ] = array( 'form_id' => absint( $form['form_id'] ), 'allowed_routes' => $routes );
	}
	return $normalized;
}

function wg_cfp_gb_static_form_render( WP_REST_Request $request ) {
	$form_id = absint( $request->get_param( 'form_id' ) );
	$forms = wg_cfp_gb_static_forms();
	$form = $forms[ $form_id ] ?? null;
	$route = wg_cfp_gb_static_form_route( $request->get_param( 'route' ) );
	if ( ! is_array( $form ) || ! in_array( $route, $form['allowed_routes'], true ) ) {
		return new WP_Error( 'wg_cfp_static_form_not_found', 'Formular ist nicht verfügbar.', array( 'status' => 404 ) );
	}
	if ( ! class_exists( 'GenerateBlocks_Pro_Form_Render' ) ) {
		return new WP_Error( 'wg_cfp_static_form_unavailable', 'Formular ist nicht verfügbar.', array( 'status' => 503 ) );
	}
	$html = GenerateBlocks_Pro_Form_Render::render( $form_id, 'public', array( 'host_post_id' => absint( $request->get_param( 'post_id' ) ) ) );
	if ( is_wp_error( $html ) || ! is_string( $html ) ) {
		return new WP_Error( 'wg_cfp_static_form_unavailable', 'Formular ist nicht verfügbar.', array( 'status' => 503 ) );
	}
	$html = str_replace( '</form>', '<p data-wg-cfp-form-status role="status" aria-live="polite"></p></form>', $html );
	return rest_ensure_response( array(
		'html' => $html, 'form_id' => $form_id, 'cfp_config' => array( 'formIds' => array( $form_id ) ),
		'form_style_url' => defined( 'GENERATEBLOCKS_PRO_DIR_URL' ) ? GENERATEBLOCKS_PRO_DIR_URL . 'dist/form-style.css?ver=' . rawurlencode( (string) GENERATEBLOCKS_PRO_VERSION ) : '',
		'form_script_url' => defined( 'GENERATEBLOCKS_PRO_DIR_URL' ) ? GENERATEBLOCKS_PRO_DIR_URL . 'dist/form.js?ver=' . rawurlencode( (string) GENERATEBLOCKS_PRO_VERSION ) : '',
		'cfp_script_url' => WG_CFP_PLUGIN_URL . 'assets/js/form-abuse-guard.js?ver=' . rawurlencode( WG_CFP_VERSION ),
	) );
}
add_action( 'rest_api_init', static function () {
	register_rest_route( 'wg-cfp/v1', '/static-form/(?P<form_id>\d+)', array(
		'methods' => WP_REST_Server::READABLE, 'permission_callback' => '__return_true', 'callback' => 'wg_cfp_gb_static_form_render',
		'args' => array( 'route' => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ), 'post_id' => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ) ),
	) );
} );
