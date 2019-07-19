<?php

/**
 * Return an excerpt with a given length.
 *
 * @param	int		$charlength		The length of excerpt we want.
 * @return	void
 */
function ctrs_excerpt( $charlength, $text = false ) {
	if ( $text ) {
		$excerpt = $text;
	} else {
		$excerpt = get_the_excerpt();
	}
	$charlength++;

	if ( mb_strlen( $excerpt ) > $charlength ) {
		$subex = mb_substr( $excerpt, 0, $charlength - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if ( $excut < 0 ) {
			echo wp_strip_all_tags( mb_substr( $subex, 0, $excut ) );
		} else {
			echo wp_strip_all_tags( $subex );
		}
		echo '...';
	} else {
		echo wp_strip_all_tags( $excerpt );
	}
}

/**
 * Display navigation to next/previous set of posts when applicable.
 * Notes: this was a newer version of this function that someone else created
 * However this removes the option to view the page numbers
 * 
 * @return void
 */
/*
function ctrs_paging_nav($query) {
    if(isset($query)) {
        $wp_query = $query;
    } else {
        global $wp_query;
    }

	// Don't print empty markup if there's only one page.
	if ( $wp_query->max_num_pages < 2 )
		return;

	if ( is_post_type_archive( 'ctrs-people' ) ) {
		$screen_reader = 'People';
		$next = 'More people';
		$prev = 'Previous people';
	} elseif ( is_post_type_archive( 'ctrs-projects' ) || is_tax( 'ctrs-groups' ) ) {
		$screen_reader = 'Projects';
		$next = 'Older projects';
		$prev = 'Newer projects';
	} else {
		$screen_reader = 'Posts';
		$next = 'Older posts';
		$prev = 'Newer posts';
	}
	?>
	<nav class="navigation paging-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php echo esc_html( $screen_reader ); ?> navigation</h1>
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( '<span class="meta-nav">&larr;</span>'. esc_html( $next ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( ''. esc_html( $prev ) .' <span class="meta-nav">&rarr;</span>' ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}*/

/**
 * Sort an array by the order field.
 *
 * @param	array	$a	The first value.
 * @param	array	$b	The second value.
 * @return	int
 */
function ctrs_cmp( $a, $b ) {
	if ( $a['order'] == $b['order'] ) {
		return 0;
	}
	return ( $a['order'] < $b['order'] ) ? -1 : 1;
}

/**
 * Get the post thumbnail src
 *
 * Handy for passing a URL to Pinterest
 *
 * @return string|bool Src if available, else false
 */
function ctrs_get_post_thumbnail_src() {
	$thumb_id = get_post_thumbnail_id();
	$src = wp_get_attachment_image_src( $thumb_id, 'large' );
	if ( ! empty( $src[0] ) ) {
		return esc_url( $src[0] );
	}
	return false;
}

/**
 * Fetch and echo sharing links
 * (deprecated function replaced by Jetpack's social share buttton)
 * @return void
 */
function ctrs_social_share() {
	$title = rawurlencode( html_entity_decode( str_replace( '&nbsp;', ' ', get_the_title() ) ) );
?>

	<div class="share-this">
		<a href="#" class="share-this-btn"><span>Share</span></a>
		<div class="share-this-links">
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>&amp;p=<?php echo esc_attr( $title ); ?>" class="share-popup share-facebook"><i class="icon icon-gallery_facebook"></i></a>
			<a href="http://pinterest.com/pin/create/link/?url=<?php echo urlencode( get_permalink() ); ?>&amp;media=<?php echo urlencode( ctrs_get_post_thumbnail_src() ); ?>&amp;description=<?php echo esc_attr( $title ); ?>" class="share-popup share-pinterest"><i class="icon icon-gallery_pinterest"></i></a>
			<a href="https://twitter.com/share?text=<?php echo esc_attr( $title ); ?>&amp;url=<?php echo urlencode( get_permalink() ); ?>&amp;related=InStyle" class="share-popup share-twitter"><i class="icon icon-twitter"></i></a>
			<a href="https://plus.google.com/share?url=<?php echo urlencode( get_permalink() ); ?>" class="share-popup share-googleplus"><i class="icon icon-googleplus"></i></a>
			<a href="mailto:?subject=<?php echo esc_attr( $title ); ?>&amp;body=<?php echo urlencode( get_permalink() ); ?>" class="share-via-email"><i class="icon icon-email"></i></a>
		</div><!-- .share-this-links -->
	</div><!-- .share-this -->

<?php
}

/**
 * Display navigation to next/previous set of posts when applicable.
 * Note: was previously commented out, added back in to allow pages for function calls
 * that required no arguments
 * Additionally, this version shows page numbers unlike the other version.
 * Uncommented out (and edited) to stop certain errors some occurring in the logs.
 * @return void
 */
function ctrs_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'base'      => $pagenum_link,
		'format'    => $format,
		'total'     => $GLOBALS['wp_query']->max_num_pages,
		'current'   => $paged,
		'mid_size'  => 5,
		'add_args'  => array_map( 'urlencode', $query_args ),
		'prev_text' => '&larr; Previous',
		'next_text' => 'Next &rarr;',
	) );

	if ( $links ) :

	?>
	<nav class="navigation paging-navigation" role="navigation">
		<div class="pagination loop-pagination">
			<?php echo $links; ?>
		</div><!-- .pagination -->
	</nav><!-- .navigation -->
	<?php
	endif;
}