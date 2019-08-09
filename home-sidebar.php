<?php
/**
 * Sidebar area on the bottom of the home page and other pages.
 */
?>
<div id="secondary" class="widget-area grid" role="complementary">
	<div class="col-2-3">
		<?php
		$event_args = array(
			'post_type'      => 'tribe_events',
			'eventDisplay'   => 'upcoming',
			'posts_per_page' => 3
		);
		$event_query = new WP_Query( $event_args );
		if ( $event_query->have_posts() ) : ?>
			<div class="related-events">
				<aside class="widget grid">
					<h3 class="widget-title">Events</h3>
					<div class="col-1-2">
						<?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
						<?php $start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
						$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
						$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) ); ?>
							<div class="event-large cf">
								<span class="date">
									<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
									<span class="year"><?php echo esc_html( $start_year ); ?></span>
								</span>
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'events-featured' ); ?>
									<p><?php the_title(); ?></p>
								</a>
							</div>
						<?php endwhile; ?>
					</div><!-- .col-1-2 -->
					<?php
					$event_args = array(
						'post_type'      => 'tribe_events',
						'eventDisplay'   => 'upcoming',
						'posts_per_page' => 3,
						'offset'         => 3
					);
					$event_query = new WP_Query( $event_args );
					if ( $event_query->have_posts() ) : ?>
					<div class="col-1-2">
						<ul>
						<?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
							<?php $start_day = date( 'd', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
								$start_month = date( 'M', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) );
								$start_year = date( 'Y', strtotime( get_post_meta( get_the_ID(), '_EventStartDate', true ) ) ); ?>
							<li>
								<div class="event-large cf">
									<span class="date">
										<span class="day"><?php echo absint( $start_day ); ?></span> <?php echo esc_html( $start_month ); ?>
										<span class="year"><?php echo esc_html( $start_year ); ?></span>
									</span>
									<a href="<?php the_permalink(); ?>">
										<div class="event-title"><p><?php the_title(); ?></p></div>
									</a>
								</div>
							</li>
						<?php endwhile; ?>
							<li>
								<a href="<?php echo esc_url( home_url( '/events/' ) ) ?>" class="schedule com-btn">Full Schedule</a>
							</li>
						</ul>
					</div><!-- .col-1-2 -->
					<?php endif; wp_reset_postdata(); ?>
				</aside>
			</div><!-- .col-1-2 -->
		<?php endif; wp_reset_postdata(); ?>
	</div><!-- .col-2-3 -->
	<div class="col-1-3">
		<aside class="widget">
			<h3 class="widget-title">Newsletter</h3>
			<p>Keep up with the latest CITRIS news, events, and research.</p>
			<div class="newsletter-form">
				<a href=" http://eepurl.com/1xu7D" class="search-submit" target="_blank">Sign Up</a>
			</div>
		</aside>
		<aside class="widget">
			<h3 class="widget-title">Room Reservation</h3>
			<p>Reserve a room at one of the beautiful meeting spaces within Sutardja Dai Hall.</p>
			<a href="<?php echo esc_url( home_url( '/reserve-a-room/' ) ) ?>" class="reserve-btn com-btn">Reserve Now</a>
		</aside>
		<aside class="widget">
			<h3 class="widget-title">Stay Connected</h3>
			<ul class="social">
				<li>
					<a href="https://twitter.com/citrisnews" class="twitter" target="_blank"><span class="screen-reader-text">Twitter</span></a>
				</li>
				<li>
					<a href="https://www.facebook.com/citris" class="facebook" target="_blank"><span class="screen-reader-text">Facebook</span></a>
				</li>
				<li>
					<a href="https://www.instagram.com/citrisnews/" class="instagram" target="_blank"><span class="screen-reader-text">Instagram</span></a>
				</li>
				<li>
					<a href="https://www.linkedin.com/company/center-for-information-technology-research-in-the-interest-of-society-citris-/" class="linkedin" target="_blank"><span class="screen-reader-text">LinkedIn</span></a>
				</li>
				<li>
					<a href="http://www.youtube.com/user/citrisuc" class="youtube" target="_blank"><span class="screen-reader-text">YouTube</span></a>
				</li>
				<li>
					<a href="https://vimeo.com/citrisproductions" class="vimeo" target="_blank"><span class="screen-reader-text">Vimeo</span></a>
				</li>
			</ul>
		</aside>
	</div><!-- .col-1-3 -->
</div><!-- .widget-area -->