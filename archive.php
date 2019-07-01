<?php
/**
 * The archive template file
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
						<?php if ( is_post_type_archive( 'ctrs-people' ) ) : ?>
							<ul class="person-filter">
								<li>Filter By:</li>
								<li><a class="button" href="<?php echo esc_url( home_url( '/people/leadership/' ) ); ?>">Leadership</a></li>
								<li><a class="button" href="<?php echo esc_url( home_url( '/people/researcher/' ) ); ?>">Researcher</a></li>
								<li><a class="button" href="<?php echo esc_url( home_url( '/people/staff/' ) ); ?>">Staff</a></li>
							</ul>
							<?php get_template_part( 'searchform', 'people' ); ?>
						<?php endif; ?>
						<h1 class="archive-title"><?php
							if ( is_day() ) :
								printf( 'Daily Archives: %s', get_the_date() );
							elseif ( is_month() ) :
								printf( 'Monthly Archives:', 'twentythirteen', get_the_date( 'F Y', 'monthly archives date format' ) );
							elseif ( is_year() ) :
								printf( 'Yearly Archives: %s', get_the_date( 'Y', 'yearly archives date format' ) );
							else :
								$queried_obj = get_queried_object();
								echo $queried_obj->labels->name;
							endif;
						?></h1>
					</header><!-- .archive-header -->

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content', 'archives' ); ?>

					<?php endwhile; ?>

					<?php ctrs_paging_nav(); ?>
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