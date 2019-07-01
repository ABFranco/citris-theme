<?php
/**
 * The main template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">
			<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>

			<?php endwhile; ?>

			</main><!-- #main -->
		</div><!-- .col-full -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>