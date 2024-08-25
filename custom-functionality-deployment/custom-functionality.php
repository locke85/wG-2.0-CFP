<?php
/*
Plugin Name: webGefährte - Custom Functionality
Description: Contains custom functionality and modifications.
Version: 1.2.2
Author: Jan (webGefährte)
*/

// GP - Add tag description to pages 

function show_tag_descriptions() {
	$taxonomy = 'post_tag';
	$terms = get_the_terms(get_the_ID(), $taxonomy);
  
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

// GP - Add list of tags to page

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
			$output .= sprintf(
				'<li><a href="%s">%s</a> <span class="term-count">(%s)</span></li>',
				esc_url( get_term_link( $term ) ),
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

// WG - Add plugin update checker for GitHub

require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/locke85/webgefaehrte/',
    __FILE__, //Full path to the main plugin file.
    'custom-functionality-deployment' // Unique-plugin-slug
);

// Optional: Set the branch that contains the stable release.
$updateChecker->setBranch('main');

// Optional: If you're using a private repository, specify the access token like this:
// $updateChecker->setAuthentication('your-token-here');
