<?php
/**
 * Render callback for the Degree Search Widget block.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package DegreeSearchUtility
 */

if ( ! function_exists( 'degree_search_widget_render_callback' ) ) {
	/**
	 * Render the Degree Search Widget block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	function degree_search_widget_render_callback( $attributes ) {
		// Get the selected area of study.
		$area_of_study = isset( $attributes['areaOfStudy'] ) ? esc_attr( $attributes['areaOfStudy'] ) : '';

		// Get the selected page ID and convert it to a URL.
		$degree_search_page = isset( $attributes['degreeSearchPage'] ) ? intval( $attributes['degreeSearchPage'] ) : 0;
		$degree_search_url  = $degree_search_page ? get_permalink( $degree_search_page ) : '';

		// Render the block container with the selected area and degree search URL as data attributes.
		return '<div id="degree-search-widget" data-area="' . $area_of_study . '" data-url="' . esc_url( $degree_search_url ) . '"></div>';
	}
}

// Output the rendered HTML.
echo wp_kses_post( degree_search_widget_render_callback( $attributes ) );
