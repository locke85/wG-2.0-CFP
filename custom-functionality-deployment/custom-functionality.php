<?php
/*
Plugin Name: webGefährte Custom Functionality Plugin
Description: The Custom Functionality Plugin (CFP) extends WordPress sites with custom post types, new shortcodes or custom widgets w/o the using multiple 3rd-party plugins.

Version: 1.5.0
Author: Jan (webGefährte)
*/


/* wG - Registers custom fields for posts, pages, and GeneratePress theme elements.
 * Fields include: wg_h1_title, wg_div_tagline, and wg_button_cta.
 * wg_button_cta contains two sub-fields: button text and URL link.
 */

function register_custom_fields() {
    // Register custom fields for the 'wg_fieldgroup_header' field group
    if( function_exists('acf_add_local_field_group') ) {

        acf_add_local_field_group(array(
            'key' => 'group_wg_fieldgroup_header',
            'title' => 'Header Fields',
            'fields' => array(
                array(
                    'key' => 'field_wg_h1_title',
                    'label' => 'Title (H1)',
                    'name' => 'wg_h1_title',
                    'type' => 'text',
                    'instructions' => 'Main headline for the page or post',
                    'default_value' => 'Fokus-Keyphrase: spannender Bezugstext',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_wg_div_tagline',
                    'label' => 'Tagline',
                    'name' => 'wg_div_tagline',
                    'type' => 'text',
                    'instructions' => 'Tagline above the main headline',
                    'default_value' => '',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_wg_button_cta',
                    'label' => 'CTA',
                    'name' => 'wg_button_cta',
                    'type' => 'group',
                    'instructions' => 'Primary call-to-action for the user',
                    'layout' => 'block',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_button_text',
                            'label' => 'Button Text',
                            'name' => 'button_text',
                            'type' => 'text',
                            'instructions' => 'Text to display on the button',
                            'default_value' => '',
                            'placeholder' => '',
                        ),
                        array(
                            'key' => 'field_button_url',
                            'label' => 'Button URL',
                            'name' => 'button_url',
                            'type' => 'url',
                            'instructions' => 'URL to redirect when the button is clicked',
                            'default_value' => '',
                            'placeholder' => '',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
                array(
                    array(
                        'param' => 'generatepress_element',
                        'operator' => '==',
                        'value' => 'element',
                    ),
                ),
            ),
            'position' => 'normal',
        ));
    }
}

// Hook into ACF initialization
add_action('acf/init', 'register_custom_fields');

function wg_register_custom_fields() {
    add_meta_box(
        'wg_feldgruppe_header', // ID der Metabox
        'wG-Feldgruppe-Header', // Titel der Metabox
        'wg_display_custom_fields', // Callback-Funktion, um die Felder anzuzeigen
        ['post', 'page', 'generatepress_page'], // Post-Typen: Beiträge, Seiten und GeneratePress Elements
        'normal', // Position der Metabox
        'high' // Priorität der Metabox
    );
}
add_action('add_meta_boxes', 'wg_register_custom_fields');

function wg_display_custom_fields($post) {
    // Abrufen der gespeicherten Werte
    $wg_h1_title = get_post_meta($post->ID, 'wg_h1_title', true);
    $wg_div_tagline = get_post_meta($post->ID, 'wg_div_tagline', true);
    $wg_button_cta_text = get_post_meta($post->ID, 'wg_button_cta_text', true);
    $wg_button_cta_url = get_post_meta($post->ID, 'wg_button_cta_url', true);

    // Felder anzeigen
    ?>
    <p>
        <label for="wg_h1_title">Titel (H1)</label>
        <input type="text" name="wg_h1_title" id="wg_h1_title" value="<?php echo esc_attr($wg_h1_title ?: 'Fokus-Keyphrase: spannender Bezugstext'); ?>" />
        <br />
        <span>Hauptüberschrift für Seite oder Beitrag</span>
    </p>
    <p>
        <label for="wg_div_tagline">Tagline</label>
        <input type="text" name="wg_div_tagline" id="wg_div_tagline" value="<?php echo esc_attr($wg_div_tagline); ?>" />
        <br />
        <span>Tagline über der Hauptüberschrift</span>
    </p>
    <p>
        <label for="wg_button_cta_text">CTA Button-Text</label>
        <input type="text" name="wg_button_cta_text" id="wg_button_cta_text" value="<?php echo esc_attr($wg_button_cta_text); ?>" />
    </p>
    <p>
        <label for="wg_button_cta_url">CTA Button-URL</label>
        <input type="url" name="wg_button_cta_url" id="wg_button_cta_url" value="<?php echo esc_attr($wg_button_cta_url); ?>" />
        <br />
        <span>Primäre Handlungsempfehlung für den Nutzer</span>
    </p>
    <?php
}

function wg_save_custom_fields($post_id) {
    // Speichern der Daten
    if (array_key_exists('wg_h1_title', $_POST)) {
        update_post_meta($post_id, 'wg_h1_title', sanitize_text_field($_POST['wg_h1_title']));
    }
    if (array_key_exists('wg_div_tagline', $_POST)) {
        update_post_meta($post_id, 'wg_div_tagline', sanitize_text_field($_POST['wg_div_tagline']));
    }
    if (array_key_exists('wg_button_cta_text', $_POST)) {
        update_post_meta($post_id, 'wg_button_cta_text', sanitize_text_field($_POST['wg_button_cta_text']));
    }
    if (array_key_exists('wg_button_cta_url', $_POST)) {
        update_post_meta($post_id, 'wg_button_cta_url', esc_url_raw($_POST['wg_button_cta_url']));
    }
}
add_action('save_post', 'wg_save_custom_fields');


// GP - Activate smooth-scroll to all page internal links 

add_filter( 'generate_smooth_scroll_elements', function( $elements ) {
    $elements[] = 'a:not([data-gpmodal-trigger="gp-search"])[href*="#"]';
    
    return $elements;
} );

// MailPoet - Disable Google Fonts

add_filter('mailpoet_display_custom_fonts', function () {return false;});

// WP - Activate Excerpt for pages

add_post_type_support( 'page', 'excerpt');

add_filter('generate_dynamic_element_text', function($custom_field, $block){

    if($block['attrs']['anchor'] = 'dynamic-excerpt'){
        if ( ! empty( $block['attrs']['gpDynamicTextCustomField'] ) && $block['attrs']['gpDynamicTextCustomField'] == 'the_excerpt' ){
            if (has_excerpt()) {
                $excerpt = wp_strip_all_tags(get_the_excerpt());
                $custom_field = $excerpt;
            }
        }
        return $custom_field;
    }
    },20, 2);

// Owl carousel - Load JQuery

add_action( 'wp_enqueue_scripts', 'tu_load_jquery' );
function tu_load_jquery() {
    wp_enqueue_script( 'jquery' );
}; 

// GP - Edit smooth-scroll

add_filter( 'generate_smooth_scroll_duration', 'tu_smooth_scroll_duration' );
function tu_smooth_scroll_duration() {
    return 1000; // milliseconds
}

// GP - Apply smooth scroll to all hash links:

add_filter( 'generate_smooth_scroll_elements', function( $elements ) {
	$elements[] = 'a[href*="#"]';
	
	return $elements;
  } );

// GP -  Limit the number of words in manual excerpts

 add_filter( 'get_the_excerpt', function( $excerpt, $post ) {
	if ( has_excerpt( $post ) ) {
		$excerpt_length = apply_filters( 'excerpt_length', 15 );
		$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$excerpt        = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
	}
	return $excerpt;
  }, 10, 2 );

// CF7 - Redirect to thank-you page dynamically - Inject JavaScript into footer

function cf7_footer_script() { ?>
	<script>
	document.addEventListener('wpcf7mailsent', function(event) {
		// Get the current domain
		var currentDomain = window.location.hostname;
  
		// Construct the URL for the contact page and thank-you page
		var contactPage = 'https://' + currentDomain + '/kontakt';
		var thankYouPage = 'https://' + currentDomain + '/kontakt/danke';
  
		// Redirect to the thank-you page after form submission
		location.href = thankYouPage;
	}, false);
	</script>
	<?php
  }
  
  // Hook the function into the WordPress footer
  add_action('wp_footer', 'cf7_footer_script');  

// CF 7 - Activate Recaptcha and CF7 JS+CSS on relevant pages only

function block_recaptcha_badge() {
	$excluded_pages = array( 'kontakt', 'podcast', 'platz-reservieren' );
  
	if ( !is_page( $excluded_pages ) ) {
	  wp_dequeue_script( 'google-recaptcha' );
	  wp_deregister_script( 'google-recaptcha' );
	  add_filter( 'wpcf7_load_js', '__return_false' );
	  add_filter( 'wpcf7_load_css', '__return_false' );
	}
  }
  add_action( 'wp_print_scripts', 'block_recaptcha_badge' );

// YOAST - Add Tag basis to breadbrumb of tag archives
add_filter( 'wpseo_breadcrumb_links', 'custom_tag_archive_breadcrumbs' );
/**
 * Custom breadcrumb paths for tag archive pages, dynamically pulling from the "Schlagwort-Basis" setting.
 *
 * @param array $links Default breadcrumb links.
 * @return array Modified breadcrumb links.
 */
function custom_tag_archive_breadcrumbs( $links ) {
    if ( is_tag() ) {
        // Get the custom "Schlagwort-Basis" from the settings
        $tag_base = get_option( 'tag_base', 'schlagwort' ); // Default to 'schlagwort' if not set
        
        // Construct the full URL based on the site's home URL and the tag base
        $url = home_url( '/' . $tag_base . '/' );
        // Convert the URL part into a human-readable format for the breadcrumb text
        // Replace hyphens with spaces, and capitalize the first letter of each word
        $text = ucwords( str_replace( '-', ' ', $tag_base ) );
        // Create the new breadcrumb for the base
        $breadcrumb_base = array(
            'url'  => $url,
            'text' => $text,
        );
        // Retain the current tag in the breadcrumbs
        $current_tag = array_pop( $links );
        // Merge the breadcrumbs with the new base and the current tag
        $links = array_merge( $links, array( $breadcrumb_base, $current_tag ) );
    }
    return $links;
}

// GP - Add list of tags to glossary page

function list_terms_shortcode( $atts ) {
	// Define default attributes for the shortcode
	$atts = shortcode_atts( array(
		'taxonomy' => 'post_tag', // Default taxonomy is 'post_tag'
		'hide_empty' => false,    // Default is to show terms even if they are empty
	), $atts, 'list_terms' );

	// Retrieve terms
	$terms = get_terms( array(
		'taxonomy' => $atts['taxonomy'],
		'hide_empty' => $atts['hide_empty'],
	) );

	// Abort if no terms found or there is an error
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	// Initialize output variable
	$output = '';
	// Initialize an array to keep track of terms grouped by their starting letter
	$grouped_terms = array();

	// Group terms by their starting letter (case-insensitive)
	foreach ( $terms as $term ) {
		$first_letter = strtoupper( mb_substr( $term->name, 0, 1 ) ); // Get the first letter, case-insensitive
		if ( !isset( $grouped_terms[$first_letter] ) ) {
			$grouped_terms[$first_letter] = array();
		}
		$grouped_terms[$first_letter][] = $term;
	}

	// Generate output for each group
	foreach ( $grouped_terms as $letter => $terms_group ) {
		$output .= '<h2 id="' . esc_attr( $letter ) . '">' . esc_html( $letter ) . '</h2>'; // Add header with anchor
		$output .= '<ul>'; // Start unordered list

		// Generate list items for each term in the group
		foreach ( $terms_group as $term ) {
			// Create list item with tooltip displaying the term description
			$output .= sprintf(
				'<li><a href="%s" title="%s">%s</a> <span class="term-count">(%s)</span></li>',
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->description ), // Add the description as the title attribute for the tooltip
				esc_html( $term->name ),
				$term->count
			);
		}
		$output .= '</ul>'; // End unordered list
	}

	return $output;
}

// Register the shortcode
add_shortcode( 'list_terms', 'list_terms_shortcode' );

// GP - Enable tags for pages in WordPress

// Function to add the 'post_tag' taxonomy to 'page' post type
function add_tags_to_pages() {
    // Register the 'post_tag' taxonomy for the 'page' post type
    register_taxonomy_for_object_type('post_tag', 'page');
}

// Hook the function to the 'init' action to execute it when WordPress initializes
add_action('init', 'add_tags_to_pages');

// Optional: Ensure tags are recognized in queries involving pages
function include_tags_in_queries($query) {
    // Check if the query is not in the admin area and is the main query
    if (!is_admin() && $query->is_main_query()) {
        // Check if we are on a tag archive page
        if ($query->is_tag()) {
            // Include pages in the tag archive query
            $query->set('post_type', array('post', 'page'));
        }
    }
}

// Hook the function to the 'pre_get_posts' action to modify queries
add_action('pre_get_posts', 'include_tags_in_queries');

// GP - Add tag description to pages 

function show_tag_descriptions() {
    $taxonomy = 'post_tag';
    $terms = array();

    // Check if it's a single post or page
    if (is_singular()) {
        // Get terms associated with the current post or page
        $terms = get_the_terms(get_the_ID(), $taxonomy);
    } 
    // Check if it's a tag archive
    elseif (is_tag()) {
        // Get the current tag object
        $current_tag = get_queried_object();
        $terms = array($current_tag);
    }
    // Check if it's any other taxonomy archive (optional, can be extended for categories)
    elseif (is_tax($taxonomy)) {
        $current_term = get_queried_object();
        $terms = array($current_term);
    }

    // Check if terms were found and no error occurred
    if ($terms && !is_wp_error($terms)) {
        echo '<ul>'; // Start the unordered list block
        foreach ($terms as $term) {
            $description = term_description($term, $taxonomy);

            if ($description && strpos($description, $term->name) !== false) {
                // Check if the tag title is in the description and mark it as bold
                $description = str_replace($term->name, '<strong>' . $term->name . '</strong>', $description);
            }

            // Replace <p> with <div> inside <li> and add it as a list item
            $description = str_replace('<p>', '<div>', $description);
            $description = str_replace('</p>', '</div>', $description);
            
            echo '<li>' . $description . '</li>'; // Output the description as a list item
        }
        echo '</ul>'; // End the unordered list block
    }
}
  
  function show_tag_descriptions_shortcode() {
	  ob_start();
	  show_tag_descriptions();
	  return ob_get_clean();
  }
  add_shortcode('show_tag_descriptions', 'show_tag_descriptions_shortcode');  

// WG - Add plugin update checker for GitHub

// Include the plugin update checker library
require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5p4\PucFactory;

// Create the update checker instance
$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/locke85/wG-2.0-CFP/',
    __FILE__, //Full path to the main plugin file.
    'custom-functionality-deployment' // Unique-plugin-slug
);

// Optional: Set the branch that contains the stable release.
$updateChecker->setBranch('main');

// Enable release assets
$updateChecker->getVcsApi()->enableReleaseAssets();

// Optional: If you're using a private repository, specify the access token like this:
// $updateChecker->setAuthentication('your-token-here');
