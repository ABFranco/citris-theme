<article id="post-<?php the_ID(); ?>" <?php post_class( 'latest-news' ); ?>>
	<div class="featured-image">
	<a href="<?php the_permalink(); ?>">
	<?php if ( has_post_thumbnail() ) {
		the_post_thumbnail( 'thumbnail' );
	} ?>
	</a>
	
	</div><!-- .featured-image -->

	<header class="entry-header">
		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
	</header><!-- .entry-header -->

	<div class="entry-summary">
		<p><?php ctrs_excerpt( 130 ); ?></p>
	</div><!-- .entry-summary -->
</article>