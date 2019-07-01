<?php

/**
 * Adds ability to curate content to certain areas.
 *
 */
class CTRS_Curation {

	/**
	 * The only instance of the CTRS_Curation object.
	 *
	 * @var CTRS_Curation
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Curation
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Curation;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  PM_Curation
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for Curation related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ctrs_curation', array( $this, 'process_suggest' ) );
	}

	/**
	 * Enqueue the scripts we need.
	 *
	 * @param   string	$hook_suffix	Current page we are on.
	 * @return  void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'post' === get_post_type() || 'tribe_events' === get_post_type() && ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) ) {
			wp_enqueue_script( 'ctrs-curation', get_stylesheet_directory_uri() . '/assets/js/citris.admin.min.js', array( 'suggest' ), '1.0', true );
			wp_localize_script( 'ctrs-curation', 'ctrs_curation',
				array(
					'ajaxurl' => add_query_arg(
						array(
							'action' => 'ctrs_curation',
						),
						wp_nonce_url( 'admin-ajax.php', 'ctrs-curation' )
					)
				)
			);
		}
	}

	/**
	 * Handle the ajax request for the suggest box.
	 *
	 * @return  void
	 */
	public function process_suggest() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'ctrs-curation' ) )
			return;

		if ( empty( $_REQUEST['q'] ) )
			die();

		$search = sanitize_text_field( strtolower( $_REQUEST['q'] ) );
		$posts = $this->search_posts( $search );

		if ( $posts ) {
			foreach ( $posts as $post ) {
				echo trim( esc_html( strip_tags( get_the_title( $post ) ) ) ) . " | " . $post->ID . "\n";
			}
		}

		die();
	}

	/**
	 * Perform a search on all content types.
	 *
	 * @param   string	$search		Search term.
	 * @return  array				Content search found.
	 */
	public function search_posts( $search = '' ) {
		$query = array(
			's' => $search,
			'sentence' => true,
			'post_type' => 'ctrs-projects',
			'suppress_filters' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'publish',
			'order' => 'DESC',
			'orderby' => 'post_date',
			'posts_per_page' => 20,
		);

		$get_posts = new WP_Query;
		$found_posts = $get_posts->query( $query );

		// Check if any posts were found.
		if ( ! $get_posts->post_count )
			return false;

		return (array) $found_posts;
	}

}

CTRS_Curation::instance();