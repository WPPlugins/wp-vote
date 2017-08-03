<?php
/**
 * The Template for displaying all single ballots
 *
 * This template can be overridden by copying it to yourtheme/wp-vote/single-ballot.php.
 *
 * HOWEVER, on occasion wp-vote will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @author 		pbearne
 * @package 	wp-vote/Templates
 * @version     1.6.4
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


//global $wp_rewrite;
//echo '<pre>';
//var_dump($wp_rewrite);
//echo '</pre>';
?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php \WP_Vote\Template_Loader::get_template_part( 'content', 'single-ballot' ); ?>

<?php endwhile; // end of the loop. ?>

<?php
/**
 * wp_vote_after_main_content hook.
 *
 * @hooked wp_vote_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'wp_vote_after_main_content' );
?>

<?php
/**
 * wp-vote_sidebar hook.
 *
 *
 */
do_action( 'wp_vote_sidebar' );
?>

<script type="application/javascript">
	jQuery( '#wp-vote-questions').submit( function(){
		if ( jQuery( '#wp-vote-questions:not( :has( :radio:checked ) )').length) {
			alert("<?php echo( esc_js( __( 'You missed answering one or more questions on the ballot.', 'wp-vote' ) ) ); ?>")
			return false;
		}
	})
</script>

<?php get_footer(); ?>
