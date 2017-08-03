<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://bearne.ca
 * @since      1.0.0
 *
 * @package    WP_Vote
 * @subpackage WP_Vote/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Vote
 * @subpackage WP_Vote/admin
 * @author     Paul Bearne, Peter Toi <paul@bearne.ca>
 */
namespace WP_Vote;

class WP_Vote_Admin {

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Vote_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Vote_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( WP_Vote::get_plugin_name(), plugin_dir_url( __FILE__ ) . 'css/wp-vote-admin.css', array(), WP_Vote::get_version(), 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Vote_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Vote_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( WP_Vote::get_plugin_name(), plugin_dir_url( __FILE__ ) . 'js/wp-vote-admin.js', array( 'jquery', 'wp-ajax-response' ), WP_Vote::get_version(), false );

//		$scripts->localize( 'wp-ajax-response', 'wpAjax', array(
//			'noPerm' => __('You do not have permission to do that.'),
//			'broken' => __('An unidentified error has occurred.')
//		) );


		//wp_localize_script( WP_Vote::get_plugin_name(),  'questions_fields', Abstract_Question_Object::get_meta_fields_for_types() );
		$voter_types = Voter::get_voter_types();
		foreach( $voter_types as $voter_type ) {
			$voter_type = $voter_type;
		}

	}

	public static function admin_menu() {
//		add_menu_page(
//			__( 'WP Vote Settings', 'wp-vote' ),
//			__( 'WP Vote', 'wp-vote' ),
//			'edit_posts',
//			'wp-vote',
//			array( __CLASS__, 'options_page' )
//		);

	}

	public static function options_page() {

	}

}
