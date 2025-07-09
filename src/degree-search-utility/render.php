<?php
/**
 * Render callback for the Degree Search Utility block.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package DegreeSearchUtility
 */

if ( ! function_exists( 'degree_search_utility_render_callback' ) ) {
	/**
	 * Render the Degree Search Utility block.
	 *
	 * @return string Rendered HTML.
	 */
	function degree_search_utility_render_callback() {
		return '<div class="areasContainer alignfull" id="filters"></div>';
	}
}

// Output the rendered HTML.
echo wp_kses_post( degree_search_utility_render_callback() );
