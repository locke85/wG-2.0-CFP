<?php
/** Ausfuehren: php tests/form-abuse-guard-regression.php */

function fail( $message ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
function assert_true( $condition, $message ) { if ( ! $condition ) { fail( $message ); } }
function add_filter() {}
function add_action() {}
function apply_filters( $hook, $value ) { return $value; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_text_field( $value ) { return trim( (string) $value ); }
function wp_unslash( $value ) { return $value; }
function wp_salt( $scheme ) { return 'cfp-regression-secret'; }
function wp_generate_password( $length, $special, $extra ) { static $i = 0; ++$i; return str_pad( 'challenge' . $i, $length, 'x' ); }
function set_transient( $key, $value, $ttl ) { $GLOBALS['transients'][ $key ] = $value; return true; }
function get_transient( $key ) { return $GLOBALS['transients'][ $key ] ?? false; }
function delete_transient( $key ) { unset( $GLOBALS['transients'][ $key ] ); return true; }
function get_option( $key, $default ) { return $GLOBALS['cfp_settings'] ?? $default; }
function register_setting() {}
function add_options_page() {}
function wp_enqueue_script() {}
function wp_add_inline_script() {}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function __( $text ) { return $text; }

class WP_Error {
	public $code;
	public function __construct( $code = '', $message = '', $data = array() ) { $this->code = $code; }
}

define( 'ABSPATH', __DIR__ );
$GLOBALS['transients'] = array();
$GLOBALS['cfp_settings'] = array(
	'generateblocks_form_ids' => array( 321 ),
	'machine_pattern_enabled' => true,
);

require __DIR__ . '/../custom-functionality-deployment/includes/form-abuse-guard.php';

$challenge = wg_cfp_form_abuse_guard_create_challenge( 321 );
assert_true( is_array( $challenge ), 'Eine konfigurierte Formular-ID muss eine Challenge erhalten.' );
assert_true( 4 === $challenge['difficulty'], 'Die Standardschwierigkeit muss vier sein.' );
assert_true( 64 === strlen( $challenge['signature'] ), 'Die Challenge muss HMAC-signiert sein.' );

$solution = null;
for ( $nonce = 0; $nonce < 2000000; ++$nonce ) {
	if ( 0 === strpos( hash( 'sha256', $challenge['salt'] . ':' . $nonce ), str_repeat( '0', $challenge['difficulty'] ) ) ) {
		$solution = (string) $nonce;
		break;
	}
}
assert_true( null !== $solution, 'Für die Test-Challenge muss eine Lösung berechenbar sein.' );

$raw = array(
	'wg_cfp_challenge_id'        => $challenge['id'],
	'wg_cfp_challenge_signature' => $challenge['signature'],
	'wg_cfp_challenge_solution'  => $solution,
);
$valid = wg_cfp_form_abuse_guard_verify_generateblocks_submission( null, 321, $raw, array() );
assert_true( null === $valid, 'Eine gültige Challenge muss akzeptiert werden.' );

$replay = wg_cfp_form_abuse_guard_verify_generateblocks_submission( null, 321, $raw, array() );
assert_true( $replay instanceof WP_Error, 'Eine verbrauchte Challenge muss abgelehnt werden.' );

$tampered = wg_cfp_form_abuse_guard_verify_generateblocks_submission( null, 321, array(
	'wg_cfp_challenge_id'        => 'invalidchallengeid000000',
	'wg_cfp_challenge_signature' => str_repeat( '0', 64 ),
	'wg_cfp_challenge_solution'  => '0',
), array() );
assert_true( $tampered instanceof WP_Error, 'Manipulierte Challenge-Daten müssen abgelehnt werden.' );

$spam = array(
	'name'    => array( 'value' => 'X80124NtXo' ),
	'anlass'  => array( 'value' => 'AikKY403Cg' ),
	'termin'  => array( 'value' => 'QRI7Mk5vi0' ),
	'message' => array( 'value' => '76aY1V1OY77vfbnTo0mc9d7SzF1Iy08EJxxKAEkZWkXdPj97ihB5YNc' ),
);
assert_true( wg_cfp_form_abuse_guard_matches_machine_pattern( $spam ), 'Die Mehrfeld-Zufallszeichensignatur muss erkannt werden.' );
$rejected = wg_cfp_form_abuse_guard_validate_generateblocks_submission( $spam, 321, array(), array() );
assert_true( $rejected instanceof WP_Error, 'Die erkannte Signatur muss vor Aktionen abgelehnt werden.' );

$normal = array(
	'name'    => array( 'value' => 'Jan Beispiel' ),
	'anlass'  => array( 'value' => 'Jahrestagung Berlin' ),
	'message' => array( 'value' => 'Wir planen eine Moderation für unsere Jahrestagung und freuen uns auf ein Gespräch.' ),
);
assert_true( ! wg_cfp_form_abuse_guard_matches_machine_pattern( $normal ), 'Normale Freitexte dürfen nicht als Spam gelten.' );
assert_true( $normal === wg_cfp_form_abuse_guard_validate_generateblocks_submission( $normal, 321, array(), array() ), 'Normale Freitexte müssen die Validierung passieren.' );

echo "Form-abuse-guard regression tests passed.\n";
