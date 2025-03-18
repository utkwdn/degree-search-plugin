<?php
/**
 * Plugin Name: Degree Search Utility
 * Description: A custom plugin to add, edit, and display degree programs.
 * Plugin URI:   https://www.utk.edu
 * Author: UT OCM
 * Version: 1.0
 * Text Domain: degree-search-utility
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package DegreeSearchUtility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Post Type: Programs
 */
require_once plugin_dir_path( __FILE__ ) . 'import-settings.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function degree_search_utility_degree_search_utility_block_init() {
	register_block_type( __DIR__ . '/build/degree-search-utility' );
}
add_action( 'init', 'degree_search_utility_degree_search_utility_block_init' );


function utk_programs_register_cpts() {

	/**
	 * Post Type: Programs
	 */

	$labels = [
		"name" => __( "Programs", "utk-programs" ),
		"singular_name" => __( "Program", "utk-programs" ),
	];

	$args = [
		"label" => __( "Programs", "utk-programs" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "program", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-welcome-learn-more",
		"supports" => [ "title", "editor", "thumbnail" ],
		"show_in_graphql" => true,
		"graphql_single_name" => "Program",
		"graphql_plural_name" => "Programs",
	];

	register_post_type( "program", $args );
}

add_action( 'init', 'utk_programs_register_cpts' );

function utk_programs_register_taxes() {

	/**
	 * Taxonomy: Areas of Study
	 */

	$labels = [
		"name" => __( "Areas of Study", "utk-programs" ),
		"singular_name" => __( "Area of Study", "utk-programs" ),
	];

	
	$args = [
		"label" => __( "Areas of Study", "utk-programs" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'area', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "area",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "AreaOfStudy",
		"graphql_plural_name" => "AreasOfStudy",
	];
	register_taxonomy( "area", [ "program" ], $args );

	/**
	 * Taxonomy: Colleges
	 */

	$labels = [
		"name" => __( "Colleges", "utk-programs" ),
		"singular_name" => __( "College", "utk-programs" ),
	];

	
	$args = [
		"label" => __( "Colleges", "utk-programs" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'college', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "college",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "College",
		"graphql_plural_name" => "Colleges",
	];
	register_taxonomy( "college", [ "program" ], $args );

	/**
	 * Taxonomy: Degrees
	 */

	$labels = [
		"name" => __( "Degrees", "utk-programs" ),
		"singular_name" => __( "Degree", "utk-programs" ),
	];

	
	$args = [
		"label" => __( "Degree", "utk-programs" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'degree', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "degree",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "Degree",
		"graphql_plural_name" => "Degrees",
	];
	register_taxonomy( "degree", [ "program" ], $args );

	/**
	 * Taxonomy: Concentrations
	 */

	$labels = [
		"name" => __( "Concentrations", "utk-programs" ),
		"singular_name" => __( "Concentration", "utk-programs" ),
	];

	
	$args = [
		"label" => __( "Concentrations", "utk-programs" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'concentration', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "concentration",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => true,
		"graphql_single_name" => "Concentration",
		"graphql_plural_name" => "Concentrations",
	];
	register_taxonomy( "concentration", [ "program" ], $args );
}
add_action( 'init', 'utk_programs_register_taxes' );

/**
 * Filter programs by degree_type and allow filtering via API
 */

 function add_degree_type_filter( $args, $request ) {
    if ( isset( $request['degree_type'] ) ) {
        $degree_terms = get_terms( array(
            'taxonomy'   => 'degree',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'   => 'degree_type',
                    'value' => sanitize_text_field( $request['degree_type'] ),
                    'compare' => '='
                )
            )
        ));

        if ( ! empty( $degree_terms ) && ! is_wp_error( $degree_terms ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'degree',
                    'field'    => 'term_id',
                    'terms'    => wp_list_pluck( $degree_terms, 'term_id' ),
                )
            );
        } else {
            // Ensure no results are returned if no matching degree terms are found
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'degree',
                    'field'    => 'term_id',
                    'terms'    => array(0), // Non-existent term ID
                    'operator' => 'IN'
                )
            );
        }
    }
    return $args;
}
add_filter('rest_program_query', function ($args, $request) {
    $area = $request->get_param('area');
    $college = $request->get_param('college');
    $degree_type = $request->get_param('degree_type');

    // Ensure the tax_query array exists
    if ($area || $college || $degree_type) {
        $args['tax_query'] = [
            'relation' => 'AND',
        ];

        if ($area) {
            $args['tax_query'][] = [
                'taxonomy' => 'area',
                'field'    => 'term_id',
                'terms'    => $area,
            ];
        }

        if ($college) {
            $args['tax_query'][] = [
                'taxonomy' => 'college',
                'field'    => 'term_id',
                'terms'    => $college,
            ];
        }

        if ($degree_type) {
            // Get all degree terms with the requested ACF degree_type
            $degree_terms = get_terms([
                'taxonomy'   => 'degree',
                'hide_empty' => true,
                'fields'     => 'ids', // Get only IDs to reduce query load
                'meta_query' => [
                    [
                        'key'     => 'degree_type',  // ACF field key
                        'value'   => $degree_type,  // User requested value
                        'compare' => '='
                    ]
                ]
            ]);

            if (!empty($degree_terms) && !is_wp_error($degree_terms)) {
                $args['tax_query'][] = [
                    'taxonomy' => 'degree',
                    'field'    => 'term_id',
                    'terms'    => $degree_terms,
                    'operator' => 'IN',
                ];
            } else {
                // If no matching degrees exist, force an empty query
                $args['tax_query'][] = [
                    'taxonomy' => 'degree',
                    'field'    => 'term_id',
                    'terms'    => [0], // No valid term ID, ensuring no results
                    'operator' => 'IN',
                ];
            }
        }
    }

    return $args;
}, 10, 2);

function register_degree_type_rest_field() {
    register_rest_field( 'degree', 'degree_type', array(
        'get_callback'    => function( $object ) {
            return get_field( 'degree_type', $object['id'] );
        },
        'schema'          => array(
            'description' => __( 'Degree Type', 'degree-search-utility' ),
            'type'        => 'string',
            'context'     => array( 'view', 'edit' )
        ),
    ));
}
add_action( 'rest_api_init', 'register_degree_type_rest_field' );


/**
 * Filter programs by online availability and allow filtering via API
 */

function register_concentration_online_rest_field() {
    register_rest_field( 'concentration', 'online', array(
        'get_callback'    => function( $object ) {
            return get_field( 'online', 'concentration_' . $object['id'] ); // ACF stores taxonomy fields with "concentration_"
        },
        'schema'          => array(
            'description' => __( 'Online Availability', 'degree-search-utility' ),
            'type'        => 'boolean',
            'context'     => array( 'view', 'edit' )
        ),
    ));
}
add_action( 'rest_api_init', 'register_concentration_online_rest_field' );

function add_online_filter( $args, $request ) {
    if ( isset( $request['online'] ) ) {
        $online_value = filter_var( $request['online'], FILTER_VALIDATE_BOOLEAN );

        $concentration_terms = get_terms( array(
            'taxonomy'   => 'concentration',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'     => 'online',
                    'value'   => $online_value ? '1' : '0', // ACF stores true as "1" and false as "0"
                    'compare' => '='
                )
            )
        ));

        if ( ! empty( $concentration_terms ) && ! is_wp_error( $concentration_terms ) ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'concentration',
                'field'    => 'term_id',
                'terms'    => wp_list_pluck( $concentration_terms, 'term_id' ),
            );
        }
    }
    return $args;
}
add_filter( 'rest_program_query', 'add_online_filter', 10, 2 );


/**
 * Add custom fields to Area of Study taxonomy, Program post type, Unit post type.
 */

 if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_6319052a29c55',
		'title' => 'Area of Study Fields',
		'fields' => array(
			array(
				'key' => 'field_631905304e39a',
				'label' => 'Image',
				'name' => 'image',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'show_in_graphql' => 1,
				'return_format' => 'url',
				'preview_size' => 'medium',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array(
				'key' => 'field_631905574e39b',
				'label' => 'Url',
				'name' => 'aos-url',
				'type' => 'url',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'show_in_graphql' => 1,
				'default_value' => '',
				'placeholder' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'area',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 1,
		'show_in_graphql' => 1,
		'graphql_field_name' => 'areaStudyFields',
	));

	acf_add_local_field_group(array(
		'key' => 'group_degree_type',
		'title' => 'Degree Type',
		'fields' => array(
			array(
				'key' => 'field_degree_type',
				'label' => 'Degree Type',
				'name' => 'degree_type',
				'type' => 'select',
				// 'instructions' => 'Select the type of degree.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'Undergraduate' => 'Undergraduate',
					'Graduate' => 'Graduate',
					'Undergraduate Certificate' => 'Undergraduate Certificate',
					'Graduate Certificate' => 'Graduate Certificate',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 1,
				'ajax' => 0,
				'return_format' => 'value',
				'placeholder' => 'Select Degree Type',
				'show_in_graphql' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'degree',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'show_in_rest' => 1,
		'show_in_graphql' => 1,
		'graphql_field_name' => 'degreeType',
	));
	
	acf_add_local_field_group(array(
		'key' => 'group_6319067c31062',
		'title' => 'Program Details',
		'fields' => array(
			array(
				'key' => 'field_63190687805bc',
				'label' => 'Url',
				'name' => 'program-url',
				'type' => 'url',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'show_in_graphql' => 1,
				'default_value' => '',
				'placeholder' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'program',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 1,
		'show_in_graphql' => 1,
		'graphql_field_name' => 'programDetailsFields',
	));

	acf_add_local_field_group( array(
        'key' => 'group_concentration_details',
        'title' => 'Concentration Details',
        'fields' => array(
			array(
                'key' => 'field_online',
                'label' => 'Online',
                'name' => 'online',
                'type' => 'true_false',
                // 'instructions' => 'Check if this concentration is available online.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                // 'message' => 'Available Online',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
                'show_in_graphql' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'concentration',
                ),
            ),
        ),
        'active' => true,
        'show_in_rest' => 1,
        'show_in_graphql' => 1,
    ));

	endif;
