<?php

/**
 * Handle the theme option page.
 *
 */
class CTRS_Theme_Options {

	/**
	 * The only instance of the CTRS_Theme_Options object.
	 *
	 * @var CTRS_Theme_Options
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Theme_Options
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Theme_Options;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Theme_Options
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for theme option related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		add_action( 'admin_menu', array( $this, 'theme_options_add_page' ) );
		add_action( 'admin_init', array( $this, 'theme_options_init' ) );
	}

	/**
	 * Add our theme options page to the admin menu.
	 *
	 * @return  void
	 */
	public function theme_options_add_page() {
		$theme_page = add_theme_page( 'Theme Options', 'Theme Options', 'edit_theme_options', 'theme_options', array( $this, 'theme_options_render_page' ) );
	}

	/**
	 * Renders the Theme Options administration screen.
	 */
	public function theme_options_render_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<?php $theme_name = function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme(); ?>
			<h2><?php printf( '%s Theme Options', $theme_name ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'citris_options' );
					do_settings_sections( 'theme_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the form setting for our citris_options array.
	 *
	 * This call to register_setting() registers a validation callback, theme_options_validate(),
	 * which is used when the option is saved, to ensure that our option values are properly
	 * formatted, and safe.
	 *
	 * @return  void
	 */
	public function theme_options_init() {
		register_setting( 'citris_options', 'citris_theme_options', array( $this, 'theme_options_validate' ) );

		// Register our settings field groups
		add_settings_section( 'misc', 'Miscellaneous', '', 'theme_options' );

		// Register our individual settings fields
		$options_fields = $this->options_fields();
		foreach ( $options_fields as $field ) {
			add_settings_field( $field['id'], $field['label'], $field['callback'], $field['page'], $field['section'], array(
				'id'	=> $field['id'],
				'name'	=> 'citris_theme_options[' . $field['id'] . ']',
				'desc'	=> $field['desc'],
			) );
		}
	}

	/**
	 * Build our option field array.
	 *
	 * @return  array
	 */
	public function options_fields() {
		return array(
			'citris_slide' => array(
				'id'       => 'citris_slide',
				'label'    => 'Citris Slide Text',
				'callback' => array( $this, 'options_text' ),
				'page'     => 'theme_options',
				'section'  => 'misc',
				'desc'     => 'The overview text shown in the Citris slide nav.',
				'type'     => 'text'
			),
		);
	}

	/**
	 * Renders the text setting field.
	 *
	 * @return  void
	 */
	public function options_text( $args ) {
	?>
		<input class="widefat" type="text" name="<?php esc_attr_e( $args['name'] ); ?>" id="<?php esc_attr_e( $args['id'] ); ?>" value="<?php echo esc_attr( $this->get_theme_options( $args['id'] ) ); ?>" />

		 &nbsp; <span class="description"><?php esc_html_e( $args['desc'] ); ?></span>
	<?php
	}

	/**
	 * Returns the options array.
	 *
	 * @return array
	 */
	function get_theme_options( $field ) {
		$saved = (array) get_option( 'citris_theme_options' );
		$defaults = array(
			'citris_slide' => '',
		);

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		$return = $options[$field] ? $options[$field] : false;

		return $return;
	}

	/**
	 * Sanitize and validate form input. Accepts an array, return a sanitized array.
	 *
	 * @param array $input Unknown values.
	 * @return array Sanitized theme options ready to be stored in the database.
	 */
	function theme_options_validate( $input ) {
		$output = array();

		$options_fields = $this->options_fields();
		foreach ( $options_fields as $field ) {
			switch( $field['type'] ) {
				case 'text' :
					if ( isset( $input[$field['id']] ) && ! empty( $input[$field['id']] ) ) {
						$output[$field['id']] = sanitize_text_field( $input[$field['id']] );
					}
				break;
			}
		}

		return apply_filters( 'citris_theme_options_validate', $output, $input );
	}

}

CTRS_Theme_Options::instance();