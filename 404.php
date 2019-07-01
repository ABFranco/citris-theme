<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Citris
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full">
			<div class="entry-breadcrumbs"></div><!-- .entry-breadcrumbs -->
			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">

					<section class="error-404 not-found">
						<header class="page-header">
							<h1 class="page-title">Oops! That page can&rsquo;t be found.</h1>
						</header><!-- .page-header -->

						<div class="page-content">
							<p>It looks like nothing was found at this location. Maybe try one of the links below or a search?</p>

							<?php get_search_form(); ?>

							<?php the_widget( 'WP_Widget_Recent_Posts' ); ?>

							<div class="widget widget_categories">
								<h2 class="widgettitle">Most Used Categories</h2>
								<ul>
								<?php
									wp_list_categories( array(
										'orderby'    => 'count',
										'order'      => 'DESC',
										'show_count' => 1,
										'title_li'   => '',
										'number'     => 10,
									) );
								?>
								</ul>
							</div><!-- .widget -->

							<?php
							$archive_content = '<p>' . sprintf( 'Try looking in the monthly archives. %1$s', convert_smilies( ':)' ) ) . '</p>';
							the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
							?>

							<?php the_widget( 'WP_Widget_Tag_Cloud' ); ?>

						</div><!-- .page-content -->
					</section><!-- .error-404 -->

				</main><!-- #main -->
				<div id="secondary" class="widget-area col-1-4" role="complementary">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div><!-- #secondary -->
			</div><!-- .grid -->
		</div><!-- .col-full -->
	</div><!-- #primary -->

<?php get_footer(); ?>