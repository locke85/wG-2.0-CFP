<?php
/**
 * Local media-library avatars for WordPress users.
 *
 * @package WebGefaehrte_CFP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WG_CFP_LOCAL_AVATAR_META_KEY' ) ) {
	define( 'WG_CFP_LOCAL_AVATAR_META_KEY', 'wg_cfp_avatar_attachment_id' );
}

if ( ! function_exists( 'wg_cfp_get_avatar_user_id' ) ) {
	/**
	 * Resolve the user ID accepted by the WordPress avatar API.
	 *
	 * @param mixed $id_or_email Avatar API subject.
	 * @return int
	 */
	function wg_cfp_get_avatar_user_id( $id_or_email ) {
		if ( $id_or_email instanceof WP_User ) {
			return absint( $id_or_email->ID );
		}

		if ( is_numeric( $id_or_email ) ) {
			return absint( $id_or_email );
		}

		if ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
			return absint( $id_or_email->user_id );
		}

		if ( $id_or_email instanceof WP_Comment ) {
			return absint( $id_or_email->user_id );
		}

		if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );

			return $user instanceof WP_User ? absint( $user->ID ) : 0;
		}

		return 0;
	}
}

if ( ! function_exists( 'wg_cfp_get_valid_avatar_attachment_url' ) ) {
	/**
	 * Return a valid image URL for an attachment on the current site.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $size          Requested square size.
	 * @return string
	 */
	function wg_cfp_get_valid_avatar_attachment_url( $attachment_id, $size = 512 ) {
		$attachment_id = absint( $attachment_id );
		$size          = max( 1, absint( $size ) );

		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return '';
		}

		$url = wp_get_attachment_image_url( $attachment_id, array( $size, $size ) );

		if ( ! $url ) {
			$url = wp_get_attachment_url( $attachment_id );
		}

		return $url ? esc_url_raw( $url ) : '';
	}
}

if ( ! function_exists( 'wg_cfp_membership_suite_has_local_avatar' ) ) {
	/**
	 * Membership Suite owns its local avatar whenever it has a valid image.
	 *
	 * The runtime check deliberately happens during avatar resolution because
	 * either plugin may register first on a shared site.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	function wg_cfp_membership_suite_has_local_avatar( $user_id ) {
		if ( ! class_exists( 'wg\\membership\\Frontend\\Avatar' ) ) {
			return false;
		}

		$attachment_id = absint( get_user_meta( $user_id, 'custom_user_avatar_id', true ) );

		return '' !== wg_cfp_get_valid_avatar_attachment_url( $attachment_id );
	}
}

if ( ! function_exists( 'wg_cfp_filter_local_avatar_data' ) ) {
	/**
	 * Add the CFP local avatar to the standard WordPress avatar data.
	 *
	 * @param array $args        Avatar arguments.
	 * @param mixed $id_or_email Avatar API subject.
	 * @return array
	 */
	function wg_cfp_filter_local_avatar_data( $args, $id_or_email ) {
		if ( is_admin() || wp_doing_ajax() ) {
			return $args;
		}

		$user_id = wg_cfp_get_avatar_user_id( $id_or_email );

		if ( ! $user_id || wg_cfp_membership_suite_has_local_avatar( $user_id ) ) {
			return $args;
		}

		$attachment_id = absint( get_user_meta( $user_id, WG_CFP_LOCAL_AVATAR_META_KEY, true ) );
		$size          = isset( $args['size'] ) ? max( 1, absint( $args['size'] ) ) : 512;
		$url           = wg_cfp_get_valid_avatar_attachment_url( $attachment_id, $size );

		if ( '' === $url ) {
			return $args;
		}

		$args['url']          = $url;
		$args['found_avatar'] = true;

		return $args;
	}
}
add_filter( 'get_avatar_data', 'wg_cfp_filter_local_avatar_data', 999, 2 );

if ( ! function_exists( 'wg_cfp_can_manage_local_avatar' ) ) {
	/**
	 * Check whether the current user may select an avatar for a profile.
	 *
	 * @param int $user_id Profile user ID.
	 * @return bool
	 */
	function wg_cfp_can_manage_local_avatar( $user_id ) {
		return current_user_can( 'edit_user', $user_id ) && current_user_can( 'upload_files' );
	}
}

if ( ! function_exists( 'wg_cfp_render_local_avatar_profile_field' ) ) {
	/**
	 * Render the media-library selector in WordPress user profiles.
	 *
	 * @param WP_User $user Edited user.
	 * @return void
	 */
	function wg_cfp_render_local_avatar_profile_field( $user ) {
		if ( ! $user instanceof WP_User || ! wg_cfp_can_manage_local_avatar( $user->ID ) ) {
			return;
		}

		$attachment_id = absint( get_user_meta( $user->ID, WG_CFP_LOCAL_AVATAR_META_KEY, true ) );
		$preview_url   = wg_cfp_get_valid_avatar_attachment_url( $attachment_id, 96 );
		?>
		<h2>Lokaler Avatar</h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="wg-cfp-avatar-attachment-id">Profilbild</label></th>
				<td>
					<input type="hidden" id="wg-cfp-avatar-attachment-id" name="wg_cfp_avatar_attachment_id" value="<?php echo esc_attr( $attachment_id ); ?>" />
					<img id="wg-cfp-avatar-preview" src="<?php echo esc_url( $preview_url ); ?>" alt="" style="<?php echo $preview_url ? '' : 'display:none;'; ?> width:96px;height:96px;object-fit:cover;vertical-align:middle;margin-right:12px;" />
					<button type="button" class="button" id="wg-cfp-select-avatar">Bild aus Mediathek wählen</button>
					<button type="button" class="button-link-delete" id="wg-cfp-remove-avatar" style="<?php echo $attachment_id ? '' : 'display:none;'; ?> margin-left:8px;">Zuordnung aufheben</button>
					<p class="description">Dieses Bild ersetzt im Frontend den Gravatar für diesen Benutzer. Ein vorhandener Avatar der Membership Suite hat Vorrang.</p>
					<?php wp_nonce_field( 'wg_cfp_save_local_avatar', 'wg_cfp_local_avatar_nonce' ); ?>
				</td>
			</tr>
		</table>
		<?php
	}
}
add_action( 'show_user_profile', 'wg_cfp_render_local_avatar_profile_field' );
add_action( 'edit_user_profile', 'wg_cfp_render_local_avatar_profile_field' );

if ( ! function_exists( 'wg_cfp_enqueue_local_avatar_profile_media' ) ) {
	/**
	 * Load the WordPress media selector only on user profile screens.
	 *
	 * @param string $hook_suffix Current admin screen.
	 * @return void
	 */
	function wg_cfp_enqueue_local_avatar_profile_media( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'profile.php', 'user-edit.php' ), true ) || ! current_user_can( 'upload_files' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
		wp_add_inline_script(
			'jquery',
			"jQuery(function($) {\n"
			. "  var input = $('#wg-cfp-avatar-attachment-id'), preview = $('#wg-cfp-avatar-preview'), remove = $('#wg-cfp-remove-avatar');\n"
			. "  $('#wg-cfp-select-avatar').on('click', function(event) {\n"
			. "    event.preventDefault();\n"
			. "    var frame = wp.media({ title: 'Profilbild aus Mediathek wählen', button: { text: 'Dieses Bild verwenden' }, library: { type: 'image' }, multiple: false });\n"
			. "    frame.on('select', function() { var image = frame.state().get('selection').first().toJSON(); var url = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url; input.val(image.id); preview.attr('src', url).show(); remove.show(); });\n"
			. "    frame.open();\n"
			. "  });\n"
			. "  remove.on('click', function(event) { event.preventDefault(); input.val(''); preview.attr('src', '').hide(); remove.hide(); });\n"
			. "});"
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wg_cfp_enqueue_local_avatar_profile_media' );

if ( ! function_exists( 'wg_cfp_save_local_avatar_profile_field' ) ) {
	/**
	 * Save or remove the CFP avatar assignment from a user profile.
	 *
	 * @param int $user_id Edited user ID.
	 * @return void
	 */
	function wg_cfp_save_local_avatar_profile_field( $user_id ) {
		if (
			! wg_cfp_can_manage_local_avatar( $user_id )
			|| ! isset( $_POST['wg_cfp_local_avatar_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wg_cfp_local_avatar_nonce'] ) ), 'wg_cfp_save_local_avatar' )
		) {
			return;
		}

		$attachment_id = isset( $_POST['wg_cfp_avatar_attachment_id'] )
			? absint( wp_unslash( $_POST['wg_cfp_avatar_attachment_id'] ) )
			: 0;

		if ( ! $attachment_id ) {
			delete_user_meta( $user_id, WG_CFP_LOCAL_AVATAR_META_KEY );
			return;
		}

		if ( '' === wg_cfp_get_valid_avatar_attachment_url( $attachment_id ) ) {
			return;
		}

		update_user_meta( $user_id, WG_CFP_LOCAL_AVATAR_META_KEY, $attachment_id );
	}
}
add_action( 'personal_options_update', 'wg_cfp_save_local_avatar_profile_field' );
add_action( 'edit_user_profile_update', 'wg_cfp_save_local_avatar_profile_field' );
