<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( (  is_single() ||  is_page())&& has_post_thumbnail() && 'ctrs-people' !== get_post_type() ) {
		the_post_thumbnail( 'large' );
	} ?>
	<header class="entry-header">
		<?php if ( is_single() ) { ?>
		<h1 class="entry-title">
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
		</h1>
		<?php
			if ( 'ctrs-people' === get_post_type() ) {
		?>
            <div class="entry-meta">
         <?php
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'large' );
            } else {
                echo get_avatar( 0, 140 );
            }
         ?>

                <span class="position"><?php echo esc_html( get_post_meta( $post->ID, '_ctrs_position', true ) ); ?>, <?php echo esc_html( get_post_meta( $post->ID, '_ctrs_department', true ) ); ?></span>
				<span class="phone"><?php echo esc_html( get_post_meta( $post->ID, '_ctrs_phone', true ) ); ?></span>
				<span class="url"><a href="<?php echo esc_url( get_post_meta( $post->ID, '_ctrs_url', true ) ); ?>">Website</a></span>
				<span class="email"><a href="mailto:<?php echo esc_attr( get_post_meta( $post->ID, '_ctrs_email', true ) ); ?>"><?php echo esc_html( get_post_meta( $post->ID, '_ctrs_email', true ) ); ?></a></span>
				<span class="address"><?php echo esc_html ( get_post_meta( $post->ID, '_ctrs_address', true ) ); ?></span>
            </div><!-- .entry-meta -->
		<?php
			} else { ?>
                <div class="entry-date"><?php the_time('M j, Y'); ?></div>
                <?php if ( has_tag() ) { ?>
                    <div class="entry-tags"><?php the_tags('tagged in: ', ', '); ?></div>
                <?php } ?>
			<?php } ?>
		<?php
		} else { ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php }
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
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
</article>