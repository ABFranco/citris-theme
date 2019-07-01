<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) {
		the_post_thumbnail( 'projects' );
	} else {
		echo '<img src="'. get_template_directory_uri() .'/images/projects.jpg">';
	} ?>
	<header class="entry-header">
		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
	</header><!-- .entry-header -->
	<div class="entry-summary">
		<?php ctrs_excerpt( 110 ); ?>
	</div><!-- .entry-summary -->
</article>