<?php
/**
 * Template Name: Research Thrusts Template
 *
 * @author Antonio franco
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full page-default">

			<div class="grid">
				<main id="main" class="site-main" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'single' ); ?>

				<?php endwhile; ?>

				</main><!-- #main -->
			</div><!-- .grid -->
		</div><!-- .col-full -->
		

	</div><!-- #primary -->

<?php get_footer(); ?>