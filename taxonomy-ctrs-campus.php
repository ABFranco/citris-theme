<?php
/**
 * The campus taxonomy template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">
			<div class="entry-breadcrumbs">
				<?php if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb();
				} ?>
			</div><!-- .entry-breadcrumbs -->

			<main id="main" class="site-main" role="main">

				<div class="grid overview">
					<div class="col-2-3">
						<?php $image = apply_filters( 'taxonomy-images-queried-term-image', '', array( 'image_size' => 'projects' ) );
							if ( $image ) {
								echo $image;
							} else {
								echo '<img src="http://placehold.it/833x460">';
							}
						?>
						<h1>
							<?php $current_group = single_term_title( '', false ); ?>
							<?php echo esc_html( $current_group ); ?>
						</h1>
					</div>

					<div class="col-1-3">
						<span>Overview</span>
						<?php echo term_description(); ?>
					</div>
				</div><!-- .grid -->

				<div class="grid">
					<div class="entry-people col-1-3">
						<?php if ( have_posts() ) : ?>
							<ul>
								<?php while ( have_posts() ) : the_post(); ?>
									<li>
										<?php if ( has_post_thumbnail() ) {
											the_post_thumbnail();
										} else {
											echo get_avatar( 0, 64 );
										} ?>
										<span>
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); ?>
											</a> <br>
											<?php echo esc_html( get_post_meta( get_the_ID(), '_ctrs_position', true ) ); ?>
										</span>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php endif; ?>
					</div><!-- .entry-people -->

					<div class="entry-content col-2-3">
						<?php
						$queried_object = get_queried_object();
						$featured_query = '';

						$sticky_posts = get_option( 'sticky_posts' );
						if ( is_array( $sticky_posts ) && count( $sticky_posts ) >= 1 ) {
							$stickies = array();
							foreach ( $sticky_posts as $sticky_post ) {
								if ( 'ctrs-projects' === get_post_type( $sticky_post ) ) {
									$stickies[] = $sticky_post;
								}
							}
							if ( $stickies && ! empty( $stickies ) ) {
								$featured_args = array(
									'post__in'       => $stickies,
									'post_type'      => 'ctrs-projects',
									'posts_per_page' => 1,
									'ctrs-campus'    => $queried_object->slug,
									'post_status'    => 'publish'
								);
								$featured_query = new WP_Query( $featured_args );
							}
						}

						$project_args = array(
							'post_type'      => 'ctrs-projects',
							'posts_per_page' => 4,
							'post__not_in'   => get_option( 'sticky_posts' ),
							'ctrs-campus'    => $queried_object->slug,
							'post_status'    => 'publish'
						);
						$project_query = new WP_Query( $project_args );
						?>
						<?php if ( $project_query->have_posts() ) : ?>
							<div class="grid list">
								<nav id="archive-nav" class="archive-nav" data-campus="<?php echo esc_attr( $queried_object->slug ); ?>">
									<ul>
										<li class="active" data-term="all">
											<a href="#">All</a>
										</li>
										<li data-term="health">
											<a href="#">Health</a>
										</li>
										<li data-term="energy">
											<a href="#">Energy</a>
										</li>
										<li data-term="democracy">
											<a href="#">Democracy</a>
										</li>
										<li data-term="infrastructure">
											<a href="#">Infrastructure</a>
										</li>
									</ul>
								</nav>
								<div id="loading" class="loading"></div>
							<?php
							$max = 4;
							if ( $featured_query && $featured_query->have_posts() ) :
								$max = 3;
								while ( $featured_query->have_posts() ) : $featured_query->the_post();
							?>
									<div class="col-1-2 sticky">
										<?php get_template_part( 'content', 'archive' ); ?>
									</div><!-- end .col-1 -->
								<?php endwhile; wp_reset_postdata(); ?>
							<?php endif; ?>

							<?php $i = 1; while ( $project_query->have_posts() ) : $project_query->the_post(); ?>

								<?php if ( $i <= $max ) : ?>
								<div class="col-1-2">
									<?php get_template_part( 'content', 'archive' ); ?>
								</div><!-- end .col-1-2 -->
								<?php endif; ?>

							<?php $i++; endwhile; wp_reset_postdata(); ?>
						<?php endif; ?>
					</div><!-- .entry-summary -->
				</div><!-- .grid -->

			</main><!-- #main -->
		</div><!-- .col-full -->

	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		<div class="col-full grid">
		<?php
		$related_args = array(
			'post_type' => 'post',
			'posts_per_page' => 4,
			'ctrs-campus' => $queried_object->slug,
			'post_status' => 'publish'
		);
		$related_query = new WP_Query( $related_args );
		if ( $related_query->have_posts() ) : ?>
			<div class="col-1-2 related-news">
				<aside class="widget grid">
					<h3 class="widget-title">Related News</h3>
					<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
						<div class="col-1-2">
							<a href="<?php the_permalink(); ?>">
								<div class="featured-image">
								<?php if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'projects-large' );
								} else {
									echo '<img src="http://placehold.it/297x166">';
								} ?>
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
								</div><!-- .featured-image -->
								<h4><?php the_title(); ?></h4>
								<p><?php ctrs_excerpt( 80 ); ?></p>
							</a>
						</div><!-- .col-1-2 -->
					<?php endwhile; ?>
				</aside>
			</div><!-- .col-1-2 -->
		<?php endif; wp_reset_postdata(); ?>
		<?php
		$event_args = array(
			'post_type' => 'tribe_events',
			'eventDisplay' => 'upcoming',
			'posts_per_page' => 2,
			'ctrs-campus' => $queried_object->slug,
			'post_status' => 'publish'
		);
		$event_query = new WP_Query( $event_args );
		if ( $event_query->have_posts() ) : ?>
			<div class="col-1-2 related-events">
				<aside class="widget grid">
					<h3 class="widget-title">Related Events</h3>
					<div class="col-1-2">
					<?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
					<?php $start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
					$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
					$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
					?>
						<div class="event-large cf">
							<span class="date">
								<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
								<span class="year"><?php echo esc_html( $start_year ); ?></span>
							</span>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'events-featured' ); ?>
								<p><?php the_title(); ?></p>
							</a>
						</div>
					<?php endwhile; ?>
					</div><!-- .col-1-2 -->
					<?php
					$event_args = array(
						'post_type' => 'tribe_events',
						'eventDisplay' => 'upcoming',
						'posts_per_page' => 4,
						'offset' => 2,
						'ctrs-campus' => $queried_object->slug,
						'post_status' => 'publish'
					);
					$event_query = new WP_Query( $event_args );
					if ( $event_query->have_posts() ) : ?>
					<div class="col-1-2">
						<ul>
						<?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
							<li>
								<a href="<?php the_permalink(); ?>">
									<span class="date">
										<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
									</span>
									<div class="event-title"><?php the_title(); ?></div>
								</a>
							</li>
						<?php endwhile; ?>
							<li>
								<a href="<?php echo esc_url( home_url( '/events/' ) ) ?>" class="schedule">Full Schedule</a>
							</li>
						</ul>
					</div><!-- .col-1-2 -->
					<?php endif; wp_reset_postdata(); ?>
				</aside>
			</div><!-- .col-1-2 -->
		<?php endif; wp_reset_postdata(); ?>
		</div><!-- .col-full -->
	</div><!-- #secondary -->

<?php get_footer(); ?>