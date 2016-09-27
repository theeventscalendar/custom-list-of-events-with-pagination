<?php
/**
 * Plugin Name: The Events Calendar Extension: Custom List of Events with Pagination
 * Description: <strong>This extension requires <a href="https://wordpress.org/plugins/wp-pagenavi/" target="_blank">the free WP-PageNavi plugin</a> to be installed; it adds a shortcode to your site that lets you display a paginated list of events with the [tribe_paginated_events_list] shortcode.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1971
 * License: GPLv2 or later
 */

defined( 'WPINC' ) or die;

class Tribe__Extension__Custom_List_of_Events_with_Pagination {

    /**
     * The semantic version number of this extension; should always match the plugin header.
     */
    const VERSION = '1.0.0';

    /**
     * Each plugin required by this extension
     *
     * @var array Plugins are listed in 'main class' => 'minimum version #' format
     */
    public $plugins_required = array(
        'Tribe__Events__Main' => '4.2'
    );

    /**
     * The constructor; delays initializing the extension until all other plugins are loaded.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 100 );
    }

    /**
     * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
     */
    public function init() {

        // Exit early if our framework is saying this extension should not run.
        if ( ! function_exists( 'tribe_register_plugin' ) || ! tribe_register_plugin( __FILE__, __CLASS__, self::VERSION, $this->plugins_required ) ) {
            return;
        }

        add_shortcode( 'tribe_paginated_events_list', array( $this, 'paginated_events_list_shortcode_callback' ) );
        add_action( 'tribe_before_paginated_list_widget', array( $this, 'paginated_events_list_css' ) );
    }

    /**
     * The shortcode callback. 
     *
     * @param array $atts
     * @return string
     */
    public function paginated_events_list_shortcode_callback( $atts ) {
    
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

    /**
     * Some styling for the widget. 
     *
     */
    public function paginated_events_list_css() { ?>
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
}

new Tribe__Extension__Custom_List_of_Events_with_Pagination();
