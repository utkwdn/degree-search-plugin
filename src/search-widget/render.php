<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if (!function_exists('degree_search_widget_render_callback')) {
    function degree_search_widget_render_callback($attributes) {
        // Get the selected area of study
        $area_of_study = isset($attributes['areaOfStudy']) ? esc_attr($attributes['areaOfStudy']) : '';

        // Get the selected page ID and convert it to a URL
        $degree_search_page = isset($attributes['degreeSearchPage']) ? intval($attributes['degreeSearchPage']) : 0;
        $degree_search_url = $degree_search_page ? get_permalink($degree_search_page) : '';

        // Render the block container with the selected area and degree search URL as data attributes
        return '<div id="degree-search-widget" data-area="' . $area_of_study . '" data-url="' . esc_url($degree_search_url) . '"></div>';
    }
}

// Output the rendered HTML
echo degree_search_widget_render_callback($attributes);