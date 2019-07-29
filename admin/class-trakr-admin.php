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
   * Adding admin page
   */
  public function admin_page() {
    add_submenu_page(
      'options-general.php',
      'Trakr Configuration',
      'Trakr',
      'manage_options',
      'trakr',
      [&$this, 'admin_page_html']
    );
  }

	public function trakr_project() {
		add_menu_page(
			'Trakr project',
			'Trakr project',
			'manage_options',
			'trakr',
			[&$this, 'trakr_project_html']
		);
	}

	public function trakr_project_html() {
		print '<h1>hi</h1>';
	}

  /**
   * Custom setting for Trakr
   */
  public function settings_init() {
    // register a new setting for "trakr" page
    register_setting( 'trakr', 'trakr_settings', [&$this, 'trakr_settings_validate']);

    add_settings_field(
      'trakr_field_token',
      __( 'API token', 'trakr' ),
      [&$this, 'trakr_field_api_token'],
      'trakr',
      'default',
      ['label_for' => 'trakr_field_token',]
    );

  }

  /**
   * Validate API token entered by the user
   * @param $input
   * @return mixed
   */
  public function trakr_settings_validate($input) {
    // $options = [
    //   'method' => 'POST',
    //   'body' => ['token' => $input['api_token']]
    // ];
    // $response = wp_remote_get(LNDR_API_VALIDATE_TOKEN, $options);
    // if (wp_remote_retrieve_response_code($response) != '200') {
    //   $message = __('You have entered an invalid API token, please copy and paste the API token from your profile in Lndr', 'lndr');
    //   add_settings_error(
    //     'API token',
    //     esc_attr('settings_updated'),
    //     $message,
    //     'error'
    //   );
    // }
    return $input;
  }

  /**
   * Admin page html
   */
  public function admin_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

    // show error/update messages
    settings_errors( 'trakr_messages' );

    print '<div class="wrap">';
    print '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
    print '<form action="options.php" method="post">';
    // output security fields for the registered setting "trakr"
    settings_fields('trakr');
    do_settings_fields('trakr', 'default');

    submit_button( 'Save configuration' );
    print '</form>';
    print'</div>';
  }

	/**
	 * Our own custom submission handler
	 */
	public function trakr_settings_form_submit() {
		print_r($_POST);
		$this->custom_redirect('success', $_POST );
		exit;
	}

  /**
   * Render the setting field for API token
   * @param $args
   */
  public function trakr_field_api_token($args) {
    // get the value of the setting we've registered with register_setting()
    $settings = get_option('trakr_settings');
    // output the field

    $default = '';
    if (!empty($settings)) {
      $default = $settings['api_token'];
    }
    $output = '<div><input type="text" name="trakr_settings[api_token]" value="' . $default . '" required="true" size="50" /></div>';
    print $output;
  }
}
