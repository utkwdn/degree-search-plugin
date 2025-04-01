<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if (!function_exists('degree_search_utility_render_callback')) {
    function degree_search_utility_render_callback() {
        return '<div class="areasContainer alignfull" id="filters"></div>';
    }
}

// Output the rendered HTML
echo degree_search_utility_render_callback();