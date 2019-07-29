<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Trakr_Activator {
	/**
	 * Plugin installation
	 * perform installation task when the plugin is activated.
	 *
	 * @since    1.0
	 */
	public function activate() {
    global $trakr_version;
    add_option('trakr_version', $trakr_version);
    flush_rewrite_rules();
	}
}
