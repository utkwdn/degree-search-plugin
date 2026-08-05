<?php
/**
 * Program CSV Import functionality.
 *
 * Provides admin tools to import programs from a CSV file, update program URLs and delete all programs
 *
 * @package DegreeSearchUtility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the "Manage Programs" submenu page.
 *
 * @return void
 */
function dsu_add_csv_import_page() {
	add_submenu_page(
		'edit.php?post_type=program',
		'Manage Programs',
		'Manage Programs',
		'manage_options',
		'dsu-manage-programs',
		'dsu_render_csv_import_page'
	);
}
add_action( 'admin_menu', 'dsu_add_csv_import_page' );

/**
 * Render the CSV import, url update and delete programs admin page.
 *
 * @return void
 */
function dsu_render_csv_import_page() {
	?>
	<div class="wrap">
		<h1>Manage Programs</h1>
		
		<!-- CSV Import Form -->
		<div class="dsu-section" style="margin-bottom: 30px; padding-bottom: 30px;  border-bottom: 1px solid #d4d4d4;">
			<h2>Import Programs from CSV</h2>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'dsu_import_csv_action', 'dsu_import_csv_nonce' ); ?>
				<input type="file" name="csv_file" accept=".csv" required>
				<input type="submit" name="dsu_import_csv" class="button-primary" value="Import">
			</form>
		</div>

		<!-- Update Program URLs -->
		<div class="dsu-section" style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #d4d4d4;">
			<h2>Update Program URLs</h2>
			<form method="post" id="dsu_update_program_urls_form">
				<?php wp_nonce_field( 'dsu_update_program_urls_action', 'dsu_update_program_urls_nonce' ); ?>
				<!-- <label for="college_select">Select College:</label> -->
				<select name="url_category" id="college_select" required>
					<option value="">-- Select College / Vols Online --</option>
					<option value="online">Vols Online</option>
					<?php
					$colleges = get_terms(
						array(
							'taxonomy'   => 'college',
							'hide_empty' => false,
						)
					);
					foreach ( $colleges as $college ) {
						printf(
							'<option value="%d">%s</option>',
							esc_attr( $college->term_id ),
							esc_html( $college->name )
						);
					}
					?>
				</select>
				<input type="submit" id="dsu_update_program_urls" name="dsu_update_program_urls" class="button button-primary" value="Update Links">
				<span id="dsu_update_spinner" class="spinner" aria-hidden="true" style="float:none;margin:0 10px;"></span>
			</form>
			<div id="dsu_update_progress" style="margin-top:15px;"></div>
			<div id="dsu_update_result" style="margin-top:15px;"></div>
		</div>

		<!-- Delete All Programs Button -->
		<div class="dsu-section" style="padding-bottom: 30px; border-bottom: 1px solid #d4d4d4;">
			<h2>Delete All Programs</h2>
			<form method="post">
				<?php wp_nonce_field( 'dsu_delete_programs_action', 'dsu_delete_programs_nonce' ); ?>
				<input type="submit" name="dsu_delete_all_programs" class="button-secondary" value="Delete All Programs" onclick="return confirm('Are you sure you want to delete all programs? This action cannot be undone.');">
			</form>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', () => {
				const form = document.querySelector('#dsu_update_program_urls_form');
				if (!form) {
					return;
				}

				const spinner   = document.getElementById('dsu_update_spinner');
				const progress  = document.getElementById('dsu_update_progress');
				const result    = document.getElementById('dsu_update_result');
				const button    = document.getElementById('dsu_update_program_urls');
				const batchSize = 10;

				const stop = (html) => {
					spinner.classList.remove('is-active');
					button.disabled = false;
					progress.innerHTML = '';
					if (html) {
						result.innerHTML = html;
					}
				};

				const post = async (params) => {
					const res = await fetch(ajaxurl, {
						method: 'POST',
						body: new URLSearchParams(params),
					});
					return res.json();
				};

				form.addEventListener('submit', async (e) => {
					e.preventDefault();

					const category = form.querySelector('#college_select').value;
					if (!category) {
						return;
					}
					const nonce = form.querySelector('#dsu_update_program_urls_nonce').value;

					button.disabled = true;
					spinner.classList.add('is-active');
					result.innerHTML = '';
					progress.textContent = 'Preparing…';

					let start;
					try {
						start = await post({ action: 'dsu_start_url_update', nonce, url_category: category });
					} catch (err) {
						return stop('<div class="error"><p>Request failed. Please try again.</p></div>');
					}
					if (!start.success) {
						return stop('<div class="error"><p>' + (start.data?.message || 'Error.') + '</p></div>');
					}

					const batchId = start.data.batch_id;
					const total   = start.data.total;
					let offset    = 0;
					let last      = null;

					while (offset < total) {
						let batch;
						try {
							batch = await post({ action: 'dsu_process_url_batch', nonce, batch_id: batchId, offset });
						} catch (err) {
							return stop('<div class="error"><p>Batch failed at ' + offset + ' of ' + total + '.</p></div>');
						}
						if (!batch.success) {
							return stop('<div class="error"><p>' + (batch.data?.message || 'Error.') + '</p></div>');
						}

						last = batch.data;
						progress.textContent = 'Checked ' + last.processed + ' of ' + last.total + ' URLs…';
						offset += batchSize;
					}

					if (last) {
						stop(
							'<div class="updated"><p>' + last.group_name +
							' URLs checked &nbsp;-&nbsp; Updated: <strong>' + last.updated +
							'</strong> &nbsp;<strong>|</strong>&nbsp; Cleared: <strong>' + last.cleared +
							'</strong><br><br><a class="button button-secondary" href="' + last.report_url +
							'" download>Download URL Report (CSV)</a></p></div>'
						);
					} else {
						stop('');
					}
				});
			});
		</script>
	</div>
	<?php

	// Handle import CSV.
	if ( isset( $_POST['dsu_import_csv'] ) && check_admin_referer( 'dsu_import_csv_action', 'dsu_import_csv_nonce' ) ) {
		dsu_handle_csv_upload();
	}

	// Handle delete all programs.
	if ( isset( $_POST['dsu_delete_all_programs'] ) && check_admin_referer( 'dsu_delete_programs_action', 'dsu_delete_programs_nonce' ) ) {
		dsu_delete_all_programs();
	}
}

/**
 * Handle CSV file upload and process its data.
 *
 * @return void
 */
function dsu_handle_csv_upload() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'degree-search-utility' ) );
	}

	if ( ! isset( $_POST['dsu_import_csv_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dsu_import_csv_nonce'] ) ), 'dsu_import_csv_action' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'degree-search-utility' ) );
	}

	if ( ! isset( $_FILES['csv_file'] ) || ! isset( $_FILES['csv_file']['error'] ) || UPLOAD_ERR_OK !== (int) $_FILES['csv_file']['error'] || empty( $_FILES['csv_file']['tmp_name'] ) ) {
		echo '<div class="error"><p>Error uploading file.</p></div>';
		return;
	}

	// Sanitize the tmp_name value.
	$file     = sanitize_text_field( wp_unslash( $_FILES['csv_file']['tmp_name'] ) );
	$csv_data = array_map(
		function ( $line ) {
			return str_getcsv( $line, ',', '"', '\\' );
		},
		file( $file )
	);

	if ( empty( $csv_data ) ) {
		echo '<div class="error"><p>CSV file is empty.</p></div>';
		return;
	}

	// Process CSV Data.
	$header = array_map(
		function ( $h ) {
			// Trim spaces + remove UTF-8 BOM if present.
			return preg_replace( '/^\xEF\xBB\xBF/', '', trim( $h ) );
		},
		$csv_data[0]
	);
	unset( $csv_data[0] );

	$programs = array();

	foreach ( $csv_data as $row ) {
		$row         = array_map( 'trim', $row );
		$data        = array_combine( $header, $row );
		$program_key = $data['MajorName'] . ' - ' . $data['DegreeName'];

		// Split Areas into array .
		$data['AreasArray'] = explode( ' | ', $data['AreaName'] );

		// Group data by unique program .
		if ( ! isset( $programs[ $program_key ] ) ) {
			$programs[ $program_key ] = array(
				'MajorName'      => $data['MajorName'],
				'DegreeName'     => $data['DegreeName'],
				'Colleges'       => array( $data['CollegeName'] ),
				'Areas'          => $data['AreasArray'],
				'Concentrations' => array(),
			);
		}

		// Store unique colleges and areas .
		if ( ! in_array( $data['CollegeName'], $programs[ $program_key ]['Colleges'], true ) ) {
			$programs[ $program_key ]['Colleges'][] = $data['CollegeName'];
		}

		foreach ( $data['AreasArray'] as $area_name ) {
			if ( ! in_array( $area_name, $programs[ $program_key ]['Areas'], true ) ) {
				$programs[ $program_key ]['Areas'][] = $area_name;
			}
		}

		// if online and no concentration, add set concentration as 'none-online' or 'none' for sorting in dsu_create_program_post function .
		if ( empty( $data['ConcentrationName'] ) ) {
			if ( '1' === $data['Online Status'] ) {
				$programs[ $program_key ]['Concentrations'][ $data['MajorName'] . ' (Online)' ] = intval( $data['Online Status'] );

			} else {

				$programs[ $program_key ]['Concentrations']['none'] = intval( $data['Online Status'] );
			}
		} else {
			// Add ' (Online)' tag to named online concentrations for sorting .
			$concentration_name = '1' === $data['Online Status'] ? $data['ConcentrationName'] . ' (Online)' : $data['ConcentrationName'];
			// Store concentration with Online Status .
			$programs[ $program_key ]['Concentrations'][ $concentration_name ] = intval( $data['Online Status'] );
		}
	}

	// Insert each program .
	foreach ( $programs as $program_key => $program_data ) {
		dsu_create_program_post( $program_data );
	}

	echo '<div class="updated"><p>Programs imported successfully.</p></div>';
}

/**
 * Check a URL's HTTP status, retrying on 429 with backoff.
 * Does not follow redirects - 301 returned directly.
 *
 * @param string $url         URL to check.
 * @param int    $max_retries Max retry attempts on a 429.
 * @return int|WP_Error HTTP status code, or WP_Error on transport failure.
 */
function dsu_check_url( $url, $max_retries = 3 ) {
	$args = array(
		'timeout'     => 10,
		'redirection' => 0,
		'user-agent'  => 'DegreeSearchUtility/1.0; ' . home_url(),
	);

	$attempt = 0;

	do {
		$response = wp_remote_head( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 429 !== $code ) {
			return $code;
		}

		$retry_after = wp_remote_retrieve_header( $response, 'retry-after' );
		$wait        = is_numeric( $retry_after ) ? (int) $retry_after : ( 2 ** $attempt );
		sleep( min( $wait, 30 ) );

		++$attempt;

	} while ( $attempt < $max_retries );

	return 429;
}

/**
 * Build the list of program URL-check jobs for a given category.
 *
 * @param int|string $url_category College term ID or 'online'.
 * @return array|WP_Error Array with 'group_name' and 'jobs', or WP_Error.
 */
function dsu_build_program_url_jobs( $url_category ) {

	$online_concentrations = get_terms(
		array(
			'taxonomy'   => 'concentration',
			'hide_empty' => false,
			'meta_query' => array(
				array(
					'key'     => 'online',
					'value'   => '1',
					'compare' => '=',
				),
			),
		)
	);

	if ( is_wp_error( $online_concentrations ) ) {
		return new WP_Error( 'dsu_terms', 'Error retrieving online concentrations.' );
	}

	if ( 'online' === $url_category ) {
		$base_url   = 'https://volsonline.utk.edu';
		$group_name = 'Vols Online';

		if ( empty( $online_concentrations ) ) {
			return new WP_Error( 'dsu_no_online', 'No online programs found.' );
		}

		$programs = get_posts(
			array(
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'concentration',
						'field'    => 'term_id',
						'terms'    => wp_list_pluck( $online_concentrations, 'term_id' ),
					),
				),
			)
		);
	} else {
		$college_id = $url_category;
		$base_url   = get_field( 'college-url', 'college_' . $college_id );

		if ( empty( $base_url ) ) {
			return new WP_Error( 'dsu_no_url', 'Selected college does not have a homepage URL set.' );
		}

		$college_term = get_term( $college_id, 'college' );
		$group_name   = ( $college_term && ! is_wp_error( $college_term ) ) ? $college_term->name : 'Unknown College';

		$tax_query = array(
			array(
				'taxonomy' => 'college',
				'field'    => 'term_id',
				'terms'    => $college_id,
			),
		);

		if ( ! empty( $online_concentrations ) ) {
			$tax_query[] = array(
				'taxonomy' => 'concentration',
				'field'    => 'term_id',
				'terms'    => wp_list_pluck( $online_concentrations, 'term_id' ),
				'operator' => 'NOT IN',
			);
		}

		$programs = get_posts(
			array(
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'tax_query'      => $tax_query,
			)
		);
	}

	$jobs = array();

	foreach ( $programs as $program ) {
		$program_slug = sanitize_title( $program->post_title );
		$degree_terms = wp_get_post_terms( $program->ID, 'degree' );

		if ( empty( $degree_terms ) || is_wp_error( $degree_terms ) ) {
			continue;
		}

		foreach ( $degree_terms as $degree ) {
			$degree_slug = $degree->slug;
			$degree_type = get_field( 'degree_type', 'degree_' . $degree->term_id );

			if ( empty( $degree_type ) ) {
				continue;
			}

			$safe_degree_type = sanitize_title( $degree_type );

			if ( 'online' === $url_category ) {
				$parent_path = 'program';

				if ( str_contains( strtolower( $degree_type ), 'certificate' ) ) {
					$degree_type_slug = 'certificate';
				} elseif ( in_array( strtolower( $degree_slug[0] ), array( 'p', 'd', 'e', 'j' ), true ) ) {
					$degree_type_slug = 'doctoral';
				} elseif ( str_starts_with( strtolower( $degree_slug ), 'b' ) ) {
					$degree_type_slug = 'bachelors';
				} elseif ( str_starts_with( strtolower( $degree_slug ), 'm' ) ) {
					$degree_type_slug = 'masters';
				} else {
					continue;
				}
			} else {
				$parent_path = 'academics';

				if ( 'undergraduate' === $safe_degree_type ) {
					$degree_type_slug = 'undergraduate-programs';
				} elseif ( 'graduate' === $safe_degree_type ) {
					$degree_type_slug = 'graduate-programs';
				} elseif ( 'graduate-certificate' === $safe_degree_type ) {
					$degree_type_slug = 'graduate-certificates';
				} elseif ( 'undergraduate-certificate' === $safe_degree_type ) {
					$degree_type_slug = 'undergraduate-certificates';
				} else {
					continue;
				}
			}

			$url = trailingslashit( $base_url ) . $parent_path . '/' . $degree_type_slug . '/' . $program_slug . ( stripos( $degree_type_slug, 'certificate' ) === false ? '-' . $degree_slug : '' ) . '/';

			$jobs[] = array(
				'program_id' => $program->ID,
				'program'    => $program->post_title,
				'degree'     => $degree->name,
				'url'        => $url,
			);
		}
	}

	return array(
		'group_name' => $group_name,
		'jobs'       => $jobs,
	);
}

/**
 * AJAX: build the job list, prep the CSV, store batch state.
 */
function dsu_ajax_start_url_update() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	check_ajax_referer( 'dsu_update_program_urls_action', 'nonce' );

	$url_category = isset( $_POST['url_category'] ) ?
		( is_numeric( $_POST['url_category'] ) ?
			intval( $_POST['url_category'] ) :
			sanitize_title( wp_unslash( $_POST['url_category'] ) )
		) : 0;

	if ( ! $url_category ) {
		wp_send_json_error( array( 'message' => 'Please select a valid college.' ) );
	}

	$data = dsu_build_program_url_jobs( $url_category );

	if ( is_wp_error( $data ) ) {
		wp_send_json_error( array( 'message' => $data->get_error_message() ) );
	}

	if ( empty( $data['jobs'] ) ) {
		wp_send_json_error( array( 'message' => 'No programs found for the selected college.' ) );
	}

	$upload_dir = wp_upload_dir();
	$report_dir = trailingslashit( $upload_dir['basedir'] ) . 'degree-search-reports';
	wp_mkdir_p( $report_dir );

	$filename    = sprintf( 'program-url-report-%s.csv', current_time( 'Y-m-d-H-i-s' ) );
	$report_path = trailingslashit( $report_dir ) . $filename;
	$report_url  = trailingslashit( $upload_dir['baseurl'] ) . 'degree-search-reports/' . $filename;

	$csv = fopen( $report_path, 'w' );
	if ( $csv ) {
		fputcsv( $csv, array( 'Program', 'Degree', 'URL', 'HTTP Code', 'Result' ), ',', '"', '\\' );
		fclose( $csv );
	}

	$batch_id = 'dsu_url_batch_' . wp_generate_password( 12, false );
	set_transient(
		$batch_id,
		array(
			'jobs'        => $data['jobs'],
			'group_name'  => $data['group_name'],
			'report_path' => $report_path,
			'report_url'  => $report_url,
			'updated'     => 0,
			'cleared'     => 0,
		),
		HOUR_IN_SECONDS
	);

	wp_send_json_success(
		array(
			'batch_id' => $batch_id,
			'total'    => count( $data['jobs'] ),
		)
	);
}
add_action( 'wp_ajax_dsu_start_url_update', 'dsu_ajax_start_url_update' );

/**
 * AJAX: process URLs in batches.
 */
function dsu_ajax_process_url_batch() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}

	check_ajax_referer( 'dsu_update_program_urls_action', 'nonce' );

	$batch_id = isset( $_POST['batch_id'] ) ? sanitize_text_field( wp_unslash( $_POST['batch_id'] ) ) : '';
	$offset   = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$size     = 10;

	if ( 0 !== strpos( $batch_id, 'dsu_url_batch_' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid batch.' ) );
	}

	$state = get_transient( $batch_id );
	if ( false === $state ) {
		wp_send_json_error( array( 'message' => 'Batch expired. Please try again.' ) );
	}

	$total = count( $state['jobs'] );
	$slice = array_slice( $state['jobs'], $offset, $size );

	$csv = fopen( $state['report_path'], 'a' );

	foreach ( $slice as $job ) {
		usleep( 150000 ); // Pause between batches.

		$code = dsu_check_url( $job['url'] );

		if ( is_wp_error( $code ) ) {
			$http_code = 'ERROR';
			$result    = $code->get_error_message();
			update_field( 'program-url', '', $job['program_id'] );
			++$state['cleared'];
		} elseif ( in_array( $code, array( 200, 301 ), true ) ) {
			update_field( 'program-url', esc_url_raw( $job['url'] ), $job['program_id'] );
			$http_code = $code;
			$result    = 'Updated';
			++$state['updated'];
		} else {
			update_field( 'program-url', '', $job['program_id'] );
			$http_code = $code;
			$result    = 'Cleared';
			++$state['cleared'];
		}

		if ( $csv ) {
			fputcsv( $csv, array( $job['program'], $job['degree'], $job['url'], $http_code, $result ), ',', '"', '\\' );
		}
	}

	if ( $csv ) {
		fclose( $csv );
	}

	$processed = min( $offset + $size, $total );
	$done      = $processed >= $total;

	if ( $done ) {
		delete_transient( $batch_id );
	} else {
		set_transient( $batch_id, $state, HOUR_IN_SECONDS );
	}

	wp_send_json_success(
		array(
			'processed'  => $processed,
			'total'      => $total,
			'done'       => $done,
			'updated'    => $state['updated'],
			'cleared'    => $state['cleared'],
			'group_name' => $state['group_name'],
			'report_url' => $state['report_url'],
		)
	);
}
add_action( 'wp_ajax_dsu_process_url_batch', 'dsu_ajax_process_url_batch' );

/**
 * Delete all program posts and associated taxonomy terms.
 *
 * @return void
 */
function dsu_delete_all_programs() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'degree-search-utility' ) );
	}

	$programs = get_posts(
		array(
			'post_type'      => 'program',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);

	if ( empty( $programs ) ) {
		echo '<div class="updated"><p>No programs found to delete.</p></div>';
		return;
	}

	foreach ( $programs as $program ) {
		wp_delete_post( $program->ID, true );
	}

	// Delete all terms associated with programs.
	$taxonomies = array( 'degree', 'college', 'area', 'concentration' );
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
	}

	echo '<div class="updated"><p>All programs and associated data have been deleted.</p></div>';
}

/**
 * Create or update a program post and assign its taxonomies and fields.
 *
 * @param array $data Program data including majors, degrees, concentrations, colleges, and areas.
 * @return void
 */
function dsu_create_program_post( $data ) {

	// Decode HTML entities and sanitize input values.
	$program_name = sanitize_text_field( html_entity_decode( $data['MajorName'], ENT_QUOTES, 'UTF-8' ) );
	$degree_name  = sanitize_text_field( html_entity_decode( $data['DegreeName'], ENT_QUOTES, 'UTF-8' ) );

	if ( array_key_exists( 'none', $data['Concentrations'] ) ) {
		// If other concentrations exist for program, use program name instead of.
		if ( count( $data['Concentrations'] ) > 1 ) {
			$data['Concentrations'][ $program_name ] = $data['Concentrations']['none'];
			// If only an unnamed offline concentration exists, prepare data to skip adding the concentration.
		} else {
			$data['Concentrations'][''] = $data['Concentrations']['none'];
		}

		unset( $data['Concentrations']['none'] );
	}

	$degrees = array( $degree_name );

	// Sanitize and decode all concentration names.
	$concentrations = array();
	foreach ( $data['Concentrations'] as $name => $online_status ) {
		$clean_name                    = sanitize_text_field( html_entity_decode( $name, ENT_QUOTES, 'UTF-8' ) );
		$concentrations[ $clean_name ] = $online_status;
	}

	$concentrations_string = implode( ', ', array_keys( $concentrations ) );

	// Sanitize and decode colleges and areas.
	$colleges = array_map( fn( $c ) => sanitize_text_field( html_entity_decode( $c, ENT_QUOTES, 'UTF-8' ) ), $data['Colleges'] );
	$areas    = array_map( fn( $a ) => sanitize_text_field( html_entity_decode( $a, ENT_QUOTES, 'UTF-8' ) ), $data['Areas'] );

	// Determine degree type based on degree name.
	if ( strpos( $degree_name, 'B' ) === 0 ) {
		$degree_type = 'Undergraduate';
	} elseif ( in_array( $degree_name, array( 'C3', 'Undergraduate Certificate' ), true ) ) {
		$degree_type = 'Undergraduate Certificate';
	} elseif ( in_array( $degree_name, array( 'C4', 'Graduate Certificate' ), true ) ) {
		$degree_type = 'Graduate Certificate';
	} else {
		$degree_type = 'Graduate';
	}

	// Add comma-separated list of concentrations to post_content for searchability.
	$program_content = ! empty( $concentrations_string ) ? '<!-- wp:paragraph --><p>' . esc_html( $concentrations_string ) . '</p><!-- /wp:paragraph -->' : '';

	$post_id = wp_insert_post(
		array(
			'post_title'   => $program_name,
			'post_type'    => 'program',
			'post_status'  => 'publish',
			'post_content' => $program_content,
		)
	);

	if ( $post_id ) {
		// Assign taxonomies.
		dsu_assign_terms( $post_id, $degrees, 'degree' );
		dsu_assign_terms( $post_id, $colleges, 'college' );
		dsu_assign_terms( $post_id, $areas, 'area' );

		// Assign degree type as an ACF field to the degree taxonomy.
		$degree_term = term_exists( $degree_name, 'degree' );
		if ( $degree_term && ! is_wp_error( $degree_term ) ) {
			$term_id = $degree_term['term_id'] ?? $degree_term;
			update_field( 'degree_type', $degree_type, 'degree_' . $term_id );
		}

		// Assign concentrations and set online status.
		foreach ( $concentrations as $concentration_name => $online_status ) {
			$term = term_exists( $concentration_name, 'concentration' );
			if ( ! $term ) {
				$term = wp_insert_term( $concentration_name, 'concentration' );
			}
			if ( ! is_wp_error( $term ) ) {
				$term_id = $term['term_id'] ?? $term;
				wp_set_object_terms( $post_id, $concentration_name, 'concentration', true );
				update_field( 'online', $online_status, 'concentration_' . $term_id );
			}
		}
	}
}

/**
 * Assign taxonomy terms to a program post.
 *
 * @param int    $post_id  Post ID.
 * @param array  $terms    Terms to assign.
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function dsu_assign_terms( $post_id, $terms, $taxonomy ) {
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( ! term_exists( $term, $taxonomy ) ) {
				wp_insert_term( $term, $taxonomy );
			}
		}
		wp_set_object_terms( $post_id, $terms, $taxonomy );
	}
}
