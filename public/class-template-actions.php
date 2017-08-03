<?php
namespace WP_Vote;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template Loader
 *
 * @class          WC_Template
 * @version        2.2.0
 * @package        WooCommerce/Classes
 * @category       Class
 * @author         WooThemes
 */
class Template_Actions {
	private static $wp_vote_token;

	/**
	 * Hook in methods.
	 */
	public function __construct() {
		add_filter( 'wp-vote_is_allowed_to_vote', array( __CLASS__, 'is_allowed_to_vote' ) );
		add_filter( 'wp-vote_is_allowed_to_see_question', array( __CLASS__, 'is_allowed_to_see_question' ) );


		add_action( 'template_redirect', array( __CLASS__, 'is_ballot_and_is_allowed_to_see_question' ) );

		add_action( 'wp_vote_before_main_content', array( __CLASS__, 'wp_vote_before_main_content' ) );
		add_action( 'wp_vote_after_main_content', array( __CLASS__, 'wp_vote_after_main_content' ) );

		// ballot single
		add_action( 'wp-vote_before_single_ballot_loop', array( __CLASS__, 'wp_vote_ballot_loop_item_title' ) );
		add_action( 'wp-vote_before_single_ballot_loop', array( __CLASS__, 'show_message' ) );
		add_action( 'wp-vote_before_single_ballot_loop', array( __CLASS__, 'show_current_voter' ) );
		add_action( 'wp-vote_before_single_ballot_loop', array( __CLASS__, 'show_ballot_status' ) );
		add_action( 'wp-vote_before_single_ballot_loop', array( __CLASS__, 'wp_vote_question_form_open' ) );
		add_action( 'wp-vote_after_single_ballot_loop', array( __CLASS__, 'wp_vote_question_form_close' ), 50 );


		// ballot archive
		add_action( 'wp_vote_before_ballots_loop', array( __CLASS__, 'show_archive_title' ), 20 );
		add_action( 'wp_vote_before_ballots_loop', array( __CLASS__, 'show_archive_description' ), 30 );

		//	add_action( 'wp_vote_before_ballot_loop_item_title', array( __CLASS__, 'wp_vote_template_loop_product_link_open' ), 30 );
		add_action( 'wp_vote_ballot_loop_item_title', array( __CLASS__, 'wp_vote_ballot_loop_item_title' ) );
		//	add_action( 'wp_vote_after_ballot_loop_item_title', array( __CLASS__, 'wp_vote_template_loop_product_link_close' ), 30 );

		add_action( 'wp-vote_after_ballots_loop', array( __CLASS__, 'pagination' ), 30 );
	}

	//TODO: move somewhere else and add logic
	public static function is_allowed_to_vote() {

		if ( self::is_preview() ) {
			return true;
		}

		$ballot_id = self::get_ballot_id();


		$token = self::get_token();

		$foreign_hash = $token['token'];
		$rep_id       = $token['rep_id'];
		$voter_id     = $token['voter_id'];

		$salt = Ballot::get_salt( $ballot_id );

		// no salt post not live
		if ( false === $salt ) {

			return false;
		}


		$valid_token = Ballot::validate_token( $foreign_hash, $ballot_id, $voter_id, $rep_id );

		if ( ! $valid_token ) {

			return false;
		}

		$eligible_voters = Ballot::get_voters( $ballot_id );

		if ( ! empty( $eligible_voters ) && array_key_exists( $voter_id, $eligible_voters ) ) {
			// if we don't have a voted key they can vote
			if ( ! array_key_exists( 'voted', $eligible_voters[ $voter_id ] ) ) {

				return true;
			} else {

				return 'used';
			}
		}

		return false;
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param bool $state
	 *
	 * @return bool|mixed|void
	 */
	public static function is_allowed_to_see_question( $state = false ) {
		if ( self::is_preview() ) {

			return true;
		}

		if ( ! self::get_token() ) {
			global $wp_query;
			header( 'HTTP/1.0 404 Not Found - Archive Empty' );
			$wp_query->set_404();
			require TEMPLATEPATH . '/404.php';
			exit;
		}

		$can_vote = self::is_allowed_to_vote();
		if ( false === $can_vote ) {
			global $wp_query;
			header( 'HTTP/1.0 404 Not Found - Archive Empty' );
			$wp_query->set_404();
			require TEMPLATEPATH . '/404.php';
			exit;
		}

		global $wp_query;

		return apply_filters( 'wp-vote_ballot_is_public', $can_vote, $wp_query->queried_object_id );
	}

	public static function is_ballot_and_is_allowed_to_see_question() {

		if ( ! is_admin() && get_post_type() === Ballot::POST_TYPE ) {
			self::is_allowed_to_see_question();
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return bool
	 */
	private static function is_preview() {

		if ( isset( $_GET['preview_id'] )
		     && isset( $_GET['preview_nonce'] )
		     && wp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . (int) $_GET['preview_id'] )
		) {
			return true;
		} elseif ( isset( $_GET['preview'] ) && 'true' === $_GET['preview'] ) {

			return true;
		}

		return false;
	}

	/**
	 *
	 *
	 * @static
	 * @return int
	 */
	private static function get_ballot_id() {
		if ( is_admin() ) {
			global $post;
			return $post->ID;
		} else {
			global $wp_query;
			return $wp_query->post->ID;
		}
	}


	public static function get_token() {

		if ( null === self::$wp_vote_token ) {
			global $wp_query;
			if ( array_key_exists( 'wp_vote_token', $wp_query->query_vars ) ) {

				$wp_vote_token = explode( '/', $wp_query->query_vars['wp_vote_token'] );

				if ( isset( $wp_vote_token[0] ) ) {
					self::$wp_vote_token['voter_id'] = $wp_vote_token[0];
				}

				if ( isset( $wp_vote_token[1] ) ) {
					self::$wp_vote_token['rep_id'] = $wp_vote_token[1];
				}

				if ( isset( $wp_vote_token[2] ) ) {
					self::$wp_vote_token['token'] = $wp_vote_token[2];
				}
			} elseif ( isset( $_REQUEST['token'] ) && isset( $_REQUEST['rep_id'] ) && isset( $_REQUEST['voter_id'] ) ) {

				if ( isset( $_REQUEST['voter_id'] ) ) {
					self::$wp_vote_token['voter_id'] = absint( $_REQUEST['voter_id'] );
				}

				if ( isset( $_REQUEST['rep_id'] ) ) {
					self::$wp_vote_token['rep_id'] = absint( $_REQUEST['rep_id'] );
				}

				if ( isset( $_REQUEST['token'] ) ) {
					self::$wp_vote_token['token'] = sanitize_text_field( $_REQUEST['token'] );
				}
			} else {
				self::$wp_vote_token = false;
			}
		}

		return self::$wp_vote_token;
	}

	public static function wp_vote_ballot_loop_item_title() {
		echo '<h2>' . get_the_title() . '</h2>';
	}

	public static function wp_vote_before_main_content() {
		echo '<div id="content" class="site-content" role="main">';
	}

	public static function wp_vote_after_main_content() {
		echo '</div>';
	}

	/**
	 *
	 *
	 * @static
	 */
	public static function show_message() {
		if ( ! isset( $_GET['state'] ) ) {
			return;
		}

		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );

		switch ( $state ) {
			case 'failed':
				$text = apply_filters( 'wp-vote_state_failed_message', __( 'We fail to save your votes. Check that you set all the votes', 'wp-vote' ), $state );
				printf( '<div class="error" >%s</div>', esc_html( $text ) );
				break;
			case 'closed':
				$text = apply_filters( 'wp-vote_state_closed_message', __( 'This Ballot is now closed. We did not save your votes.', 'wp-vote' ), $state );
				printf( '<div class="error" >%s</div>', esc_html( $text ) );
				break;
			case 'success':
				$text = apply_filters( 'wp-vote_state_success_message', __( 'Saved. Thank you for your vote.', 'wp-vote' ), $state );
				printf( '<div class="success" >%s</div>', esc_html( $text ) );
				break;
		}
	}

	/**
	 *
	 *
	 * @static
	 */
	public static function show_current_voter() {

		if ( self::is_preview() ) {
			return false;
		}
		$ballot_id = Ballot::get_ballot_id();
		$voters    = get_post_meta( $ballot_id, Ballot::get_prefix( 'voters' ), true );

		$voter_types = Voter::get_voter_types();
		$token       = self::get_token();
		$voter_class = $voter_types[ $voters[ $token['voter_id'] ]['voter_type'] ]['class'];
		$voter       = new $voter_class( $voters[ $token['voter_id'] ] );

		$rep_name = $voter->get_rep_name( $token['rep_id'] );

//		$user     = get_the_title( $rep_id );
//		$company  = get_the_title( $voter_id );

//		printf( '<span><strong>%s</strong> %s <strong>%s</strong></span>',
//			esc_html( $rep_name ),
//			esc_html__( 'is voting for' ),
//			esc_html( $voter->get_title() )
//		);
	}


	public static function show_ballot_status() {

		if ( self::is_preview() ) {
			return false;
		}

		$ballot_id = Ballot::get_ballot_id();
		$voters    = get_post_meta( $ballot_id, Ballot::get_prefix( 'voters' ), true );
		$token     = self::get_token();

		$voter_types = Voter::get_voter_types();
		$voter_class = $voter_types[ $voters[ $token['voter_id'] ]['voter_type'] ]['class'];
		$voter       = new $voter_class( $voters[ $token['voter_id'] ] );
		$rep_name    = $voter->get_rep_name( $token['rep_id'] );


		if ( isset( $voters[ $token['voter_id'] ]['voted'] ) ) {
			// Voter has already voted

			if ( $voters[ $token['voter_id'] ]['voted']['rep'] === $token['rep_id'] ) {
				// We voted
				if( isset( $_GET['state'] ) ) {
					printf( '<div class="success" > %s </div>',
						esc_html__( apply_filters( Ballot::get_prefix( 'has_just_voted_message' ), __( 'Here are the votes you made and we have emailed you a copy.', 'wp-vote' ) ) )
					);
				} else{
					printf( '<div class="error" > %s <strong>%s</strong></div>',
						esc_html__( apply_filters( Ballot::get_prefix( 'has_voted_message' ), __( 'You have already voted for', 'wp-vote' ) ) ),
						esc_html( $voter->get_title() )
					);
				}


			} else {
				// Another rep voted
				printf( '<div class="success" ><strong>%s</strong> %s <strong>%s</strong></div>',
					esc_html( $voter->get_rep_name( $voters[ $token['voter_id'] ]['voted']['rep'] ) ),
					esc_html__( apply_filters( Ballot::get_prefix( 'has_voted_on_behalf_message' ), __( 'has already voted on behalf of', 'wp-vote' ) ) ),
					esc_html( $voter->get_title() )
				);

			}
		} else {

			if ( Ballot::STATUS_CLOSED !== Ballot::get_ballot_status( $ballot_id ) ) {
				// We haven't voted.

				printf( '<span><strong>%s</strong> %s <strong>%s</strong></span>',
					esc_html( $rep_name ),
					esc_html__( apply_filters( Ballot::get_prefix( 'is_voting_for_message' ), __( 'is voting for', 'wp-vote' ) ) ),
					esc_html( $voter->get_title() )
				);
			} else {
				printf( '<span><strong>%s </strong>%s.</span>',
				esc_html( apply_filters( Ballot::get_prefix( 'voted_closed_and_failed_to_vote_message' ), __( 'Welcome', 'wp-vote' ) ) ),
					esc_html( $rep_name )
				);
			}


		}

//		$user     = get_the_title( $rep_id );
//		$company  = get_the_title( $voter_id );


	}

//	/**
//	 * Insert the opening anchor tag for ballots in the loop.
//	 */
//	public static function wp_vote_template_loop_product_link_open() {
//		echo '<a href="' . get_the_permalink() . '">';
//	}
//
//	/**
//	 * Insert the opening anchor tag for ballots in the loop.
//	 */
//	public static function wp_vote_template_loop_product_link_close() {
//		echo '</a>';
//	}

	public static function show_archive_title() {
		$title_text = Addon_Plugins::get_option_value( 'title_text' );
		if ( ! empty( $title_text ) ) {
			printf(
				apply_filters( 'wp-vote_archive_title_template', '<h2>%s</h2>' ), esc_html( $title_text )
			);
		}
	}

	public static function show_archive_description() {
		$achive_description = Addon_Plugins::get_option_value( 'achive_description' );
		if ( ! empty( $achive_description ) ) {
			printf(
				apply_filters( 'wp-vote_archive_title_template', '<div>%s</div>' ),
				apply_filters( 'the_content', $achive_description )
			);
		}
	}

	/**
	 * Output the pagination.
	 *
	 * @subpackage    Loop
	 */
	public static function pagination() {
		Template_Loader::get_template_part( 'pagination.php' );
	}

	public static function wp_vote_question_form_open() {
		global $post;

		$is_allowed_to_vote = apply_filters( 'wp-vote_is_allowed_to_vote', false );
		if ( true === $is_allowed_to_vote && ! self::is_preview() ) {
			printf( '<form id="wp-vote-questions" action="" method="post" >' );
			wp_nonce_field( 'submit_ballot_' . self::$wp_vote_token['token'], WP_Vote::_get_prefix( 'ballot' ) );
		} else {
			printf( '<div id="wp-vote-questions">' );
		}

	}

	public static function wp_vote_question_form_close() {
		$is_allowed_to_vote = apply_filters( 'wp-vote_is_allowed_to_vote', false );

		if ( true === $is_allowed_to_vote && Ballot::STATUS_CLOSED !== Ballot::get_ballot_status( self::get_ballot_id() ) ) {
			if ( self::is_preview() ) {
				printf( '<button id="wp-vote_submit"  type="submit" disabled="disabled">%s</button>', __( 'Submit disabled in preview', 'wp-vote' ) );
				printf( '</div>' );
			} else {
				printf( '<button id="wp-vote_submit" type="submit">%s</button>', __( 'Submit Ballot', 'wp-vote' ) );
				echo( '</form>' );
			}

		} else {
			printf( '</div>' );
		}
	}


}

