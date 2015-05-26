<?php
/**
 * Plugin Name: WooCommerce Restrict Categories
 * Plugin URI: http://www.woothemes.com/products/woocommerce-restrict-categories/
 * Description:
 * Version: 1.0.0
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: Frankie Jarrett
 * Developer URI: http://frankiejarrett.com/
 * Depends: WooCommerce
 * Text Domain: woocommerce-restrict-categories
 * Domain Path: /languages
 *
 * Copyright: © 2009-2015 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 *
 *
 *
 * @version 1.0.0
 * @package WooCommerce
 * @author  Frankie Jarrett
 */
class WC_Restrict_Categories {

	/**
	 * Hold class instance
	 *
	 * @access public
	 * @static
	 *
	 * @var WC_Restrict_Categories
	 */
	public static $instance;

	/**
	 * Hold the taxonomy slugs where restriction is enabled
	 *
	 * @access public
	 * @static
	 *
	 * @var array
	 */
	public static $taxonomies;

	/**
	 * Plugin version number
	 *
	 * @const string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique key prefix
	 *
	 * @const string
	 */
	const PREFIX = 'wcrc_';

	/**
	 * Class constructor
	 *
	 * @access private
	 */
	private function __construct() {
		if ( ! $this->woocommerce_exists() ) {
			return;
		}

		define( 'WC_RESTRICT_CATEGORIES_URL', plugins_url( '/', __FILE__ ) );
		define( 'WC_RESTRICT_CATEGORIES_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WC_RESTRICT_CATEGORIES_INC_DIR', WC_RESTRICT_CATEGORIES_DIR . 'includes/' );

		add_action( 'plugins_loaded', function() {
			foreach ( glob( WC_RESTRICT_CATEGORIES_INC_DIR . '*.php' ) as $include ) {
				if ( is_readable( $include ) ) {
					require_once $include;
				}
			}
		} );

		/**
		 * Register the taxonomy slugs where restriction will be enabled
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		self::$taxonomies = (array) apply_filters( 'wcrc_taxonomies', array( 'product_cat', 'product_tag' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Return an active instance of this class
	 *
	 * @access public
	 * @since 1.0.0
	 * @static
	 *
	 * @return WC_Restrict_Categories
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns true if WooCommerce exists
	 *
	 * Looks at the active list of plugins on the site to
	 * determine if WooCommerce is installed and activated.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function woocommerce_exists() {
		return in_array( 'woocommerce/woocommerce.php', (array) apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	/**
	 * Enqueue scripts and styles in the admin
	 *
	 * @action admin_enqueue_scripts
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) ) {
			return;
		}

		$screen_ids = array();

		foreach ( self::$taxonomies as $taxonomy ) {
			$screen_ids[] = 'edit-' . $taxonomy;
		}

		$screen_ids = implode( ' ', $screen_ids );

		if ( false === strpos( $screen_ids, $screen->id ) ) {
			return;
		}

		// Scripts
		wp_enqueue_script( 'wcrc-admin', WC_RESTRICT_CATEGORIES_URL . 'ui/js/admin.js', array( 'jquery', 'select2' ), self::VERSION );

		// Styles
		wp_enqueue_style( 'wcrc-admin', WC_RESTRICT_CATEGORIES_URL . 'ui/css/admin.css', array(), self::VERSION );
	}

	/**
	 * Return an array of user role labels
	 *
	 * @access public
	 * @since 1.0.0
	 * @static
	 *
	 * @return array
	 */
	public static function get_role_labels() {
		$roles  = array();
		$_roles = new WP_Roles();

		foreach ( $_roles->get_names() as $role => $label ) {
			$roles[ $role ] = translate_user_role( $label );
		}

		return (array) $roles;
	}

}

/**
 * Instantiate the plugin instance
 *
 * @global WC_Restrict_Categories
 */
$GLOBALS['wc_restrict_categories'] = WC_Restrict_Categories::get_instance();
