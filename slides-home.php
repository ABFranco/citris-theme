
<div class="col-full">
	<div id="slides" class="featured">
	<?php
	$home_query = new WP_Query( array( 'post_type' => 'ctrs-projects', 'posts_per_page' => 4, 'fields' => 'ids', 'meta_key' => '_thumbnail_id' ) );
	if ( $home_query->have_posts() ) :
		$post_ids = $home_query->posts;
		$first_post = array_shift( $post_ids );
	?>
		<div id="home" class="slide grid">
			<div class="featured-left col-3-4">
			<?php global $post;
			$post = get_post( (int) $first_post );
			setup_postdata( $post );
			?>
				<article <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_post_thumbnail( 'projects' ); ?>
						<h1 class="entry-title">
							<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
						</h1>
					</header><!-- .entry-header -->
				</article>
			</div><!-- .featured-left -->
			<div class="featured-right col-1-4">
				<?php foreach ( $post_ids as $post_id ) : ?>
					<?php
					$post = get_post( (int) $post_id );
					setup_postdata( $post );
					$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );
					$has_group = false;
					if( $groups && is_array( $groups ) && ! is_wp_error( $groups ) ){
						$group = array_shift( $groups );
						$group_name = strtoupper( $group->slug );
						$has_group = true;
					}
					?>
					<article <?php post_class( 'type-' . $group->slug  ); ?>>
						<div class="ft-right-project-img"><?php the_post_thumbnail( 'projects-small' ); ?></div>
                        <header class="entry-header">
							<h2 class="entry-title">
								<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
							</h2>
						</header><!-- .entry-header -->
					</article>
				<?php endforeach; ?>
			</div><!-- .featured-right -->
		</div><!-- .slide -->
	<?php endif; wp_reset_postdata(); ?>

	</div><!-- #slides -->
	<div class="clear"></div>
</div><!-- .col-full -->
