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
			<div class="entry-breadcrumbs">
				<span xmlns:v="http://rdf.data-vocabulary.org/#">
					<span typeof="v:Breadcrumb">
						<span class="breadcrumb_last" property="v:title">Events</span>
					</span>
				</span>
			</div><!-- .entry-breadcrumbs -->

			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">
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
				</main><!-- #main -->
				<div id="secondary" class="widget-area col-1-4" role="complementary">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div><!-- #secondary -->
			</div><!-- .grid -->
		</div><!-- .col-full -->

<?php get_footer(); ?>