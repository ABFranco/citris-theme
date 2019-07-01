<?php

/**
 * Define functionality related to the Projects CPT.
 */
class CTRS_Projects {

	/**
	 * The CPT name.
	 *
	 * @var string
	 */
	public $_post_type = 'ctrs-projects';

	/**
	 * The only instance of the CTRS_Projects CPT.
	 *
	 * @var CTRS_Projects
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Projects
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Projects;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Projects
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for projects related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CPT
		add_action( 'init', array( $this, 'register_post_type' ) );

		add_action( "save_post_{$this->_post_type}", array( $this, 'add_campus' ), 99, 3 );
		add_action( "save_post_{$this->_post_type}", array( $this, 'save_primary_initiative_meta_box' ), 10, 2 );

		add_filter( 'pre_get_posts', array( $this, 'modify_archive_query' ) );

		// Handle our project filtering
		add_action( 'wp_ajax_ctrs_filter_project', array( $this, 'filter_group' ) );
		add_action( 'wp_ajax_nopriv_ctrs_filter_project', array( $this, 'filter_group' ) );
	}

	/**
	 * Register the CPT.
	 *
	 * @return  void
	 */
	public function register_post_type() {
		register_post_type(
			$this->_post_type,
			array(
				'labels' => array(
					'name'               => 'Projects',
					'singular_name'      => 'Project',
					'add_new'            => 'Add New Project',
					'all_items'          => 'All Projects',
					'add_new_item'       => 'Add New Project',
					'edit_item'          => 'Edit Project',
					'new_item'           => 'New Project',
					'view_item'          => 'View Project',
					'search_items'       => 'Search Projects',
					'not_found'          => 'No projects found',
					'not_found_in_trash' => 'No projects found in trash',
					'menu_name'          => 'Projects',
				),
				'public'        => true,
				'show_ui'       => true,
				'menu_position' => 5,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'register_meta_box_cb' => array( $this, 'setup_metabox' ),
				'has_archive'   => 'projects',
				'menu_icon'     => '',
				'rewrite'       => array(
					'slug'       => '%project_group%/project',
					'with_front' => false,
				),
				'taxonomies'  => array( 'ctrs-groups', 'ctrs-technologies', 'ctrs-tax-people', 'ctrs-campus' ),
			)
		);
	}

	/**
	 * Setup the metabox for this post type.
	 *
	 * @param	object	$post	Post object.
	 * @return  void
	 */
	public function setup_metabox( $post ) {
		add_meta_box( 'ctrs-primary-initiative', 'Primary Initiative', array( $this, 'render_metabox' ), $this->_post_type, 'side', 'default' );
	}

	/**
	 * Render the metabox for this post type.
	 *
	 * @param	object	$post	Post object.
	 * @return  void
	 */
	public function render_metabox( $post ) {
		$primary_initiative = get_post_meta( $post->ID, '_ctrs_primary_initiative', true ) ? get_post_meta( $post->ID, '_ctrs_primary_initiative', true ) : '';
		$terms = get_terms( 'ctrs-groups', array( 'hide_empty' => 0 ) );
	?>

			<p>
				<select id="ctrs-primary-initiative" name="ctrs-primary-initiative">
					<option value="none"<?php selected( $primary_initiative, 'none', false ); ?>>None</option>
				<?php foreach ( $terms as $term ) {
					echo '<option value="' . $term->term_id . '"'
						. selected( $primary_initiative, $term->term_id, false )
						. '>'. $term->name . '</option>';
				} ?>
				</select>
			</p>
			<small>If desired, select the primary initiative used for this project.</small>

		<?php
		wp_nonce_field( 'save', 'ctrs-primary-initiative-meta-box' );
	}

	/**
	 * Save the meta box for the primary initiative.
	 *
	 * @param  int    $post_id Current post ID
	 * @param  object $post    Current post object.
	 * @return void
	 */
	public function save_primary_initiative_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['ctrs-primary-initiative-meta-box'] ) || ! wp_verify_nonce( $_POST['ctrs-primary-initiative-meta-box'], 'save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Menu ID
		if ( isset( $_POST['ctrs-primary-initiative'] ) && '' !== trim( $_POST['ctrs-primary-initiative'] ) ) {
			update_post_meta( $post_id, '_ctrs_primary_initiative', absint( $_POST['ctrs-primary-initiative'] ) );
		} else {
			delete_post_meta( $post_id, '_ctrs_primary_initiative' );
		}
	}

	/**
	 * Add the correct campus terms to the campus taxonomy.
	 *
	 * @param	int		$post_id	Post ID.
	 * @param	obj		$post		Post object.
	 * @param	bool	$update		Whether post has been updated.
	 * @return  void
	 */
	public function add_campus( $post_id, $post, $update ) {
		// check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		$people_terms = wp_get_object_terms( $post_id, 'ctrs-tax-people', array( 'fields' => 'names' ) );

		if ( ! empty( $people_terms ) && ! is_wp_error( $people_terms ) ) {
			$campus = array();

			foreach ( $people_terms as $person ) {
				$post = get_page_by_title( $person, OBJECT, 'ctrs-people' );

				if ( $post && null !== $post ) {
					$campus_terms = wp_get_object_terms( $post->ID, 'ctrs-campus', array( 'fields' => 'names' ) );

					if ( ! empty( $campus_terms ) && ! is_wp_error( $campus_terms ) ) {
						$campus = array_merge( $campus, $campus_terms );
					}
				}
			}

			if ( ! empty( $campus ) ) {
				wp_set_object_terms( $post_id, array_unique( $campus ), 'ctrs-campus' );
			} else {
				$current_campuses = wp_get_object_terms( $post_id, 'ctrs-campus', array( 'fields' => 'ids' ) );
				if ( ! empty( $current_campuses ) && ! is_wp_error( $current_campuses ) ) {
					wp_remove_object_terms( $post_id, $current_campuses, 'ctrs-campus' );
				}
			}
		} else {
			$current_campuses = wp_get_object_terms( $post_id, 'ctrs-campus', array( 'fields' => 'ids' ) );
			if ( ! empty( $current_campuses ) && ! is_wp_error( $current_campuses ) ) {
				wp_remove_object_terms( $post_id, $current_campuses, 'ctrs-campus' );
			}
		}
	}

	/**
	 * Make sure Campus tax archives pages only show people.
	 *
	 * @param	object	$query	The main query.
	 * @return	object			Modified query.
	 */
	function modify_archive_query( $query ) {
		if ( $query->is_main_query() && is_post_type_archive( $this->_post_type ) && ! is_admin() ) {
			$query->set( 'posts_per_page', 9 );
		}
	}

	/**
	 * Filter the Projects that belong to a specific Group.
	 *
	 * @return void
	 */
	public function filter_group() {
		if ( isset( $_POST['group'] ) ) {
			$paged = $_POST['page'] ? intval( $_POST['page'] ) : 1;

			$project_args = array(
				'post_type'      => 'ctrs-projects',
				'posts_per_page' => 9,
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

CTRS_Projects::instance();