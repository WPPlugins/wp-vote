<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}


class For_Against_Abstain_Question extends Abstract_Question_Object {

	/**
	 * Class
	 */
	protected static $slug;
	protected static $label;
	protected static $fields;
	protected static $answers;

	public static function init() {

		self::$slug    = 'for-against-abstain';
		self::$label   = __( 'For/Against/Abstain', 'wp-vote' );
		self::$fields  = array( 'question_title', 'question_description' );
		self::$answers = apply_filters( 'wp-vote_answer_options_' . self::$slug, array(
			'for'     => __( 'For', 'wp-vote' ),
			'against' => __( 'Against', 'wp-vote' ),
			'abstain' => __( 'Abstain', 'wp-vote' ),
		) );

		parent::init();

	}

	public static function render_meta_fields() {


	}


	/**
	 * Instance
	 */
	protected $data;

	public function __construct( $args ) {

	}

}
