<?php

/**
 * Define functionality related to the People CPT.
 */
class CTRS_People {

	/**
	 * The CPT name.
	 *
	 * @var string
	 */
	public $post_type = 'ctrs-people';

	/**
	 * The only instance of the CTRS_People CPT.
	 *
	 * @var CTRS_People
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_People
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_People;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_People
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for people related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CPT
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Register query var
		add_filter( 'query_vars', array( $this, 'register_query_var' ) );

		// Load template
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		// Add this person to the people taxonomy
		add_action( "publish_$this->post_type", array( $this, 'add_person_to_taxonomy' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_person_meta_box' ), 10, 2 );

		// Remove group from taxonomy on deletion
		add_action( 'wp_trash_post', array( $this, 'remove_person_taxonomy' ) );
		add_action( 'delete_post', array( $this, 'remove_person_taxonomy' ) );

		add_filter( 'pre_get_posts', array( $this, 'modify_tax_query' ) );
		add_filter( 'the_posts', array( $this, 'move_sticky_to_top' ) );
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
					'name'               => 'People',
					'singular_name'      => 'People',
					'add_new'            => 'Add New Person',
					'all_items'          => 'All People',
					'add_new_item'       => 'Add New Person',
					'edit_item'          => 'Edit Person',
					'new_item'           => 'New Person',
					'view_item'          => 'View Person',
					'search_items'       => 'Search People',
					'not_found'          => 'No people found',
					'not_found_in_trash' => 'No people found in trash',
					'menu_name'          => 'People',
				),
				'public'        => true,
				'show_ui'       => true,
				'hierarchical'  => true,
				'menu_position' => 5,
				'supports'      => array( 'title', 'excerpt', 'editor', 'thumbnail', 'custom-fields', 'page-attributes' ),
				'register_meta_box_cb' => array( $this, 'setup_metabox' ),
				'has_archive'   => 'people',
				'menu_icon'     => '',
				'rewrite'       => array(
					'slug'       => 'person',
					'with_front' => false,
				),
				'taxonomies'  => array( 'ctrs-campus', 'ctrs-groups', 'ctrs-technologies' ),
			)
		);

		add_rewrite_rule( 'people/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?person_type=$matches[1]&paged=$matches[2]', 'top' );
		add_rewrite_rule( 'people/([^/]+)?/?$', 'index.php?person_type=$matches[1]', 'top' );
	}

	/**
	 * Register custom query vars needed for people type archives.
	 *
	 * @var		array	$vars	Array of current query vars.
	 * @return	array
	 */
	public function register_query_var( $vars ) {
		$vars[] = 'person_type';

		return $vars;
	}

	/**
	 * Load correct template based on query var.
	 *
	 * @return	void
	 */
	public function template_redirect() {
		if ( get_query_var( 'person_type' ) && '' !== trim( get_query_var( 'person_type' ) ) ) {
			include( get_template_directory() . '/archive-person-type.php' );
			exit();
		}
	}

	/**
	 * Setup the metabox for this post type.
	 *
	 * @param	object	$post	Post object.
	 * @return  void
	 */
	public function setup_metabox( $post ) {
		add_meta_box( 'ctrs-people-meta', 'Person Information', array( $this, 'render_metabox' ), $this->post_type, 'normal', 'high' );
	}

	/**
	 * Render the metabox for this post type.
	 *
	 * @param	object	$post	Post object.
	 * @return  void
	 */
	public function render_metabox( $post ) {
		$honorific  = get_post_meta( $post->ID, '_ctrs_honorific', true ) ? get_post_meta( $post->ID, '_ctrs_honorific', true ) : '';
		$fname      = get_post_meta( $post->ID, '_ctrs_fname', true ) ? get_post_meta( $post->ID, '_ctrs_fname', true ) : '';
		$lname      = get_post_meta( $post->ID, '_ctrs_lname', true ) ? get_post_meta( $post->ID, '_ctrs_lname', true ) : '';
		$suffix     = get_post_meta( $post->ID, '_ctrs_suffix', true ) ? get_post_meta( $post->ID, '_ctrs_suffix', true ) : '';
		$position   = get_post_meta( $post->ID, '_ctrs_position', true ) ? get_post_meta( $post->ID, '_ctrs_position', true ) : '';
		$leadership = get_post_meta( $post->ID, '_ctrs_leadership', true ) ? get_post_meta( $post->ID, '_ctrs_leadership', true ) : '';
		$researcher = get_post_meta( $post->ID, '_ctrs_researcher', true ) ? get_post_meta( $post->ID, '_ctrs_researcher', true ) : '';
		$staff      = get_post_meta( $post->ID, '_ctrs_staff', true ) ? get_post_meta( $post->ID, '_ctrs_staff', true ) : '';
		$department = get_post_meta( $post->ID, '_ctrs_department', true ) ? get_post_meta( $post->ID, '_ctrs_department', true ) : '';
		$phone      = get_post_meta( $post->ID, '_ctrs_phone', true ) ? get_post_meta( $post->ID, '_ctrs_phone', true ) : '';
		$email      = get_post_meta( $post->ID, '_ctrs_email', true ) ? get_post_meta( $post->ID, '_ctrs_email', true ) : '';
		$url        = get_post_meta( $post->ID, '_ctrs_url', true ) ? get_post_meta( $post->ID, '_ctrs_url', true ) : '';
		$address    = get_post_meta( $post->ID, '_ctrs_address', true ) ? get_post_meta( $post->ID, '_ctrs_address', true ) : '';
		$exclude    = get_post_meta( $post->ID, '_ctrs_exclude', true ) ? get_post_meta( $post->ID, '_ctrs_exclude', true ) : '';
	?>

		<table width="100%" border="0" style="border-spacing: 1em;">
			<tr>
				<td valign="top">
					<label for="ctrs-honorific"><strong>Honorific</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-honorific" id="ctrs-honorific" size="30" value="<?php echo esc_attr( $honorific ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top" width="100">
					<label for="ctrs-fname"><strong>First Name</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-fname" id="ctrs-fname" size="30" value="<?php echo esc_attr( $fname ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-lname"><strong>Last Name</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-lname" id="ctrs-lname" size="30" value="<?php echo esc_attr( $lname ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-suffix"><strong>Suffix</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-suffix" id="ctrs-suffix" size="30" value="<?php echo esc_attr( $suffix ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-position"><strong>Position</strong></label>
				</td>
				<td valign="top">
					<input class="widefat" type="text" name="ctrs-position" id="ctrs-position" value="<?php echo esc_attr( $position ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-type"><strong>Type</strong></label>
				</td>
				<td valign="top">
					<input type="checkbox" name="ctrs-type-leadership" value="leadership" <?php checked( 'leadership', $leadership ); ?>> Leadership<br />
					<input type="checkbox" name="ctrs-type-researcher" value="researcher" <?php checked( 'researcher', $researcher ); ?>> Researcher<br />
					<input type="checkbox" name="ctrs-type-staff" value="staff" <?php checked( 'staff', $staff ); ?>> Staff
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-department"><strong>Department</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-department" id="ctrs-department" size="30" value="<?php echo esc_attr( $department ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-phone"><strong>Phone</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-phone" id="ctrs-phone" size="15" value="<?php echo esc_attr( $phone ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-email"><strong>Email</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-email" id="ctrs-email" size="30" value="<?php echo esc_attr( $email ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-url"><strong>URL</strong></label>
				</td>
				<td valign="top">
					<input type="text" name="ctrs-url" id="ctrs-url" size="30" value="<?php echo esc_attr( $url ); ?>" />
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-address"><strong>Address</strong></label>
				</td>
				<td valign="top">
					<textarea name="ctrs-address" id="ctrs-address" class="widefat" rows="5"><?php echo esc_textarea( $address ); ?></textarea>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="ctrs-exclude"><strong>Exclude From Campus Page(s)</strong></label>
				</td>
				<td valign="top">
					<input type="checkbox" name="ctrs-exclude" value="yes" <?php checked( 'yes', $exclude ); ?>>
				</td>
			</tr>
		</table>

	<?php
		wp_nonce_field( 'save', 'ctrs-person-meta-box' );
	}

	/**
	 * Save the meta box for people.
	 *
	 * @param   int		$post_id	Current post ID
	 * @param   object	$post		Current post object.
	 * @return  void
	 */
	public function save_person_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! isset( $_POST['ctrs-person-meta-box'] ) || ! wp_verify_nonce( $_POST['ctrs-person-meta-box'], 'save' ) )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Save Honorific
		if ( isset( $_POST['ctrs-honorific'] ) && '' !== trim( $_POST['ctrs-honorific'] ) )
			update_post_meta( $post_id, '_ctrs_honorific', sanitize_text_field( $_POST['ctrs-honorific'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_honorific' );

		// Save First Name
		if ( isset( $_POST['ctrs-fname'] ) && '' !== trim( $_POST['ctrs-fname'] ) )
			update_post_meta( $post_id, '_ctrs_fname', sanitize_text_field( $_POST['ctrs-fname'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_fname' );

		// Save Last Name
		if ( isset( $_POST['ctrs-lname'] ) && '' !== trim( $_POST['ctrs-lname'] ) )
			update_post_meta( $post_id, '_ctrs_lname', sanitize_text_field( $_POST['ctrs-lname'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_lname' );

		// Save Suffix
		if ( isset( $_POST['ctrs-suffix'] ) && '' !== trim( $_POST['ctrs-suffix'] ) )
			update_post_meta( $post_id, '_ctrs_suffix', sanitize_text_field( $_POST['ctrs-suffix'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_suffix' );

		// Save Position
		if ( isset( $_POST['ctrs-position'] ) && '' !== trim( $_POST['ctrs-position'] ) )
			update_post_meta( $post_id, '_ctrs_position', sanitize_text_field( $_POST['ctrs-position'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_position' );

		// Save Type
		if ( isset( $_POST['ctrs-type-leadership'] ) && '' !== trim( $_POST['ctrs-type-leadership'] ) )
			update_post_meta( $post_id, '_ctrs_leadership', sanitize_text_field( $_POST['ctrs-type-leadership'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_leadership' );
		if ( isset( $_POST['ctrs-type-researcher'] ) && '' !== trim( $_POST['ctrs-type-researcher'] ) )
			update_post_meta( $post_id, '_ctrs_researcher', sanitize_text_field( $_POST['ctrs-type-researcher'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_researcher' );
		if ( isset( $_POST['ctrs-type-staff'] ) && '' !== trim( $_POST['ctrs-type-staff'] ) )
			update_post_meta( $post_id, '_ctrs_staff', sanitize_text_field( $_POST['ctrs-type-staff'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_staff' );

		// Save Department
		if ( isset( $_POST['ctrs-department'] ) && '' !== trim( $_POST['ctrs-department'] ) )
			update_post_meta( $post_id, '_ctrs_department', sanitize_text_field( $_POST['ctrs-department'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_department' );

		// Save Phone
		if ( isset( $_POST['ctrs-phone'] ) && '' !== trim( $_POST['ctrs-phone'] ) )
			update_post_meta( $post_id, '_ctrs_phone', sanitize_text_field( $_POST['ctrs-phone'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_phone' );

		// Save Email
		if ( isset( $_POST['ctrs-email'] ) && '' !== trim( $_POST['ctrs-email'] ) )
			update_post_meta( $post_id, '_ctrs_email', sanitize_text_field( $_POST['ctrs-email'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_email' );

		// Save URL
		if ( isset( $_POST['ctrs-url'] ) && '' !== trim( $_POST['ctrs-url'] ) )
			update_post_meta( $post_id, '_ctrs_url', esc_url_raw( $_POST['ctrs-url'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_url' );

		// Save Address
		if ( isset( $_POST['ctrs-address'] ) && '' !== trim( $_POST['ctrs-address'] ) )
			update_post_meta( $post_id, '_ctrs_address', wp_kses_post( $_POST['ctrs-address'] ) );
		else
			delete_post_meta( $post_id, '_ctrs_address' );

		// Save Exclude value
		if ( isset( $_POST['ctrs-exclude'] ) && 'yes' === trim( $_POST['ctrs-exclude'] ) )
			update_post_meta( $post_id, '_ctrs_exclude', 'yes' );
		else
			delete_post_meta( $post_id, '_ctrs_exclude' );
	}

	/**
	 * Create a corresponding term in the person taxonomy.
	 *
	 * @param	int		$post_id	Post ID.
	 * @param	obj		$post		Post object.
	 * @return  void
	 */
	public function add_person_to_taxonomy( $post_id, $post ) {
		$person_term = term_exists( htmlentities( $post->post_title ), 'ctrs-tax-people' );

		if ( ! is_array( $person_term) && ( 0 === $person_term || null === $person_term ) ) {
			$inserted_term = wp_insert_term( $post->post_title, 'ctrs-tax-people', array( 'slug' => $post->post_name, 'description' => $post->post_content ) );
		} else {
			wp_update_term( $person_term['term_id'], 'ctrs-tax-people', array( 'name' => $post->post_title, 'description' => $post->post_content ) );
		}
	}

	/**
	 * Remove person from person taxonomy on CPT deletion.
	 *
	 * @param	int  $post_id Post ID.
	 * @return  void
	 */
	public function remove_person_taxonomy( $post_id ) {
		if ( $this->post_type !== get_post_type( $post_id ) ) {
			return;
		}

		$post_title = get_the_title( $post_id );
		$post_title = htmlentities( $post_title );
		$person_term = term_exists( $post_title, 'ctrs-tax-people' );

		if ( is_array( $person_term ) && ( 0 !== $person_term || null !== $person_term ) ) {
			wp_delete_term( $person_term['term_id'], 'ctrs-tax-people' );
		}
	}

	/**
	 * Make sure Campus tax archives pages only show people.
	 *
	 * @param	object	$query	The main query.
	 * @return	object			Modified query.
	 */
	function modify_tax_query( $query ) {
		if ( $query->is_main_query() && ! is_admin() ) {
			if ( is_tax( 'ctrs-campus' ) ) {
				$query->set( 'post_status', 'publish' );
				$query->set( 'post_type', $this->post_type );
				$query->set( 'posts_per_page', 100 );
				$query->set( 'order', 'ASC' );
				$query->set( 'orderby', 'menu_order' );

				$meta_query = array(
					array(
						'key'     => '_ctrs_researcher',
						'value'   => 'researcher',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => '_ctrs_exclude',
						'value'   => 'yes',
						'compare' => 'NOT EXISTS'
					),
				);

				$query->set( 'meta_query', $meta_query );
			}
		}
	}

	/**
	 * Make sure sticky People are shown on top of the People archive page.
	 *
	 * @param	array	$posts	The currently queried posts.
	 * @return	array			Modified array of posts.
	 */
	function move_sticky_to_top( $posts ) {
		// apply it on the archives only
	    if ( is_main_query() && ! is_search() && is_post_type_archive( $this->post_type ) && isset( $posts[0] ) && 'ctrs-people' === get_post_type( $posts[0]->ID ) ) {
			global $wp_query;

			// Don't move sticky posts up on paginated pages.
			if ( isset( $wp_query->query['paged'] ) && $wp_query->query['paged'] >= 2 ) {
				return $posts;
			}

			$sticky_posts = get_option( 'sticky_posts' );
			$num_posts = count( $posts );
			$sticky_offset = 0;

			// Find the sticky posts
			for ( $i = 0; $i < $num_posts; $i++ ) {
	            // Put sticky posts at the top of the posts array
				if ( in_array( $posts[$i]->ID, $sticky_posts ) ) {
					$sticky_post = $posts[$i];

					// Remove sticky from current position
					array_splice( $posts, $i, 1 );

					// Move to front, after other stickies
					array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
					$sticky_offset++;

					// Remove post from sticky posts array
					$offset = array_search( $sticky_post->ID, $sticky_posts );
					unset( $sticky_posts[$offset] );
				}
			}

			// Look for more sticky posts if needed
			if ( ! empty( $sticky_posts ) ) {
				$stickies = get_posts( array(
					'post__in' => $sticky_posts,
					'post_type' => $wp_query->query_vars['post_type'],
					'post_status' => 'publish',
					'nopaging' => true
				) );

				foreach ( $stickies as $sticky_post ) {
					array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
					$sticky_offset++;
				}
			}
		}

		return $posts;
	}

}

CTRS_People::instance();