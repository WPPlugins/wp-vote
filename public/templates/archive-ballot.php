<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/wp-vote/archive-product.php.
 *
 * HOWEVER, on occasion wp-vote will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @author 		pbearne
 * @package 	wp-vote/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


get_header(); ?>
<?php
/**
 * wp-vote_before_main_content hook.
 *
 * @hooked wp_vote_output_content_wrapper - 10 (outputs opening divs for the content)
 */
do_action( 'wp_vote_before_main_content' );
?>

<?php if ( apply_filters( 'wp-vote_show_page_title', true ) ) : ?>

	<h1 class="page-title"><?php get_the_title(); ?></h1>

<?php endif; ?>

<?php
/**
 * wp_vote_archive_description hook.
 *
 */
do_action( 'wp_vote_archive_description' );
?>

<?php if ( have_posts() ) : ?>

	<?php
	/**
	 * wp-vote_before_shop_loop hook.
	 *
	 * @hooked wp-vote/Template_Actions::show_archive_title - 20
	 * @hooked wp-vote/Template_Actions::show_archive_description - 30
	 */
	do_action( 'wp_vote_before_ballots_loop' );
	?>

	<ul class="ballots">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php \WP_Vote\Template_Loader::get_template_part( 'content', 'ballot' ); ?>

	<?php endwhile; // end of the loop. ?>

	</ul>

	<?php
	/**
	 * wp-vote_after_shop_loop hook.
	 *
	 * @hooked wp-vote/Template_Actions::pagination - 10
	 */
	do_action( 'wp-vote_after_ballots_loop' );
	?>
<?php else : ?>

	<?php printf( '<h3>%s</h3>', __( 'No Ballots', 'wp-vote' ) ); ?>

<?php endif; ?>
<?php
/**
 * wp-vote_after_main_content hook.
 *
 * @hooked wp-vote_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'wp-vote_after_main_content' );
?>



<?php get_footer(); ?>