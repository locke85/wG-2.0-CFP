<?php
/**
 * Server-side GenerateBlocks fallbacks for author archive profile fields.
 *
 * GenerateBlocks resolves its author tags through the current post author. On
 * author archives without posts, that context does not exist even though
 * WordPress has a valid queried WP_User object. The documented replacement
 * filter is preferable to shortcodes here: it preserves the existing block
 * structure and only fills values that GenerateBlocks could not resolve.
 *
 * @package WebGefaehrte_CFP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wg_cfp_get_queried_author' ) ) {
	/**
	 * Return the queried author, but never infer a user outside author archives.
	 *
	 * @return WP_User|null
	 */
	function wg_cfp_get_queried_author() {
		if ( ! is_author() ) {
			return null;
		}

		$queried_object = get_queried_object();

		if ( $queried_object instanceof WP_User ) {
			return $queried_object;
		}

		$user_id = absint( get_queried_object_id() );

		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return $user instanceof WP_User ? $user : null;
	}
}

if ( ! function_exists( 'wg_cfp_get_author_archive_avatar_size' ) ) {
	/**
	 * Get the fallback avatar size.
	 *
	 * Define WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE or use the
	 * wg_cfp_author_archive_avatar_size filter to change the 150px default.
	 *
	 * @return int
	 */
	function wg_cfp_get_author_archive_avatar_size() {
		$default_size = defined( 'WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE' )
			? absint( WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE )
			: 150;

		if ( ! $default_size ) {
			$default_size = 150;
		}

		$size = absint( apply_filters( 'wg_cfp_author_archive_avatar_size', $default_size ) );

		return max( 1, min( 512, $size ? $size : $default_size ) );
	}
}

if ( ! function_exists( 'wg_cfp_author_archive_dynamic_tag_replacement' ) ) {
	/**
	 * Fill unresolved GenerateBlocks author tags from the queried WP_User.
	 *
	 * Existing, non-empty GenerateBlocks output is returned unchanged. This
	 * avoids duplicate or altered output on archives that have a regular post
	 * context.
	 *
	 * @param mixed $replacement Existing GenerateBlocks replacement.
	 * @param array $context     Dynamic tag context.
	 * @return mixed
	 */
	function wg_cfp_author_archive_dynamic_tag_replacement( $replacement, $context ) {
		if ( '' !== (string) $replacement || ! is_array( $context ) ) {
			return $replacement;
		}

		$user = wg_cfp_get_queried_author();

		if ( ! $user ) {
			return $replacement;
		}

		$tag     = isset( $context['tag'] ) ? (string) $context['tag'] : '';
		$options = isset( $context['options'] ) && is_array( $context['options'] )
			? $context['options']
			: array();

		if ( 'author_meta' === $tag ) {
			$key = isset( $options['key'] ) ? (string) $options['key'] : '';

			switch ( $key ) {
				case 'display_name':
					$value = $user->display_name;
					break;
				case 'first_name':
					$value = get_user_meta( $user->ID, 'first_name', true );
					break;
				case 'last_name':
					$value = get_user_meta( $user->ID, 'last_name', true );
					break;
				default:
					return $replacement;
			}

			return esc_html( (string) $value );
		}

		if ( 'author_avatar_url' !== $tag ) {
			return $replacement;
		}

		$avatar_args = array(
			'size' => isset( $options['size'] ) && absint( $options['size'] )
				? min( 512, absint( $options['size'] ) )
				: wg_cfp_get_author_archive_avatar_size(),
		);

		if ( ! empty( $options['default'] ) ) {
			$avatar_args['default'] = (string) $options['default'];
		}

		if ( ! empty( $options['forceDefault'] ) ) {
			$avatar_args['force_default'] = true;
		}

		if ( ! empty( $options['rating'] ) ) {
			$avatar_args['rating'] = (string) $options['rating'];
		}

		$avatar_url = get_avatar_url( $user->ID, $avatar_args );

		return $avatar_url ? esc_url( $avatar_url ) : $replacement;
	}
}
add_filter( 'generateblocks_dynamic_tag_replacement', 'wg_cfp_author_archive_dynamic_tag_replacement', 10, 2 );

if ( ! function_exists( 'wg_cfp_author_archive_avatar_alt' ) ) {
	/**
	 * Add accessible alt text to the image that receives the fallback avatar.
	 *
	 * @param mixed $content Dynamic block HTML.
	 * @param array $context GenerateBlocks replacement context.
	 * @return mixed
	 */
	function wg_cfp_author_archive_avatar_alt( $content, $context ) {
		if ( ! is_string( $content ) || ! is_array( $context ) ) {
			return $content;
		}

		if (
			'author_avatar_url' !== ( $context['tag'] ?? '' )
			|| empty( $context['replacement'] )
			|| 'generateblocks/media' !== ( $context['block']['blockName'] ?? '' )
		) {
			return $content;
		}

		$user = wg_cfp_get_queried_author();

		if ( ! $user ) {
			return $content;
		}

		$display_name = trim( wp_strip_all_tags( (string) $user->display_name ) );
		$alt_text     = $display_name
			? sprintf( 'Profilbild von %s', $display_name )
			: 'Profilbild';

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new WP_HTML_Tag_Processor( $content );

			if ( $processor->next_tag( 'img' ) ) {
				$current_alt = $processor->get_attribute( 'alt' );

				if ( false === $current_alt || '' === trim( (string) $current_alt ) ) {
					$processor->set_attribute( 'alt', $alt_text );
				}

				return $processor->get_updated_html();
			}
		}

		$escaped_alt = esc_attr( $alt_text );

		if ( preg_match( '/\s+alt\s*=\s*(["\'])\s*\1/i', $content ) ) {
			return preg_replace(
				'/\s+alt\s*=\s*(["\'])\s*\1/i',
				' alt="' . $escaped_alt . '"',
				$content,
				1
			);
		}

		if ( ! preg_match( '/<img\b[^>]*\s+alt\s*=/i', $content ) ) {
			return preg_replace( '/<img\b/i', '<img alt="' . $escaped_alt . '"', $content, 1 );
		}

		return $content;
	}
}
add_filter( 'generateblocks_before_dynamic_tag_replace', 'wg_cfp_author_archive_avatar_alt', 10, 2 );
