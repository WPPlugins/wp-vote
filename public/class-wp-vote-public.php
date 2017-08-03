<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://bearne.ca
 * @since      1.0.0
 *
 * @package    WP_Vote
 * @subpackage WP_Vote/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Vote
 * @subpackage WP_Vote/public
 * @author     Paul Bearne, Peter Toi <paul@bearne.ca>
 */
namespace WP_Vote;

class WP_Vote_Public {

	public static $post_token = 'wp_vote_token';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {


		$this->template_action = new Template_Actions();
		$this->template_loader = new Template_Loader();

		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'init', array( __CLASS__, 'rewrites_init' ), 10, 0 );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	static public function enqueue_styles() {

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

		wp_enqueue_style( WP_Vote::get_plugin_name(), plugin_dir_url( __FILE__ ) . 'css/wp-vote-public.css', array(), WP_Vote::get_version(), 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	static public function enqueue_scripts() {

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

		wp_enqueue_script( WP_Vote::get_plugin_name(), plugin_dir_url( __FILE__ ) . 'js/wp-vote-public.js', array( 'jquery' ), WP_Vote::get_version(), false );

	}


	public static function add_query_vars( $a_vars ) {
		$a_vars[] = self::$post_token;

		return $a_vars;
	}

	public static function rewrites_init() {
		add_rewrite_tag( '%' . self::$post_token . '%', '([^&]+)' );

		add_rewrite_rule(
			'^' . Ballot::get_slug() . '/([^/]+)/(.*)?',
			'index.php?' . Ballot::get_post_type() . '=$matches[1]&' . self::$post_token . '=$matches[2]',
			'top'
		);
		
	}
}
