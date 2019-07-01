<?php
/**
 * The news archive template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area archives news">
		<div class="col-full">
			<div class="entry-breadcrumbs">
				<?php if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb();
				} ?>
			</div><!-- .entry-breadcrumbs -->

			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">
					<header class="archive-header">
						<h1 class="archive-title">News</h1>
					</header><!-- .archive-header -->

					<?php
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					$orig_query = $wp_query;
					$wp_query = new WP_Query( array( 'posts_per_page' => 20, 'post_type' => 'post', 'paged' => $paged, 'post_status' => 'publish' ) ); ?>
					<div id="news-listings">
						<?php
						// Start the loop.
						while ( have_posts() ) : 
							
								
								the_post();

								/*
								* Include the Post-Format-specific template for the content.
								* If you want to override this in a child theme, then include a file
								* called content-___.php (where ___ is the Post Format name) and that will be used instead.
								*/
								get_template_part( 'template-parts/content', get_post_format() );
							// End the loop.
						endwhile;
						
						// Previous/next page navigation.
						if(function_exists('wp_paginate')):
							wp_paginate();  
						else :
						the_posts_pagination(
							array(
								'prev_text'          => __( 'Previous page', 'citris' ),
								'next_text'          => __( 'Next page', 'citris' ),
								'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'citris' ) . ' </span>',
							)
							) ;
    					endif; ?>

					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

						<?php get_template_part( 'content', 'archives' ); ?>

					<?php endwhile; ?>
					</div>

					<?php
						// Start the loop.
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content', get_post_format() );
						endwhile;

						// Previous/next page navigation.
						if(function_exists('wp_paginate')):
							wp_paginate();  
						else :
						the_posts_pagination(
							array(
								'prev_text'          => __( 'Previous page', 'citris' ),
								'next_text'          => __( 'Next page', 'citris' ),
								'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'citris' ) . ' </span>',
							)
							) ;
    					endif; ?>

					<?php ctrs_paging_nav(); $wp_query = $orig_query; wp_reset_postdata(); ?>
					
				</main><!-- #main -->
				<div id="secondary" class="widget-area col-1-4" role="complementary">
					<?php dynamic_sidebar( 'sidebar-3' ); ?>
				</div><!-- #secondary -->
			</div><!-- .grid -->
		</div><!-- .col-full -->

	</div><!-- #primary -->

	<div class="home-sidebar">
		<div class="col-full">
			<?php get_template_part( 'home', 'sidebar' ); ?>
		</div><!-- .col-full -->
	</div><!-- home-sidebar -->

<?php get_footer(); ?>