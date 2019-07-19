<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$event_id = get_the_ID();

?>

<div id="tribe-events-content" class="tribe-events-single">

	<p class="tribe-events-back"><a href="<?php echo tribe_get_events_link() ?>"> <?php _e( '&laquo; All Events', 'tribe-events-calendar' ) ?></a></p>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<?php the_title( '<h1 class="tribe-events-single-event-title summary">', '</h1>' ); ?>

	<div class="tribe-events-schedule updated published tribe-clearfix">
		<h3><?php echo tribe_events_event_schedule_details(); ?></h3>
		<?php echo tribe_events_event_recurring_info_tooltip(); ?>
		<?php  if ( tribe_get_cost() ) :  ?>
			<span class="tribe-events-divider">|</span>
			<span class="tribe-events-cost"><?php echo tribe_get_cost( null, true ) ?></span>
		<?php endif; ?>
	</div>

	<?php while ( have_posts() ) :  the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class('vevent'); ?>>
			<!-- Event featured image -->
			<?php echo tribe_event_featured_image(); ?>

			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
			<div class="tribe-events-single-event-description tribe-events-content entry-content description">
				<?php the_content(); ?>
			</div><!-- .tribe-events-single-event-description -->
			
			<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>
			<!-- Event meta -->
			<?php do_action( 'tribe_events_single_event_before_the_meta' ) ?>
				<?php echo tribe_get_template_part( 'modules/meta' ); ?>
			<?php do_action( 'tribe_events_single_event_after_the_meta' ) ?>
			<?php
				if ( function_exists( 'sharing_display' ) ) {
					sharing_display( '', true );
				}
				 
				if ( class_exists( 'Jetpack_Likes' ) ) {
					$custom_likes = new Jetpack_Likes;
					echo $custom_likes->post_likes( '' );
				}
			?>

			
			</div><!-- .hentry .vevent -->
		<?php if( get_post_type() == TribeEvents::POSTTYPE && tribe_get_option( 'showComments','no' ) == 'yes' ) { comments_template(); } ?>
	<?php endwhile; ?>

</div><!-- #tribe-events-content -->
