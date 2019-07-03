<?php
/**
 * The archive template file
 *
 * @package Citris
 * @since 0.1.0
 */

$type = get_query_var( 'person_type' );
if ( 'leadership' === $type ) {
	$type_key = '_ctrs_leadership';
} elseif ( 'researcher' === $type ) {
	$type_key = '_ctrs_researcher';
} elseif ( 'staff' === $type ) {
	$type_key = '_ctrs_staff';
} else {
	$type_key = '_ctrs_type';
}
get_header(); ?>

	<div id="primary" class="content-area archives archive test">
		<div class="col-full">
			<div class="entry-breadcrumbs">
				<span class="breadcrumb-last" property="v:title">People</span>
			</div><!-- .entry-breadcrumbs -->

			<div class="grid">
				<main id="main" class="site-main col-3-4" role="main">
					<header class="archive-header">
						<ul class="person-filter">
							<li>Filter By:</li>
							<li><a class="button" href="<?php echo esc_url( home_url( '/people/researcher/' ) ); ?>">Researcher</a></li>
							<li><a class="button" href="<?php echo esc_url( home_url( '/people/staff/' ) ); ?>">Staff</a></li>
						</ul>
						<?php get_template_part( 'searchform', 'people' ); ?>
						<h1 class="archive-title"><?php echo esc_html( ucfirst( $type ) ); ?></h1>
					</header><!-- .archive-header -->

					<?php
					global $wp_query;
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					$orig_query = $wp_query;
					// Meta query only needed until we convert all people over to use the new format of Type.
					$type_args = array(
						'post_type'      => 'ctrs-people',
						'posts_per_page' => 10,
						'post_status'    => 'publish',
						'paged'          => $paged,
						'meta_query' => array(
							array(
								'key'   => $type_key,
								'value' => $type
							),
						)
					);
					if ( 'leadership' === $type ) {
						$type_args['posts_per_page'] = -1;
                        $type_args['orderby'] = 'menu_order';
                        $type_args['order'] = 'ASC';
					} else {
                        $type_args['meta_key'] = '_ctrs_lname';
                        $type_args['orderby'] = 'meta_value';
                        $type_args['order'] = 'ASC';
                    }
					$wp_query = new WP_Query( $type_args );

					// Show sticky posts up top on leadership page
//					if ( 'leadership' === $type && $paged === 1 ) {
//						$posts = $wp_query->posts;
//
//						// Don't move sticky posts up on paginated pages.
//						if ( isset( $wp_query->query['paged'] ) && $wp_query->query['paged'] >= 2 ) {
//							return $posts;
//						}
//
//						$sticky_posts = get_option( 'sticky_posts' );
//						$num_posts = count( $posts );
//						$sticky_offset = 0;
//						$posts_new = array();
//
//						// Find the sticky posts
//						for ( $i = 0; $i < $num_posts; $i++ ) {
//				            // Put sticky posts at the top of the posts array
//							if ( in_array( $posts[$i]->ID, $sticky_posts ) ) {
//								$sticky_post = $posts[$i];
//								$posts_new[] = $sticky_post;
//
//								$sticky_offset++;
//
//								// Remove post from sticky posts array
//								$offset = array_search( $sticky_post->ID, $sticky_posts );
//								unset( $sticky_posts[$offset] );
//							}
//						}
//
//						// Look for more sticky posts if needed
//						if ( ! empty( $sticky_posts ) ) {
//							$stickies = get_posts( array(
//								'post__in' => $sticky_posts,
//								'post_type' => $wp_query->query_vars['post_type'],
//								'post_status' => 'publish',
//								'nopaging' => true
//							) );
//
//							foreach ( $stickies as $sticky_post ) {
//								$posts_new[] = $sticky_post;
//								$sticky_offset++;
//							}
//						}
//						$wp_query->found_posts = count( $posts_new );
//						$wp_query->post_count = count( $posts_new );
//						$wp_query->posts = $posts_new;
//					}
					?>

					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

						<?php get_template_part( 'content', 'archives' ); ?>

					<?php endwhile; ?>

					<?php ctrs_paging_nav($wp_query); $wp_query = $orig_query; wp_reset_postdata(); ?>
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