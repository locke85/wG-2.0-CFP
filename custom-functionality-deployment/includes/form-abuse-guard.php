<?php
/**
 * Self-hosted proof-of-work protection for explicitly configured forms.
 *
 * The module is dormant until a site administrator lists GenerateBlocks Pro
 * form IDs in Settings > CFP Formularschutz. It uses no external service and
 * stores no form values, addresses, IP addresses or browser identifiers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WG_CFP_FORM_ABUSE_GUARD_OPTION = 'wg_cfp_form_abuse_guard';
const WG_CFP_FORM_ABUSE_GUARD_TTL = 180;
const WG_CFP_FORM_ABUSE_GUARD_DIFFICULTY = 4;

/**
 * @return array<int, array<string, mixed>>
 */
function wg_cfp_form_abuse_guard_forms() {
	$settings = get_option( WG_CFP_FORM_ABUSE_GUARD_OPTION, array() );
	$form_ids = is_array( $settings ) ? ( $settings['generateblocks_form_ids'] ?? array() ) : array();
	$forms    = array();

	foreach ( is_array( $form_ids ) ? $form_ids : array() as $form_id ) {
		$form_id = absint( $form_id );
		if ( $form_id < 1 ) {
			continue;
		}

		$forms[ $form_id ] = array(
			'difficulty'             => WG_CFP_FORM_ABUSE_GUARD_DIFFICULTY,
			'ttl'                    => WG_CFP_FORM_ABUSE_GUARD_TTL,
			'machine_pattern_enabled' => ! empty( $settings['machine_pattern_enabled'] ),
		);
	}

	/**
	 * Add or amend a site-specific form configuration.
	 *
	 * @param array<int, array<string, mixed>> $forms GenerateBlocks form IDs.
	 */
	$forms = apply_filters( 'wg_cfp_form_abuse_guard_forms', $forms );

	return is_array( $forms ) ? $forms : array();
}

/**
 * @param int $form_id GenerateBlocks form ID.
 * @return array<string, mixed>|null
 */
function wg_cfp_form_abuse_guard_form_config( $form_id ) {
	$forms = wg_cfp_form_abuse_guard_forms();
	$config = $forms[ absint( $form_id ) ] ?? null;

	if ( ! is_array( $config ) ) {
		return null;
	}

	$config['difficulty'] = min( 5, max( 3, absint( $config['difficulty'] ?? WG_CFP_FORM_ABUSE_GUARD_DIFFICULTY ) ) );
	$config['ttl']        = min( 600, max( 60, absint( $config['ttl'] ?? WG_CFP_FORM_ABUSE_GUARD_TTL ) ) );

	return $config;
}

/**
 * @param array<string, mixed> $challenge Challenge values.
 * @return string
 */
function wg_cfp_form_abuse_guard_sign( $challenge ) {
	$payload = implode(
		'|',
		array(
			(string) ( $challenge['id'] ?? '' ),
			(string) ( $challenge['salt'] ?? '' ),
			(string) ( $challenge['expires'] ?? '' ),
			(string) ( $challenge['difficulty'] ?? '' ),
			(string) ( $challenge['form_id'] ?? '' ),
		)
	);

	return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
}

/**
 * @param int $form_id GenerateBlocks form ID.
 * @return array<string, mixed>|WP_Error
 */
function wg_cfp_form_abuse_guard_create_challenge( $form_id ) {
	$config = wg_cfp_form_abuse_guard_form_config( $form_id );
	if ( null === $config ) {
		return new WP_Error( 'wg_cfp_form_not_protected', 'This form is not protected.', array( 'status' => 404 ) );
	}

	$challenge = array(
		'id'         => wp_generate_password( 24, false, false ),
		'salt'       => wp_generate_password( 32, false, false ),
		'expires'    => time() + absint( $config['ttl'] ),
		'difficulty' => absint( $config['difficulty'] ),
		'form_id'    => absint( $form_id ),
	);
	$challenge['signature'] = wg_cfp_form_abuse_guard_sign( $challenge );

	set_transient( 'wg_cfp_form_challenge_' . $challenge['id'], $challenge, absint( $config['ttl'] ) );

	return $challenge;
}

/**
 * @return WP_Error
 */
function wg_cfp_form_abuse_guard_rejection() {
	return new WP_Error(
		'wg_cfp_form_abuse_rejected',
		'Form protection could not be confirmed.',
		array(
			'status'         => 422,
			'public_message' => __( 'Die Anfrage konnte nicht bestätigt werden. Bitte lade die Seite neu und versuche es erneut.', 'webgefaehrte-cfp' ),
		)
	);
}

/**
 * Verify raw CFP fields before a form integration removes unknown fields.
 *
 * @param null|WP_Error $pre_result Existing validation result.
 * @param int           $form_id GenerateBlocks form ID.
 * @param array         $raw_fields All submitted fields.
 * @param array         $context Submission context.
 * @return null|WP_Error
 */
function wg_cfp_form_abuse_guard_verify_generateblocks_submission( $pre_result, $form_id, $raw_fields, $context ) {
	if ( is_wp_error( $pre_result ) || null === wg_cfp_form_abuse_guard_form_config( $form_id ) ) {
		return $pre_result;
	}

	$id        = isset( $raw_fields['wg_cfp_challenge_id'] ) ? sanitize_text_field( wp_unslash( $raw_fields['wg_cfp_challenge_id'] ) ) : '';
	$signature = isset( $raw_fields['wg_cfp_challenge_signature'] ) ? sanitize_text_field( wp_unslash( $raw_fields['wg_cfp_challenge_signature'] ) ) : '';
	$solution  = isset( $raw_fields['wg_cfp_challenge_solution'] ) ? sanitize_text_field( wp_unslash( $raw_fields['wg_cfp_challenge_solution'] ) ) : '';

	if ( ! preg_match( '/^[A-Za-z0-9]{16,64}$/', $id ) || ! preg_match( '/^[a-f0-9]{64}$/', $signature ) || ! preg_match( '/^\d{1,12}$/', $solution ) ) {
		return wg_cfp_form_abuse_guard_rejection();
	}

	$challenge = get_transient( 'wg_cfp_form_challenge_' . $id );
	if ( ! is_array( $challenge ) || absint( $challenge['form_id'] ?? 0 ) !== absint( $form_id ) || time() > absint( $challenge['expires'] ?? 0 ) || ! hash_equals( (string) ( $challenge['signature'] ?? '' ), $signature ) || ! hash_equals( wg_cfp_form_abuse_guard_sign( $challenge ), $signature ) ) {
		return wg_cfp_form_abuse_guard_rejection();
	}

	// One proof is consumed on its first verification attempt, successful or not.
	delete_transient( 'wg_cfp_form_challenge_' . $id );
	$hash = hash( 'sha256', (string) $challenge['salt'] . ':' . $solution );
	if ( 0 !== strpos( $hash, str_repeat( '0', absint( $challenge['difficulty'] ) ) ) ) {
		return wg_cfp_form_abuse_guard_rejection();
	}

	return null;
}
add_filter( 'generateblocks_form_verify_submission', 'wg_cfp_form_abuse_guard_verify_generateblocks_submission', 20, 4 );

/**
 * @param array<string, mixed> $sanitized_data Sanitized form values.
 * @return bool
 */
function wg_cfp_form_abuse_guard_matches_machine_pattern( $sanitized_data ) {
	$compact_tokens = 0;
	$long_token     = false;

	foreach ( $sanitized_data as $field ) {
		$value = is_array( $field ) ? ( $field['value'] ?? '' ) : '';
		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$value = trim( (string) $value );
		if ( preg_match( '/^[A-Za-z0-9]{7,}$/', $value ) ) {
			++$compact_tokens;
		}
		if ( preg_match( '/^[A-Za-z0-9]{30,}$/', $value ) ) {
			$long_token = true;
		}
	}

	return $compact_tokens >= 3 && $long_token;
}

/**
 * @param array|WP_Error $sanitized_data Sanitized GB-Pro values.
 * @param int            $form_id GenerateBlocks form ID.
 * @param array          $form_settings Form settings.
 * @param array          $context Submission context.
 * @return array|WP_Error
 */
function wg_cfp_form_abuse_guard_validate_generateblocks_submission( $sanitized_data, $form_id, $form_settings, $context ) {
	$config = wg_cfp_form_abuse_guard_form_config( $form_id );
	if ( is_wp_error( $sanitized_data ) || null === $config || empty( $config['machine_pattern_enabled'] ) || ! wg_cfp_form_abuse_guard_matches_machine_pattern( $sanitized_data ) ) {
		return $sanitized_data;
	}

	return new WP_Error(
		'wg_cfp_form_machine_pattern',
		'Implausible submission pattern.',
		array( 'public_message' => __( 'Bitte prüfe Deine Angaben und versuche es erneut.', 'webgefaehrte-cfp' ) )
	);
}
add_filter( 'generateblocks_form_validate_submission', 'wg_cfp_form_abuse_guard_validate_generateblocks_submission', 10, 4 );

/**
 * REST endpoint used by the versioned, same-origin browser asset.
 */
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wg-cfp/v1',
			'/form-challenge',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => array(
					'form_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
				'callback'            => static function ( WP_REST_Request $request ) {
					$challenge = wg_cfp_form_abuse_guard_create_challenge( absint( $request->get_param( 'form_id' ) ) );

					return is_wp_error( $challenge ) ? $challenge : rest_ensure_response( $challenge );
				},
			)
		);
	}
);

/**
 * @param array<string, mixed> $input Submitted settings.
 * @return array<string, mixed>
 */
function wg_cfp_form_abuse_guard_sanitize_settings( $input ) {
	$ids = is_array( $input ) ? (string) ( $input['generateblocks_form_ids'] ?? '' ) : '';
	$ids = array_unique( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $ids ) ?: array() ) ) );

	return array(
		'generateblocks_form_ids' => array_values( $ids ),
		'machine_pattern_enabled' => ! empty( $input['machine_pattern_enabled'] ),
	);
}

add_action(
	'admin_init',
	static function () {
		register_setting( 'wg_cfp_form_abuse_guard', WG_CFP_FORM_ABUSE_GUARD_OPTION, array( 'sanitize_callback' => 'wg_cfp_form_abuse_guard_sanitize_settings' ) );
	}
);

add_action(
	'admin_menu',
	static function () {
		add_options_page( 'CFP Formularschutz', 'CFP Formularschutz', 'manage_options', 'wg-cfp-form-abuse-guard', 'wg_cfp_form_abuse_guard_settings_page' );
	}
);

function wg_cfp_form_abuse_guard_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = get_option( WG_CFP_FORM_ABUSE_GUARD_OPTION, array() );
	?>
	<div class="wrap">
		<h1>CFP Formularschutz</h1>
		<form action="options.php" method="post">
			<?php settings_fields( 'wg_cfp_form_abuse_guard' ); ?>
			<p><label for="wg-cfp-gb-form-ids">GenerateBlocks-Pro-Formular-IDs</label></p>
			<input class="regular-text" id="wg-cfp-gb-form-ids" name="<?php echo esc_attr( WG_CFP_FORM_ABUSE_GUARD_OPTION ); ?>[generateblocks_form_ids]" value="<?php echo esc_attr( implode( ', ', (array) ( $settings['generateblocks_form_ids'] ?? array() ) ) ); ?>" />
			<p class="description">Kommagetrennte IDs. Nur diese Formulare erhalten den Schutz.</p>
			<p><label><input type="checkbox" name="<?php echo esc_attr( WG_CFP_FORM_ABUSE_GUARD_OPTION ); ?>[machine_pattern_enabled]" value="1" <?php checked( ! empty( $settings['machine_pattern_enabled'] ) ); ?> /> Mehrfeld-Zufallszeichenmuster zusätzlich ablehnen</label></p>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Load a versioned same-origin asset only when at least one form is enabled.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$forms = wg_cfp_form_abuse_guard_forms();
		if ( empty( $forms ) || ! defined( 'WG_CFP_PLUGIN_URL' ) || ! defined( 'WG_CFP_VERSION' ) ) {
			return;
		}

		wp_enqueue_script( 'wg-cfp-form-abuse-guard', WG_CFP_PLUGIN_URL . 'assets/js/form-abuse-guard.js', array(), WG_CFP_VERSION, true );
		wp_add_inline_script(
			'wg-cfp-form-abuse-guard',
			'window.WGCfpFormAbuseGuardConfig=' . wp_json_encode( array( 'formIds' => array_map( 'absint', array_keys( $forms ) ) ) ) . ';',
			'before'
		);
	}
);
