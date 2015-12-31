<?php
/**
 * RestaurantPress Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @author   ThemeGrill
 * @category Core
 * @package  RestaurantPress/Functions
 * @version  1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include Widget classes.
include_once( 'abstracts/abstract-rp-widget.php' );
include_once( 'widgets/class-rp-widget-menu.php' );

/**
 * Register Widgets.
 * @since 1.2.0
 */
function rp_register_widgets() {
	register_widget( 'RP_Widget_Menu' );
}
add_action( 'widgets_init', 'rp_register_widgets' );
