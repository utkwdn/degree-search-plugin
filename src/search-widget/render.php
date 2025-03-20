<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

function degree_search_widget_render_callback($attributes) {
    // Get the selected Area of Study from block attributes
    $area_of_study = isset($attributes['areaOfStudy']) ? esc_attr($attributes['areaOfStudy']) : '';

    // Render the block container with the selected area as a data attribute
    return '<section class="areasContainer alignfull" id="search-widget" data-area="' . $area_of_study . '"></section>';
}

// Output the rendered HTML
echo degree_search_widget_render_callback($attributes);