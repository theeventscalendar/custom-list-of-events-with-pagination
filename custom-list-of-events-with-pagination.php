<?php
/**
 * Plugin Name: The Events Calendar — Custom List of Events with Pagination
 * Description: This snippet requires the free WP-PageNavi plugin to be installed; it adds a shortcode to your site that lets you display a paginated list of events with the [tribe_paginated_events_list] shortcode.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1x
 * License: GPLv2 or later
 */
 
defined( 'WPINC' ) or die;

/**
 * The shortcode callback. 
 *
 * @return string
 */
function tribe_paginated_events_list_shortcode_callback( $atts ) {

	if ( ! class_exists( 'Tribe__Events__Main' ) ) {
		return;
	}

	$args = shortcode_atts( array( 'title' => 'Upcoming Events' ), $atts );
		
	// Has the user paged forward, i.e. are they on /page-slug/page/2/?
	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	// Query events.
	$upcoming = new WP_Query( array(
		'post_type' => Tribe__Events__Main::POSTTYPE,
		'paged'     => $paged
	));

	ob_start();

	do_action( 'tribe_before_paginated_list_widget' );

	if ( $upcoming->have_posts() ) : ?>

		<h3><?php echo $args['title']; ?></h3>

		<ul class="tribe-paginated-list-widget">

		<?php while ( $upcoming->have_posts() ) : ?>
			<?php $upcoming->the_post(); ?>

				<li class="tribe-events-list-widget-events <?php tribe_events_event_classes() ?>">

					<?php do_action( 'tribe_events_list_widget_before_the_event_title' ); ?>
					<!-- Event Title -->
					<h4 class="tribe-event-title">
						<a href="<?php echo esc_url( tribe_get_event_link() ); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h4>

					<?php do_action( 'tribe_events_list_widget_after_the_event_title' ); ?>
					<!-- Event Time -->

					<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>

					<div class="tribe-event-duration">
						<?php echo tribe_events_event_schedule_details(); ?>
					</div>

					<?php do_action( 'tribe_events_list_widget_after_the_meta' ) ?>
				</li>
		
		<?php endwhile; ?>
		</ul><!-- .tribe-list-widget -->

	<?php endif;
	
	if ( function_exists( 'wp_pagenavi' ) ) {
		wp_pagenavi( array( 'query' => $upcoming ) );
	}

	wp_reset_query();

	return ob_get_clean();
}

add_shortcode( 'tribe_paginated_events_list', 'tribe_paginated_events_list_shortcode_callback' );

/**
 * Some styling for the widget. 
 *
 * @return void
 */
function tribe_paginated_events_list_css() { ?>
	<style>
		ul.tribe-paginated-list-widget {
			list-style: none;
		}
		ul.tribe-paginated-list-widget h4.tribe-event-title {
			margin-bottom: 0;
			margin-top: 0;
		}
		ul.tribe-paginated-list-widget h4.tribe-event-title a {
			text-decoration: none;
		}
		ul.tribe-paginated-list-widget .type-tribe_events {
			margin: 0.75em 0 0 0;
			border-bottom: 1px dashed #aaa;
			padding-bottom: 0.75em;
		}
		ul.tribe-paginated-list-widget .type-tribe_events:last-of-type {
			border-bottom: none;
		}
	</style><?php
}

add_action( 'tribe_before_paginated_list_widget', 'tribe_paginated_events_list_css' );
