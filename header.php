<?php
/**
 * The template for displaying the header.
 *
 * @package Citris
 * @since 0.1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner">
		<div class="col-full">
			<a class="site-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo get_template_directory_uri() . '/images/logo.png'; ?>">
			</a><!-- .site-branding -->

			<nav id="site-navigation" class="main-navigation" role="navigation">
				<h2 class="menu-toggle">Menu</h2>
				<div class="skip-link"><a class="screen-reader-text" href="#content">Skip to content</a></div>

				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container_class' => 'nav-menu-container cf' ) ); ?>

				<div class="menu-extra cf">
					<?php get_search_form(); ?>
				</div>
			</nav><!-- #site-navigation -->
		</div><!-- .col-full -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
	<?php
	if ( is_page() || is_tax( 'ctrs-groups' ) ) {
		if ( is_page() ) {
			$content_id = get_the_ID();
		} else {
			$queried_object = get_queried_object();
			$current_group = single_term_title( '', false );
			if ( preg_match( '([0-9]+)', term_description(), $matches ) ) {
				$content_id = (int) $matches[0];
			} else {
				$content_id = 0;
			}
		}

		if ( $nav_menu = get_post_meta( $content_id, '_ctrs_menu', true ) ) {
			$nav_menu = wp_get_nav_menu_object( $nav_menu );
		?>
			<nav id="initiative-navigation" class="secondary-navigation">
                <?php wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu ) ); ?>
			</nav>
		<?php }
	}
	?>