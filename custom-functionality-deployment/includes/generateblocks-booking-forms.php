<?php
/**
 * Optional, site-configured archive contract for GenerateBlocks Pro forms.
 * The module has no effect until an administrator creates an enabled profile.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const WG_CFP_GB_BOOKING_FORMS_OPTION = 'wg_cfp_generateblocks_booking_forms';

function wg_cfp_booking_route( $value ) {
	$path = wp_parse_url( trim( (string) $value ), PHP_URL_PATH );
	$path = is_string( $path ) ? $path : (string) $value;
	$path = trim( $path, '/' );
	return '' === $path ? '/' : '/' . $path . '/';
}

function wg_cfp_booking_profiles() {
	$raw = get_option( WG_CFP_GB_BOOKING_FORMS_OPTION, array() );
	$raw = is_array( $raw ) ? ( $raw['profiles'] ?? array() ) : array();
	$profiles = array();
	foreach ( is_array( $raw ) ? $raw : array() as $profile ) {
		if ( ! is_array( $profile ) ) { continue; }
		$id = sanitize_key( $profile['id'] ?? '' );
		$type = sanitize_key( $profile['archive_post_type'] ?? '' );
		$form_id = absint( $profile['form_id'] ?? 0 );
		if ( ! $id || ! $form_id || ! preg_match( '/^[a-z0-9_]{1,20}$/', $type ) ) { continue; }
		$choices = array();
		foreach ( (array) ( $profile['allowed_choices'] ?? array() ) as $field => $values ) {
			$field = sanitize_key( $field );
			$values = array_values( array_filter( array_map( 'sanitize_text_field', (array) $values ), 'strlen' ) );
			if ( $field && $values ) { $choices[ $field ] = $values; }
		}
		$profiles[ $id ] = array(
			'id' => $id, 'enabled' => ! empty( $profile['enabled'] ), 'form_id' => $form_id,
			'allowed_routes' => array_values( array_unique( array_filter( array_map( 'wg_cfp_booking_route', (array) ( $profile['allowed_routes'] ?? array() ) ) ) ) ),
			'archive_post_type' => $type, 'archive_label' => sanitize_text_field( $profile['archive_label'] ?? 'Formularanfragen' ),
			'payload_meta_key' => preg_match( '/^_[a-z0-9_]+$/', (string) ( $profile['payload_meta_key'] ?? '' ) ) ? (string) $profile['payload_meta_key'] : '_wg_cfp_booking_payload',
			'export_filename' => sanitize_file_name( $profile['export_filename'] ?? ( $id . '-anfragen.csv' ) ),
			'rate_limit' => min( 20, max( 1, absint( $profile['rate_limit'] ?? 5 ) ) ),
			'rate_window' => min( HOUR_IN_SECONDS, max( MINUTE_IN_SECONDS, absint( $profile['rate_window'] ?? ( 10 * MINUTE_IN_SECONDS ) ) ) ),
			'machine_pattern_enabled' => ! empty( $profile['machine_pattern_enabled'] ), 'allowed_choices' => $choices,
		);
	}
	return $profiles;
}

function wg_cfp_booking_profile_for_form( $form_id ) {
	foreach ( wg_cfp_booking_profiles() as $profile ) {
		if ( $profile['enabled'] && absint( $profile['form_id'] ) === absint( $form_id ) ) { return $profile; }
	}
	return null;
}

add_filter( 'wg_cfp_form_abuse_guard_forms', static function ( $forms ) {
	foreach ( wg_cfp_booking_profiles() as $profile ) {
		if ( $profile['enabled'] ) { $forms[ $profile['form_id'] ] = array( 'difficulty' => 4, 'ttl' => 180, 'machine_pattern_enabled' => $profile['machine_pattern_enabled'] ); }
	}
	return $forms;
} );
add_filter( 'wg_cfp_generateblocks_static_forms', static function ( $forms ) {
	foreach ( wg_cfp_booking_profiles() as $profile ) {
		if ( $profile['enabled'] && $profile['allowed_routes'] ) { $forms[] = array( 'form_id' => $profile['form_id'], 'allowed_routes' => $profile['allowed_routes'] ); }
	}
	return $forms;
} );

add_action( 'init', static function () {
	foreach ( wg_cfp_booking_profiles() as $profile ) {
		if ( ! $profile['enabled'] ) { continue; }
		register_post_type( $profile['archive_post_type'], array(
			'labels' => array( 'name' => $profile['archive_label'], 'singular_name' => rtrim( $profile['archive_label'], 'n' ) ),
			'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=gblocks_form', 'supports' => array( 'title' ), 'exclude_from_search' => true, 'show_in_rest' => false, 'map_meta_cap' => false,
			'capabilities' => array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts' ), 'manage_options' ),
		) );
	}
} );

function wg_cfp_booking_value( $data, $field ) { $value = is_array( $data ) && isset( $data[ $field ]['value'] ) ? $data[ $field ]['value'] : ''; return is_scalar( $value ) ? trim( (string) $value ) : ''; }
add_filter( 'generateblocks_form_validate_submission', static function ( $data, $form_id ) {
	$profile = wg_cfp_booking_profile_for_form( $form_id );
	if ( is_wp_error( $data ) || ! $profile ) { return $data; }
	foreach ( $profile['allowed_choices'] as $field => $values ) { if ( ! in_array( wg_cfp_booking_value( $data, $field ), $values, true ) ) { return new WP_Error( 'wg_cfp_booking_invalid_choice', 'Ungültige Auswahl.', array( 'public_message' => 'Bitte prüfe Deine Auswahl und versuche es erneut.' ) ); } }
	return $data;
}, 15, 4 );

function wg_cfp_booking_client_address() { $address = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : ''; return filter_var( $address, FILTER_VALIDATE_IP ) ? (string) $address : 'unknown'; }
add_filter( 'generateblocks_form_validate_submission', static function ( $data, $form_id ) {
	$profile = wg_cfp_booking_profile_for_form( $form_id );
	if ( is_wp_error( $data ) || ! $profile ) { return $data; }
	$key = 'wg_cfp_booking_rate_' . $profile['id'] . '_' . hash( 'sha256', wg_cfp_booking_client_address() );
	if ( absint( get_transient( $key ) ) >= $profile['rate_limit'] ) { return new WP_Error( 'wg_cfp_booking_rate_limited', 'Zu viele Anfragen.', array( 'status' => 429, 'public_message' => 'Zu viele Anfragen in kurzer Zeit. Bitte warte zehn Minuten und versuche es dann erneut.' ) ); }
	$post_id = wp_insert_post( array( 'post_type' => $profile['archive_post_type'], 'post_status' => 'publish', 'post_title' => $profile['archive_label'] . ' ' . current_time( 'Y-m-d H:i:s', true ) . ' UTC' ), true );
	if ( is_wp_error( $post_id ) || ! $post_id || false === update_post_meta( $post_id, $profile['payload_meta_key'], wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) { return new WP_Error( 'wg_cfp_booking_archive_failed', 'Ablage fehlgeschlagen.', array( 'status' => 500, 'public_message' => 'Die Anfrage konnte gerade nicht gespeichert werden. Bitte versuche es erneut oder schreibe direkt an uns.' ) ); }
	update_post_meta( $post_id, '_wg_cfp_booking_profile', $profile['id'] ); update_post_meta( $post_id, '_wg_cfp_booking_form_id', absint( $form_id ) ); set_transient( $key, absint( get_transient( $key ) ) + 1, $profile['rate_window'] ); return $data;
}, 30, 4 );

function wg_cfp_booking_profile_for_type( $type ) { foreach ( wg_cfp_booking_profiles() as $profile ) { if ( $profile['enabled'] && $profile['archive_post_type'] === $type ) { return $profile; } } return null; }
add_action( 'add_meta_boxes', static function ( $type ) { $profile = wg_cfp_booking_profile_for_type( $type ); if ( ! $profile ) { return; } add_meta_box( 'wg-cfp-booking-data', 'Gespeicherte Anfrage', static function ( $post ) use ( $profile ) { if ( ! current_user_can( 'manage_options' ) ) { return; } $payload = json_decode( (string) get_post_meta( $post->ID, $profile['payload_meta_key'], true ), true ); if ( ! is_array( $payload ) ) { echo '<p>Die Anfrage konnte nicht gelesen werden.</p>'; return; } echo '<table class="widefat striped"><tbody>'; foreach ( $payload as $key => $value ) { if ( is_scalar( $value ) ) { echo '<tr><th scope="row">' . esc_html( (string) $key ) . '</th><td>' . nl2br( esc_html( (string) $value ) ) . '</td></tr>'; } } echo '</tbody></table>'; }, $type, 'normal', 'high' ); } );
add_action( 'admin_post_wg_cfp_booking_export', static function () { $id = sanitize_key( $_GET['profile'] ?? '' ); $profile = wg_cfp_booking_profiles()[ $id ] ?? null; if ( ! $profile || ! current_user_can( 'manage_options' ) ) { wp_die( 'Nicht berechtigt.' ); } check_admin_referer( 'wg_cfp_booking_export_' . $id ); $posts = get_posts( array( 'post_type' => $profile['archive_post_type'], 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ) ); $keys = array(); $rows = array(); foreach ( $posts as $post ) { $payload = json_decode( (string) get_post_meta( $post->ID, $profile['payload_meta_key'], true ), true ); $payload = is_array( $payload ) ? $payload : array(); $keys = array_values( array_unique( array_merge( $keys, array_keys( $payload ) ) ) ); $rows[] = array( 'created_at_gmt' => get_post_time( 'c', true, $post ) ) + $payload; } nocache_headers(); header( 'Content-Type: text/csv; charset=utf-8' ); header( 'Content-Disposition: attachment; filename=' . $profile['export_filename'] ); $out = fopen( 'php://output', 'w' ); $columns = array_merge( array( 'created_at_gmt' ), $keys ); fputcsv( $out, $columns, ';' ); foreach ( $rows as $row ) { fputcsv( $out, array_map( static function ( $key ) use ( $row ) { return isset( $row[ $key ] ) && is_scalar( $row[ $key ] ) ? (string) $row[ $key ] : ''; }, $columns ), ';' ); } fclose( $out ); exit; } );
add_action( 'restrict_manage_posts', static function ( $type ) { $profile = wg_cfp_booking_profile_for_type( $type ); if ( ! $profile || ! current_user_can( 'manage_options' ) ) { return; } $url = wp_nonce_url( admin_url( 'admin-post.php?action=wg_cfp_booking_export&profile=' . rawurlencode( $profile['id'] ) ), 'wg_cfp_booking_export_' . $profile['id'] ); echo '<a class="button" href="' . esc_url( $url ) . '">CSV exportieren</a>'; } );

function wg_cfp_booking_sanitize_setting( $input ) { return is_array( $input ) ? $input : array( 'profiles' => array() ); }
add_action( 'init', static function () { register_setting( 'general', WG_CFP_GB_BOOKING_FORMS_OPTION, array( 'type' => 'object', 'sanitize_callback' => 'wg_cfp_booking_sanitize_setting', 'show_in_rest' => array( 'schema' => array( 'type' => 'object', 'additionalProperties' => true ) ), 'default' => array( 'profiles' => array() ) ) ); } );
