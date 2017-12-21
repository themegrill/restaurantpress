<?php
/**
 * RestaurantPress setup
 *
 * @author   WPEverest
 * @category Core
 * @package  RestaurantPress
 * @since    1.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main RestaurantPress Class.
 *
 * @class   RestaurantPress
 * @version 1.6.0
 */
final class RestaurantPress {

	/**
	 * RestaurantPress Version.
	 *
	 * @var string
	 */
	public $version = '1.6.0';

	/**
	 * The single instance of the class.
	 *
	 * @var RestaurantPress
	 */
	protected static $_instance = null;

	/**
	 * Session instance.
	 *
	 * @var RP_Session|RP_Session_Handler
	 */
	public $session = null;

	/**
	 * Query instance.
	 *
	 * @var RP_Query
	 */
	public $query = null;

	/**
	 * Food factory instance.
	 *
	 * @var RP_Food_Factory
	 */
	public $food_factory = null;

	/**
	 * Integrations instance.
	 *
	 * @var RP_Integrations
	 */
	public $integrations = null;

	/**
	 * Array of deprecated hook handlers.
	 *
	 * @var array of RP_Deprecated_Hooks
	 */
	public $deprecated_hook_handlers = array();

	/**
	 * Main RestaurantPress Instance.
	 *
	 * Ensure only one instance of RestaurantPress is loaded or can be loaded.
	 *
	 * @static
	 * @see    RP()
	 * @return RestaurantPress - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		rp_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'restaurantpress' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		rp_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'restaurantpress' ), '1.0' );
	}

	/**
	 * RestaurantPress Constructor.
	 */
	private function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'restaurantpress_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( RP_PLUGIN_FILE, array( 'RP_Install', 'install' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( 'RP_Shortcodes', 'init' ) );
		add_action( 'init', array( $this, 'wpdb_table_fix' ), 0 );
		add_action( 'switch_blog', array( $this, 'wpdb_table_fix' ), 0 );
	}

	/**
	 * Define RP Constants.
	 */
	private function define_constants() {
		$this->define( 'RP_ABSPATH', dirname( RP_PLUGIN_FILE ) . '/' );
		$this->define( 'RP_PLUGIN_BASENAME', plugin_basename( RP_PLUGIN_FILE ) );
		$this->define( 'RP_VERSION', $this->version );
		$this->define( 'RP_ROUNDING_PRECISION', 4 );
		$this->define( 'RP_SESSION_CACHE_GROUP', 'rp_session_id' );
		$this->define( 'RP_TEMPLATE_DEBUG_MODE', false );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Includes the required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Class autoloader.
		 */
		include_once( RP_ABSPATH . 'includes/class-rp-autoloader.php' );

		/**
		 * Abstract classes.
		 */
		include_once( RP_ABSPATH . 'includes/abstracts/abstract-rp-food.php' ); // Foods.
		include_once( RP_ABSPATH . 'includes/abstracts/abstract-rp-settings-api.php' ); // Settings API (for integrations).
		include_once( RP_ABSPATH . 'includes/abstracts/abstract-rp-integration.php' ); // An integration with a service.
		include_once( RP_ABSPATH . 'includes/abstracts/abstract-rp-deprecated-hooks.php' );
		include_once( RP_ABSPATH . 'includes/abstracts/abstract-rp-session.php' );

		/**
		 * Core classes.
		 */
		include_once( RP_ABSPATH . 'includes/rp-core-functions.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-post-types.php' ); // Registers post types.
		include_once( RP_ABSPATH . 'includes/class-rp-install.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-post-data.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-ajax.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-query.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-food-factory.php' ); // Food factory.
		include_once( RP_ABSPATH . 'includes/class-rp-integrations.php' ); // Loads integrations.
		include_once( RP_ABSPATH . 'includes/class-rp-cache-helper.php' ); // Cache Helper.
		include_once( RP_ABSPATH . 'includes/class-rp-deprecated-action-hooks.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-deprecated-filter-hooks.php' );

		if ( $this->is_request( 'admin' ) ) {
			include_once( RP_ABSPATH . 'includes/admin/class-rp-admin.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {
			include_once( RP_ABSPATH . 'includes/class-rp-session-handler.php' );
		}

		$this->query = new RP_Query();
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {
		include_once( RP_ABSPATH . 'includes/rp-notice-functions.php' );
		include_once( RP_ABSPATH . 'includes/rp-template-hooks.php' );
		include_once( RP_ABSPATH . 'includes/class-rp-template-loader.php' );  // Template Loader.
		include_once( RP_ABSPATH . 'includes/class-rp-frontend-scripts.php' ); // Frontend Scripts.
		include_once( RP_ABSPATH . 'includes/class-rp-shortcodes.php' );       // Shortcodes class.
	}

	/**
	 * Function used to Init RestaurantPress Template Functions - This makes them pluggable by plugins and themes.
	 */
	public function include_template_functions() {
		include_once( RP_ABSPATH . 'includes/rp-template-functions.php' );
	}

	/**
	 * Init RestaurantPress when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_restaurantpress_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Load class instances.
		$this->food_factory                        = new RP_Food_Factory(); // Food Factory to create new food instances.
		$this->integrations                        = new RP_Integrations(); // Integrations class.
		$this->deprecated_hook_handlers['actions'] = new RP_Deprecated_Action_Hooks();
		$this->deprecated_hook_handlers['filters'] = new RP_Deprecated_Filter_Hooks();

		// Classes/actions loaded for the frontend and for ajax requests.
		if ( $this->is_request( 'frontend' ) ) {
			// Session class, handles session data for users - can be overwritten if custom handler is needed.
			$session_class  = apply_filters( 'restaurantpress_session_handler', 'RP_Session_Handler' );
			$this->session  = new $session_class();
			$this->session->init();
		}

		// Init action.
		do_action( 'restaurantpress_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/restaurantpress/restaurantpress-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/restaurantpress-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'restaurantpress' );

		unload_textdomain( 'restaurantpress' );
		load_textdomain( 'restaurantpress', WP_LANG_DIR . '/restaurantpress/restaurantpress-' . $locale . '.mo' );
		load_plugin_textdomain( 'restaurantpress', false, plugin_basename( dirname( RP_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Ensure theme compatibility and setup image sizes.
	 */
	public function setup_environment() {
		$this->add_thumbnail_support();
		$this->add_image_sizes();
	}

	/**
	 * Ensure post thumbnail support is turned on.
	 */
	private function add_thumbnail_support() {
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}
		add_post_type_support( 'food_menu', 'thumbnail' );
	}

	/**
	 * Add RP Image sizes to WP.
	 */
	private function add_image_sizes() {
		$food_grid      = rp_get_image_size( 'food_grid' );
		$food_single    = rp_get_image_size( 'food_single' );
		$food_thumbnail = rp_get_image_size( 'food_thumbnail' );

		add_image_size( 'food_grid', $food_grid['width'], $food_grid['height'], $food_grid['crop'] );
		add_image_size( 'food_single', $food_single['width'], $food_single['height'], $food_single['crop'] );
		add_image_size( 'food_thumbnail', $food_thumbnail['width'], $food_thumbnail['height'], $food_thumbnail['crop'] );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', RP_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( RP_PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'restaurantpress_template_path', 'restaurantpress/' );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * RestaurantPress Term Meta API - set table name.
	 */
	public function wpdb_table_fix() {
		global $wpdb;

		if ( get_option( 'db_version' ) < 34370 ) {
			$wpdb->restaurantpress_termmeta = $wpdb->prefix . 'restaurantpress_termmeta';
			$wpdb->tables[]                 = 'restaurantpress_termmeta';
		}
	}
}
