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

<div class="tribe-events-featured-loop col-1-2 grid">

	<?php $start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
	$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) ); ?>
	<!-- Event  -->
	<div id="post-<?php the_ID() ?>" class="hentry vevent type-tribe_events tribe-clearfix <?php tribe_events_event_classes(); ?>">
		<header class="entry-header">
			<?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>"><div class="featured-image">
				<?php the_post_thumbnail( 'events-featured' ); ?>
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
				<span class="date">
				<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
			</span>
			</div></a><!-- .featured-image -->
			<?php endif; ?>
		</header>
		<div class="entry-content">
			<a href="<?php the_permalink(); ?>"><h2 class="entry-title"><?php the_title(); ?></h2></a>
			<p><?php ctrs_excerpt( 110 ); ?></p>
			<?php if ( $eventbrite_id = get_post_meta( get_the_ID(), '_EventBriteId', true ) ) : ?>
				<a href="<?php echo esc_url( "https://www.eventbrite.com/e/$eventbrite_id/" );?>" class="register">Register</a>
			<?php endif; ?>
		</div><!-- .entry-content -->
	</div><!-- .hentry .vevent -->

</div><!-- .tribe-events-featured-loop -->