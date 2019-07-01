<?php
/**
 * The template for displaying the footer.
 *
 * @package Citris
 * @since 0.1.0
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="col-full">
			<div class="site-info">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo get_template_directory_uri() . '/images/logo-footer.png'; ?>">
				</a>
				<span>Copyright &copy; CITRIS <?php echo date( 'Y' ); ?></span>
			</div><!-- .site-info -->
			<nav id="footer-navigation" class="footer-navigation" role="navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'footer' ) ); ?>
			</nav><!-- #site-navigation -->
		</div><!-- .col-full -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
