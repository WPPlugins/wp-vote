<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/wp-vote/content-product.php.
 *
 * HOWEVER, on occasion wp-vote will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package wp-vote/Templates
 * @version 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<li <?php post_class(); ?>>

	<?php
	/**
	 * wp-vote_before_shop_loop_item hook.
	 *
	 * @hooked wp_vote_template_loop_ballot_link_open - 10
	 */
	do_action( 'wp_vote_before_ballot_loop_item' );

	/**
	 * wp_vote_before_shop_loop_item_title hook.
	 *
	 *
	 */
	do_action( 'wp_vote_before_ballot_loop_item_title' );

	/**
	 * wp_vote_shop_loop_item_title hook.
	 *
	 * @hooked wp_vote_template_loop_ballot_title - 10
	 */
	do_action( 'wp_vote_ballot_loop_item_title' );

	/**
	 * wp_vote_after_shop_loop_item_title hook.
	 *
	 */
	do_action( 'wp_vote_after_ballot_loop_item_title' );

	/**
	 * wp_vote_after_shop_loop_item hook.
	 *
	 * @hooked wp_vote_template_loop_ballot_link_close - 5
	 * @hooked wp_vote_template_loop_vote_button - 10
	 */
	do_action( 'wp-vote_after_ballot_item' );
	?>

</li>
