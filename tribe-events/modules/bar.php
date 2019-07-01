<?php
/**
 * Events Navigation Bar Module Template
 * Renders our events navigation bar used across our views
 *
 * $filters and $views variables are loaded in and coming from
 * the show funcion in: lib/tribe-events-bar.class.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php

$filters = tribe_events_get_filters();
$views   = tribe_events_get_views();
?>

<?php do_action('tribe_events_bar_before_template') ?>
<div id="tribe-events-bar">

	<?php if ( ! is_tax( 'tribe_events_cat' ) ) : ?>
	<form id="tribe-bar-form" class="tribe-clearfix" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">

		<!-- Mobile Filters Toggle -->

		<div id="tribe-bar-collapse-toggle" <?php if ( count( $views ) == 1 ) { ?> class="tribe-bar-collapse-toggle-full-width"<?php } ?>>
			<?php _e( 'Find Events', 'tribe-events-calendar' ) ?><span class="tribe-bar-toggle-arrow"></span>
		</div>

		<!-- Views -->
		<?php if ( count( $views ) > 1 ) { ?>
		<div id="tribe-bar-views">
			<div class="tribe-bar-views-inner tribe-clearfix">
				<h3 class="tribe-events-visuallyhidden"><?php _e( 'Event Views Navigation', 'tribe-events-calendar' ) ?></h3>
				<label><?php _e( 'View As', 'tribe-events-calendar' ); ?></label>
				<select class="tribe-bar-views-select tribe-no-param" name="tribe-bar-view">
					<?php foreach ( $views as $view ) : ?>
						<option <?php echo tribe_is_view($view['displaying']) ? 'selected' : 'tribe-inactive' ?> value="<?php echo $view['url'] ?>" data-view="<?php echo $view['displaying'] ?>">
							<?php echo $view['anchor'] ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div><!-- .tribe-bar-views-inner -->
		</div><!-- .tribe-bar-views -->
		<?php } // if ( count( $views ) > 1 ) ?>

		<div class="tribe-bar-filters">
			<div class="tribe-bar-filters-inner tribe-clearfix">
				<?php $terms = get_terms( 'ctrs-campus' );
				if ( $terms && ! is_wp_error( $terms ) ) : ?>
					<div class="ctrs-campus-filter">
						<label class="label-ctrs-campus" for="ctrs-campus">Campus</label>
						<select id="ctrs-campus-filter">
							<option value="0">Select a Campus...</option>
							<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo $term->slug; ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div><!-- .campus-filter -->
				<?php endif; ?>
				<div class="month-filter">
					<label class="label-month" for="month">Month</label>
					<select id="month-filter">
						<option value="0">Select a Month...</option>
						<option value="<?php echo date( 'n Y' ); ?>"><?php echo date( 'F Y' ); ?></option>
						<?php for ( $i = 1; $i < 13; $i++ ) : ?>
							<option value="<?php echo date( 'n Y', strtotime( "+$i month" ) ); ?>"><?php echo date( 'F Y', strtotime( "+$i month" ) ); ?></option>
						<?php endfor; ?>
					</select>
				</div><!-- .month-filter -->
			</div><!-- .tribe-bar-filters-inner -->
		</div><!-- .tribe-bar-filters -->
	</form><!-- #tribe-bar-form -->
	<?php endif; ?>

</div><!-- #tribe-events-bar -->
<?php do_action('tribe_events_bar_after_template') ?>
