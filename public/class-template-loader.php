<?php
namespace WP_Vote;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template Loader
 *
 * @class 		WC_Template
 * @version		2.2.0
 * @package		WooCommerce/Classes
 * @category	Class
 * @author 		WooThemes
 */
class Template_Loader {

	private static $dd;

	private static $template_dir;

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		self::$template_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'public/templates/';

		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. woocommerce looks for theme.
	 * overrides in /theme/wp-vote/ by default.
	 *
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function template_loader( $template ) {
		$find = array();
		$file = '';
		if ( is_single() && get_post_type() === Ballot::POST_TYPE ) {
			add_action( 'wp_enqueue_scripts', array( 'WP_Vote\WP_Vote_Public', 'enqueue_styles' ) );

			$file 	= 'single-ballot.php';
			$find[] = $file;
			$find[] = self::template_path() . $file;

		} elseif ( is_post_type_archive( Ballot::get_post_type() ) ) {
			add_action( 'wp_enqueue_scripts', array( 'WP_Vote\WP_Vote_Public', 'enqueue_styles' ) );

			$file 	= 'archive-ballot.php';
			$find[] = $file;
			$find[] = self::template_path() . $file;

		}

		if ( $file ) {
			$template       = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = self::$template_dir . $file;
			}
		}

		return $template;
	}

//	public static function enqueure_styles() {
//
//			wp_enqueue_style( 'wp-vote', wp_unslash( Config::get_plugin_url() ) . '/css/wp-vote.css' );
//	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public static function template_path() {
		return apply_filters( 'wp_vote_template_path', 'wp-vote/' );
	}


	/**
	 * Get template part (for templates like the shop-loop).
	 *
	 *
	 * @access public
	 * @param mixed $slug
	 * @param string $name (default: '')
	 */
	public static function get_template_part( $slug, $name = '' ) {
		$template = '';

		// Look in yourtheme/slug-name.php and yourtheme/wp-vote/slug-name.php
		if ( $name  ) {
			$template = locate_template( array( "{$slug}-{$name}.php", self::template_path() . "{$slug}-{$name}.php" ) );
		}

		// Get default slug-name.php
		if ( ! $template && $name && file_exists( self::$template_dir . "{$slug}-{$name}.php" ) ) {
			$template = self::$template_dir . "{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", get_template_directory() . "{$slug}.php" ) );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'wp-vote_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}

