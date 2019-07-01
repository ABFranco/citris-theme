<?php
/**
 * The campus archive template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area archives">
		<div class="col-full">
			<div class="entry-breadcrumbs">
				<?php if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb();
				} ?>
			</div><!-- .entry-breadcrumbs -->

			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">
					<header class="archive-header">
						<h1 class="archive-title">Campuses</h1>
					</header><!-- .archive-header -->

					<?php
					$campuses = apply_filters( 'taxonomy-images-get-terms', '', array( 'having_images' => false, 'taxonomy' => 'ctrs-campus' ) );
					foreach ( $campuses as $campus ) : ?>
						<article id="post-<?php the_ID(); ?>" class="hentry">
							<?php if ( $campus->image_id ) {
								$image = get_image_tag( $campus->image_id, $campus->name, '', '', 'projects-similar' );
							} else {
								$image = '<img src="http://placehold.it/330x177">';
							}
							if ( $image ) {
								echo $image;
							} else {
								echo '<img src="http://placehold.it/330x177">';
							}
							?>
							<header class="entry-header">
								<h2 class="entry-title">
									<a href="<?php echo esc_url( get_term_link( $campus, 'ctrs-campus' ) ); ?>" rel="bookmark">
										<?php echo esc_html( $campus->name ); ?>
									</a>
								</h2>
							</header><!-- .entry-header -->
							<div class="entry-summary">
								<?php ctrs_excerpt( 200, $campus->description ); ?>
							</div><!-- .entry-summary -->
						</article>
					<?php endforeach; ?>
				</main><!-- #main -->
				<div id="secondary" class="widget-area col-1-4" role="complementary">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div><!-- #secondary -->
			</div><!-- .grid -->
		</div><!-- .col-full -->

		<?php $recent_query = new WP_Query( array( 'posts_per_page' => 4, 'post_type' => 'post', 'post_status' => 'publish' ) ); ?>
		<?php if ( $recent_query->have_posts() ) : ?>
			<div class="home-news">
				<div class="col-full">
					<h2 class="section-heading">News</h2>

					<?php while ( $recent_query->have_posts() ) : $recent_query->the_post(); ?>
						<div class="col-1-4">
							<?php get_template_part( 'content', 'home' ); ?>
						</div>
					<?php endwhile; wp_reset_postdata(); ?>

					<div class="clear"></div>
				</div><!-- .col-full -->
			</div><!-- .home-news -->
		<?php endif; ?>

	</div><!-- #primary -->

	<div class="home-sidebar">
		<div class="col-full">
			<?php get_template_part( 'home', 'sidebar' ); ?>
		</div><!-- .col-full -->
	</div><!-- home-sidebar -->

<?php get_footer(); ?>