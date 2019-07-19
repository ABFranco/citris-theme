<?php

/**
 * Syndicate events from RSS feeds.
 *
 */
class CTRS_Events_Syndication {

	/**
	 * The feed we will process.
	 *
	 * @var string
	 */
	public $feed = 'http://events.berkeley.edu/index.php/live_export/sn/citris/type/future.html';

	protected $groups = array(
		'health'         => '1775',
		'infrastructure' => '1776',
		'democracy'      => '1778',
		'energy'         => '1779'
	);

	/**
	 * The only instance of the CTRS_Events_Syndication object.
	 *
	 * @var CTRS_Events_Syndication
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return  CTRS_Events_Syndication
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new CTRS_Events_Syndication;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @return  CTRS_Events_Syndication
	 */
	private function __construct() {

	}

	/**
	 * Initiate the main actions and scheduled event for syndication functionality.
	 *
	 * @return  void
	 */
	public function setup_actions() {
		add_action( 'init',  array( $this, 'init_manual_syndication') );
		add_action( 'admin_init', array( $this, 'setup_schedule' ) );
		add_action( 'import_feed_daily', array( $this, 'import_feed' ) );
		add_action( 'all_admin_notices', array( $this, 'add_calendar_buttons' ) );
	}

	/**
	 * Remove TribeEvent hook that add the View Calendar button on the event edit screen
	 * in order to re add it so that we can add our own custom button to run manual syndications
	 */
	public function init_manual_syndication(){
		$tribe_ecp = TribeEvents::instance();
		remove_action( 'all_admin_notices', array( $tribe_ecp , 'addViewCalendar' ) );

		if( isset( $_GET['syndicate'] ) ){
			$this->import_feed();
			add_action( 'admin_notices', array( $this, 'syndication_notice' ) );
		}

	}

	/**
	 * Adds view calendar and run syndication to the page so the TribeEvents javascript
	 * can load it into pages H2 tag
	 */
	public function add_calendar_buttons() {
		global $current_screen;
		$tribe_ecp = TribeEvents::instance();

		if ( $current_screen->id == 'edit-' . $tribe_ecp::POSTTYPE ) {
			//Output hidden DIV with Calendar link to be displayed via javascript
			echo '<div id="view-calendar-link-div" style="display:none;">
				<a class="add-new-h2" href="' . $tribe_ecp->getLink() . '">' . __( 'View Calendar', 'tribe-events-calendar' ) . '</a><a class="add-new-h2" href="' . add_query_arg( array('syndicate' => true ), admin_url('edit.php?post_type=tribe_events') ) . '">' . __( 'Run RSS Syndication', 'ctrs' ) . '</a>
			</div>';
		}
	}

	public function syndication_notice(){
		?>
		<div class="updated">
			<p><?php _e( 'The manual rss syndication has been ran', 'ctrs' ); ?></p>
		</div>
	<?php
	}
	/**
	 * Set up our scheduled event.
	 *
	 * @return  void
	 */
	public function setup_schedule() {
		if ( ! wp_next_scheduled( 'import_feed_daily' ) ) {
			wp_schedule_event( time(), 'daily', 'import_feed_daily' );
		}
	}

	/**
	 * Import the RSS feed.
	 *
	 * @return  void
	 */
	public function import_feed() {

		// Process all of our feeds
		$feed = wp_remote_retrieve_body( wp_remote_get( $this->feed ) );
		if ( ! is_wp_error( $feed ) ) {
			$this->process_feed( simplexml_load_string( $feed ) );
		}
	}

	/**
	 * Process the RSS feed.
	 *
	 * @var        obj    $feed  SimplePie feed object.
	 * @var        string $group Group feed is associated with.
	 * @return  void
	 */
	public function process_feed( $xml ) {
		global $wpdb;

		if ( count( $xml->Event ) ) {
			foreach ( $xml->Event as $item ) {
				$event_id   = $item->ID;
				$title   = $item->Title;
				$url     = 'http://events.berkeley.edu/index.php/calendar/sn/citris.html?event_ID=' . $event_id;
				$content = $item->ShortDescription;
				$group  = 'main';

				$start_date =  $item->DateTime->StartDate . ' ' . $item->DateTime->StartTime;
				if( isset( $item->DateTime->RecurrenceDates ) ){
					$end_date   = $item->DateTime->RecurrenceRules->Rule->Until->Date. ' ' . $item->DateTime->EndTime;
				} else {
					$end_date   = $item->DateTime->StartDate . ' ' . $item->DateTime->EndTime;
				}

				foreach(  $item->EventTypes->EventType as $event_type ){
					if( in_array( $event_type->EventTypeID ,$this->groups ) ){
						$group = array_search( $event_type->EventTypeID, $this->groups );
					}
				}

				// if ( $event_id !== 0 ) {
				// 	// grab the existing post ID (if it exists).
				// 	$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = '_event_import_id' AND meta_value = " . $event_id );
				// } else {
				// 	$wp_id = false;
				// }
				// Bugfix: event_id fails to get saved in metadata
				// uses URL in the metadata instead to check if a repeat occurs

				if ( $event_id !== 0 ) {
					// grab the existing meta_key _EventURL (if it exists).
					$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = '_EventURL' AND meta_value = " . $url );
				} else {
					$wp_id = false;
				}

				// If we find the event ID in meta, skip this event.
				if ( $wp_id ) {
					$this->add_group( $wp_id, $group );
					continue;
				} else {
					if ( '' !== trim( $title ) && is_null( get_page_by_title( html_entity_decode( $title ), OBJECT, 'tribe_events' ) ) ) {
						if ( get_page_by_title( $title, OBJECT, 'tribe_events' ) ) {
							$this->add_group( $wp_id, $group );
							continue;
						}
						$this->save_event( $title, $url, $content, $start_date, $end_date, $group, $event_id );
						
					}
				}

			}
		}

	}
	/**
	 * Save the event post, using information from the RSS feed.
	 *
	 * @var        str $title    Event title.
	 * @var        str $url      Event permalink.
	 * @var        str $content  Event content.
	 * @var        str $date     Start date of event.
	 * @var        str $group    Group event is associated with.
	 * @var        int $event_id ID of event.
	 * @return  void
	 */
	public function save_event( $title, $url, $content, $start_date, $end_date, $group, $event_id ) {
		$post_data = array(
			'post_status' => 'publish',
			'post_type'   => 'tribe_events',
			'post_author' => '1',
			'post_date'   => date( 'Y-m-d 01:01:01', strtotime( 'yesterday' ) ), //using yesterday, so we dont get 'missed schedule' errors;
		);

		$post_data['post_content'] = trim( apply_filters( 'the_content', $content ) );
		$post_data['post_title']   = trim( apply_filters( 'the_title', $title ) );

		$post_id = wp_insert_post( $post_data );

		if ( $post_id ) {
			if ( $url ) {
				// Set our start date and end date.  We have a start date but no time, and so we just assume 8:00 AM.
				// Also have no end date or time, so assume the same day as start and a time of 5:00 PM.
				$startdate = $start_date;
				$enddate   = $end_date;
				update_post_meta( $post_id, '_EventStartDate', $startdate );
				update_post_meta( $post_id, '_EventEndDate', $enddate );

				// Add event URL
				update_post_meta( $post_id, '_EventURL', esc_url_raw( $url ) );

				// Add event ID
				if ( is_int( $event_id ) && $event_id !== 0 ) {
					update_post_meta( $post_id, '_event_import_id', $event_id );
				} else {
					// bugfix attempt to import this meta_value anyway
					// since database doesn't show this being added
					update_post_meta( $post_id, '_event_import_id', $event_id );
				}

				// Add our Group term
				if ( 'main' !== $group ) {
					wp_set_object_terms( $post_id, $group, 'ctrs-groups', true );
				}
			}
		}
	}

	/**
	 * If an event already exists in another feed, just add the right group.
	 *
	 * @var        int $post_id ID of event.
	 * @var        str $group   Group event belongs to.
	 * @return  bool
	 */
	public function add_group( $post_id, $group ) {
		if ( 'main' !== $group ) {
			$term = wp_set_object_terms( $post_id, $group, 'ctrs-groups', true );

			if ( $term && ! is_wp_error( $term ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

}

CTRS_Events_Syndication::instance();

