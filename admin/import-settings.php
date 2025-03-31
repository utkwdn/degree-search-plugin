<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Add CSV Import submenu page
function dsu_add_csv_import_page() {
    add_submenu_page(
        'edit.php?post_type=program',
        'Import Programs',
        'Import Programs',
        'manage_options',
        'dsu-import-programs',
        'dsu_render_csv_import_page'
    );
}
add_action('admin_menu', 'dsu_add_csv_import_page');

// Render CSV Import Page
function dsu_render_csv_import_page() {
    ?>
    <div class="wrap">
        <h1>Manage Programs</h1>
        
        <!-- CSV Import Form -->
        <h2>Import Programs from CSV</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <input type="submit" name="dsu_import_csv" class="button-primary" value="Import">
        </form>

        <!-- Delete All Programs Button -->
        <h2>Delete All Programs</h2>
        <form method="post">
            <input type="submit" name="dsu_delete_all_programs" class="button-secondary" value="Delete All Programs" onclick="return confirm('Are you sure you want to delete all programs? This action cannot be undone.');">
        </form>
    </div>
    <?php

    if (isset($_POST['dsu_import_csv'])) {
        dsu_handle_csv_upload();
    }

    if (isset($_POST['dsu_delete_all_programs'])) {
        dsu_delete_all_programs();
    }
}

// Handle CSV Upload
function dsu_handle_csv_upload() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="error"><p>Error uploading file.</p></div>';
        return;
    }
    
    $file = $_FILES['csv_file']['tmp_name'];
    $csv_data = array_map('str_getcsv', file($file));
    
    if (empty($csv_data)) {
        echo '<div class="error"><p>CSV file is empty.</p></div>';
        return;
    }
    
    // Process CSV Data
    $header = array_map('trim', $csv_data[0]);
    unset($csv_data[0]);

    $programs = [];

    foreach ($csv_data as $row) {
        $row = array_map('trim', $row);
        $data = array_combine($header, $row);

        $program_key = $data['MajorName'] . ' - ' . $data['DegreeName'];
        // Split Areas into array
        $data['AreasArray'] = explode(' | ', $data['AreaName']);

        // Group data by unique program
        if (!isset($programs[$program_key])) {
            $programs[$program_key] = [
                'MajorName' => $data['MajorName'],
                'DegreeName' => $data['DegreeName'],
                'Colleges' => [$data['CollegeName']],
                'Areas' => $data['AreasArray'],
                'Concentrations' => [],
            ];
        }

        // Store unique colleges and areas
        if (!in_array($data['CollegeName'], $programs[$program_key]['Colleges'])) {
            $programs[$program_key]['Colleges'][] = $data['CollegeName'];
        }

        foreach($data['AreasArray'] as $areaName) {
            if (!in_array($areaName, $programs[$program_key]['Areas'])) {
                $programs[$program_key]['Areas'][] = $areaName;
            }
        }
        
        
        // If online and no concentration, add set concentration as 'none-online' or 'none' for sorting in dsu_create_program_post function
        if ( empty($data['ConcentrationName'])) {
            if($data['Online Status'] == '1') {
                $programs[$program_key]['Concentrations'][$data['MajorName'] . ' (Online)'] = intval($data['Online Status']);
                
            } else {

                $programs[$program_key]['Concentrations']['none'] = intval($data['Online Status']);
            }
        } else {
            // Add ' (Online)' tag to named online concentrations for sorting
            $concentrationName = $data['Online Status'] == '1' ? $data['ConcentrationName'] . ' (Online)' : $data['ConcentrationName'];
            // Store concentration with Online Status
            $programs[$program_key]['Concentrations'][$concentrationName] = intval($data['Online Status']);
        }
    }

    // echo '<pre>';
    // print_r($programs);
    // echo '</pre>';

    // Insert each program
    foreach ($programs as $program_key => $program_data) {
        dsu_create_program_post($program_data);
    }

    echo '<div class="updated"><p>Programs imported successfully.</p></div>';
}

// Create or Update Program Post
function dsu_create_program_post($data) {

    // Decode HTML entities and sanitize input values
    $program_name = sanitize_text_field(html_entity_decode($data['MajorName'], ENT_QUOTES, 'UTF-8'));
    $degree_name = sanitize_text_field(html_entity_decode($data['DegreeName'], ENT_QUOTES, 'UTF-8'));

    if (array_key_exists('none', $data['Concentrations'])) {
        // If other concentrations exist for program, use program name instead of 
        if(count($data['Concentrations']) > 1) {
            $data['Concentrations'][$program_name] = $data['Concentrations']['none'];
        // If only an unnamed offline concentration exists, prepare data to skip adding the concentration
        } else {
            $data['Concentrations'][''] = $data['Concentrations']['none'];
        }

        unset($data['Concentrations']['none']);
    }

    $degrees = [$degree_name];

    // Sanitize and decode all concentration names
    $concentrations = [];
    foreach ($data['Concentrations'] as $name => $online_status) {
        $clean_name = sanitize_text_field(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
        $concentrations[$clean_name] = $online_status;
    }

    $concentrations_string = implode(', ', array_keys($concentrations));

    // Sanitize and decode colleges and areas
    $colleges = array_map(fn($c) => sanitize_text_field(html_entity_decode($c, ENT_QUOTES, 'UTF-8')), $data['Colleges']);
    $areas = array_map(fn($a) => sanitize_text_field(html_entity_decode($a, ENT_QUOTES, 'UTF-8')), $data['Areas']);

    // Determine degree type based on degree name
    if (strpos($degree_name, 'B') === 0) {
        $degree_type = 'Undergraduate';
    } elseif (in_array($degree_name, ['C3', 'Undergraduate Certificate'])) {
        $degree_type = 'Undergraduate Certificate';
    } elseif (in_array($degree_name, ['C4', 'Graduate Certificate'])) {
        $degree_type = 'Graduate Certificate';
    } else {
        $degree_type = 'Graduate';
    }

    // Add comma-separated list of concentrations to post_content for searchability
    $program_content = !empty($concentrations_string) ? "<!-- wp:paragraph --><p>" . esc_html($concentrations_string) . "</p><!-- /wp:paragraph -->" : '';

    $post_id = wp_insert_post([
        'post_title'   => $program_name,
        'post_type'    => 'program',
        'post_status'  => 'publish',
        'post_content' => $program_content,
    ]);

    if ($post_id) {
        // Assign taxonomies
        dsu_assign_terms($post_id, $degrees, 'degree');
        dsu_assign_terms($post_id, $colleges, 'college');
        dsu_assign_terms($post_id, $areas, 'area');

        // Assign degree type as an ACF field to the degree taxonomy
        $degree_term = term_exists($degree_name, 'degree');
        if ($degree_term && !is_wp_error($degree_term)) {
            $term_id = $degree_term['term_id'] ?? $degree_term;
            update_field('degree_type', $degree_type, 'degree_' . $term_id);
        }

        // Assign concentrations and set online status
        foreach ($concentrations as $concentration_name => $online_status) {
            $term = term_exists($concentration_name, 'concentration');
            if (!$term) {
                $term = wp_insert_term($concentration_name, 'concentration');
            }
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'] ?? $term;
                wp_set_object_terms($post_id, $concentration_name, 'concentration', true);
                update_field('online', $online_status, 'concentration_' . $term_id);
            }
        }
    }
}

// Assign Terms to Post
function dsu_assign_terms($post_id, $terms, $taxonomy) {
    if (!empty($terms)) {
        foreach ($terms as $term) {
            if (!term_exists($term, $taxonomy)) {
                wp_insert_term($term, $taxonomy);
            }
        }
        wp_set_object_terms($post_id, $terms, $taxonomy);
    }
}

// Delete All Programs
function dsu_delete_all_programs() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.'));
    }

    $programs = get_posts([
        'post_type'      => 'program',
        'posts_per_page' => -1,
        'post_status'    => 'any'
    ]);

    if (empty($programs)) {
        echo '<div class="updated"><p>No programs found to delete.</p></div>';
        return;
    }

    foreach ($programs as $program) {
        wp_delete_post($program->ID, true);
    }

    // Delete all terms associated with programs
    $taxonomies = ['degree', 'college', 'area', 'concentration'];
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }

    echo '<div class="updated"><p>All programs and associated data have been deleted.</p></div>';
}