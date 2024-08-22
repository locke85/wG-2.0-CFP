<?php
/*
Plugin Name: Custom Functionality
Description: Contains custom functionality and modifications.
Version: 1.0
Author: Jan (webGefährte)
*/

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

