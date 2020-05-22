<?php
/**
 * Plugin Name: Thoughtcloud Affiliate Manager
 * Plugin URI: https://wbcomdesigns.com/
 * Description: Thoughtcloud Affiliate Manager.
 * Version: 1.0.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com/
 * Requires at least: 4.0
 * Tested up to: 4.9.4
 *
 * Text Domain: thoughtcl0ud-affiliate-manager
 * Domain Path: /languages/
 *
 * @package Thoughtcloud_Affiliate_Manager
 * @category Core
 * @author Wbcom Designs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Thoughtcloud_Affiliate_Manager' ) ) :

	/**
	 * Main Thoughtcloud_Affiliate_Manager Class.
	 *
	 * @class Thoughtcloud_Affiliate_Manager
	 * @version 1.0.0
	 */
	class Thoughtcloud_Affiliate_Manager {


		/**
		 * Thoughtcloud_Affiliate_Manager version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class.
		 *
		 * @var Thoughtcloud_Affiliate_Manager
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main Thoughtcloud_Affiliate_Manager Instance.
		 *
		 * Ensures only one instance of Thoughtcloud_Affiliate_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see INSTANTIATE_Thoughtcloud_Affiliate_Manager()
		 * @return Thoughtcloud_Affiliate_Manager - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}


		/**
		 * Thoughtcloud_Affiliate_Manager Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
			$this->includes();
			do_action( 'thoughtcl0ud_affiliate_manager_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since  1.0.0
		 */
		private function init_hooks() {
			add_filter('plugin_action_links_' . Thoughtcloud_Affiliate_Manager_PLUGIN_BASENAME, array( $this, 'alter_plugin_action_links' ));
		}

		/**
         * Add plugin settings link.
         *
         * @since  1.0.0
         * @access public
         *
         * @param array $plugin_links The plugin list links.
         */
        public function alter_plugin_action_links( $plugin_links ) {
            $settings_link = '<a href="options-general.php?page=thoughtcl0ud-affiliate-manager">' . __('Settings', 'peepso-bbpress') . '</a>';
            array_unshift($plugin_links, $settings_link);
            return $plugin_links;
        }


		/**
		 * Define Thoughtcloud_Affiliate_Manager Constants.
		 *
		 * @since  1.0.0
		 */
		private function define_constants() {
			$this->define( 'Thoughtcloud_Affiliate_Manager_PLUGIN_FILE', __FILE__ );
			$this->define( 'Thoughtcloud_Affiliate_Manager_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'Thoughtcloud_Affiliate_Manager_VERSION', $this->version );
			$this->define( 'Thoughtcloud_Affiliate_Manager_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'Thoughtcloud_Affiliate_Manager_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string      $name Define constant name.
		 * @param  string|bool $value Define constant value.
		 * @since  1.0.0
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since  1.0.0
		 */
		public function includes() {
			include_once 'core/class-thoughtcloud-manage-coupon-amount.php';
			include_once 'admin/class-thoughtcloud-discount-rule-panel.php';
		}

	}

endif;

/**
 * Main instance of Thoughtcloud_Affiliate_Manager.
 *
 * Returns the main instance of Thoughtcloud_Affiliate_Manager to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Thoughtcloud_Affiliate_Manager
 */
function instantiate_thoughtcl0ud_affiliate_manager() {
	return Thoughtcloud_Affiliate_Manager::instance();
}

// Global for backwards compatibility.
$GLOBALS['thoughtcl0ud_affiliate_manager'] = instantiate_thoughtcl0ud_affiliate_manager();