<?php
/**
 * Handle frontend scripts.
 *
 * @class    RP_Frontend_Scripts
 * @version  1.0.0
 * @package  RestaurantPress/Classes
 * @category Class
 * @author   ThemeGrill
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RP_Frontend_Scripts Class
 */
class RP_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by RP
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by RP
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Hooks in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	/**
	 * Get styles for the frontend.
	 * @access private
	 * @return array
	 */
	public static function get_styles() {
		return apply_filters( 'restaurantpress_enqueue_styles', array(
			'restaurantpress-general' => array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', RP()->plugin_url() ) . '/assets/css/restaurantpress.css',
				'deps'    => '',
				'version' => RP_VERSION,
				'media'   => 'all'
			)
		) );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = RP_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = RP_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = RP_VERSION, $media = 'all' ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = RP_VERSION, $media = 'all' ) {
		if ( ! in_array( $handle, self::$styles ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register/enqueue frontend scripts.
	 */
	public static function load_scripts() {
		$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$lightbox_en          = 'yes' === get_option( 'restaurantpress_enable_lightbox' );
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', RP()->plugin_url() ) . '/assets/';
		$frontend_script_path = $assets_path . 'js/frontend/';

		if ( apply_filters( 'restaurantpress_is_widget_menu_active', is_active_widget( false, false, 'restaurantpress_widget_menu', true ) ) || rp_shortcode_tag( 'restaurantpress_menu' ) ) {

			// Register frontend scripts conditionally
			if ( $lightbox_en ) {
				self::enqueue_script( 'prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.6', true );
				self::enqueue_script( 'prettyPhoto-init', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery','prettyPhoto' ) );
				self::enqueue_style( 'restaurantpress_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css' );
			}

			// Global frontend scripts
			self::enqueue_script( 'restaurantpress', $frontend_script_path . 'restaurantpress' . $suffix . '.js', array( 'jquery' ) );

			// CSS Styles
			if ( $enqueue_styles = self::get_styles() ) {
				foreach ( $enqueue_styles as $handle => $args ) {
					self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
				}
			}

			// Inline Styles
			if ( current_user_can( 'manage_restaurantpress' ) ) {
				$inline_styles = self::create_primary_styles();
				wp_add_inline_style( 'restaurantpress-general', $inline_styles );
			}
		}
	}

	/**
	 * Create primary color styles.
	 * @return string
	 */
	private static function create_primary_styles() {
		$primary_color  = get_option( 'restaurantpress_primary_color' );

		// Load styles
		return "
		.restaurantpress .rp-chef-badge {
			background: {$primary_color} !important;
		}

		.restaurantpress .rp-chef-badge:before,
		.restaurantpress .rp-chef-badge:after {
			border-top-color: {$primary_color} !important;
		}

		.restaurantpress .rp-price {
			background: {$primary_color} !important;
		}

		.restaurantpress .rp-price:before {
			border-right-color: {$primary_color} !important;
		}

		.restaurantpress .rp-content-wrapper {
			border-bottom-color: {$primary_color} !important;
		}
		";

		return $inline_css;
	}
}

RP_Frontend_Scripts::init();
