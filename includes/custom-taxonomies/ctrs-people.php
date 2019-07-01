<?php

/**
 * Define functionality related to the People CTT.
 */
class CTRS_Tax_People {

	/**
	 * The CTT name.
	 *
	 * @var string
	 */
	public $tax_name = 'ctrs-tax-people';

	/**
	 * The post types we want this taxonomy on.
	 *
	 * @var string
	 */
	public $post_types = array( 'post', 'ctrs-projects' );

	/**
	 * The only instance of the CTRS_Tax_People CTT.
	 *
	 * @var CTRS_Tax_People
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Tax_People
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Tax_People;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Tax_People
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for people related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CTT
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		//add_action( 'save_post', array( $this, 'save_post' ) );
		foreach ( $this->post_types as $post_type ) {
			add_action( "save_post_{$post_type}", array( $this, 'save_post' ), 10, 3 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Register the CTT.
	 *
	 * @return  void
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->tax_name,
			$this->post_types,
			array(
				'labels' => array(
					'name'          => 'People',
					'singular_name' => 'Person',
					'search_items'  => 'Search People',
					'all_items'     => 'All People',
					'edit_item'     => 'Edit Person',
					'update_item'   => 'Update Person',
					'add_new_item'  => 'Add New Person',
					'new_item_name' => 'New Person Name',
					'menu_name'     => 'People',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_ui'           => false
			)
		);
	}

	/**
	 * Remove the default meta boxes for the taxonomy and add the replacement.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		foreach ( $this->post_types as $post_type ) {
			remove_meta_box( $this->tax_name . 'div', $post_type, 'side' );
			add_meta_box( 'chosen-people', 'Choose People', array( $this, 'meta_box_display' ), $post_type, 'side', 'default' );
		}
	}

	/**
	 * Display the replacement meta box itself
	 */
	public function meta_box_display() {
		$post_type = get_post_type();

		if ( ! in_array( $post_type, $this->post_types ) )
			return;

		wp_nonce_field( 'chosen-save-people-terms', 'chosen_people_meta_box_nonce' );
		?>

		<script type="text/javascript">
		jQuery(document).ready(function($){
			$( '.chosen-select' ).chosen({no_results_text: '<a href="/wp-admin/post-new.php?post_type=ctrs-people" target="_blank">Create new person?</a> No results match '});
		});
		</script>

		<?php
		$taxonomy = get_taxonomy( $this->tax_name );
		$terms = get_terms( $this->tax_name, array( 'hide_empty' => 0 ) );
		$current_terms = wp_get_post_terms ( get_the_ID(), $this->tax_name, array( 'fields' => 'ids' ) );
		?>

		<p>
			<select name="<?php echo "tax_input[$this->tax_name]"; ?>[]" class="chosen-select widefat" data-placeholder="Select one or more people" multiple="multiple">
			<?php foreach ( $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>"<?php selected( in_array( $term->term_id, $current_terms ) ); ?>><?php echo $term->name; ?></option>
			<?php endforeach; ?>
			</select>
		</p>
	<?php
	}

	/**
	 * When saving the post, check to see if the taxonomy has been emptied out.
	 * If so, it will not exist in the tax_input array and thus WP won't be aware of it,
	 * so we have to take of emptying the terms for the object.
	 */
	public function save_post( $post_id, $post, $update ) {
		// verify nonce
		if ( ! isset($_POST['chosen_people_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['chosen_people_meta_box_nonce'], 'chosen-save-people-terms' ) ) {
			return;
		}

		// check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		$post_type = get_post_type( $post_id );

		if ( ! in_array( $post_type, $this->post_types ) ) {
			return;
		}

		// if nothing is posted for a relevant taxonomy, remove the object terms!
		// otherwise, WP will take care of assigning the terms
		$input = isset( $_POST['tax_input'][$this->tax_name] ) ? $_POST['tax_input'][$this->tax_name] : '';

		if ( empty( $input ) ) {
			$taxonomy = get_taxonomy( $this->tax_name );
			if ( $taxonomy && current_user_can( $taxonomy->cap->assign_terms ) ) {
				wp_set_object_terms( $post_id, '', $this->tax_name );
			}
		}
	}

	/**
	 * Chosen JS and CSS enqueue
	 */
	function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'post' !== $screen->base || ! in_array( $screen->post_type, $this->post_types ) )
			return;

		wp_enqueue_script(  'chosen', get_template_directory_uri().'/assets/js/vendor/chosen.jquery.min.js', array( 'jquery' ), '1.0' );
		wp_enqueue_style( 'chosen', get_template_directory_uri().'/assets/css/vendor/chosen.css' );
	}

}

CTRS_Tax_People::instance();