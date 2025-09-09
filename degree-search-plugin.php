<?php
/**
 * Plugin Name: Degree Search Utility
 * Description: A custom plugin to add, edit, and display degree programs
 * Plugin URI:   https://github.com/utkwdn/degree-search-plugin
 * Author: The University of Tennessee, Knoxville
 * Version: 1.1.3
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
 * Include Import Settings Page
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/import-settings.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function degree_search_utility_degree_search_utility_block_init() {
	register_block_type( __DIR__ . '/build/degree-search-utility' );
	register_block_type( __DIR__ . '/build/search-widget' );
}
add_action( 'init', 'degree_search_utility_degree_search_utility_block_init' );

/**
 * Register the Programs custom post type
 */
function utk_programs_register_cpts() {

	/**
	 * Post Type: Programs
	 */

	$labels = array(
		'name'          => __( 'Programs', 'utk-programs' ),
		'singular_name' => __( 'Program', 'utk-programs' ),
	);

	$args = array(
		'label'                 => __( 'Programs', 'utk-programs' ),
		'labels'                => $labels,
		'description'           => '',
		'public'                => false,
		'publicly_queryable'    => true,
		'show_ui'               => true,
		'show_in_rest'          => true,
		'rest_base'             => '',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'rest_namespace'        => 'wp/v2',
		'has_archive'           => false,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'delete_with_user'      => false,
		'exclude_from_search'   => true,
		'capability_type'       => 'post',
		'map_meta_cap'          => true,
		'hierarchical'          => false,
		'can_export'            => false,
		'rewrite'               => false,
		'query_var'             => false,
		'menu_icon'             => 'dashicons-welcome-learn-more',
		'supports'              => array( 'title', 'editor', 'thumbnail' ),
		'show_in_graphql'       => true,
		'graphql_single_name'   => 'Program',
		'graphql_plural_name'   => 'Programs',
	);

	register_post_type( 'program', $args );
}

add_action( 'init', 'utk_programs_register_cpts' );

/**
 * Register taxonomies for programs post type
 */
function utk_programs_register_taxes() {

	/**
	 * Taxonomy: Areas of Study
	 */

	$labels = array(
		'name'          => __( 'Areas of Study', 'utk-programs' ),
		'singular_name' => __( 'Area of Study', 'utk-programs' ),
	);

	$args = array(
		'label'                 => __( 'Areas of Study', 'utk-programs' ),
		'labels'                => $labels,
		'public'                => false,
		'publicly_queryable'    => false,
		'has_archive'           => false,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => false,
		'rewrite'               => array(
			'slug'       => 'area',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'show_tagcloud'         => false,
		'rest_base'             => 'area',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'rest_namespace'        => 'wp/v2',
		'show_in_quick_edit'    => false,
		'sort'                  => false,
		'show_in_graphql'       => true,
		'graphql_single_name'   => 'AreaOfStudy',
		'graphql_plural_name'   => 'AreasOfStudy',
	);
	register_taxonomy( 'area', array( 'program' ), $args );

	/**
	 * Taxonomy: Colleges
	 */

	$labels = array(
		'name'          => __( 'Colleges', 'utk-programs' ),
		'singular_name' => __( 'College', 'utk-programs' ),
	);

	$args = array(
		'label'                 => __( 'Colleges', 'utk-programs' ),
		'labels'                => $labels,
		'public'                => false,
		'publicly_queryable'    => false,
		'has_archive'           => false,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => false,
		'rewrite'               => array(
			'slug'       => 'college',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'show_tagcloud'         => false,
		'rest_base'             => 'college',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'rest_namespace'        => 'wp/v2',
		'show_in_quick_edit'    => false,
		'sort'                  => false,
		'show_in_graphql'       => true,
		'graphql_single_name'   => 'College',
		'graphql_plural_name'   => 'Colleges',
	);
	register_taxonomy( 'college', array( 'program' ), $args );

	/**
	 * Taxonomy: Degrees
	 */

	$labels = array(
		'name'          => __( 'Degrees', 'utk-programs' ),
		'singular_name' => __( 'Degree', 'utk-programs' ),
	);

	$args = array(
		'label'                 => __( 'Degree', 'utk-programs' ),
		'labels'                => $labels,
		'public'                => false,
		'publicly_queryable'    => false,
		'has_archive'           => false,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => false,
		'rewrite'               => array(
			'slug'       => 'degree',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'show_tagcloud'         => false,
		'rest_base'             => 'degree',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'rest_namespace'        => 'wp/v2',
		'show_in_quick_edit'    => false,
		'sort'                  => false,
		'show_in_graphql'       => true,
		'graphql_single_name'   => 'Degree',
		'graphql_plural_name'   => 'Degrees',
	);
	register_taxonomy( 'degree', array( 'program' ), $args );

	/**
	 * Taxonomy: Concentrations
	 */

	$labels = array(
		'name'          => __( 'Concentrations', 'utk-programs' ),
		'singular_name' => __( 'Concentration', 'utk-programs' ),
	);

	$args = array(
		'label'                 => __( 'Concentrations', 'utk-programs' ),
		'labels'                => $labels,
		'public'                => false,
		'publicly_queryable'    => false,
		'has_archive'           => false,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => false,
		'rewrite'               => array(
			'slug'       => 'concentration',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'show_tagcloud'         => false,
		'rest_base'             => 'concentration',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'rest_namespace'        => 'wp/v2',
		'show_in_quick_edit'    => false,
		'sort'                  => false,
		'show_in_graphql'       => true,
		'graphql_single_name'   => 'Concentration',
		'graphql_plural_name'   => 'Concentrations',
	);
	register_taxonomy( 'concentration', array( 'program' ), $args );
}
add_action( 'init', 'utk_programs_register_taxes' );

/**
 * Filters programs by degree type and allows filtering via the API.
 *
 * Adds a tax_query to the arguments if a 'degree_type' is present in the request.
 *
 * @param array           $args    Query arguments to filter.
 * @param WP_REST_Request $request The REST request object.
 * @return array Filtered query arguments.
 */
function add_degree_type_filter( $args, $request ) {
	if ( isset( $request['degree_type'] ) ) {
		$degree_terms = get_terms(
			array(
				'taxonomy'   => 'degree',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'     => 'degree_type',
						'value'   => sanitize_text_field( $request['degree_type'] ),
						'compare' => '=',
					),
				),
			)
		);

		if ( ! empty( $degree_terms ) && ! is_wp_error( $degree_terms ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'degree',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $degree_terms, 'term_id' ),
				),
			);
		} else {
			// Ensure no results are returned if no matching degree terms are found.
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'degree',
					'field'    => 'term_id',
					'terms'    => array( 0 ), // Non-existent term ID.
					'operator' => 'IN',
				),
			);
		}
	}
	return $args;
}

add_filter(
	'rest_program_query',
	function ( $args, $request ) {
		$area        = $request->get_param( 'area' );
		$college     = $request->get_param( 'college' );
		$degree_type = $request->get_param( 'degree_type' );

		// Ensure the tax_query array exists.
		if ( $area || $college || $degree_type ) {
			$args['tax_query'] = array(
				'relation' => 'AND',
			);

			if ( $area ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'area',
					'field'    => 'term_id',
					'terms'    => $area,
				);
			}

			if ( $college ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'college',
					'field'    => 'term_id',
					'terms'    => $college,
				);
			}

			if ( $degree_type ) {
				// Use 'LIKE' comparison for 'certificate to bring back both graduate and undergraduate certificates.
				$compare_operator = 'certificate' === $degree_type ? 'LIKE' : '=';

				// Get all degree terms with the requested ACF degree_type.
				$degree_terms = get_terms(
					array(
						'taxonomy'   => 'degree',
						'hide_empty' => true,
						'fields'     => 'ids', // Get only IDs to reduce query load.
						'meta_query' => array(
							array(
								'key'     => 'degree_type',  // ACF field key.
								'value'   => $degree_type,  // User requested value.
								'compare' => $compare_operator,
							),
						),
					)
				);

				if ( ! empty( $degree_terms ) && ! is_wp_error( $degree_terms ) ) {
					$args['tax_query'][] = array(
						'taxonomy' => 'degree',
						'field'    => 'term_id',
						'terms'    => $degree_terms,
						'operator' => 'IN',
					);
				} else {
					// If no matching degrees exist, force an empty query.
					$args['tax_query'][] = array(
						'taxonomy' => 'degree',
						'field'    => 'term_id',
						'terms'    => array( 0 ), // No valid term ID, ensuring no results.
						'operator' => 'IN',
					);
				}
			}
		}

		return $args;
	},
	10,
	2
);

/**
 * Registers the 'degree_type' field on the 'degree' REST API endpoint.
 *
 * Adds a custom field to REST API responses for the 'degree' post type,
 * exposing the ACF field 'degree_type'.
 *
 * @return void
 */
function register_degree_type_rest_field() {
	register_rest_field(
		'degree',
		'degree_type',
		array(
			'get_callback' => function ( $post_data ) {
				return get_field( 'degree_type', $post_data['id'] );
			},
			'schema'       => array(
				'description' => __( 'Degree Type', 'degree-search-utility' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'register_degree_type_rest_field' );


/**
 * Filter programs by online availability and allow filtering via API
 */
function register_concentration_online_rest_field() {
	register_rest_field(
		'concentration',
		'online',
		array(
			'get_callback' => function ( $post_data ) {
				return get_field( 'online', 'concentration_' . $post_data['id'] ); // ACF stores taxonomy fields with "concentration_".
			},
			'schema'       => array(
				'description' => __( 'Online Availability', 'degree-search-utility' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'register_concentration_online_rest_field' );

/**
 * Filters programs by whether they are available online.
 *
 * If the 'online' parameter is present in the request, this function filters
 * the 'concentration' taxonomy terms by the ACF 'online' field, which is stored
 * as "1" for true and "0" for false.
 *
 * @param array           $args    Query arguments to filter.
 * @param WP_REST_Request $request The REST request object.
 * @return array Modified query arguments including the online filter, if applicable.
 */
function add_online_filter( $args, $request ) {
	if ( isset( $request['online'] ) ) {
		$online_value = filter_var( $request['online'], FILTER_VALIDATE_BOOLEAN );

		$concentration_terms = get_terms(
			array(
				'taxonomy'   => 'concentration',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'     => 'online',
						'value'   => $online_value ? '1' : '0', // ACF stores true as "1" and false as "0".
						'compare' => '=',
					),
				),
			)
		);

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
add_filter( 'rest_program_query', 'add_online_filter', 30, 2 );


/**
 * Expands keyword search to add support for searching common degree terms (e.g. "Bachelors in Nursing")
 *
 * @param array           $args    Query arguments to filter.
 * @param WP_REST_Request $request The REST request object.
 * @return array Modified query arguments
 */
function keyword_search_with_degree_search_support( $args, $request ) {
	if ( isset( $request['search_term'] ) ) {
		$search_term = strtolower( sanitize_text_field( $request['search_term'] ) );

		// Break search into keywords.
		$words = preg_split( '/\s+/', $search_term );

		// Words to skip in search.
		$skip_words = array( 'and', 'or', 'in', 'of', 'the', 'a', 'an', 'for', 'to', 'with', 'on', 'at', 'by', 'from' );

		$degree_matched_ids  = array();
		$non_degree_keywords = array();

		foreach ( $words as $word ) {
			// Skip $skip_words.
			if ( in_array( $word, $skip_words, true ) ) {
				continue;
			}

			// Match "phd" with or without "." after letters and any capitalization.
			if ( preg_match( '/^p\.?h\.?d\.?$/i', $word ) ) {
				$degree_matched_ids = array_merge( $degree_matched_ids, get_degree_terms_starting_with( array( 'P' ) ) );
			} elseif ( str_contains( $word, 'bachelor' ) ) {
				$degree_matched_ids = array_merge( $degree_matched_ids, get_degree_terms_starting_with( array( 'B' ) ) );
			} elseif ( str_contains( $word, 'master' ) ) {
				$degree_matched_ids = array_merge( $degree_matched_ids, get_degree_terms_starting_with( array( 'M' ) ) );
			} elseif ( str_contains( $word, 'doctor' ) ) {
				$degree_matched_ids = array_merge( $degree_matched_ids, get_degree_terms_starting_with( array( 'D', 'E', 'J', 'P' ) ) );
			} else {
				// Remove trailing 's' if $word is longer than 2 characters ("Communications" will also match "Communication").
				if ( strlen( $word ) > 2 ) {
					$word = preg_replace( '/s$/i', '', $word );
				}
				$non_degree_keywords[] = $word;
			}
		}

		// If any degree matches found, add tax_query.
		if ( ! empty( $degree_matched_ids ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'degree',
				'field'    => 'term_id',
				'terms'    => array_unique( $degree_matched_ids ),
				'operator' => 'IN',
			);
		}

		// If non-degree search terms exist, use keyword search.
		if ( ! empty( $non_degree_keywords ) ) {
			$args['s'] = implode( ' ', $non_degree_keywords );
		}
	}

	return $args;
}

/**
 * Helper to find degree terms starting with a specific letter
 *
 * @param array $prefixes Array of starting letter for the degree name.
 * @return array IDs of matching degrees
 */
function get_degree_terms_starting_with( $prefixes ) {
	$matched = array();

	if ( empty( $prefixes ) || ! is_array( $prefixes ) ) {
		return $matched;
	}

	// Terms to exclude explicitly.
	$excluded = array( 'EDS' );

	// Get all degree terms once.
	$terms = get_terms(
		array(
			'taxonomy'   => 'degree',
			'hide_empty' => false,
		)
	);

	foreach ( $terms as $term ) {
		$term_name_upper = strtoupper( $term->name );

		// Skip if term is in the excluded list.
		if ( in_array( $term_name_upper, $excluded, true ) ) {
			continue;
		}

		foreach ( $prefixes as $prefix ) {
			if ( stripos( $term->name, $prefix ) === 0 ) {
				$matched[] = $term->term_id;
				break; // Avoid duplicates if multiple prefixes match.
			}
		}
	}

	return array_unique( $matched );
}
add_filter( 'rest_program_query', 'keyword_search_with_degree_search_support', 20, 2 );


/**
 * Add custom fields to Area of Study taxonomy, Program post type, Unit post type.
 */

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_6319052a29c55',
			'title'                 => 'Area of Study Fields',
			'fields'                => array(
				array(
					'key'               => 'field_631905304e39a',
					'label'             => 'Image',
					'name'              => 'image',
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'show_in_graphql'   => 1,
					'return_format'     => 'url',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'min_width'         => '',
					'min_height'        => '',
					'min_size'          => '',
					'max_width'         => '',
					'max_height'        => '',
					'max_size'          => '',
					'mime_types'        => '',
				),
				array(
					'key'               => 'field_631905574e39b',
					'label'             => 'Url',
					'name'              => 'aos-url',
					'type'              => 'url',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'show_in_graphql'   => 1,
					'default_value'     => '',
					'placeholder'       => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'area',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 1,
			'show_in_graphql'       => 1,
			'graphql_field_name'    => 'areaStudyFields',
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_degree_type',
			'title'                 => 'Degree Type',
			'fields'                => array(
				array(
					'key'               => 'field_degree_type',
					'label'             => 'Degree Type',
					'name'              => 'degree_type',
					'type'              => 'select',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'Undergraduate'             => 'Undergraduate',
						'Graduate'                  => 'Graduate',
						'Undergraduate Certificate' => 'Undergraduate Certificate',
						'Graduate Certificate'      => 'Graduate Certificate',
					),
					'default_value'     => '',
					'allow_null'        => 1,
					'multiple'          => 0,
					'ui'                => 1,
					'ajax'              => 0,
					'return_format'     => 'value',
					'placeholder'       => 'Select Degree Type',
					'show_in_graphql'   => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'degree',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'show_in_rest'          => 1,
			'show_in_graphql'       => 1,
			'graphql_field_name'    => 'degreeType',
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_6319067c31062',
			'title'                 => 'Program Details',
			'fields'                => array(
				array(
					'key'               => 'field_63190687805bc',
					'label'             => 'Url',
					'name'              => 'program-url',
					'type'              => 'url',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'show_in_graphql'   => 1,
					'default_value'     => '',
					'placeholder'       => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'program',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 1,
			'show_in_graphql'       => 1,
			'graphql_field_name'    => 'programDetailsFields',
		)
	);

	acf_add_local_field_group(
		array(
			'key'             => 'group_concentration_details',
			'title'           => 'Concentration Details',
			'fields'          => array(
				array(
					'key'               => 'field_online',
					'label'             => 'Online',
					'name'              => 'online',
					'type'              => 'true_false',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => 'Yes',
					'ui_off_text'       => 'No',
					'show_in_graphql'   => 1,
				),
			),
			'location'        => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'concentration',
					),
				),
			),
			'active'          => true,
			'show_in_rest'    => 1,
			'show_in_graphql' => 1,
		)
	);

	endif;
