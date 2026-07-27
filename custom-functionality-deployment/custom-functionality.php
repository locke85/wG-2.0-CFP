<?php
/*
Plugin Name: webGefährte Custom Functionality Plugin
Description: The Custom Functionality Plugin (CFP) extends WordPress sites with custom post types, new shortcodes or custom widgets w/o the using multiple 3rd-party plugins.

Version: 2.0.2
Author: Jan (webGefährte)
*/

if ( ! function_exists( 'wg_cfp_load_module' ) ) {
    function wg_cfp_load_module( $relative_path ) {
        $module_path = __DIR__ . '/' . ltrim( $relative_path, '/' );

        if ( file_exists( $module_path ) ) {
            require_once $module_path;
        }
    }
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) ) {
    function wg_cfp_is_main_site_context() {
        if ( ! is_multisite() ) {
            return true;
        }

        return (int) get_current_blog_id() === (int) get_main_site_id();
    }
}

// WP - Activate Drop down filter for authors on Posts and Pages

if ( ! function_exists( 'add_author_filter_to_posts_and_pages' ) ) {
    function add_author_filter_to_posts_and_pages() {
        global $typenow;

        $selected_author = isset( $_GET['author'] ) ? absint( wp_unslash( $_GET['author'] ) ) : 0;

        if ( in_array( $typenow, array( 'post', 'page' ), true ) ) {
            wp_dropdown_users(
                array(
                    'name'            => 'author',
                    'who'             => 'authors',
                    'show_option_all' => __( 'Alle Autoren' ),
                    'selected'        => $selected_author,
                )
            );
        }
    }
}
add_action( 'restrict_manage_posts', 'add_author_filter_to_posts_and_pages' );

// Fontawesome - Activate local icons and styles

if ( ! function_exists( 'wg_cfp_get_fontawesome_url' ) ) {
    function wg_cfp_get_fontawesome_url() {
        $default_url  = '';
        $default_path = plugin_dir_path( __FILE__ ) . 'assets/fontawesome/css/all.css';

        if ( file_exists( $default_path ) ) {
            $default_url = plugin_dir_url( __FILE__ ) . 'assets/fontawesome/css/all.css';
        }

        return apply_filters( 'wg_cfp_fontawesome_url', $default_url );
    }
}

if ( ! function_exists( 'wg_cfp_enqueue_fontawesome' ) ) {
    function wg_cfp_enqueue_fontawesome() {
        if ( ! apply_filters( 'wg_cfp_load_fontawesome', true ) ) {
            return;
        }

        $fontawesome_url = wg_cfp_get_fontawesome_url();

        if ( empty( $fontawesome_url ) ) {
            return;
        }

        wp_enqueue_style( 'fontawesome-local', esc_url_raw( $fontawesome_url ), array(), null );
    }
}
add_action( 'wp_enqueue_scripts', 'wg_cfp_enqueue_fontawesome' );

// wG - TOC-Shortcode und Anchor-Filter

if ( ! function_exists( 'wp_h2_toc_debug' ) ) {
    function wp_h2_toc_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[TOC DEBUG] ' . $message );
        }
    }
}

if ( ! function_exists( 'wp_h2_toc_shortcode' ) ) {
    function wp_h2_toc_shortcode() {
        if ( ! is_singular() ) {
            wp_h2_toc_debug( 'Nicht singular.' );
            return '';
        }

        global $post;
        if ( ! $post ) {
            wp_h2_toc_debug( 'Kein Post gefunden.' );
            return '';
        }
        $content = $post->post_content;

        // KEIN apply_filters('the_content', $content) hier!
        preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches );

        if ( empty( $matches[1] ) ) {
            wp_h2_toc_debug( 'Keine H2 gefunden.' );
            return '';
        }

        $toc = '<div style="text-align:center;"><ul style="margin-left:20px; text-align:left;">';

        foreach ( $matches[1] as $heading ) {
            $slug = sanitize_title( $heading );
            $toc .= '<li><a href="#' . esc_attr( $slug ) . '">' . esc_html( wp_strip_all_tags( $heading ) ) . '</a></li>';
        }

        $toc .= '</ul></div>';

        wp_h2_toc_debug( 'TOC erfolgreich generiert.' );

        return $toc;
    }
}

// H2-Anker automatisch einfügen
if ( ! function_exists( 'wp_h2_add_anchors' ) ) {
    function wp_h2_add_anchors( $content ) {
        if ( ! is_singular() ) {
            wp_h2_toc_debug( 'Anchor: Nicht singular.' );
            return $content;
        }

        preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches );

        if ( empty( $matches[1] ) ) {
            wp_h2_toc_debug( 'Anchor: Keine H2 gefunden.' );
            return $content;
        }

        foreach ( $matches[1] as $heading ) {
            $slug = sanitize_title( $heading );
            if ( strpos( $content, 'id="' . $slug . '"' ) === false ) {
                $content = preg_replace(
                    '/(<h2[^>]*>)' . preg_quote( $heading, '/' ) . '(<\/h2>)/i',
                    '$1<a id="' . esc_attr( $slug ) . '"></a>' . $heading . '$2',
                    $content,
                    1
                );
                wp_h2_toc_debug( 'Anchor hinzugefügt: ' . $slug );
            }
        }

        return $content;
    }
}
add_filter( 'the_content', 'wp_h2_add_anchors' );
add_shortcode( 'toc', 'wp_h2_toc_shortcode' );

// GP - Esimated reading time

if ( ! function_exists( 'tu_estimated_reading_time_shortcode' ) ) {
    function tu_estimated_reading_time_shortcode() {
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
add_shortcode( 'lesezeit', 'tu_estimated_reading_time_shortcode' );

/* wG - Minimal SMTP Setup via wp-config.php - Nutzt Konstanten aus wp-config.php für den SMTP-Versand über wp_mail() */

add_action( 'phpmailer_init', function( $phpmailer ) {
    if ( defined( 'SMTP_HOST' ) && defined( 'SMTP_USER' ) && defined( 'SMTP_PASS' ) ) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = SMTP_HOST;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = defined( 'SMTP_PORT' ) ? SMTP_PORT : 587;
        $phpmailer->Username   = SMTP_USER;
        $phpmailer->Password   = SMTP_PASS;
        $phpmailer->SMTPSecure = defined( 'SMTP_SECURE' ) ? SMTP_SECURE : 'tls';
        $phpmailer->From       = defined( 'SMTP_FROM' ) ? SMTP_FROM : SMTP_USER;
        $phpmailer->FromName   = defined( 'SMTP_FROM_NAME' ) ? SMTP_FROM_NAME : get_bloginfo( 'name' );
    }
} );

// Excerpt für Seiten (Pages) aktivieren
if ( ! function_exists( 'wg_cfp_enable_page_excerpts' ) ) {
    function wg_cfp_enable_page_excerpts() {
        add_post_type_support( 'page', 'excerpt' );
    }
}
add_action( 'init', 'wg_cfp_enable_page_excerpts' );

/* wG - Registers custom fields for posts, pages, and podcast. */
 
if ( ! function_exists( 'wg_register_custom_fields' ) ) {
    function wg_register_custom_fields() {
        add_meta_box(
            'wg_fieldgroup_header',
            'wG Fieldgroup Header',
            'wg_display_custom_fields',
            array( 'post', 'page', 'generatepress_page', 'podcast' ),
            'normal',
            'high'
        );
    }
}
add_action( 'add_meta_boxes', 'wg_register_custom_fields' );

// Display the custom fields in the metabox.
if ( ! function_exists( 'wg_display_custom_fields' ) ) {
    function wg_display_custom_fields( $post ) {
        $wg_h1_title          = get_post_meta( $post->ID, 'wg_h1_title', true );
        $wg_div_tagline       = get_post_meta( $post->ID, 'wg_div_tagline', true );
        $wg_button_cta_text   = get_post_meta( $post->ID, 'wg_button_cta_text', true );
        $wg_button_cta_url    = get_post_meta( $post->ID, 'wg_button_cta_url', true );
        $wg_button_cta_tagline = get_post_meta( $post->ID, 'wg_button_cta_tagline', true );

        wp_nonce_field( 'wg_custom_fields', 'wg_custom_fields_nonce' );
        ?>
        <p>
            <label for="wg_h1_title">Title (H1)</label>
            <input type="text" name="wg_h1_title" id="wg_h1_title" value="<?php echo esc_attr( $wg_h1_title ?: 'Fokus-Keyphrase: spannender Bezugstext' ); ?>"
                maxlength="100" style="width: 100%;" />
            <br />
            <span>Main headline for the page or post (Max 100 characters)</span>
        </p>
        <p>
            <label for="wg_div_tagline">Tagline</label>
            <input type="text" name="wg_div_tagline" id="wg_div_tagline" value="<?php echo esc_attr( $wg_div_tagline ); ?>"
                maxlength="100" style="width: 100%;" />
            <br />
            <span>Tagline above the main headline (Max 60 characters)</span>
        </p>
        <p>
            <label for="wg_button_cta_text">CTA Button Text</label>
            <input type="text" name="wg_button_cta_text" id="wg_button_cta_text" value="<?php echo esc_attr( $wg_button_cta_text ); ?>"
                maxlength="100" style="width: 100%;" />
        </p>
        <p>
            <label for="wg_button_cta_tagline">CTA Button Tagline</label>
            <input type="text" name="wg_button_cta_tagline" id="wg_button_cta_tagline" value="<?php echo esc_attr( $wg_button_cta_tagline ); ?>"
                maxlength="100" style="width: 100%;" />
            <br />
            <span>Kurzer Text unter dem CTA-Button (Max 100 Zeichen)</span>
        </p>
        <p>
            <label for="wg_button_cta_url">CTA Button URL</label>
            <input type="url" name="wg_button_cta_url" id="wg_button_cta_url" value="<?php echo esc_attr( $wg_button_cta_url ); ?>"
                maxlength="100" style="width: 100%;" />
            <br />
        </p>
        <?php
    }
}

// Save the custom fields
if ( ! function_exists( 'wg_plugin_save_custom_fields' ) ) {
    function wg_plugin_save_custom_fields( $post_id ) {
        if ( ! isset( $_POST['wg_custom_fields_nonce'] ) ) {
            return $post_id;
        }

        $nonce = sanitize_text_field( wp_unslash( $_POST['wg_custom_fields_nonce'] ) );

        if ( ! wp_verify_nonce( $nonce, 'wg_custom_fields' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';

        if ( 'page' === $post_type ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        $wg_h1_title           = isset( $_POST['wg_h1_title'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_h1_title'] ) ) : '';
        $wg_div_tagline        = isset( $_POST['wg_div_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_div_tagline'] ) ) : '';
        $wg_button_cta_text    = isset( $_POST['wg_button_cta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_button_cta_text'] ) ) : '';
        $wg_button_cta_url     = isset( $_POST['wg_button_cta_url'] ) ? esc_url_raw( wp_unslash( $_POST['wg_button_cta_url'] ) ) : '';
        $wg_button_cta_tagline = isset( $_POST['wg_button_cta_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_button_cta_tagline'] ) ) : '';

        update_post_meta( $post_id, 'wg_h1_title', $wg_h1_title );
        update_post_meta( $post_id, 'wg_div_tagline', $wg_div_tagline );
        update_post_meta( $post_id, 'wg_button_cta_text', $wg_button_cta_text );
        update_post_meta( $post_id, 'wg_button_cta_url', $wg_button_cta_url );
        update_post_meta( $post_id, 'wg_button_cta_tagline', $wg_button_cta_tagline );
    }
}
add_action( 'save_post', 'wg_plugin_save_custom_fields' );

// GP - Activate smooth-scroll to all page internal links 

if ( ! function_exists( 'wg_cfp_register_smooth_scroll_elements' ) ) {
    function wg_cfp_register_smooth_scroll_elements( $elements ) {
        $elements[] = 'a:not([data-gpmodal-trigger="gp-search"])[href*="#"]';

        return array_values( array_unique( $elements ) );
    }
}
add_filter( 'generate_smooth_scroll_elements', 'wg_cfp_register_smooth_scroll_elements' );

/* wG - Support local Fonts */

// GP - Activate local fonts in editor
if ( ! function_exists( 'wg_cfp_add_custom_css_to_editor' ) ) {
    function wg_cfp_add_custom_css_to_editor( $editor_settings ) {
        $custom_css_post = wp_get_custom_css_post();
        $css             = $custom_css_post && isset( $custom_css_post->post_content ) ? $custom_css_post->post_content : '';

        if ( '' !== $css ) {
            $editor_settings['styles'][] = array( 'css' => $css );
        }

        return $editor_settings;
    }
}
add_filter( 'block_editor_settings_all', 'wg_cfp_add_custom_css_to_editor' );

if ( ! function_exists( 'wg_cfp_enable_generatepress_modal_script' ) ) {
    function wg_cfp_enable_generatepress_modal_script( $enabled ) {
        return true;
    }
}
add_filter( 'generate_enable_modal_script', 'wg_cfp_enable_generatepress_modal_script' );

if ( ! function_exists( 'wg_cfp_fix_generatepress_embeds' ) ) {
    function wg_cfp_fix_generatepress_embeds( $content ) {
        global $wp_embed;

        if ( $wp_embed && is_object( $wp_embed ) && method_exists( $wp_embed, 'autoembed' ) ) {
            return $wp_embed->autoembed( $content );
        }

        return $content;
    }
}
add_filter( 'generate_do_block_element_content', 'wg_cfp_fix_generatepress_embeds' );

// MailPoet - Disable Google Fonts

add_filter('mailpoet_display_custom_fonts', function () {return false;});

/* wG - Customize excerpts */

add_filter('generate_dynamic_element_text', function($custom_field, $block){
    if ( ! isset( $block['attrs']['anchor'] ) || 'dynamic-excerpt' !== $block['attrs']['anchor'] ) {
        return $custom_field;
    }

    if ( ! empty( $block['attrs']['gpDynamicTextCustomField'] ) && $block['attrs']['gpDynamicTextCustomField'] == 'the_excerpt' ){
        if (has_excerpt()) {
            $excerpt = wp_strip_all_tags(get_the_excerpt());
            $custom_field = $excerpt;
        }
    }
    return $custom_field;
},20, 2);

// GP -  Limit the number of words in manual excerpts

add_filter( 'get_the_excerpt', function( $excerpt, $post ) {
	if ( has_excerpt( $post ) ) {
		$excerpt_length = apply_filters( 'excerpt_length', 15 );
		$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$excerpt        = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
	}
	return $excerpt;
  }, 10, 2 );

/* wG - Add Support for carousels */

// Owl carousel - Load JQuery

add_action( 'wp_enqueue_scripts', 'wg_cfp_load_jquery' );
if ( ! function_exists( 'wg_cfp_load_jquery' ) ) {
    function wg_cfp_load_jquery() {
        wp_enqueue_script( 'jquery' );
    }
}

/* wG - Add Support for smooth scrolling */

// GP - Edit smooth-scroll

add_filter( 'generate_smooth_scroll_duration', 'tu_smooth_scroll_duration' );
if ( ! function_exists( 'tu_smooth_scroll_duration' ) ) {
    function tu_smooth_scroll_duration() {
        return 1000; // milliseconds
    }
}

/* wG - Customize Contact Form 7 */

if ( ! function_exists( 'wg_cfp_get_cf7_redirect_form_ids' ) ) {
    function wg_cfp_get_cf7_redirect_form_ids() {
        return apply_filters( 'wg_cfp_cf7_redirect_form_ids', array() );
    }
}

if ( ! function_exists( 'wg_cfp_get_cf7_thank_you_url' ) ) {
    function wg_cfp_get_cf7_thank_you_url() {
        return apply_filters( 'wg_cfp_cf7_thank_you_url', home_url( '/kontakt/danke' ) );
    }
}

if ( ! function_exists( 'cf7_footer_script' ) ) {
    function cf7_footer_script() {
        $redirect_form_ids = array_map( 'absint', (array) wg_cfp_get_cf7_redirect_form_ids() );
        $thank_you_url     = esc_url_raw( wg_cfp_get_cf7_thank_you_url() );
        ?>
	<script>
	document.addEventListener('wpcf7mailsent', function(event) {
		var allowedFormIds = <?php echo wp_json_encode( $redirect_form_ids ); ?>;
		var thankYouPage = <?php echo wp_json_encode( $thank_you_url ); ?>;
		var currentPath = window.location.pathname.replace(/\/+$/, '');
		var isKontaktPath = currentPath === '/kontakt';
		var contactFormId = event && event.detail ? parseInt(event.detail.contactFormId, 10) : 0;

		if (allowedFormIds.length > 0) {
			if (allowedFormIds.indexOf(contactFormId) === -1) {
				return;
			}
		} else if (!isKontaktPath) {
			return;
		}

		if (thankYouPage) {
			window.location.href = thankYouPage;
		}
	}, false);
	</script>
	<?php
    }
}
add_action( 'wp_footer', 'cf7_footer_script' );

// YOAST - Add Tag basis to breadbrumb of tag archives
add_filter( 'wpseo_breadcrumb_links', 'custom_tag_archive_breadcrumbs' );

/**
 * Custom breadcrumb paths for tag archive pages, dynamically pulling from the "Schlagwort-Basis" setting.
 *
 * @param array $links Default breadcrumb links.
 * @return array Modified breadcrumb links.
 */
if ( ! function_exists( 'custom_tag_archive_breadcrumbs' ) ) {
    function custom_tag_archive_breadcrumbs( $links ) {
        if ( is_tag() ) {
            $tag_base = get_option( 'tag_base', 'schlagwort' );
            $url      = home_url( '/' . $tag_base . '/' );
            $text     = ucwords( str_replace( '-', ' ', $tag_base ) );
            $breadcrumb_base = array(
                'url'  => $url,
                'text' => $text,
            );
            $current_tag = array_pop( $links );
            $links       = array_merge( $links, array( $breadcrumb_base, $current_tag ) );
        }

        return $links;
    }
}

// GP - Add list of tags to glossary page

if ( ! function_exists( 'list_terms_shortcode' ) ) {
    function list_terms_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'taxonomy' => 'post_tag',
		'hide_empty' => false,
	), $atts, 'list_terms' );

	$terms = get_terms( array(
		'taxonomy' => $atts['taxonomy'],
		'hide_empty' => $atts['hide_empty'],
	) );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	$output = '';
	$grouped_terms = array();

	foreach ( $terms as $term ) {
		$first_letter = strtoupper( mb_substr( $term->name, 0, 1 ) );
		if ( !isset( $grouped_terms[$first_letter] ) ) {
			$grouped_terms[$first_letter] = array();
		}
		$grouped_terms[$first_letter][] = $term;
	}

	foreach ( $grouped_terms as $letter => $terms_group ) {
		$output .= '<h2 id="' . esc_attr( $letter ) . '">' . esc_html( $letter ) . '</h2>';
		$output .= '<ul>';

		foreach ( $terms_group as $term ) {
			$output .= sprintf(
				'<li><a href="%s" title="%s">%s</a> <span class="term-count">(%s)</span></li>',
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->description ),
				esc_html( $term->name ),
				esc_html( (string) $term->count )
			);
		}
		$output .= '</ul>';
	}

	return $output;
    }
}
add_shortcode( 'list_terms', 'list_terms_shortcode' );

// GP - Enable tags for pages in WordPress

if ( ! function_exists( 'add_tags_to_pages' ) ) {
    function add_tags_to_pages() {
        register_taxonomy_for_object_type( 'post_tag', 'page' );
    }
}
add_action( 'init', 'add_tags_to_pages' );

if ( ! function_exists( 'include_tags_in_queries' ) ) {
    function include_tags_in_queries( $query ) {
        if ( ! is_admin() && $query->is_main_query() && $query->is_tag() ) {
            $query->set( 'post_type', array( 'post', 'page' ) );
        }
    }
}
add_action( 'pre_get_posts', 'include_tags_in_queries' );

// GP - Add tag description to pages

if ( ! function_exists( 'show_tag_descriptions' ) ) {
    function show_tag_descriptions() {
        $taxonomy = 'post_tag';
        $terms    = array();

        if ( is_singular() ) {
            $terms = get_the_terms( get_the_ID(), $taxonomy );
        } elseif ( is_tag() || is_tax( $taxonomy ) ) {
            $current_term = get_queried_object();
            if ( $current_term ) {
                $terms = array( $current_term );
            }
        }

        if ( $terms && ! is_wp_error( $terms ) ) {
            echo '<ul>';
            foreach ( $terms as $term ) {
                $description = term_description( $term, $taxonomy );

                if ( $description && false !== strpos( $description, $term->name ) ) {
                    $description = str_replace( $term->name, '<strong>' . esc_html( $term->name ) . '</strong>', $description );
                }

                $description = str_replace( '<p>', '<div>', $description );
                $description = str_replace( '</p>', '</div>', $description );

                echo '<li>' . wp_kses_post( $description ) . '</li>';
            }
            echo '</ul>';
        }
    }
}

if ( ! function_exists( 'show_tag_descriptions_shortcode' ) ) {
    function show_tag_descriptions_shortcode() {
	  ob_start();
	  show_tag_descriptions();
	  return ob_get_clean();
    }
}
add_shortcode('show_tag_descriptions', 'show_tag_descriptions_shortcode');

if ( ! function_exists( 'wg_cfp_enable_ratgeber_permalinks' ) ) {
    function wg_cfp_enable_ratgeber_permalinks() {
        return (bool) apply_filters( 'wg_cfp_enable_ratgeber_permalinks', true );
    }
}

if ( ! function_exists( 'wg_cfp_get_ratgeber_permalink_base' ) ) {
    function wg_cfp_get_ratgeber_permalink_base() {
        $base = apply_filters( 'wg_cfp_ratgeber_permalink_base', 'ratgeber' );
        $base = sanitize_title( (string) $base );

        return '' !== $base ? $base : 'ratgeber';
    }
}

if ( ! function_exists( 'wg_cfp_get_post_category_slug' ) ) {
    function wg_cfp_get_post_category_slug( $post_id ) {
        if ( class_exists( 'WPSEO_Primary_Term' ) ) {
            $primary_term = new WPSEO_Primary_Term( 'category', $post_id );
            $primary_id   = (int) $primary_term->get_primary_term();

            if ( $primary_id > 0 ) {
                $primary_term_object = get_term( $primary_id, 'category' );

                if ( $primary_term_object && ! is_wp_error( $primary_term_object ) && ! empty( $primary_term_object->slug ) ) {
                    return sanitize_title( $primary_term_object->slug );
                }
            }
        }

        $categories = get_the_category( $post_id );

        if ( empty( $categories ) || is_wp_error( $categories ) || empty( $categories[0]->slug ) ) {
            return '';
        }

        return sanitize_title( $categories[0]->slug );
    }
}

if ( ! function_exists( 'wg_cfp_add_ratgeber_rewrite_rule' ) ) {
    function wg_cfp_add_ratgeber_rewrite_rule() {
        if ( ! wg_cfp_enable_ratgeber_permalinks() ) {
            return;
        }

        $base = wg_cfp_get_ratgeber_permalink_base();
        add_rewrite_rule(
            '^' . $base . '/(.+)/([^/]+)/?$',
            'index.php?category_name=$matches[1]&name=$matches[2]',
            'top'
        );
    }
}
add_action( 'init', 'wg_cfp_add_ratgeber_rewrite_rule' );

if ( ! function_exists( 'wg_cfp_filter_ratgeber_pre_post_link' ) ) {
    function wg_cfp_filter_ratgeber_pre_post_link( $permalink, $post, $leavename ) {
        if ( ! wg_cfp_enable_ratgeber_permalinks() ) {
            return $permalink;
        }

        if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
            return $permalink;
        }

        if ( '' === wg_cfp_get_post_category_slug( $post->ID ) ) {
            return $permalink;
        }

        if ( strpos( $permalink, '%category%' ) === false ) {
            return $permalink;
        }

        $base                  = wg_cfp_get_ratgeber_permalink_base();
        $normalized_permalink  = ltrim( $permalink, '/' );

        if ( 0 === strpos( $normalized_permalink, $base . '/' ) ) {
            return $permalink;
        }

        return str_replace( '%category%', $base . '/%category%', $permalink );
    }
}
add_filter( 'pre_post_link', 'wg_cfp_filter_ratgeber_pre_post_link', 10, 3 );

if ( ! function_exists( 'wg_cfp_filter_ratgeber_post_link' ) ) {
    function wg_cfp_filter_ratgeber_post_link( $permalink, $post ) {
        if ( ! wg_cfp_enable_ratgeber_permalinks() ) {
            return $permalink;
        }

        if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
            return $permalink;
        }

        if ( ! apply_filters( 'wg_cfp_ratgeber_force_permalink_without_category_placeholder', false ) ) {
            return $permalink;
        }

        $category_slug = wg_cfp_get_post_category_slug( $post->ID );

        if ( '' === $category_slug ) {
            return $permalink;
        }
        $base = wg_cfp_get_ratgeber_permalink_base();

        $path = wp_parse_url( $permalink, PHP_URL_PATH );

        if ( ! is_string( $path ) || '' === $path ) {
            return $permalink;
        }

        $normalized_path = ltrim( $path, '/' );

        if ( 0 === strpos( $normalized_path, $base . '/' ) ) {
            return $permalink;
        }

        return home_url( user_trailingslashit( $base . '/' . $category_slug . '/' . $post->post_name ) );
    }
}
add_filter( 'post_link', 'wg_cfp_filter_ratgeber_post_link', 10, 2 );

// Seriously Simple Podcasting - Adds support for revisions to the custom post type "podcast".

add_filter( 'ssp_register_post_type_args', function ( $args ) {
	if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
		$args['supports'] = array();
	}

	$args['supports'][] = 'revisions';

	return $args;
} );

wg_cfp_load_module( 'includes/author-archive-dynamic-profile.php' );

if ( wg_cfp_is_main_site_context() ) {
    wg_cfp_load_module( 'includes/main-site-chat-cpt.php' );
    wg_cfp_load_module( 'includes/main-site-sharing-news-cpt.php' );
    wg_cfp_load_module( 'includes/podcast-show-notes.php' );
    wg_cfp_load_module( 'includes/chat-archives.php' );
    wg_cfp_load_module( 'includes/user-profile-html.php' );
}

// WG - Add plugin update checker for GitHub

require_once __DIR__ . '/includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5p4\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/locke85/wG-2.0-CFP/',
    __FILE__,
    'custom-functionality-deployment'
);

$updateChecker->setBranch('main');

$updateChecker->getVcsApi()->enableReleaseAssets();
