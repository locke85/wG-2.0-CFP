<?php
/** Ausfuehren: php tests/generateblocks-booking-forms-regression.php */
function fail( $message ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
function assert_true( $test, $message ) { if ( ! $test ) { fail( $message ); } }
function add_action() {} function add_filter() {} function get_option() { return $GLOBALS['settings']; }
function absint( $value ) { return abs( (int) $value ); } function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( (string) $value ); } function sanitize_file_name( $value ) { return preg_replace( '/[^A-Za-z0-9._-]/', '-', (string) $value ); }
function wp_parse_url( $value, $part ) { return parse_url( $value, $part ); } function wp_unslash( $value ) { return $value; } function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_transient( $key ) { return $GLOBALS['transients'][ $key ] ?? false; } function set_transient( $key, $value ) { $GLOBALS['transients'][ $key ] = $value; return true; }
function current_time() { return '2026-10-06 12:00:00'; } function wp_insert_post() { return 123; } function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][ $id ][ $key ] = $value; return true; } function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
class WP_Error { public $code; public function __construct( $code = '' ) { $this->code = $code; } }
define( 'ABSPATH', __DIR__ ); define( 'MINUTE_IN_SECONDS', 60 ); define( 'HOUR_IN_SECONDS', 3600 );
$GLOBALS['settings'] = array( 'profiles' => array( array( 'id' => 'booking', 'enabled' => true, 'form_id' => 200507, 'allowed_routes' => array( '/', '/online-angebote/' ), 'archive_post_type' => 'alissa_booking_req', 'payload_meta_key' => '_alissa_booking_payload', 'allowed_choices' => array( 'anliegen' => array( 'Moderationsanfrage', 'Wirkungscheck' ), 'marketing' => array( 'Google', 'Instagram' ) ), 'rate_limit' => 2, 'rate_window' => 600, 'machine_pattern_enabled' => true ) ) );
$GLOBALS['transients'] = array(); $GLOBALS['meta'] = array();
require __DIR__ . '/../custom-functionality-deployment/includes/generateblocks-booking-forms.php';
$profile = wg_cfp_booking_profile_for_form( 200507 );
assert_true( 'alissa_booking_req' === $profile['archive_post_type'], 'Ein Profil muss einen vorhandenen Archivtyp weiterverwenden können.' );
assert_true( '/online-angebote/' === wg_cfp_booking_route( 'https://example.test/online-angebote/' ), 'Routen müssen kanonisch normalisiert werden.' );
$valid = array( 'anliegen' => array( 'value' => 'Moderationsanfrage' ), 'marketing' => array( 'value' => 'Google' ) );
assert_true( 'Moderationsanfrage' === wg_cfp_booking_value( $valid, 'anliegen' ), 'Feldwerte müssen aus dem GB-Formularvertrag gelesen werden.' );
assert_true( in_array( wg_cfp_booking_value( $valid, 'marketing' ), $profile['allowed_choices']['marketing'], true ), 'Konfigurierte Auswahlwerte müssen exakt vergleichbar sein.' );
$_SERVER['REMOTE_ADDR'] = '203.0.113.25'; $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.9';
assert_true( '203.0.113.25' === wg_cfp_booking_client_address(), 'X-Forwarded-For darf nicht vertraut werden.' );
echo "GenerateBlocks booking-form regression tests passed.\n";
