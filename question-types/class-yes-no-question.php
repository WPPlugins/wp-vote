<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}


class Yes_No_Question extends Abstract_Question_Object {

	/**
	 * Class
	 */
	protected static $slug;
	protected static $label;
	protected static $fields;
	protected static $answers;

	public static function init() {

		self::$slug    = 'yes-no';
		self::$label   = __( 'Yes/No', 'wp-vote' );
		self::$fields  = array( 'question_title', 'question_description' );
		self::$answers = apply_filters( 'wp-vote_answer_options_' . self::$slug, array(
			'yes' => __( 'Yes', 'wp-vote' ),
			'no'  => __( 'No', 'wp-vote' ),
		) );

		parent::init();

	}

	public static function render_meta_fields() {

//		$cmb = new_cmb2_box( array(
//			'id'           => Voter::get_prefix( 'voter_details_metabox' ),
//			'title'        => __( 'Voter Details', 'wp_vote' ),
//			'object_types' => array( Voter::get_post_type() ), // Post type
//			'context'      => 'normal',
//			'priority'     => 'high',
//			'show_names'   => true, // Show field names on the left
//		) );
//
//		$cmb->add_field( array(
//			'name'       => __( 'Email Address', 'wp-vote' ),
//			'desc'       => '',
//			'id'         => Voter::get_prefix( 'email_address' ),
//			'type'       => 'text_email',
//			'show_names' => true, // Show field names on the left
//			'attributes' => array(
//				'required' => 'required',
//			)
//		) );

	}


	/**
	 * Instance
	 */
	protected $data;

	public function __construct( $args ) {

	}

}
