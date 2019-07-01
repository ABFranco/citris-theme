<?php

/**
 * Sets up the customizer.
 *
 */
class CTRS_Customizer {

	/**
	 * The only instance of the CTRS_Customizer object.
	 *
	 * @var CTRS_Customizer
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Customizer
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Customizer;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Customizer
	 */
	private function __construct() {}

	/**
	 * Add actions to create the new button.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'wp_head', array( $this, 'group_styles' ) );
	}

	/**
	 * Register our section, settings, and controls.
	 *
	 * @return  void
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_section( 'ctrs_group_colors', array(
			'title'    => 'Group Colors',
			'priority' => 45,
		) );
		$wp_customize->add_section( 'ctrs_group_icons', array(
			'title'    => 'Group Icons',
			'priority' => 55,
		) );

		$customize = array();
		$terms = get_terms( 'ctrs-groups', array( 'hide_empty' => 0 ) );

		foreach ( $terms as $term ) {
			$customize[] = array(
				'slug'    => $term->slug,
				'default' => '',
				'label'   => $term->name
			);
		}

		// Add all color settings
		foreach ( $customize as $color ) {
			$wp_customize->add_setting( 'ctrs_term_colors['. $color['slug'] .']', array(
				'default'    => $color['default'],
				'type'       => 'option',
				'capability' => 'edit_theme_options',
			) );

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					$color['slug'],
					array(
						'label'    => $color['label'],
						'section'  => 'ctrs_group_colors',
						'settings' => 'ctrs_term_colors['. $color['slug'] .']'
					)
				)
			);
		}

		// Add all icon settings
		foreach ( $customize as $icon ) {
			$wp_customize->add_setting( 'ctrs_term_icons['. $icon['slug'] .']', array(
				'default'    => $icon['default'],
				'type'       => 'option',
				'capability' => 'edit_theme_options',
			) );

			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					$icon['slug'] . '_icon',
					array(
						'label'    => $icon['label'],
						'section'  => 'ctrs_group_icons',
						'settings' => 'ctrs_term_icons['. $icon['slug'] .']'
					)
				)
			);
		}

		// Remove sections we don't want
		$wp_customize->remove_section( 'title_tagline');
		$wp_customize->remove_section( 'nav');
		$wp_customize->remove_section( 'static_front_page');
	}

	/**
	 * Styles the Group terms displayed on the site.
	 *
	 * @return  void
	 */
	function group_styles() {
		$colors = get_option( 'ctrs_term_colors' );
		$terms = get_terms( 'ctrs-groups', array( 'hide_empty' => 0 ) );

		// If no custom options for text are set, let's bail.
		if ( ! $colors ) {
			return;
		}
		// If we get this far, we have custom styles.
		?>
		<style type="text/css">
		<?php foreach ( $terms as $term ) : ?>
			<?php if ( isset( $colors[ $term->slug ] ) && '' !== trim( $colors[ $term->slug ] ) ) : ?>
				.term-<?php echo esc_attr( $term->slug ); ?> .site-main .overview,
				.term-<?php echo esc_attr( $term->slug ); ?> .site-main .overview .col-1-3,
				.term-<?php echo esc_attr( $term->slug ); ?> .secondary-navigation,
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .entry-header,
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .overview,
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .events ul li a .date,
				.page .news .post > a .group.<?php echo esc_attr( $term->slug ); ?>,
				.tax-ctrs-campus .widget-area .related-news .col-1-2 .featured-image .group.<?php echo esc_attr( $term->slug ); ?>,
				.tax-ctrs-groups .widget-area .related-news .col-1-2 .featured-image .group.<?php echo esc_attr( $term->slug ); ?> {
					background-color: <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
				}
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .entry-header:after {
					border-left-color: <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
				}
				.term-<?php echo esc_attr( $term->slug ); ?> .list .entry-header a,
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .entry-people ul li a,
				.single-ctrs-projects .<?php echo esc_attr( $term->slug ); ?> .events h3 {
					color: <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
				}
				.home-news article.<?php echo esc_attr( $term->slug ); ?>:hover .featured-image {
					-webkit-box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
					-moz-box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
					-ms-box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
					-o-box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
					-box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
					box-shadow: inset 0 -5px 0 0 <?php echo esc_attr( $colors[ $term->slug ] ); ?>;
				}
			<?php endif; ?>
		<?php endforeach; ?>
		</style>
		<?php
	}

}

CTRS_Customizer::instance();