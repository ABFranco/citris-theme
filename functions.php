<?php
/**
 * Citris functions and definitions
 *
 * @package Citris
 * @since 0.1.0
 */

// Useful global constants
define( 'CTRS_VERSION', '0.1.0' );

// Include CPT's
include dirname( __FILE__ ) . '/includes/custom-content-types/ctrs-groups.php';
include dirname( __FILE__ ) . '/includes/custom-content-types/ctrs-people.php';
include dirname( __FILE__ ) . '/includes/custom-content-types/ctrs-projects.php';

// Include CTT's
include dirname( __FILE__ ) . '/includes/custom-taxonomies/ctrs-campus.php';
include dirname( __FILE__ ) . '/includes/custom-taxonomies/ctrs-groups.php';
include dirname( __FILE__ ) . '/includes/custom-taxonomies/ctrs-people.php';
include dirname( __FILE__ ) . '/includes/custom-taxonomies/ctrs-technologies.php';

// Other Functionality
include dirname( __FILE__ ) . '/includes/class-curation.php';
include dirname( __FILE__ ) . '/includes/class-customizer.php';
include dirname( __FILE__ ) . '/includes/class-events-syndication.php';
include dirname( __FILE__ ) . '/includes/class-shared-meta-boxes.php';
include dirname( __FILE__ ) . '/includes/class-theme-options.php';
include dirname( __FILE__ ) . '/includes/shared-functions.php';

// Include our wp-cli commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include dirname( __FILE__ ) . '/includes/wp-cli/drupalport.php';
}

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 873; /* pixels */

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 *
 * @since 0.1.0
 */
function ctrs_setup() {
	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Switches default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

	/**
	 * Enable support for Post Thumbnails on posts and pages
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 64, 64, true );
	add_image_size( 'projects', 1013, 580, true );
	add_image_size( 'projects-small', 330, 177, true );
	add_image_size( 'projects-medium', 507, 229, true );
	add_image_size( 'projects-similar', 330, 177, true );
	add_image_size( 'projects-large', 577, 322, true );
	add_image_size( 'archive-small', 120, 120, true );
	add_image_size( 'intiative-thumb', 417, 210, true );
	add_image_size( 'events-thumb', 133, 100, true );
	add_image_size( 'events-featured', 610, 236, true );

	/**
	 * Register our menus.
	 */
	register_nav_menus( array(
		'primary' => 'Primary Menu',
		'footer'  => 'Footer Menu',
	) );
}
add_action( 'after_setup_theme', 'ctrs_setup' );

/**
 * Enqueue scripts and styles for front-end.
 *
 * @since 0.1.0
 */
function ctrs_scripts_styles() {
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$postfix = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'ctrs', get_template_directory_uri() . "/assets/js/citris{$postfix}.js", array( 'jquery' ), CTRS_VERSION, true );

	if ( is_tax( 'ctrs-campus' ) || is_tax( 'ctrs-groups' ) || is_post_type_archive( 'ctrs-projects' ) || 'tribe_events' === get_post_type() ) {
		wp_localize_script( 'ctrs', 'ctrs_filter',
			array(
				'ajaxurl'      => home_url( '/wp-admin/admin-ajax.php' ),
				'filter_nonce' => wp_create_nonce( 'ctrs-filter-group' ),
			)
		);
	}

	wp_enqueue_style( 'ctrs', get_template_directory_uri() . "/assets/css/citris{$postfix}.css", array(), CTRS_VERSION );
}
add_action( 'wp_enqueue_scripts', 'ctrs_scripts_styles' );

/**
 * Enqueue styles for admin.
 *
 * @since 0.1.0
 */
function ctrs_admin_styles() {
	wp_enqueue_style( 'ctrs-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), CTRS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'ctrs_admin_styles' );

/**
 * Register our widget areas.
 *
 * @return void
 */
function ctrs_widgets_init() {
	register_sidebar( array(
		'name'          => 'Home Widget Area',
		'id'            => 'sidebar-1',
		'description'   => 'Appears in the footer section of the homepage.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => 'Single Project Widget Area',
		'id'            => 'sidebar-2',
		'description'   => 'Appears in the footer section of single projects.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => 'Single Left Widget Area',
		'id'            => 'sidebar-3',
		'description'   => 'Appears on the left of single posts.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => 'Reservations Left Widget Area',
		'id'            => 'sidebar-4',
		'description'   => 'Appears on the left of reservations pages.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	// Add sidebars for each of our top-level initiatives
	// Tried to use get_terms here but the taxonomy wasn't found.
	$groups = array(
		array(
			'name' => 'Big Data',
			'slug' => 'big-data'
		),
		array(
			'name' => 'Data & Democracy',
			'slug' => 'democracy'
		),
		array(
			'name' => 'Energy',
			'slug' => 'energy'
		),
		array(
			'name' => 'Health Care',
			'slug' => 'health'
		),
		array(
			'name' => 'Intelligent Infrastructure',
			'slug' => 'infrastructure'
		),
	);
	foreach ( $groups as $group ) {
		register_sidebar( array(
			'name'          => $group['name'] . ' Sidebar',
			'id'            => 'sidebar-'. $group['slug'] .'',
			'description'   => 'Appears on the left of '. $group['name'] .' archives.',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}
}
add_action( 'widgets_init', 'ctrs_widgets_init' );

/**
 * Modify the main query on the home page.
 *
 * Change the posts_per_page from the default 10
 * to 4.
 *
 * @param	object	$query	The main query.
 * @return	object			Modified query.
 */
function ctrs_modify_home_query( $query ) {
	if ( $query->is_main_query() && $query->is_home() && ! is_admin() ) {
		$query->set( 'posts_per_page', 4 );
	}
}
add_filter( 'pre_get_posts', 'ctrs_modify_home_query' );

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function ctrs_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
	        <div class="comment-body">
	                Pingback: <?php comment_author_link(); ?> <?php edit_comment_link( 'Edit', '<span class="edit-link">', '</span>' ); ?>
	        </div>

	<?php else : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
	        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body cf">
	                <footer class="comment-meta">
	                        <div class="comment-author vcard">
	                                <?php if ( 0 != $args['avatar_size'] ) { echo get_avatar( $comment, $args['avatar_size'] ); } ?>
	                                <?php printf( '%s <span class="says">says:</span>', sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
	                        </div><!-- .comment-author -->

	                        <div class="comment-metadata">
	                                <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
	                                        <time datetime="<?php comment_time( 'c' ); ?>">
	                                                <?php printf( '%1$s at %2$s', get_comment_date(), get_comment_time() ); ?>
	                                        </time>
	                                </a>
	                                <?php edit_comment_link( 'Edit', '<span class="edit-link">', '</span>' ); ?>
	                        </div><!-- .comment-metadata -->

	                        <?php if ( '0' == $comment->comment_approved ) : ?>
	                        <p class="comment-awaiting-moderation">Your comment is awaiting moderation.</p>
	                        <?php endif; ?>
	                </footer><!-- .comment-meta -->

	                <div class="comment-content">
	                        <?php comment_text(); ?>
	                </div><!-- .comment-content -->

	                <?php
	                        comment_reply_link( array_merge( $args, array(
	                                'add_below' => 'div-comment',
	                                'depth'     => $depth,
	                                'max_depth' => $args['max_depth'],
	                                'before'    => '<div class="reply">',
	                                'after'     => '</div>',
	                        ) ) );
	                ?>
	        </article><!-- .comment-body -->

	<?php
	endif;
}

/**
 * For customizing where share buttons are located.
 *
 * Code taken from: https://jetpack.com/support/sharing/
 */
function jptweak_remove_share() {
    remove_filter( 'the_content', 'sharing_display', 19 );
    remove_filter( 'the_excerpt', 'sharing_display', 19 );
    if ( class_exists( 'Jetpack_Likes' ) ) {
        remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    }
}
add_action( 'loop_start', 'jptweak_remove_share' );