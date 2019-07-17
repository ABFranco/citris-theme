<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="featured-image col-1-3">
		<a href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) {
			if ( is_post_type_archive( 'ctrs-people' ) || 'ctrs-people' === get_post_type() ) {
				the_post_thumbnail( 'archive-small' );
			} else {
				the_post_thumbnail( 'projects-medium' );
				$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );

				if ( $groups && is_array( $groups ) && ! is_wp_error( $groups ) ) :
					$icons = get_option( 'ctrs_term_icons' );
					$icon = '';
					foreach ( $groups as $group ) {
						if ( $icons && isset( $icons[ $group->slug ] ) && '' !== trim( $icons[ $group->slug ] ) ) {
							$icon = '<img src="'. esc_url( $icons[ $group->slug ] ) .'" class="group '. $group->slug .'">';
						}
					}
					if ( $icon ) {
						echo wp_kses_post( $icon );
					} else {
						$group = array_pop( $groups );
						echo '<img src="'. get_template_directory_uri() .'/images/icons-small/'.$group->slug.'.png" class="group '. $group->slug .'">';
					}
				else :
					echo '<img src="'. get_template_directory_uri() .'/images/icons-small/citris.png" class="group health">';
				endif;
			}
		} else {
			if ( is_post_type_archive( 'ctrs-people' ) ) {
				echo get_avatar( 0, 140 );
			} elseif ( is_post_type_archive( 'ctrs-projects' ) ) {
				echo '<img src="'. get_template_directory_uri() .'/images/projects.jpg">';
			}
		} ?>
		</a>
    </div>
	<div class="col-2-3">
		<header class="entry-header">
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>" rel="bookmark">
				<?php if ( is_post_type_archive( 'ctrs-people' ) && get_post_meta( $post->ID, '_ctrs_fname', true ) ) {
					echo esc_html( get_post_meta( $post->ID, '_ctrs_honorific', true ) ) . ' ';
					echo esc_html( get_post_meta( $post->ID, '_ctrs_fname', true ) ) . ' ';
					echo esc_html( get_post_meta( $post->ID, '_ctrs_lname', true ) ) . ' ';
					echo esc_html( get_post_meta( $post->ID, '_ctrs_suffix', true ) );
				} else {
					the_title();
				}
				?>
				</a>
			</h2>
			<div class="entry-date"><?php the_time('M j, Y'); ?></div>
			<?php if ( has_tag() ) { ?>
				<div class="entry-tags"><?php the_tags('tagged in: ', ', '); ?></div>
			<?php } ?>

		</header><!-- .entry-header -->
		<div class="entry-summary">
			<?php
			if( is_post_type_archive( 'ctrs-people')){
				the_excerpt();
			} else {
				ctrs_excerpt( 200 );
			}
			?>
		</div><!-- .entry-summary -->
	</div>
	
</article>