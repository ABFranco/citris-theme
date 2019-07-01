<?php
/**
 * The project archive template file
 *
 * @package Citris
 * @since 0.1.0
 */

$paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">
			<div class="entry-breadcrumbs">
				<?php if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb();
				} ?>
			</div><!-- .entry-breadcrumbs -->

			<main id="main" class="site-main" role="main">

				<div class="grid list">

					<nav id="archive-nav" class="archive-nav" data-campus="none" data-page="<?php echo absint( $paged ); ?>">
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
							<li data-term="infrastructure">
								<a href="#">Infrastructure</a>
							</li>
							<li data-term="democracy">
								<a href="#">Democracy</a>
							</li>
						</ul>
					</nav>
					<div id="loading" class="loading"></div>

				<?php while ( have_posts() ) : the_post(); ?>

					<div class="col-1-3">
						<?php get_template_part( 'content', 'archive' ); ?>
					</div><!-- end .col-1-3 -->

				<?php endwhile; ?>

				</div><!-- .grid -->

				<?php ctrs_paging_nav(); ?>

			</main><!-- #main -->
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