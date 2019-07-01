<?php

/**
 * Handle meta boxes that are used on multiple content types.
 *
 */
class CTRS_Meta_Boxes {

	private $_blacklisted_post_types = array(
		'ctrs-people',
		'ctrs-projects',
		'ctrs-cpt-groups'
	);

	/**
	 * The only instance of the CTRS_Meta_Boxes object.
	 *
	 * @var CTRS_Meta_Boxes
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Meta_Boxes
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Meta_Boxes;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Meta_Boxes
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for meta box related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		add_action( 'add_meta_boxes_page', array( $this, 'add_menu_meta_box' ) );
		add_action( 'add_meta_boxes_ctrs-cpt-groups', array( $this, 'add_menu_meta_box' ) );

		// Queues the metabox save actions
		add_action( 'save_post', array( $this, 'save_alternates_meta_box' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_menu_meta_box' ), 10, 2 );
	}

	/**
	 * Add Meta Boxes.
	 *
	 * @param   string          $post_type          Current post type name.
	 * @param   object          $post               Current post object.
	 * @return  void
	 */
	public function add_meta_boxes( $post_type, $post ) {
		if ( in_array( $post_type, $this->_blacklisted_post_types ) ) {
			return;
		}

		add_meta_box( 'ctrs-project-association', 'Project Assocation', array( $this, 'display_project_meta_box' ), $post_type, 'normal', 'high' );
	}

	/**
	 * Add Menu Meta Box.
	 *
	 * @param   object $post Current post object.
	 * @return  void
	 */
	public function add_menu_meta_box( $post ) {
		add_meta_box( 'ctrs-menu', 'Menu', array( $this, 'display_menu_meta_box' ), $post->post_type, 'side', 'low' );
	}

	/**
	 * Display the alternates meta box.
	 *
	 * @param   object          $post               Current post object.
	 * @param   array           $args               Arguments passed to meta box.
	 * @return  void
	 */
	public function display_project_meta_box( $post, $args ) {
		$project    = get_post_meta( $post->ID, '_ctrs_project', true ) ? get_post_meta( $post->ID, '_ctrs_project', true ) : '';
		$project_id = get_post_meta( $post->ID, '_ctrs_project_id', true ) ? get_post_meta( $post->ID, '_ctrs_project_id', true ) : '';
	?>

		<table width="100%" border="0" style="border-spacing: 1em;">
			<tr>
				<td valign="top" width="70">
					<label for="ctrs-project"><strong>Project</strong></label>
				</td>
				<td valign="top">
					<input class="widefat curate" type="text" name="ctrs-project" id="ctrs-project" value="<?php echo esc_attr( $project ); ?>" />
					<input id="ctrs-project-id" type="hidden" name="ctrs-project-id" value="<?php echo esc_attr( $project_id ); ?>" />
				</td>
			</tr>
		</table>

	<?php
		wp_nonce_field( 'save', 'ctrs-project-meta-box' );
	}

	/**
	 * Save the meta box for headings.
	 *
	 * @param   int             $post_id            Current post ID
	 * @param   object          $post               Current post object.
	 * @return  void
	 */
	public function save_alternates_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['ctrs-project-meta-box'] ) || ! wp_verify_nonce( $_POST['ctrs-project-meta-box'], 'save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Project Name
		if ( isset( $_POST['ctrs-project'] ) && '' !== trim( $_POST['ctrs-project'] ) ) {
			update_post_meta( $post_id, '_ctrs_project', sanitize_text_field( $_POST['ctrs-project'] ) );
		} else {
			delete_post_meta( $post_id, '_ctrs_project' );
		}

		// Save Project ID
		if ( isset( $_POST['ctrs-project-id'] ) && is_numeric( $_POST['ctrs-project-id'] ) ) {
			update_post_meta( $post_id, '_ctrs_project_id', (int) $_POST['ctrs-project-id'] );
		} else {
			delete_post_meta( $post_id, '_ctrs_project_id' );
		}
	}

	/**
	 * Display the menu meta box.
	 *
	 * @param   object $post Current post object.
	 * @return  void
	 */
	public function display_menu_meta_box( $post ) {
		$nav_menu = get_post_meta( $post->ID, '_ctrs_menu', true ) ? get_post_meta( $post->ID, '_ctrs_menu', true ) : '';
		$menus    = wp_get_nav_menus( array( 'orderby' => 'name' ) );

		// If no menus exists, direct the user to go and create some.
		if ( ! $menus ) {
			echo '<p>'. sprintf( 'No menus have been created yet. <a href="%s">Create some</a>.', admin_url( 'nav-menus.php' ) ) .'</p>';
		} else { ?>
			<p>
				<label for="ctrs-menu">Select Menu: </label>
				<select id="ctrs-menu" name="ctrs-menu">
					<option value="none"<?php selected( $nav_menu, 'none', false ); ?>>None</option>
				<?php foreach ( $menus as $menu ) {
					echo '<option value="' . $menu->term_id . '"'
						. selected( $nav_menu, $menu->term_id, false )
						. '>'. $menu->name . '</option>';
				} ?>
				</select>
			</p>
			<small><?php echo sprintf( 'Create a new <a href="%s">menu</a>.', admin_url( 'nav-menus.php' ) ); ?></small>
		<?php }
		wp_nonce_field( 'save', 'ctrs-menu-meta-box' );
	}

	/**
	 * Save the meta box for the menu.
	 *
	 * @param  int    $post_id Current post ID
	 * @param  object $post    Current post object.
	 * @return void
	 */
	public function save_menu_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['ctrs-menu-meta-box'] ) || ! wp_verify_nonce( $_POST['ctrs-menu-meta-box'], 'save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Menu ID
		if ( isset( $_POST['ctrs-menu'] ) && '' !== trim( $_POST['ctrs-menu'] ) ) {
			update_post_meta( $post_id, '_ctrs_menu', absint( $_POST['ctrs-menu'] ) );
		} else {
			delete_post_meta( $post_id, '_ctrs_menu' );
		}
	}

}

CTRS_Meta_Boxes::instance();