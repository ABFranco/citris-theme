<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Template -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
// http://citris.dev/events/category/research-exchange-events/past/?action=tribe_list&tribe_paged=1
get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">

			<div class="grid">
				<main id="main" class="site-main col-4-4" role="main">
					<?php tribe_events_before_html(); ?>
					<?php if ( ! is_single() && ! is_tax( 'tribe_events_cat' ) ) : ?>
						<?php $featured_args = array(
							'eventDisplay'   => 'upcoming',
							'post_type'      => 'tribe_events',
							'posts_per_page' => 2,
							'post_status'    => 'publish',
							'meta_query'     => array(
								array(
									'key'   => '_ecp_custom_1',
									'value' => 'Yes',
								)
							)
						);

						$featured_query = new WP_Query( $featured_args );
						if ( $featured_query->have_posts() ) :
						?>
						<div class="featured-events clearfix">
							<?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); ?>
								<?php get_template_part( 'tribe-events/list/loop', 'featured' ); ?>
							<?php endwhile; ?>
						</div><!-- .featured-events -->
						<?php endif; wp_reset_postdata(); ?>
					<?php endif; ?>

					<?php tribe_get_view(); ?>
					<?php tribe_events_after_html(); ?>
					<a href="http://events.berkeley.edu/index.php/calendar/sn/citris.html" target="_blank">
						<h2>View our events on UC Berkeley's Event Calendar.</h2>
					</a>
				</main><!-- #main -->
			</div><!-- .grid -->
		</div><!-- .col-full -->

<?php get_footer(); ?>