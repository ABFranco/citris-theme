<?php
/**
 * List View Loop
 * This file sets up the structure for the list loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/loop.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php
global $more;
$more = false;
?>

<div class="tribe-events-loop hfeed vcalendar">

	<?php while ( have_posts() ) : the_post(); ?>
		<?php do_action( 'tribe_events_inside_before_loop' ); ?>
		<?php $start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
		$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
		$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
		$start_time = date( 'g:i a', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) ); ?>

		<!-- Event  -->
		<div id="post-<?php the_ID() ?>" class="hentry vevent type-tribe_events tribe-clearfix <?php tribe_events_event_classes(); ?> <?php if ( ! tribe_is_new_event_day() ) echo 'same-day'; ?>">
			<header class="entry-header col-1-4">
				<span class="date">
					<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
					<span class="year"><?php echo esc_html( $start_year ); ?></span>
				</span>
				<span class="time"><?php echo esc_html( $start_time ); ?></span>
			</header>
			<div class="entry-content col-3-4">
				<?php if ( has_post_thumbnail() ) :
					$thumb_id = get_post_thumbnail_id();
					$thumb_url = wp_get_attachment_thumb_url( $thumb_id );
				?>
                <a href="<?php the_permalink(); ?>" class="featured-image" style="background-image: url(<?php echo esc_attr( $thumb_url ) ?>);">
					<?php
					$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );
					if ( $groups && is_array( $groups ) && ! is_wp_error( $groups ) ) :
						$group = array_shift( $groups );
						$icons = get_option( 'ctrs_term_icons' );
						if ( $icons && isset( $icons[ $group->slug ] ) && '' !== trim( $icons[ $group->slug ] ) ) : ?>
							<img src="<?php echo esc_url( $icons[ $group->slug ] ); ?>" class="group <?php echo $group->slug; ?>">
						<?php else : ?>
							<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$group->slug.png";?>" class="group <?php echo $group->slug; ?>">
						<?php endif; ?>
					<?php endif; ?>
				</a><!-- .featured-image -->
				<?php endif; ?>
				<div class="entry-content-text">
					<a href="<?php the_permalink(); ?>"><h2 class="entry-title"><?php the_title(); ?></h2></a>
					<p><?php ctrs_excerpt( 110 ); ?></p>
					<?php if ( $eventbrite_id = get_post_meta( get_the_ID(), '_EventBriteId', true ) ) : ?>
						<a href="<?php echo esc_url( "https://www.eventbrite.com/e/htnm-lecture-lisa-nakamura-indigenous-circuits-feb-6-tickets-$eventbrite_id/" );?>" class="register">Register</a>
					<?php endif; ?>
				</div> <!--.entry-content-text -->
			</div><!-- .entry-content -->
		</div><!-- .hentry .vevent -->


		<?php do_action( 'tribe_events_inside_after_loop' ); ?>
	<?php endwhile; ?>

</div><!-- .tribe-events-loop -->