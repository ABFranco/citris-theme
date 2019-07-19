<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-breadcrumbs">
		<?php if ( function_exists('yoast_breadcrumb') ) {
			yoast_breadcrumb();
		} ?>
	</div><!-- .entry-breadcrumbs -->

	<div class="grid overview">
		<header class="entry-header col-1-3">
			<h1 class="entry-title">
                <?php
                    $short_title = get_post_meta($post->ID, 'short_title', true);
                    if($short_title) {
                        echo $short_title;
                    } else {
                        the_title();
                    }
                ?>
            </h1>

			<div class="entry-meta">
				<ul class="campus-list">
					<?php the_terms( get_the_ID(), 'ctrs-campus', '<li>', '</li><li>', '</li>' ); ?>
				</ul>
			</div><!-- .entry-meta -->

			<div class="entry-group">
				<?php
				$groups = get_the_terms( get_the_ID(), 'ctrs-groups' );
				if ( $groups && is_array( $groups ) && ! is_wp_error( $groups ) ) :
					$group = array_shift( $groups );

					$icons = get_option( 'ctrs_term_icons' );
					if ( $icons && isset( $icons[ $group->slug ] ) && '' !== trim( $icons[ $group->slug ] ) ) : ?>
						<img src="<?php echo esc_url( $icons[ $group->slug ] ); ?>">
					<?php elseif ( $group->parent ) : ?>
						<?php $parent_term = get_term( $group->parent, 'ctrs-groups' ); ?>
						<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$parent_term->slug.png";?>">
					<?php else : ?>
						<img src="<?php echo get_template_directory_uri() . "/images/icons-small/$group->slug.png";?>">
					<?php endif; ?>
					<h2><a href="<?php echo esc_url( get_term_link( $group ) ); ?>"><?php echo esc_html( $group->name ); ?></a></h2>
					<span><?php ctrs_excerpt( 150, get_post_field( 'post_excerpt', (int) $group->description ) ); ?></span>
				<?php endif; ?>
			</div>
		</header><!-- .entry-header -->

		<div class="col-2-3">
			<?php the_post_thumbnail( 'projects' ); ?>
		</div><!-- .col-2-3 -->
	</div><!-- .grid -->

	<div class="grid">
		<div class="entry-people col-1-3">
			<?php $people = get_the_terms( get_the_ID(), 'ctrs-tax-people' );
			if ( $people && is_array( $people ) && ! is_wp_error( $people ) ) : ?>
				<ul>
					<?php foreach ( $people as $person ) :
						$post = get_page_by_title( $person->name, OBJECT, 'ctrs-people' );
						if ( $post && null !== $post ) : ?>
							<li>
								<?php if ( has_post_thumbnail( $post->ID ) ) {
									echo get_the_post_thumbnail( $post->ID );
								} else {
									echo get_avatar( 0, 64 );
								} ?>
								<span>
									<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
									<?php
									if ( get_post_meta( $post->ID, '_ctrs_fname', true ) ) {
										echo esc_html( get_post_meta( $post->ID, '_ctrs_honorific', true ) ) . ' ';
										echo esc_html( get_post_meta( $post->ID, '_ctrs_fname', true ) ) . ' ';
										echo esc_html( get_post_meta( $post->ID, '_ctrs_lname', true ) ) . ' ';
										echo esc_html( get_post_meta( $post->ID, '_ctrs_suffix', true ) );
									} else {
										the_title();
									}
									?>
									</a> <br>
									<?php echo esc_html( get_post_meta( $post->ID, '_ctrs_position', true ) ) . ', '; ?>
									<?php echo esc_html( get_post_meta( $post->ID, '_ctrs_department', true ) ); ?>
								</span>
							</li>
						<?php endif; ?>
					<?php endforeach; wp_reset_postdata(); ?>
				</ul>
			<?php endif; ?>
		</div><!-- .entry-people -->

		<div class="col-2-3">
			<div class="entry-content">
				<?php the_content(); ?>
				<?php $event_query = new WP_Query( array( 'post_type' => 'tribe_events', 'eventDisplay' => 'upcoming', 'posts_per_page' => 2, 'ctrs-groups' => $group->slug ) );
				if ( $event_query->have_posts() ) : ?>
				<div class="events">
					<h3>Events</h3>
					<ul>
					<?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
						<?php
						$start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
						$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
						$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
						?>
						<li>
							<a href="<?php the_permalink(); ?>">
								<span class="date">
									<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
									<span class="year"><?php echo esc_html( $start_year ); ?></span>
								</span>
								<?php ctrs_excerpt( 110 ); ?>
							</a>
						</li>
					<?php endwhile; ?>
					</ul>
				</div><!-- .events -->
			</div><!-- .entry-content -->
			<?php endif; wp_reset_postdata(); ?>
		</div><!-- .entry-summary -->
		<?php
				if ( function_exists( 'sharing_display' ) ) {
					sharing_display( '', true );
				}
				 
				if ( class_exists( 'Jetpack_Likes' ) ) {
					$custom_likes = new Jetpack_Likes;
					echo $custom_likes->post_likes( '' );
				}
			?>
	</div><!-- .grid -->

	<div class="clear"></div>
</article>