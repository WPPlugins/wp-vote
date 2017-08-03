<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}

interface Question_Object_Interface {

	public static function init();

	// Admin UI
	//public static function render_meta_fields();


}

abstract class Abstract_Question_Object implements Question_Object_Interface {

	protected static $slug;
	protected static $label;
	protected static $fields;
	protected static $answers;

	/**
	 * Returns the slug for the voter type
	 * @return string
	 */
	public static function get_slug() {

		return static::$slug;
	}

	/**
	 * Create compound slugs by combining the question type slug with a provided string
	 *
	 * @param string $append Provided string to append to voter type slug (run through sanitize_title)
	 *
	 * @return string
	 */
	public static function get_prefix( $append = '' ) {

		return static::get_slug() . '_' . sanitize_title( $append );
	}

	/**
	 * Same as get_prefix( $append ) with the addition of a leading '_'
	 * Used for creating hidden post meta that won't show up in the Custom Fields meta box
	 *
	 * @param string $append
	 *
	 * @return string
	 */
	public static function _get_prefix( $append = '' ) {

		return '_' . get_prefix( $append );
	}

	/**
	 * Initializes the question type
	 * @return bool
	 */
	public static function init() {

		if ( empty( static::$slug ) || empty( static::$label ) || ! isset( static::$fields ) || ! isset( static::$answers ) ) {
			trigger_error(
				sprintf( __( 'self::$slug and self::$label must be set in %s::init() before calling parent::init() to initialize WP Vote question type.', 'wp-vote' ), get_called_class() ),
				E_USER_WARNING
			);

			return false;
		}

		add_filter( 'wp-vote_register_question_types', array( get_called_class(), 'register_question_type_hook' ) );

		return true;
	}

	/**
	 * Hook for registering the question type
	 *
	 * @param array $question_types
	 *
	 * @return array
	 */
	public static function register_question_type_hook( $question_types ) {

		if ( ! empty( static::$slug ) ) {
			$question_types[ static::$slug ] = array(
				'class'   => get_called_class(),
				'label'   => static::$label,
				'answers' => static::$answers,
				'fields'  => static::$fields,
			);
		}

		return $question_types;
	}

	public static function get_fields() {

		return static::$fields;

	}

	public static function get_questions() {

		return static::$answers;

	}

	/**
	 * Instance variables and methods
	 */
	protected $data;

	public function __construct( $args ) {

		$this->data = $args;

	}

}