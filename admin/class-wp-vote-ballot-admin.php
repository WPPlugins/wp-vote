<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Wp_Vote_Ballot_Admin
 * @package WP_Vote
 */
class Wp_Vote_Ballot_Admin {

	/**
	 * Wp_Vote_Ballot_Admin constructor.
	 */
	public function __construct() {
		add_filter( 'title_save_pre' , array( __CLASS__, 'title_save_pre' ) );
	}


	/**
	 * @param $title
	 *
	 * @return mixed
	 */
	public static function title_save_pre( $title ){

		if ( '' == $title ) {
			Admin_Notices::display_error( __( 'Title Requeired', 'wp-vote' ) );
			wp_safe_redirect( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			die();
		}

		return $title;
	}
}
