<?php
/**
 * The search template file
 * Used for /people/
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
						<?php if ( 'ctrs-people' === get_post_type() ) : ?>
							<ul class="person-filter">
								<li>Filter By:</li>
								<li><a href="<?php echo esc_url( home_url( '/people/leadership/' ) ); ?>">Leadership</a></li>
								<li><a href="<?php echo esc_url( home_url( '/people/researcher/' ) ); ?>">Researcher</a></li>
								<li><a href="<?php echo esc_url( home_url( '/people/staff/' ) ); ?>">Staff</a></li>
							</ul>
							<?php get_template_part( 'searchform', 'people' ); ?>
						<?php endif; ?>
						<h1 class="archive-title"><?php printf( 'Search Results for: %s', get_search_query() ); ?></h1>
					</header><!-- .archive-header -->

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content', 'archives' ); ?>

					<?php endwhile; ?>

					<?php ctrs_paging_nav(); $wp_query = $orig_query; wp_reset_postdata(); ?>
					
				</main><!-- #main -->
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