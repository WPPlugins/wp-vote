<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wp-vote.com
 * @since             1.0.0
 * @package           WP_Vote
 *
 * @wordpress-plugin
 * Plugin Name:       WP Vote
 * Plugin URI:        http://wp-vote.com
 * Description:       Manages voting on your site via email invites.
 * Version:           1.0.1
 * Author:            Paul Bearne, Peter Toi
 * Author URI:        http://wp-vote.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-vote
 * Domain Path:       /languages
 */

namespace WP_Vote;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP_VOTE_BASE_FILE_PATH', plugin_dir_path( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-vote-activator.php
 */
function activate_wp_vote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-vote-activator.php';
	WP_Vote_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-vote-deactivator.php
 */
function deactivate_wp_vote() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-vote-deactivator.php';
	WP_Vote_Deactivator::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate_wp_vote' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate_wp_vote' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once 'autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_vote() {

	$plugin = new WP_Vote();
	$plugin->run();

}

run_wp_vote();
