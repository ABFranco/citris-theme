<?php
/**
 * The home template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<div id="slider" class="slider">
				<?php get_template_part( 'slides', 'home' ); ?>
			</div><!-- .slider -->

			<?php if ( have_posts() ) : ?>
				<div class="home-news">
					<div class="col-full">
						<h2 class="section-heading">News</h2>

						<?php while ( have_posts() ) : the_post(); ?>
							<div class="col-1-4">
								<?php get_template_part( 'content', 'home' ); ?>
							</div>
						<?php endwhile; ?>

						<div class="clear"></div>
						<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="more com-btn">More News</a>
					</div><!-- .col-full -->
				</div><!-- .home-news -->
			<?php endif; ?>

			<div class="home-sidebar">
				<div class="col-full">
					<?php get_template_part( 'home', 'sidebar' ); ?>
				</div><!-- .col-full -->
			</div><!-- home-sidebar -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>