<?php
/**
 * The groups taxonomy template file
 *
 * @package Citris
 * @since 0.1.0
 */

$paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">
			<div class="entry-breadcrumbs"></div><!-- .entry-breadcrumbs -->

			<main id="main" class="site-main" role="main">

				<div class="grid overview">
					<div class="col-2-3">
						<?php
						$queried_object = get_queried_object();
						$current_group = single_term_title( '', false );
						if ( preg_match( '([0-9]+)', term_description(), $matches ) ) {
							$group_id = (int) $matches[0];
						} else {
							$group_id = 0;
						}

						if ( has_post_thumbnail( $group_id ) ) {
							echo get_the_post_thumbnail( $group_id, 'projects' );
						} else {
							echo '<img src="http://placehold.it/833x460">';
						} ?>
						<h1>
						<?php $icons = get_option( 'ctrs_term_icons' );
						if ( $icons && isset( $icons[ $queried_object->slug ] ) && '' !== trim( $icons[ $queried_object->slug ] ) ) : ?>
							<img src='<?php echo esc_url( $icons[ $queried_object->slug ] ); ?>'>
						<?php elseif ( $queried_object->parent ) : ?>
							<?php $parent_term = get_term( $queried_object->parent, 'ctrs-groups' ); ?>
							<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$parent_term->slug.png";?>">
						<?php else : ?>
							<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$queried_object->slug.png";?>">
						<?php endif; ?>
						<?php echo esc_html( $current_group ); ?>
						</h1>
					</div>

					<div class="col-1-3">
						<span>Overview</span>
						<?php if ( $excerpt = get_post_field( 'post_excerpt', $group_id ) ) {
							echo apply_filters( 'the_excerpt', $excerpt );
						} else {
							ctrs_excerpt( 500, get_post_field( 'post_content', $group_id ) );
						} ?>
					</div>
				</div><!-- .grid -->

				<div class="grid">
					<div class="col-full">
						<?php echo wp_kses_post( get_post_field( 'post_content', $group_id ) ); ?>
					</div>
				</div>

				<div class="grid list">

				<?php $term_children = get_term_children( $queried_object->term_id, 'ctrs-groups' );
				if ( is_array( $term_children ) && ! empty( $term_children ) ) : ?>
					<nav id="initiative-nav" class="archive-nav" data-page="<?php echo absint( $paged ); ?>">
						<ul>
							<li class="active" data-term="<?php echo esc_attr( $queried_object->slug ); ?>">
								<a href="#">All</a>
							</li>
						<?php foreach ( $term_children as $child ) :
							$term = get_term_by( 'id', $child, 'ctrs-groups' );
						?>
							<li data-term="<?php echo esc_attr( $term->slug ); ?>">
								<a href="<?php echo esc_url( get_term_link( $child, 'ctrs-groups' ) ); ?>">
									<?php echo esc_html( $term->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
					<div id="loading" class="loading"></div>

					<div id="initiative-container" class="grid list">
					<?php while ( have_posts() ) : the_post(); ?>

						<div class="col-1-3">
							<?php get_template_part( 'content', 'archive' ); ?>
						</div><!-- end .col-1-3 -->

					<?php endwhile; ?>
					</div><!-- end .grid -->

				</div><!-- .grid -->

				<?php ctrs_paging_nav(); ?>

			</main><!-- #main -->
		</div><!-- .col-full -->

	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		<div class="col-full grid">
		<?php
		$related_args = array(
			'post_type' => 'post',
			'posts_per_page' => 4,
			'ctrs-groups' => $queried_object->slug,
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
								<?php $icons = get_option( 'ctrs_term_icons' );
								if ( $icons && isset( $icons[ $queried_object->slug ] ) && '' !== trim( $icons[ $queried_object->slug ] ) ) : ?>
									<img src="<?php echo esc_url( $icons[ $queried_object->slug ] ); ?>" class="group <?php echo $queried_object->slug; ?>">
								<?php else : ?>
									<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$queried_object->slug.png";?>" class="group <?php echo $queried_object->slug; ?>">
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
			'ctrs-groups' => $queried_object->slug,
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
					$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) ); ?>
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
						'ctrs-groups' => $queried_object->slug,
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