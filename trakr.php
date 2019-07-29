<?php
/*
Plugin Name: Trakr, automated visual testing and monitoring
Description: This plugin allows you to link your Wordpress website to Trakr to perform visual monitoring
Author: Trakr
Version: 1.0
Author URI: http://www.trakr.tech
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

global $trakr_version;
$trakr_version = '1.0';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function trakr_activate() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-trakr-activator.php';
  $activator = new Trakr_Activator();
  $activator->activate();
}

/**
 * check for Trakr version and perform updates accordingly
 */
function trakr_update_check() {
  global $trakr_version;
  if (get_site_option('trakr_version') != $trakr_version) {
    // update for specific versions as needed
    update_option( "trakr_version", $trakr_version);
  }
}
add_action( 'plugins_loaded', 'trakr_update_check' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function trakr_deactivate() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-trakr-deactivator.php';
  Trakr_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'trakr_activate' );
register_deactivation_hook( __FILE__, 'trakr_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-trakr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function trakr_run() {
  $plugin = new Trakr();
  $plugin->run();
}

trakr_run();
