<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}


class Yes_No_Abstain_Question extends Abstract_Question_Object {

	/**
	 * Class
	 */
	protected static $slug;
	protected static $label;
	protected static $fields;
	protected static $answers;

	public static function init() {

		self::$slug    = 'yes-no-abstain';
		self::$label   = __( 'Yes/No/Abstain', 'wp-vote' );
		self::$fields  = array( 'question_title', 'question_description' );
		self::$answers = apply_filters( 'wp-vote_answer_options_' . self::$slug, array(
			'yes'     => __( 'Yes', 'wp-vote' ),
			'no'      => __( 'No', 'wp-vote' ),
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
