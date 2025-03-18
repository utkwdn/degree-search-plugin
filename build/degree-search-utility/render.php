<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

function degree_search_utility_render_callback() {
    
    echo '<section class="areasContainer alignfull" id="filters"> </section>';
}

// Output the rendered HTML
echo degree_search_utility_render_callback();