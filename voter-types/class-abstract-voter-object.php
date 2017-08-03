<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}

interface Voter_Object_Interface {

	public static function init();

	// Admin UI
	public static function render_meta_fields();

	// Actions
	public function send_email_notification( $ballot_id, $ballot_status );

}

abstract class Abstract_Voter_Object implements Voter_Object_Interface {

	/**
	 * Class variables and static functions
	 */

	/**
	 * @var string Unique identifier for this voter, lowercase letters and hyphens only
	 */
	protected static $slug;

	/**
	 * @var string Human-readable label for use in dropdown fields and labels, supports translation
	 */
	protected static $label;

	/**
	 * @var string Set automatically via get_called_class() when voter type is registered
	 */
	protected static $class;

	/**
	 * @var string Define which fields are required. Used during import.
	 */
	protected static $required_fields;

	/**
	 * Returns the slug for the voter type
	 * @return string
	 */
	public static function get_slug() {
		return static::$slug;
	}

	/**
	 * Create compound slugs by combining the voter type slug with a provided string
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
	 * Initializes the voter type
	 * @return bool
	 */
	public static function init() {
		if ( empty( static::$slug ) || empty( static::$label ) ) {
			trigger_error(
				sprintf( __( 'self::$slug and self::$label must be set in %s::init() before calling parent::init() to initialize WP Vote voter type.', 'wp-vote' ), get_called_class() ),
				E_USER_WARNING
			);

			return false;
		}

		static::$class = get_called_class();

//		if ( get_called_class() instanceof Abstract_Voter_Object ) {
//
//		}
		add_filter( 'wp-vote_register_voter_types', array( get_called_class(), 'register_voter_type_hook' ) );
		add_filter( 'enter_title_here', array( get_called_class(), 'enter_title_here_hook' ) );

		return true;
	}

	/**
	 * Hook for registering the voter type
	 *
	 * @param array $voter_types
	 *
	 * @return array
	 */
	public static function register_voter_type_hook( $voter_types ) {

		if ( ! empty( static::$slug ) ) {
			$voter_types[ static::$slug ] = array(
				'class' => get_called_class(),
				'label' => static::$label,
			);
		}

		return $voter_types;
	}


	public static function import_voter( $header, $data ) {

		$postarr = array(
			'ID'           => $data[ array_search( 'ID', $header ) ],
			'post_title'   => $data[ array_search( 'title', $header ) ],
			'post_content' => '',
			'post_type'    => Voter::get_post_type(),
			'post_status'  => 'publish',
		);

		$voter_meta = self::filter_import_data( $header, $data );

		if ( ! self::validate_required_fields( $voter_meta ) ) {
			return false;
		}

		if ( ! empty( $voter_meta ) ) {
			$postarr['meta_input'] = $voter_meta;
		}


		$existing_post = get_post( $postarr['ID'] );
		if ( ! empty( $postarr['ID'] ) && $existing_post && $existing_post->post_type == Voter::get_post_type() ) {
			$post_id = wp_update_post( $postarr );
		} else {
			unset( $postarr['ID']);
			$post_id = wp_insert_post( $postarr );
		}
		unset( $postarr );

		return $post_id;

	}

	public static function filter_import_data( $header, $data ) {

		$voter_type  = $data[ array_search( 'voter_type', $header ) ];
		$voter_types = Voter::get_voter_types();
		$voter_class = $voter_types[ $voter_type ]['class'];

		$filtered_data = array(
			Voter::get_prefix( 'voter_type' ) => $voter_type,
		);

		foreach ( $header as $col_name ) {
			if ( stristr( $col_name, static::get_prefix() ) ) {
				$filtered_data[ $col_name ] = $data[ array_search( $col_name, $header ) ];
			}
		}

		return $filtered_data;

	}

	public static function validate_required_fields( $voter_meta ) {

		if ( is_array( static::$required_fields ) ) {
			foreach ( static::$required_fields as $required_field ) {
				if ( empty( $voter_meta[ static::get_prefix( $required_field ) ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	public static function get_details_for_ballot() {

		$format = array(
			'ID'    => 'ID',
			'title' => 'post_title',
			'reps'  => array(
				'rep_email' => array(
					'email' => 'email',
					'...'   => '...',
				),
			),
		);
	}


	public static function enter_title_here_hook( $text ) {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( Voter::get_post_type() ) ) ) {
			return $text;
		}
	}


	/**
	 * Instance variables and methods
	 */
	protected $data;

	protected $ID;

	protected $title;

	protected $reps;

	protected $post;

	protected $meta;

	public function __construct( $args ) {

		$this->data = $args;

		// Create Voter from existing post ID
		if ( ! is_array( $args ) ) {
			$voter_post = get_post( $args );
			if ( $voter_post ) {
				$this->ID    = $args;
				$this->post  = $voter_post;
				$this->meta  = get_post_meta( $args );
				$this->title = $this->post->post_title;
				$this->reps  = array();

			}
		} else {
			// Build the voter object from voter meta stored in ballot ($ID, $title and $reps)
			$this->ID    = $args['ID'];
			$this->title = $args['title'];
			$this->reps  = $args['reps'];
		}

	}

	public function get_meta_for_ballot() {
		return null;
	}

	public function send_email_notification( $ballot_id, $ballot_status ) {

		foreach ( $this->reps as $rep_index => $rep ) {
			$rep_email = $rep['email'];
			$rep_name  = ( isset( $rep['name'] ) ) ? $rep['name'] : '';
			Voter::email_representative( $this->ID, $rep_index, $rep_email, $rep_name, $ballot_id, $ballot_status );
		}

	}


	/**
	 * Control how we identify a representative (name, email, ...?)
	 */
	public function get_rep_name( $rep_id = false ) {
		return false;
	}

	/**
	 * Control how we identify a representative (name, email, ...?)
	 */
	public function get_title() {
		return $this->title;
	}


}