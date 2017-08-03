<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}


class Voter extends Abstract_Post_Type {

	const SLUG = 'voter';

	const POST_TYPE = 'wp-vote-voter';

	public static $voter_type;

	public static $voter_types;

	public static function plugins_loaded() {

		parent::plugins_loaded();

		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'cmb2_admin_init', array( __CLASS__, 'cmb2_admin_init_hook' ) );

		add_action( 'admin_head', array( __CLASS__, 'hide_minor_publishing_actions' ) );
		add_action( 'admin_head', array( __CLASS__, 'hide_misc_publishing_actions' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'disable_quick_edit' ) );
		//add_filter( 'enter_title_here', array( __CLASS__, 'enter_title_here_hook' ) );


		add_filter( 'post_updated_messages', array( __CLASS__, 'post_published_message' ) );
	}

	public static function init() {
		self::$voter_types = apply_filters( 'wp-vote_register_voter_types', self::$voter_types );
		add_filter( 'gettext', array( __CLASS__, 'filter_published_on' ), 10000, 2 );
	}

	public static function register_custom_post_type() {
		$labels = array(
			'name'               => _x( 'Voter', 'post type general name', 'wp_vote' ),
			'singular_name'      => _x( 'Voter', 'post type singular name', 'wp_vote' ),
			'menu_name'          => _x( 'Voters', 'admin menu', 'wp_vote' ),
			'name_admin_bar'     => _x( 'Voter', 'add new on admin bar', 'wp_vote' ),
			'add_new'            => _x( 'Add New', 'Voter', 'wp_vote' ),
			'add_new_item'       => __( 'Add New Voter', 'wp_vote' ),
			'new_item'           => __( 'New Voter', 'wp_vote' ),
			'edit_item'          => __( 'Edit Voter', 'wp_vote' ),
			'view_item'          => __( 'View Voter', 'wp_vote' ),
			'all_items'          => __( 'Voters', 'wp_vote' ),
			'search_items'       => __( 'Search Voters', 'wp_vote' ),
			'parent_item_colon'  => __( 'Parent Voters:', 'wp_vote' ),
			'not_found'          => __( 'No voters found.', 'wp_vote' ),
			'not_found_in_trash' => __( 'No voters found in Trash.', 'wp_vote' ),
		);

		$current_user    = wp_get_current_user();
		$roles           = $current_user->roles;
		$allowed_roles   = Settings::get_option_value( 'allowed_roles' );
		$allowed_roles[] = 'administrator';

		$authorized = (bool) array_intersect( $roles, $allowed_roles );

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp_vote' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => $authorized,
			'show_in_menu'       => ( $authorized ) ? WP_Vote::get_plugin_name() : false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => self::get_slug() ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 50,
			'supports'           => array( 'title' ),
		);

		register_post_type( self::get_post_type(), $args );
	}

	public static function enter_title_here_hook( $text ) {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( self::get_post_type() ) ) ) {

			return self::call_voter_type_func( 'enter_title_here_hook' );
			//return __( 'Enter voter name here', 'wp-vote' );
		}
	}


	public static function cmb2_admin_init_hook() {

		$cmb_voter_type = new_cmb2_box( array(
			'id'           => self::get_prefix( 'type_metabox' ),
			'title'        => __( 'Voter Type', 'wp_vote' ),
			'object_types' => array( self::get_post_type() ), // Post type
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb_voter_type->add_field( array(
			'name'             => __( 'Voter Type', 'wp-vote' ),
			'desc'             => __( 'Select a voter type.', 'wp-vote' ),
			'id'               => self::get_prefix( 'voter_type' ),
			'type'             => 'select',
			'show_option_none' => false,
			'before'           => array( __CLASS__, 'voter_type_spinner' ),
			'default'          => self::get_voter_type(),
			'options'          => self::get_voter_types_for_select(),
			'escape_cb'        => array( __CLASS__, 'change_selected_voter_type' ),
		) );

		if ( array_key_exists( self::get_voter_type(), self::$voter_types ) ) {
			$voter_type_meta_func = join( '::', array(
				self::$voter_types[ self::get_voter_type() ]['class'],
				'render_meta_fields',
			) );

		} else {
			Admin_Notices::display_error( __( 'Voter was found with an deactivated voter type. Select a new type', 'wp-vote' ) );
			$voter_type_meta_func = join( '::', array(
				reset( self::$voter_types )['class'],
				'render_meta_fields',
			) );
		}

		if ( is_callable( $voter_type_meta_func ) ) {
			call_user_func( $voter_type_meta_func );
		}
	}

	public static function change_selected_voter_type( $value, $field_args, $field ) {

		return self::get_voter_type();

	}


	public static function hide_minor_publishing_actions() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( self::get_post_type() ) ) ) {
			echo '<style>#misc-publishing-actions { display: none; }</style>';
		}
	}

	public static function hide_misc_publishing_actions() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( self::get_post_type() ) ) ) {
			echo '<style>#minor-publishing-actions { display: none; }</style>';
		}
	}

	public static function disable_quick_edit( $actions ) {
		global $post;
		if ( $post->post_type == self::get_post_type() ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	public static function get_voter_type() {

		if ( empty( self::get_voter_types() ) ) {
			return false;
		}

		// First grab the voter type from the meta
		if ( ! empty( $_REQUEST['post'] ) ) {
			self::$voter_type = get_post_meta( $_REQUEST['post'], self::get_prefix( 'voter_type' ), true );
		}

		// Override meta if we have a $_GET variable (we've changed voter types on an existing voter)
		if ( ! empty( $_GET['voter_type'] ) ) {
			self::$voter_type = $_GET['voter_type'];
		}

		// Override again if we have a $_POST variable (we're saving a voter)
		if ( ! empty( $_POST[ self::get_prefix( 'voter_type' ) ] ) ) {
			self::$voter_type = $_POST[ self::get_prefix( 'voter_type' ) ];
		}

		return ( self::$voter_type ) ?: key( self::get_voter_types() );

	}

	public static function get_voter_types() {

		return apply_filters( 'wp-vote_get_voter_types', self::$voter_types );

	}

	public static function voter_type_spinner() {
		return '<span id="wp-vote-voter_voter_type_spinner" class="spinner"></span>';
	}

	public static function get_voter_types_for_select() {
		$voter_types = self::get_voter_types();
		$options     = array();
		if ( ! empty( $voter_types ) ) {
			foreach ( $voter_types as $slug => $data ) {
				$options[ $slug ] = $data['label'];
			}
		}

		return $options;
	}


	/**
	 * @param        $voter_id
	 * @param        $rep_index
	 * @param        $rep_email
	 * @param        $rep_name
	 * @param        $ballot_id
	 * @param string $ballot_status
	 * @param bool $echo
	 *
	 * @return bool|mixed
	 */
	public static function email_representative( $voter_id, $rep_index, $rep_email, $rep_name, $ballot_id, $ballot_status = 'open', $echo = false ) {

		$ballot = get_post( $ballot_id );

		$url = Ballot::get_representative_url( $ballot_id, $voter_id, $rep_index );

		$subject = $ballot->post_title;

		$options = get_option( '_options' );
		$message = ( isset( $options[ 'email_template_' . $ballot_status ] ) ) ? wpautop( $options[ 'email_template_' . $ballot_status ] ) : false;

		if ( false === $message ) {
			$message = Settings::default_email_templates( $ballot_status );
		}

		$link    = sprintf( '<a href="%s">%s</a>', esc_url_raw( $url ), apply_filters( 'wp-vote_ballot_link_text', __( 'Vote Now', 'wp-vote' ) ) );
		$message = str_replace( '{ballot_link}', $link, $message );

		$message = str_replace( '{rep_name}', $rep_name, $message );

		$message = str_replace( '{ballot_title}', $subject, $message );

		//representative_voter_id
		$voter_name = get_the_title( $voter_id );
		$message    = str_replace( '{voter_name}', $voter_name, $message );

		if ( strpos( $message, '{voted_name}' ) ) {

			$voters        = get_post_meta( $ballot_id, Ballot::get_prefix( 'voters' ), true );
			$current_voter = $voters[ $voter_id ];
			$voted_id      = $current_voter['voted']['rep'];
			$name          = $current_voter['reps'][ $voted_id ]['name'];

			$message = str_replace( '{voted_name}', $name, $message );
		}

		if ( strpos( $message, '{vote_record}' ) ) {
			$vote_record = sprintf(
				apply_filters( 'display_voters_stats_html_template', '<div id="wp-vote-questions">%s</div>' ),
				Ballot::display_voters_stats( $ballot_id, $voter_id, false )
			);
			$vote_record = apply_filters( 'wp-vote-email-representative-vote_rocord', $vote_record );
			$message     = str_replace( '{vote_record}', $vote_record, $message );
		}


		$bccs    = explode( ',', Settings::get_option_value( WP_Vote::get_prefix( 'bcc_email' ) ) );
		$headers = array();
		if ( $bccs && Ballot::STATUS_VOTED === $ballot_status ) {
			foreach ( $bccs as $bcc ) {
				$headers[] = "Bcc: {$bcc}";
			}
		}


		$status = wp_mail(
			"$rep_name <{$rep_email}>",
			$subject,
			$message,
			$headers
		);

		return $status;

	}


	public static function display_voters_stats_html_template() {
		return '<li class="clearfix"><h4>%2$s :- <span>%1$s</span></h4><div>%3$s</div></li>';
	}

	public static function get_voter_details( $voter_id ) {

		$voter_type  = get_post_meta( $voter_id, static::get_prefix( 'voter_type' ), true );
		$voter_types = self::get_voter_types();

		if ( array_key_exists( $voter_type, $voter_types ) ) {
			$voter_class = $voter_types[ $voter_type ]['class'];

			$voter = new $voter_class( $voter_id );

			return $voter->get_meta_for_ballot();
		} else {
			Admin_Notices::display_error( sprintf( __( 'Voter %s has a deactivated voter type. Please fix', 'wp-vote' ), get_the_title( $voter_id ) ) );
			wp_safe_redirect( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			die();
		}
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $messages
	 *
	 * @return mixed
	 */
	public static function post_published_message( $messages ) {
		global $post;
		if ( null !== $post && $post->post_type === self::get_post_type() && is_admin() ) {
			$messages['post'][1] = sprintf( __( 'Voter updated', 'wp-vote' ) );
			$messages['post'][6] = sprintf( __( 'Voter saved', 'wp-vote' ) );
		}

		return $messages;
	}

	/**
	 *
	 *
	 * @static
	 *
	 * @param $trans
	 * @param $text
	 *
	 * @return string|void
	 */
	public static function filter_published_on( $trans, $text ) {

		if ( function_exists( 'get_current_screen' ) ) {

			$screen = get_current_screen();
			if ( null !== $screen && $screen->post_type === self::get_post_type() && is_admin() ) {

				switch ( $text ) {
					case 'Publish':

						return __( 'Save Voter', 'wp-vote' );
						break;
					case 'Update':

						return __( 'Update Voter', 'wp-vote' );
						break;
				}
			}
		}

		return $trans;
	}


	/**
	 * Call a static function on a Voter Type
	 *
	 * @param      $name       Function name
	 * @param bool $voter_type (optional) Leave blank to use current voter type
	 *
	 * @return mixed
	 */
	public static function call_voter_type_func( $callback, $voter_type = false, $args = false ) {

		if ( ! $voter_type ) {
			$voter_type = self::get_voter_type();
		}

		$voter_types = self::get_voter_types();
		$voter_class = $voter_types[ $voter_type ]['class'];
		if ( method_exists( $voter_class, $callback ) ) {
			return call_user_func( array( $voter_class, $callback ), $args );
		}

		return false;


	}
}






