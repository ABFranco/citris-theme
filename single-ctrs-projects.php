<?php
/**
 * The Single Project template file
 *
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main col-full" role="main">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'single-project' ); ?>
				<?php endwhile; wp_reset_postdata(); ?>

			<?php endif; ?>

			<div class="related-news">
			<?php
			$related_args = array(
				'post_type' => 'post',
				'posts_per_page' => 3,
				'meta_key' => '_ctrs_project_id',
				'meta_value' => get_the_ID(),
				'post_status' => 'publish'
			);
			$related_query = new WP_Query( $related_args );
			if ( $related_query->have_posts() ) : ?>
				<h2>Related News</h2>
				<div class="grid">
					<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
						<div class="col-1-3">
							<?php
								$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );
								if ( $groups && is_array( $groups ) && ! is_wp_error( $groups ) ) :
									$group = array_shift( $groups );
									$icons = get_option( 'ctrs_term_icons' );
									if ( $icons && isset( $icons[ $group->slug ] ) && '' !== trim( $icons[ $group->slug ] ) ) : ?>
										<img src="<?php echo esc_url( $icons[ $group->slug ] ); ?>" class="group <?php echo $group->slug; ?>">
									<?php else : ?>
										<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$group->slug-color.png";?>" class="group <?php echo $group->slug; ?>">
									<?php endif; ?>
								<?php endif; ?>
							<a href="<?php the_permalink(); ?>">
								<h3><?php the_title(); ?></h3>
							</a>
						</div><!-- .col-1-3 -->
					<?php endwhile; wp_reset_postdata(); ?>
				</div><!-- .grid -->
			<?php endif; ?>
			</div><!-- .related-news -->
		</main><!-- #main -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
	<?php
	$current_id = get_the_ID();
	$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );
	$group = array_shift( $groups );

	$similar_args = array(
		'post_type' => 'ctrs-projects',
		'posts_per_page' => 5,
		'ctrs-groups' => $group->slug
	);
	$similar_query = new WP_Query( $similar_args );
	if ( $similar_query->have_posts() ) : ?>
		<div class="col-full">
			<aside class="widget grid">
				<h3 class="widget-title">Similar Projects</h3>
				<?php $i = 1; while ( $similar_query->have_posts() ) : $similar_query->the_post(); ?>
					<?php if ( $i <= 4 && $current_id !== get_the_ID() ) : ?>
						<div class="col-1-4">
                            <article <?php post_class(); ?>>
								<a href="<?php the_permalink(); ?>">
                                    <div class="featured-image">
									<?php if ( has_post_thumbnail() ) {
										the_post_thumbnail( 'projects-small' );
									} else {
										echo '<img src="'. get_template_directory_uri() .'/images/projects.jpg">';
									}

									$icons = get_option( 'ctrs_term_icons' );
									if ( $icons && isset( $icons[ $group->slug ] ) && '' !== trim( $icons[ $group->slug ] ) ) : ?>
										<img src="<?php echo esc_url( $icons[ $group->slug ] ); ?>" class="group <?php echo $group->slug; ?>">
									<?php else : ?>
										<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$group->slug.png";?>" class="group <?php echo $group->slug; ?>">
									<?php endif; ?>
                                    </div>
									<h4><?php the_title(); ?></h4>
								</a>
							</article>
						</div><!-- .col-1-4 -->
					<?php $i++; endif; ?>
				<?php endwhile; wp_reset_postdata(); ?>
			</aside>
		</div><!-- .col-full -->
	<?php endif; ?>
	</div><!-- #secondary -->

<?php get_footer(); ?>