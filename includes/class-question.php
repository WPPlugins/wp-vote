<?php
/**
 * wp-vote.
 * User: Paul
 * Date: 2016-01-18
 *
 * The text and type of a question
 *
 */

namespace WP_Vote;


if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Question
 * @package WP_Vote
 */
class Question {

	/**
	 * @var int
	 */
	private static $question_count = 0;

	/**
	 * Question constructor.
	 */
	public function __construct() {


	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $question
	 */
	public static function show_question( $question_index, $question ) {

		$is_allowed_to_vote     = apply_filters( 'wp-vote_is_allowed_to_vote', false );
		$answers                = ( isset( $question[Ballot::get_prefix( 'question_answers' )] ) ) ? $question[Ballot::get_prefix( 'question_answers' )] : '';
		$question_title         = ( isset( $question[Ballot::get_prefix( 'question_title' )] ) ) ? $question[Ballot::get_prefix( 'question_title' )] : '';
		$question_description   = ( isset( $question[Ballot::get_prefix( 'question_description' )] ) ) ? $question[Ballot::get_prefix( 'question_description' )] : '';

		if ( empty( $answers ) ) {
			$type = self::convert_slug_to_question_class( $question[Ballot::get_prefix( 'question_type' )] );
			$answers = $type::get_questions();
		}

		if ( Ballot::STATUS_CLOSED !== Ballot::get_ballot_status( Ballot::get_ballot_id() ) ) {
			$question_html = ( true === $is_allowed_to_vote ) ? self::answer_options( $question_index, $answers ) : self::answer_stats( $question_index, $answers );
		} else {
			$question_html = ( true === $is_allowed_to_vote ) ? apply_filters( 'did_not_vote_question_message', __( 'No vote recorded.', 'wp-vote' ) ) : self::answer_stats( $question_index, $answers );
		}

		printf(
			apply_filters( 'show_question_html_template', '<li class="clearfix"><span>%s</span><h4>%s</h4><div>%s</div></li>' ),
			$question_html,
			esc_html( $question_title ),
			apply_filters( 'the_content', $question_description )
		);
	}

	private static function convert_slug_to_question_class( $slug ) {

		$class_name = str_replace( '-', ' ', $slug );
		$class_name = ucwords( $class_name );
		$class_name = str_replace( ' ', '_', $class_name );

		return '\\WP_Vote\\' . $class_name . '_Question';
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $question_index
	 * @param $answers
	 *
	 * @return mixed|string|void
	 */
	private static function answer_options( $question_index, $answers ) {


		if ( empty( $answers ) ) {
			return esc_html__( 'No questions set', 'wp-vote' );
		}
		$question_count = self::$question_count;

		if ( ! is_array( $answers ) ) {
			$text_answers = explode( PHP_EOL, $answers );
			$answers = array();
			foreach ( $text_answers as $text_answer ) {
				$name_label_pair = explode( ' : ', $text_answer );

				$key = $name_label_pair[0];
				$label = ( 1 === count( $name_label_pair ) ) ? $name_label_pair[0] : $name_label_pair[1];

				$answers[$key] = $label;
			}
		}

		$answer_options = array();
		$label_id = 1;
		foreach ( $answers as $key => $answer ) {
			if ( is_array( $answer ) ) {
				$answer_options[] = self::create_checkbox( $question_index, $key, $answer['label'], $label_id . '-' . $question_count );
			} else {
				$answer_options[] = self::create_checkbox( $question_index, $key, $answer, $label_id . '-' . $question_count );
			}

			$label_id ++;
		}
		self::$question_count ++;

		return apply_filters( 'wp-vote_answer_options_html', implode( ' ', $answer_options ) );
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $question_index
	 * @param $key
	 * @param $option
	 * @param $label_id
	 *
	 * @return mixed|void
	 */
	private static function create_checkbox( $question_index, $key, $option, $label_id ) {
		return apply_filters( 'wp-vote_questions_create_checkbox',
			sprintf( '<input required="required" type="radio" id="%1$s_id" name="%3$s" value="%2$s"><label for="%1$s_id">%4$s</label>',
				esc_attr( $label_id ),
				esc_attr( $key ),
				Ballot::get_prefix( get_the_ID() . '_' . $question_index ),
				esc_html( $option )
			)
		);
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param int $question_index
	 * @param array $answers
	 *
	 * @return string
	 */
	private static function answer_stats( $question_index, $answers ) {
		if ( is_admin() ) {

			if ( false === $answers || empty( $answers ) ) {
				return apply_filters( 'wp-vote_no_stats_message', __( 'no stats', 'wp-vote' ) );
			}

			$html = '';
			$is_first = true;

			foreach ( $answers as $answer ) {
				$html .= sprintf( '%s<strong>%s:</strong> %d',
					( $is_first ) ? '' : ', ',
					esc_html( $answer['label'] ),
					esc_html( $answer['count'] )
				);
				$is_first = false;
			}

			return $html;
		} else {
			if ( ! apply_filters( 'wp-vote_show_stats', true ) ) {
				return apply_filters( 'wp-vote_recorded_message', __( 'Vote recorded', 'wp-vote' ) );
			}
			$token = Template_Actions::get_token();

			$ballot_voters = get_post_meta( get_the_ID(), 'wp-vote-ballot_voters', true );

			$vote = ( isset( $ballot_voters[$token['voter_id']]['votes'][$question_index] ) ) ? $ballot_voters[$token['voter_id']]['votes'][$question_index] : '';

			if ( '' !== $vote ) {

				return sprintf( '<strong>%s</strong>', esc_html( $vote ) );
			} else {

				return sprintf( '<strong>%s</strong>', esc_html__( apply_filters( 'wp-vote_no_stats_message', __( 'no stats', 'wp-vote' ) ) ) );
			}
		}

	}
}