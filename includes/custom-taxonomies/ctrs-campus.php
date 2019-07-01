<?php

/**
 * Define functionality related to the Campus CTT.
 */
class CTRS_Campus {

	/**
	 * The CTT name.
	 *
	 * @var string
	 */
	public $tax_name = 'ctrs-campus';

	/**
	 * The only instance of the CTRS_Campus CTT.
	 *
	 * @var CTRS_Campus
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Campus
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Campus;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Campus
	 */
	private function __construct() {}

	/**
	 * Initiate the main actions and filters for campus related functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		// Setup the CTT
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_box' ) );

		// Handle our project filtering
		add_action( 'wp_ajax_ctrs_filter_group', array( $this, 'filter_group' ) );
		add_action( 'wp_ajax_nopriv_ctrs_filter_group', array( $this, 'filter_group' ) );

		// Handle event filtering
		add_action( 'wp_ajax_ctrs_filter_event', array( $this, 'filter_event' ) );
		add_action( 'wp_ajax_nopriv_ctrs_filter_event', array( $this, 'filter_event' ) );
	}

	/**
	 * Register the CTT.
	 *
	 * @return  void
	 */
	public function register_taxonomy() {
		register_taxonomy(
			$this->tax_name,
			array( 'post', 'ctrs-people', 'ctrs-projects', 'tribe_events' ),
			array(
				'labels' => array(
					'name'          => 'Campus',
					'singular_name' => 'Campus',
					'search_items'  => 'Search Campus\'s',
					'all_items'     => 'All Campus\'s',
					'edit_item'     => 'Edit Campus',
					'update_item'   => 'Update Campus',
					'add_new_item'  => 'Add New Campus',
					'new_item_name' => 'New Campus Name',
					'menu_name'     => 'Campus',
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => 'campus',
					'with_front' => false
				)
			)
		);
	}

	/**
	 * Remove the default meta box for the taxonomy.
	 *
	 * @return void
	 */
	public function remove_meta_box() {
		remove_meta_box( $this->tax_name . 'div', 'ctrs-projects', 'side' );
	}

	/**
	 * Filter the Projects that belong to a specific Campus, based on Group.
	 *
	 * @return void
	 */
	public function filter_group() {
		if ( isset( $_POST['group'] ) && isset( $_POST['campus'] ) ) {
			$sticky_posts = get_option( 'sticky_posts' );
			$featured_args = array();
			$featured_query = '';
			if ( is_array( $sticky_posts ) && count( $sticky_posts ) >= 1 ) {
				$stickies = array();
				foreach ( $sticky_posts as $sticky_post ) {
					if ( 'ctrs-projects' === get_post_type( $sticky_post ) ) {
						$stickies[] = $sticky_post;
					}
				}
				if ( $stickies && ! empty( $stickies ) ) {
					$featured_args['post__in'] = $stickies;
					$featured_args['post_type'] = 'ctrs-projects';
					$featured_args['posts_per_page'] = 1;
					$featured_args['post_status'] = 'publish';
				}
			}

			$project_args = array(
				'post_type'      => 'ctrs-projects',
				'posts_per_page' => 4,
				'post_status'    => 'publish',
				'post__not_in'   => get_option( 'sticky_posts' ),
			);

			if ( 'all' === $_POST['group'] ) {
				$project_args['ctrs-campus'] = sanitize_text_field( $_POST['campus'] );
				$featured_args['ctrs-campus'] = sanitize_text_field( $_POST['campus'] );
			} else {
				$featured_args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'ctrs-groups',
						'field' => 'slug',
						'terms' => sanitize_text_field( $_POST['group'] )
					),
					array(
						'taxonomy' => 'ctrs-campus',
						'field' => 'slug',
						'terms' => sanitize_text_field( $_POST['campus'] ),
					)
				);

				$project_args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'ctrs-groups',
						'field' => 'slug',
						'terms' => sanitize_text_field( $_POST['group'] )
					),
					array(
						'taxonomy' => 'ctrs-campus',
						'field' => 'slug',
						'terms' => sanitize_text_field( $_POST['campus'] ),
					)
				);
			}

			if ( isset( $featured_args['post__in'] ) ) {
				$featured_query = new WP_Query( $featured_args );
			}
			$project_query = new WP_Query( $project_args );

			ob_start();

			$max = 4;
			if ( $featured_query && $featured_query->have_posts() ) :
				$max = 3;
				while ( $featured_query->have_posts() ) : $featured_query->the_post();
			?>

					<div class="col-1-2 sticky">
						<?php get_template_part( 'content', 'archive' ); ?>
					</div><!-- end .col-1 -->

				<?php endwhile; wp_reset_postdata();
			endif;

			if ( $project_query->have_posts() ) :
			$i = 1; while ( $project_query->have_posts() ) : $project_query->the_post(); ?>

				<?php if ( $i <= $max ) : ?>
				<div class="col-1-2">
					<?php get_template_part( 'content', 'archive' ); ?>
				</div><!-- end .col-1-2 -->
				<?php endif; ?>

			<?php $i++; endwhile;
			else : ?>
				<div class="col-1-2" style="min-height: 100px;">
					<article>
						<p>No Results Found</p>
					</article>
				</div><!-- end .col-1-2 -->
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

	/**
	 * Filter the Events that belong to a specific Campus or are in a certain month or both.
	 *
	 * @return void
	 */
	public function filter_event() {
		if ( isset( $_POST['campus'] ) && isset( $_POST['month'] ) && isset( $_POST['year'] ) ) {
			TribeEventsQuery::init();

			$event_args = array(
				'eventDisplay'   => 'upcoming',
				'post_type'      => 'tribe_events',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			);

			if ( '0' !== $_POST['month'] ) {
				if ( (int) $_POST['month'] >= (int) date( 'n' ) ) {
					$event_args['eventDisplay'] = 'monthly';
				} else {
					$event_args['eventDisplay'] = 'past';
				}

				$startdate = $_POST['year'] .'-'. $_POST['month'] . '-01 01:00:00';
				if ( $_POST['month'] === '12' ) {
					$enddate = $_POST['year'] .'-'. $_POST['month'] . '-31 23:00:00';
				} else {
					$enddate = $_POST['year'] .'-'. ( $_POST['month'] + 1 ) . '-01 01:00:00';
				}

				$event_args['meta_query'] = array(
					array(
						'key' => '_EventStartDate',
						'value' => $startdate,
						'type' => 'DATETIME',
						'compare' => '>='
					),
					array(
						'key' => '_EventStartDate',
						'value' => $enddate,
						'type' => 'DATETIME',
						'compare' => '<'
					)
				);
			}

			if ( '0' !== $_POST['campus'] ) {
				$event_args['ctrs-campus'] = sanitize_text_field( $_POST['campus'] );
			}

			if ( '0' === $_POST['month'] ) {
				$event_query = TribeEventsQuery::getEvents( $event_args, true );
			} else {
				$event_query = new WP_Query( $event_args );
			}

			global $wp_query, $post;
			$wp_query = $event_query;
			if ( ! empty( $event_query->posts ) ) {
				$post = $event_query->posts[0];
			}

			ob_start();

			if ( have_posts() ) { ?>
				<div class="events-container">
					<!-- Notices -->
					<?php tribe_events_the_notices() ?>

					<!-- Events Loop -->
					<?php if ( have_posts() ) : ?>
						<?php do_action( 'tribe_events_before_loop' ); ?>
						<?php tribe_get_template_part( 'list/loop' ) ?>
						<?php do_action( 'tribe_events_after_loop' ); ?>
					<?php endif; ?>
				</div>
			<?php } else { ?>
				<div class="tribe-events-notices"><ul><li>There were no results found.</li></ul></div>
			<?php }

			$filtered_events = ob_get_contents();
			ob_end_clean();

			wp_reset_postdata();

			echo $filtered_events;
			die();
		} else {
			die();
		}
	}

}

CTRS_Campus::instance();