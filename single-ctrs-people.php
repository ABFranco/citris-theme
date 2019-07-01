<?php
/**
 * The single person template file
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

			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'single' ); ?>

				<?php endwhile; ?>

				</main><!-- #main -->
				<div id="secondary" class="widget-area col-1-4" role="complementary">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div><!-- #secondary -->
			</div><!-- .grid -->
		</div><!-- .col-full -->

		<?php
		$term = get_term_by( 'name', get_the_title(), 'ctrs-tax-people' );
		if ( $term ) :
			$project_args = array(
				'post_type'       => 'ctrs-projects',
				'posts_per_page'  => 4,
				'post_status'     => 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'ctrs-tax-people',
						'field'    => 'id',
						'terms'    => (int) $term->term_id
					)
				)
			);
			$project_query = new WP_Query( $project_args );
			if ( $project_query->have_posts() ) :
			?>
				<div class="home-news">
					<div class="col-full">
						<h2 class="section-heading"><?php the_title(); ?>'s Projects</h2>

						<?php while ( $project_query->have_posts() ) : $project_query->the_post(); ?>
							<div class="col-1-4">
								<?php get_template_part( 'content', 'home' ); ?>
							</div>
						<?php endwhile; wp_reset_postdata(); ?>

						<div class="clear"></div>
					</div><!-- .col-full -->
				</div><!-- .home-news -->
			<?php endif; ?>
		<?php endif; ?>

	</div><!-- #primary -->

	<div class="home-sidebar">
		<div class="col-full">
			<?php get_template_part( 'home', 'sidebar' ); ?>
		</div><!-- .col-full -->
	</div><!-- home-sidebar -->

<?php get_footer(); ?>