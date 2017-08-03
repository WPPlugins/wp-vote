<?php

namespace WP_Vote;

if ( ! defined( 'WPINC' ) ) {
	die;
}


interface Post_Type_Interface {

	public static function plugins_loaded();

	public static function register_custom_post_type();


}


abstract class Abstract_Post_Type {

	const POST_TYPE = '';

	const SLUG = '';

	public static function plugins_loaded() {
		add_filter( 'cmb2_init', array( get_called_class(), 'register_custom_post_type' ) );
	}

	public static function register_custom_post_type() {
		die( __( 'Override register_custom_post_type in child class', 'wp-vote' ) );
	}

	public static function get_post_type() {
		return static::POST_TYPE;
	}

	public static function get_slug() {
		return static::SLUG;
	}

	public static function get_prefix( $append = '' ) {
		return join( '_', array( static::get_post_type(), sanitize_title( $append ) ) );
	}

	public static function _get_prefix( $append = '' ) {
		return '_' . static::get_prefix( $append );
	}

}