<?php
/**
 * The groups archive template file
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
						<h1 class="archive-title">Initiatives</h1>
					</header><!-- .archive-header -->

					<?php
					$groups = get_terms( 'ctrs-groups', array( 'parent' => 0 ) );
					$group_data = array();

					/*
					 * Add all information about a group to an array.
					 * The main reason for this is so we can add the
					 * order field, and then sort by this field, giving
					 * the client the ability to sort these groups.
					 */
					foreach ( $groups as $group ) {
						if ( preg_match( '([0-9]+)', $group->description, $matches ) ) {
							$group_id = (int) $matches[0];
						} else {
							$group_id = 0;
						}

						if ( 'publish' === get_post_status( $group_id ) ) {
							if ( $group_id && has_post_thumbnail( $group_id ) ) {
								$thumb = get_the_post_thumbnail( $group_id, 'projects-similar' );
							} else {
								$thumb = '<img src="http://placehold.it/330x177">';
							}

							$group_data[] = array(
								'term_id'      => $group->term_id,
								'term_name'    => $group->name,
								'term_desc'    => $group->description,
								'term_link'    => get_term_link( $group, 'ctrs-groups' ),
								'post_id'      => $group_id,
								'post_thumb'   => $thumb,
								'post_content' => get_post_field( 'post_content', $group_id ),
								'post_excerpt' => get_post_field( 'post_excerpt', $group_id ),
								'order'        => get_post_field( 'menu_order', $group_id )
							);
						}
					}
					usort( $group_data, 'ctrs_cmp' );
					foreach ( $group_data as $key => $group ) :
					?>
						<article id="post-<?php echo (int) $group['term_id']; ?>" class="hentry">
                            <div class="featured-image col-1-3">
                                <a href="<?php echo esc_url( $group['term_link'] ); ?>" rel="bookmark">
                                    <?php echo $group['post_thumb']; ?>
                                </a>
                            </div>
							<header class="entry-header">
								<h2 class="entry-title">
									<a href="<?php echo esc_url( $group['term_link'] ); ?>" rel="bookmark">
										<?php echo esc_html( $group['term_name'] ); ?>
									</a>
								</h2>
							</header><!-- .entry-header -->
							<div class="entry-summary">
								<?php
								if ( $group['post_excerpt'] ) {
									echo apply_filters( 'the_excerpt', $group['post_excerpt'] );
								} else {
									ctrs_excerpt( 400, $group['post_content'] );
								} ?>
							</div><!-- .entry-summary -->
						</article>
					<?php endforeach;?>
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