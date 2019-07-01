<?php

/**
 * Define functionality related to the Groups CTT.
 */
class CTRS_Groups {

	/**
	 * The CTT name.
	 *
	 * @var string
	 */
	public $tax_name = 'ctrs-groups';

	/**
	 * Stores the rewrite tag complete with percentage signs.
	 *
	 * @var string
	 */
	public $rewrite_tag = '%project_group%';

	/**
	 * The only instance of the CTRS_Groups CTT.
	 *
	 * @var CTRS_Groups
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Groups
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Groups;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Groups
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for groups related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CTT
		add_action( 'init', array( $this, 'register_taxonomy' ) );

		add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_item' ) );

		// Add taxonomy to post class
		add_filter( 'post_class', array( $this, 'custom_taxonomy_post_class' ), 10, 3 );

		// Add taxonomy to body class
		add_filter( 'body_class', array( $this, 'custom_taxonomy_body_class' ) );

		// Add taxonomy term to post type link
		add_filter( 'post_type_link', array( $this, 'filter_post_link' ), 10, 2 );

		add_filter( 'pre_get_posts', array( $this, 'modify_tax_query' ) );
	}

	/**
	 * Register the CTT.
	 *
	 * @return  void
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->tax_name,
			array( 'post', 'ctrs-projects', 'ctrs-people', 'tribe_events' ),
			array(
				'labels' => array(
					'name'          => 'Initiatives',
					'singular_name' => 'Initiative',
					'search_items'  => 'Search Initiatives',
					'all_items'     => 'All Initiatives',
					'edit_item'     => 'Edit Initiative',
					'update_item'   => 'Update Initiative',
					'add_new_item'  => 'Add New Initiative',
					'new_item_name' => 'New Initiative Name',
					'menu_name'     => 'Initiatives',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => 'initiatives',
					'with_front' => false
				)
			)
		);

		// Add our rewrite rule which makes the permalinks with the Group taxonomy work
		add_rewrite_rule( '([^/]+)/project/([^/]+)(/[0-9]+)?/?$', 'index.php?project_group=$matches[1]&ctrs-projects=$matches[2]&page=$matches[3]', 'top' );

		// Add a rewrite rule to make our Group CPT show the Group CTT page
		add_rewrite_rule( 'ctrs-cpt-groups/([^/]+)/?$', 'index.php?ctrs-groups=$matches[1]', 'top' );
		add_rewrite_rule( 'ctrs-cpt-groups/([^/]+)/([^/]+)/?$', 'index.php?ctrs-groups=$matches[2]', 'top' );
	}

	/**
	 * Remove edit link from admin bar.
	 *
	 * @return void
	 */
	public function remove_admin_bar_item() {
		global $wp_admin_bar;

		if ( is_tax( 'ctrs-groups' ) ) {
			$wp_admin_bar->remove_menu( 'edit' );
		}
	}

	/**
	 * Add the taxonomy name to the post class.
	 *
	 * @param	array	$classes	Current classes.
	 * @param	array	$class		Current class.
	 * @param	int		$ID			Post ID.
	 * @return	array				Modified classes.
	 */
	public function custom_taxonomy_post_class( $classes, $class, $ID ) {
		$taxonomy = $this->tax_name;

		if ( $primary_initiative = get_post_meta( (int) $ID, '_ctrs_primary_initiative', true ) ) {
			$term = get_term( (int) $primary_initiative, $taxonomy );
			$classes[] = $term->slug;

			return $classes;
		}

		$terms = get_the_terms( (int) $ID, $taxonomy );

		if ( ! empty( $terms ) ) {
			foreach ( (array) $terms as $order => $term ) {
				if ( ! in_array( $term->slug, $classes ) ) {
					$classes[] = $term->slug;
				}
				if ( $term->parent ) {
					$parent_term = get_term( $term->parent, $taxonomy );
					if ( ! in_array( $parent_term->slug, $classes ) ) {
						$classes[] = $parent_term->slug;
					}
				}
			}
		}

		return $classes;
    }

	/**
	 * Add the taxonomy name to the body class.
	 *
	 * @param	array	$classes	Current classes.
	 * @return	array				Modified classes.
	 */
	public function custom_taxonomy_body_class( $classes ) {
		if ( is_tax( 'ctrs-groups' ) ) {
			$term = get_queried_object();
			if ( $term->parent ) {
				$parent_term = get_term( $term->parent, $this->tax_name );
				$classes[] = 'term-' . $parent_term->slug;
			}
		}

		return $classes;
    }

	/**
	 * Filters a post permalink to replace the tag placeholder with the first
	 * used term from the taxonomy in question.
	 *
	 * @param	string		$permalink		The existing permalink URL.
	 * @return	string		$permalink		The modified permalink.
	 */
	public function filter_post_link( $permalink, $post ) {
		// Abort early if we aren't on the right CPT
		if ( 'ctrs-projects' !== $post->post_type )
			return $permalink;

		// Abort early if the placeholder rewrite tag isn't in the generated URL
		if ( false === strpos( $permalink, $this->rewrite_tag ) )
			return $permalink;

		// Get the custom taxonomy terms in use by this post
		$terms = get_the_terms( $post->ID, $this->tax_name );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$terms = array_pop( $terms );
		}

		// If no terms are assigned to this post, use the taxonomy slug instead (can't leave the placeholder there)
		if ( ! $terms && empty( $terms ) ) {
			$permalink = str_replace( $this->rewrite_tag, $this->tax_name, $permalink );
		}

		// Replace the placeholder rewrite tag with the first term's slug
		else {
			$permalink = str_replace( $this->rewrite_tag, $terms->slug, $permalink );
		}

		return $permalink;
	}

	/**
	 * Modify the main query on the tax archive page.
	 *
	 * Only show posts from the Projects CPT in the main query.
	 *
	 * @param	object	$query	The main query.
	 * @return	object			Modified query.
	 */
	public function modify_tax_query( $query ) {
		if ( $query->is_main_query() && is_tax( $this->tax_name ) && ! is_admin() ) {
			$query->set( 'post_type', 'ctrs-projects' );
			$query->set( 'posts_per_page', 6 );
		}
	}

}

CTRS_Groups::instance();