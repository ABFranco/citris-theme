<?php

/**
 * Define functionality related to the Groups CPT.
 */
class CTRS_CPT_Groups {

	/**
	 * The CPT name.
	 *
	 * @var string
	 */
	public $post_type = 'ctrs-cpt-groups';

	/**
	 * The only instance of the CTRS_CPT_Groups CPT.
	 *
	 * @var CTRS_CPT_Groups
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_CPT_Groups
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_CPT_Groups;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_CPT_Groups
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for group related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CPT
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Add this group to the group taxonomy
		add_action( "publish_$this->post_type", array( $this, 'add_group_to_taxonomy' ), 10, 2 );

		// Remove group from taxonomy on deletion
		add_action( 'wp_trash_post', array( $this, 'remove_group_taxonomy' ) );
		add_action( 'delete_post', array( $this, 'remove_group_taxonomy' ) );

		// Handle our initiative filtering
		add_action( 'wp_ajax_ctrs_filter_initiatives', array( $this, 'filter_initiative' ) );
		add_action( 'wp_ajax_nopriv_ctrs_filter_initiatives', array( $this, 'filter_initiative' ) );
	}

	/**
	 * Register the CPT.
	 *
	 * @return  void
	 */
	public function register_post_type() {
		register_post_type(
			$this->post_type,
			array(
				'labels' => array(
					'name'               => 'Research Thrusts',
					'singular_name'      => 'Research Thrust',
					'add_new'            => 'Add New Research Thrust',
					'all_items'          => 'All Research Thrusts',
					'add_new_item'       => 'Add New Research Thrust',
					'edit_item'          => 'Edit Research Thrust',
					'new_item'           => 'New Research Thrusts',
					'view_item'          => 'View Research Thrust',
					'search_items'       => 'Search Research Thrusts',
					'not_found'          => 'No Research Thrusts found',
					'not_found_in_trash' => 'No Research Thrusts found in trash',
					'menu_name'          => 'Research Thrusts',
				),
				'public'              => true,
				'hierarchical'        => true,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'menu_position'       => 5,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
				'menu_icon'           => '',
			)
		);
	}

	/**
	 * Create a corresponding term in the group taxonomy.
	 *
	 * @param	int		$post_id	Post ID.
	 * @param	obj		$post		Post object.
	 * @return  void
	 */
	public function add_group_to_taxonomy( $post_id, $post ) {
		if ( $post->post_parent ) {
			$parent_title = get_the_title( $post->post_parent );
			$parent_title = str_replace( '&#038;', '&amp;', $parent_title );
			$parent_term = term_exists( $parent_title, 'ctrs-groups' );

			if ( 0 !== $parent_term && null !== $parent_term ) {
				if ( is_array( $parent_term ) ) {
					$parent_term = $parent_term['term_id'];
				}
			} else {
				$parent_term = 0;
			}
		} else {
			$parent_term = 0;
		}

		$post_title = str_replace( '&#038;', '&amp;', $post->post_title );
		$group_term = term_exists( $post_title, 'ctrs-groups' );

		if ( ! is_array( $group_term ) && ( 0 === $group_term || null === $group_term ) ) {
			$inserted_term = wp_insert_term( $post->post_title, 'ctrs-groups', array( 'slug' => $post->post_name, 'description' => $post_id, 'parent' => $parent_term ) );
		} else {
			wp_update_term( $group_term['term_id'], 'ctrs-groups', array( 'name' => $post->post_title, 'description' => $post_id, 'parent' => $parent_term ) );
		}
	}

	/**
	 * Remove Group from Group taxonomy on CPT deletion.
	 *
	 * @param	int  $post_id Post ID.
	 * @return  void
	 */
	public function remove_group_taxonomy( $post_id ) {
		if ( $this->post_type !== get_post_type( $post_id ) ) {
			return;
		}

		$post_title = get_the_title( $post_id );
		$post_title = str_replace( '&#038;', '&amp;', $post_title );
		$group_term = term_exists( $post_title, 'ctrs-groups' );

		if ( is_array( $group_term ) && ( 0 !== $group_term || null !== $group_term ) ) {
			wp_delete_term( $group_term['term_id'], 'ctrs-groups' );
		}
	}

	/**
	 * Filter the Initiatives that belong to a specific Group.
	 *
	 * @return void
	 */
	public function filter_initiative() {
		if ( isset( $_POST['group'] ) ) {
			$paged = $_POST['page'] ? intval( $_POST['page'] ) : 1;

			$project_args = array(
				'post_type'      => 'ctrs-projects',
				'posts_per_page' => 6,
				'post_status'    => 'publish',
				'paged'          => $paged,
			);

			if ( 'all' !== $_POST['group'] ) {
				$project_args['ctrs-groups'] = sanitize_text_field( $_POST['group'] );
			}

			$project_query = new WP_Query( $project_args );

			ob_start();

			if ( $project_query->have_posts() ) :
			while ( $project_query->have_posts() ) : $project_query->the_post(); ?>

				<div class="col-1-3">
					<?php get_template_part( 'content', 'archive' ); ?>
				</div><!-- end .col-1-3 -->

			<?php endwhile;
			else : ?>
				<div class="col-1-3">
					<article>
						<p>No Results Found</p>
					</article>
				</div><!-- end .col-1-3 -->
			<?php endif;

			$filtered_posts = ob_get_contents();
			ob_end_clean();

			wp_reset_postdata();

			echo $filtered_posts;
			die();
		} else {
			die();
		}
	}

}

CTRS_CPT_Groups::instance();