<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trakr
 * @subpackage Trakr/admin
 */

 CONST TRAKR_BASE_URL = 'https://app.trakr.tech/';
 CONST TRAKR_API_PROJECT = TRAKR_BASE_URL . 'api/v1/project/';
 CONST TRAKR_API_DELETE_PROJECT = TRAKR_BASE_URL . 'api/v1/delete/project/';

class Trakr_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Adding the menu page for our main form
	 */
	public function trakr_project() {
		add_menu_page(
			'Trakr',
			'Trakr',
			'manage_options',
			'trakr',
			[&$this, 'trakr_project_html']
		);
	}

	/**
	 * Printing out our project form
	 */
	public function trakr_project_html() {

		// Checking user permissions
		if( current_user_can( 'manage_options' ) ) {
			// Generate our unique form token
			$wp_nonce = wp_create_nonce( 'trakr_settings_form_nonce' );

			// Setting the default option
	    $settings = get_option('trakr_settings');
	    $default_token = '';
	    if (!empty($settings)) {
	      $default_token = $settings['api_token'];
	    }

			// Getting the existing linked project information
			$trakr_project = get_option('trakr_project', []);

			// When we load this page, we try to make sure local option values are not
			// orphaned (e.g. if someone deleted the project remotely). Otherwise we
			// clean it up
			if (!empty($trakr_project)) {
				$response = wp_remote_get($trakr_project['link']);
				if (wp_remote_retrieve_response_code($response) != 403) {
						delete_option('trakr_project');
						$trakr_project = [];
				}
			}
			include_once 'trakr_project_html.php';
		} else {
			print __("You are not authorized to perform this operation.", $this->plugin_name);
		}
	}

	/**
	 * Our own form submission handler
	 */
	public function trakr_project_form_submit() {
		// Making sure the form nonce is set
		if( isset( $_POST['trakr_settings_form_nonce'] )
		&& wp_verify_nonce( $_POST['trakr_settings_form_nonce'], 'trakr_settings_form_nonce')
		) {
			// Handling our primary submission processing of saving the option
			$admin_notice = 'save';
			$api_token = sanitize_text_field($_POST['trakr_settings']['api_token']);
			$debug_payload = [];
			$message = '';
			if (array_key_exists('submit', $_POST)) {
				$settings = [
					'api_token' => $api_token,
				];
				$admin_notice = 'saved';
				update_option('trakr_settings', $settings);
				$debug_payload = $_POST;
			}

			// Handling our secondary submission processing of syncing the project
			if (array_key_exists('link_project', $_POST)) {
				// Perform project syncing
				list($response, $response_body) = $this->syncProject($api_token);
				if (!is_array($response)) {
					$admin_notice = 'failed';
				} else {
					$admin_notice = ($response_body['status'] == 'error') ? 'failed' : 'linked';
	        $message = ($admin_notice == 'failed') ? $response_body['message'] : $response_body['result']['message'];
					$debug_payload = $response_body;
				}
			}

			// Handling our secondary submission processing of deleting the project
			if (array_key_exists('delete_project', $_POST)) {
				list($response, $response_body) = $this->deleteProject($api_token);
				if (!is_array($response)) {
					$admin_notice = 'failed';
				} else {
					$admin_notice = ($response_body['status'] == 'error') ? 'failed' : 'linked';
					$message = ($admin_notice == 'failed') ? $response_body['message'] : $response_body['result'];
					$debug_payload = $response;
				}
			}

			// Perform the redirect
			$this->custom_redirect( $admin_notice, $message, False, $debug_payload);
			exit;
		}
		else {
			wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), [
						'response' 	=> 403,
						'back_link' => 'admin.php?page=' . $this->plugin_name,
				] );
		}
	}

	/**
	 * Delete a project from Trakr
	 * @param $api_token | The user API token from Trakr
	 */
	public function deleteProject($api_token) {
		// In case we disabled the api token field from the form
		// We get it from option
		if (empty($api_token)) {
			$settings = get_option('trakr_settings');
	    if (!empty($settings)) {
	      $api_token = $settings['api_token'];
	    }
		}

		$trakr_project = get_option('trakr_project', []);
		if (empty($trakr_project)) {
			return;
		}
		// Extracting the project id from the info
		$pieces = explode('/', $trakr_project['link']);
		$project_id = end($pieces);

		$options = [
			'headers' => [
				'x-api-key' => $api_token,
				'Content-type' => 'text/plain',
			],
    ];

		$response = wp_remote_post(TRAKR_API_DELETE_PROJECT . $project_id, $options);

		$response_body = wp_remote_retrieve_body($response);
		$response_body = json_decode($response_body, True);

		// API response successful, let's delete the local option
		if ($response_body['status'] != 'error') {
			delete_option('trakr_project');
		}

		return [$response, $response_body];
	}

	/**
	 * Perform the API call to link the project
	 * @param $api_token | The api token associated with Trakr
	 */
	public function syncProject($api_token) {
		$project_info = [
			'notification' => get_option('admin_email'),
			'name' => get_option('blogname'),
		];

		$project_api_params = [
      'production' => [
        'url' => get_option('home'),
      ],
      'uris' => [
        '/',
      ],
      'scan_url' => 1,
      'title' => $project_info['name'],
      'schedule' => [
        'interval' => 1,
        'comparison' => 1,
      ],
      'notification' => [
        'emails' => [
          $project_info['notification'],
        ],
      ],
    ];

		$options = [
			'headers' => [
				'x-api-key' => $api_token,
				'Content-type' => 'text/plain',
			],
      'body' => json_encode($project_api_params),
    ];

    $response = wp_remote_post(TRAKR_API_PROJECT, $options);

		$response_body = wp_remote_retrieve_body($response);
		$response_body = json_decode($response_body, True);

		if ($response_body['status'] != 'error') {
			$project_info['link'] = TRAKR_BASE_URL . 'node/' . $response_body['result']['id'];
			update_option('trakr_project', $project_info);
		}

		return [$response, $response_body];
	}

	/**
	 * Redirect back to the main form with the corresponding notices
	 * @param $admin_notice | success | fail or other code to display
	 * @param $debug | Whether we want to debug the response
	 * @param $payload | The payload data to be displayed if debug is true
	 */
	public function custom_redirect( $admin_notice, $message = '', $debug = False, $payload = []) {
		$args['trakr_form_add_notice'] = $admin_notice;
		if ($message != '') {
				$args['message'] = $message;
		}
		if ($debug) {
			$args['trakr_response'] = $payload;
		}
		wp_redirect(esc_url_raw(add_query_arg($args, admin_url('admin.php?page='. $this->plugin_name ))));
	}

	/**
	 * Print message upon the form submission
	 */
	public function form_submission_notice() {
	  if ( !isset( $_REQUEST['trakr_form_add_notice'] ) ) {
			return;
		}
		$html = '';
		switch($_REQUEST['trakr_form_add_notice']) {
			case 'saved':
				$html =	'<div class="notice notice-success is-dismissible">
							<p><strong>The configuration has been updated</strong></p><br />
							</div>';
			break;
			case 'linked':
				$html =	'<div class="notice notice-success is-dismissible"><p><strong>';
					if (isset($_REQUEST['message'])) {
						$html .= esc_html($_REQUEST['message']);
					}
				$html .= '</strong></p><br /></div>';
			break;
			case 'failed':
			$html =	'<div class="notice notice-error is-dismissible"><p><strong>';
				if (isset($_REQUEST['message'])) {
					$html .= esc_html($_REQUEST['message']);
				} else {
					$html .= 'An error has occured';
				}
			$html .= '</strong></p><br /></div>';
			break;
		}

		// Display debug_payload
		if (isset($_REQUEST['trakr_response'])) {
			$html .= '<pre>' . htmlspecialchars( print_r( $_REQUEST['trakr_response'], True) ) . '</pre>';
		}
		print $html;
	}
}
