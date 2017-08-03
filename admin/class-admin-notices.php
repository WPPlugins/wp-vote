<?php
/**
 * Created by IntelliJ IDEA.
 * User: pbearne
 * Date: 2016-05-04
 * Time: 8:41 PM
 */

namespace wp_vote;


/**
 * Class Admin_Notices
 * @package wp_vote
 */
class Admin_Notices {
	/**
	 * @var
	 */
	private static $instance;
	/**
	 *
	 */
	private static $notice_field;

	/**
	 * Admin_Notices constructor.
	 */
	public function __construct() {
		self::get_notice_fields();
	}

	/**
	 *
	 */
	private function __clone() {
	}


	/**
	 * @return string
	 */
	private static function get_notice_fields() {
		if ( null === self::$notice_field ) {
			self::$notice_field = WP_Vote::get_prefix( 'admin_notice_message' );
		}

		return self::$notice_field;
	}

	/**
	 *
	 */
	public static function display_admin_notice() {

		$option = get_option( self::get_notice_fields() );
		$message      = isset( $option['message'] ) ? $option['message'] : false;
		$notice_level = ! empty( $option['notice-level'] ) ? $option['notice-level'] : 'notice-error';

		if ( $message ) {
			printf( '<div class="notice %s is-dismissible"><p>%s</p></div>', wp_kses_post( $notice_level ), esc_html( $message ) );
			delete_option( self::get_notice_fields() );
		}
	}

	/**
	 * @param $message
	 */
	public static function display_error( $message ) {
		self::update_option( $message, 'notice-error' );
	}

	/**
	 * @param $message
	 */
	public static function display_warning( $message ) {
		self::update_option( $message, 'notice-warning' );
	}

	/**
	 * @param $message
	 */
	public static function display_info( $message ) {
		self::update_option( $message, 'notice-info' );
	}

	/**
	 * @param $message
	 */
	public static function display_success( $message ) {
		self::update_option( $message, 'notice-success' );
	}

	/**
	 * @param $message
	 * @param $notice_level
	 */
	protected static function update_option( $message, $notice_level ) {

		update_option( self::get_notice_fields(),
			array(
				'message'      => wp_kses_post( $message ),
				'notice-level' => esc_attr( $notice_level ),
			)
		);
	}
}
