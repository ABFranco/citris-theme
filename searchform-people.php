<?php
/*
 * Search form used on the People archive page.
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text">Search for:</span>
		<input type="search" class="search-field" placeholder="Search People ..." value="" name="s" title="Search People:" />
	</label>
	<input type="submit" class="search-submit" value="Search" />
	<input type="hidden" name="post_type" value="ctrs-people" />
</form>