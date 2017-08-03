<?php
/**
 * wp-vote.
 * User: Paul
 * Date: 2016-05-01
 *
 */

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Ballot_Ajax extends Ballot {

	public function __construct() {

		add_action( 'wp_ajax_email_ballot_to_individual', array( __CLASS__, 'email_ballot_to_individual_callback' ) );
		add_action( 'wp_ajax_email_ballot_to_all_voters', array( __CLASS__, 'email_ballot_to_all_voters_callback' ) );
		add_action( 'wp_ajax_show-individual-votes', array( __CLASS__, 'show_individual_votes_callback' ) );
	}

	public static function email_ballot_to_individual_callback() {

		// Setup the response meta
		$response = array(
			'what'   => 'ballot_action',
			'action' => 'email_ballot_to_individual',
		);

		// Strip referer URL for query variables in an attempt to isolate ballot ID
		$referer_args = array();
		parse_str( parse_url( $_SERVER["HTTP_REFERER"], PHP_URL_QUERY ), $referer_args );

		// Bail if we didn't get a ballot id
		if ( ! isset( $referer_args['post'] ) ) {
			$response['id']   = new \WP_Error( 'ballot-id-missing-error', __( 'Ballot ID missing.', 'wp-vote' ) );
			$response['data'] = __( 'Ballot ID missing.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();

		}

		// Try to get the ballot
		$ballot_id = $referer_args['post'];
		$ballot    = get_post( $ballot_id );

		// Bail if there isn't a ballot with that ID
		if ( ! $ballot ) {
			$response['id']   = new \WP_Error( 'ballot-missing-error', __( 'No ballot with that ID.', 'wp-vote' ) );
			$response['data'] = __( 'No ballot with that ID.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();
		}

		// Try to get the voter

		// Bail if we don't have any eligible voters
		if ( ! isset ( $_POST['voter_id'] ) || empty( $_POST['voter_id'] ) ) {
			$response['id']   = new \WP_Error( 'ballot-email-error', __( 'Voter ID missing.', 'wp-vote' ) );
			$response['data'] = __( 'No eligible voters.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();
		}

		$voter_id = $_POST['voter_id'];


		// Try to get the eligible voter list
		$eligible_voters = Ballot::get_voters( $ballot_id );

		// Bail if we don't have any eligible voters
		if ( empty( $eligible_voters ) || ! array_key_exists( $voter_id, $eligible_voters ) ) {
			$response['id']   = new \WP_Error( 'ballot-email-error', __( 'No eligible voters.', 'wp-vote' ) );
			$response['data'] = __( 'No eligible voters.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();
		}


		// Things are looking good! Let's email the voter.
		$voter_types = Voter::get_voter_types();

		$voter_class = $voter_types[ $eligible_voters[ $voter_id ]['voter_type'] ]['class'];

		$voter = new $voter_class( $eligible_voters[ $voter_id ] );

		add_filter( 'wp_mail_content_type', array( __NAMESPACE__ . '\\Settings', 'set_content_type' ) );
		add_filter( 'wp_mail_from', array( __NAMESPACE__ . '\\Settings', 'custom_wp_mail_from' ), 99 );
		add_filter( 'wp_mail_from_name', array( __NAMESPACE__ . '\\Settings', 'wp_mail_from_name' ), 99 );

		$email_status = $voter->send_email_notification( $ballot_id, Ballot::STATUS_REMINDER );

		remove_filter( 'wp_mail_from_name', array( __NAMESPACE__ . '\\Settings', 'wp_mail_from_name' ), 99 );
		remove_filter( 'wp_mail_from', array( __NAMESPACE__ . '\\Settings', 'custom_wp_mail_from' ), 99 );
		remove_filter( 'wp_mail_content_type', array( __NAMESPACE__ . '\\Settings', 'set_content_type' ) );

		$response['id']   = $email_status;
		$response['data'] = __( 'Successfully emailed voter.', 'wp-vote' );
		$xmlResponse      = new \WP_Ajax_Response( $response );
		$xmlResponse->send();

	}


	public static function email_ballot_to_all_voters_callback() {
		// Setup the response meta
		$response = array(
			'what'   => 'ballot_action',
			'action' => 'email_ballot_to_all_voters',
		);

		// Strip referer URL for query variables in an attempt to isolate ballot ID
		$referer_args = array();
		parse_str( parse_url( $_SERVER["HTTP_REFERER"], PHP_URL_QUERY ), $referer_args );

		// Bail if we didn't get a ballot id
		if ( ! isset( $referer_args['post'] ) ) {
			$response['id']   = new \WP_Error( 'ballot-id-missing-error', __( 'Ballot ID missing.', 'wp-vote' ) );
			$response['data'] = __( 'Ballot ID missing.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();

		}

		// Try to get the ballot
		$ballot_id = $referer_args['post'];
		$ballot    = get_post( $ballot_id );

		// Bail if there isn't a ballot with that ID
		if ( ! $ballot ) {
			$response['id']   = new \WP_Error( 'ballot-missing-error', __( 'No ballot with that ID.', 'wp-vote' ) );
			$response['data'] = __( 'No ballot with that ID.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();
		}

		// Try to get the eligible voter list
		$eligible_voters = Ballot::get_voters( $ballot_id );

		// Bail if we don't have any eligible voters
		if ( empty( $eligible_voters ) ) {
			$response['id']   = new \WP_Error( 'ballot-email-error', __( 'No eligible voters.', 'wp-vote' ) );
			$response['data'] = __( 'No eligible voters.', 'wp-vote' );
			$xmlResponse      = new \WP_Ajax_Response( $response );
			$xmlResponse->send();
		}

		$voter_types = Voter::get_voter_types();
		// Things are looking good! Let's email the voter.
		foreach ( $eligible_voters as $eligible_voter ) {
			if ( ! isset( $eligible_voter['voted'] ) ) {
				$voter = new $voter_types[ $eligible_voter['voter_type'] ]['class']( $eligible_voter );

				add_filter( 'wp_mail_content_type', array( __NAMESPACE__ . '\\Settings', 'set_content_type' ) );
				add_filter( 'wp_mail_from', array( __NAMESPACE__ . '\\Settings', 'custom_wp_mail_from' ), 99 );
				add_filter( 'wp_mail_from_name', array( __NAMESPACE__ . '\\Settings', 'wp_mail_from_name' ), 99 );

				$email_status = $voter->send_email_notification( $ballot_id, Ballot::STATUS_REMINDER );

				remove_filter( 'wp_mail_from_name', array( __NAMESPACE__ . '\\Settings', 'wp_mail_from_name' ), 99 );
				remove_filter( 'wp_mail_from', array( __NAMESPACE__ . '\\Settings', 'custom_wp_mail_from' ), 99 );
				remove_filter( 'wp_mail_content_type', array( __NAMESPACE__ . '\\Settings', 'set_content_type' ) );

			}
		}

		$response['id']   = $email_status;
		$response['data'] = __( 'Successfully emailed voters.', 'wp-vote' );
		$xmlResponse      = new \WP_Ajax_Response( $response );
		$xmlResponse->send();

	}

	public static function show_individual_votes_callback() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			//var_dump( $_POST['voter_id'] );
			$voter_id  = absint( $_POST['voter_id'] );
			$ballot_id = absint( $_POST['ballot_id'] );
			Ballot::display_voters_stats( $ballot_id, $voter_id );
			die();
		}


	}

}