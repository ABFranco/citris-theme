<?php

/**
 * Define functionality related to the Technologies CTT.
 */
class CTRS_Technologies {

	/**
	 * The CTT name.
	 *
	 * @var string
	 */
	public $tax_name = 'ctrs-technologies';

	/**
	 * The only instance of the CTRS_Technologies CTT.
	 *
	 * @var CTRS_Technologies
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Technologies
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Technologies;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Technologies
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for technology related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CTT
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the CTT.
	 *
	 * @return  void
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->tax_name,
			array( 'post', 'ctrs-projects', 'ctrs-people' ),
			array(
				'labels' => array(
					'name'          => 'Technologies',
					'singular_name' => 'Technology',
					'search_items'  => 'Search Technologies',
					'all_items'     => 'All Technologies',
					'edit_item'     => 'Edit Technology',
					'update_item'   => 'Update Technology',
					'add_new_item'  => 'Add New Technology',
					'new_item_name' => 'New Technology Name',
					'menu_name'     => 'Technology',
				),
				'public'            => false,
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'       => 'technology',
					'with_front' => false
				)
			)
		);
	}

}

CTRS_Technologies::instance();