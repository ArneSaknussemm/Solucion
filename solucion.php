<?php

/**
 *
 * The plugin bootstrap file
 *
 * This file is responsible for starting the plugin using the main plugin class file.
 *
 * @since 0.0.1
 * @package Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:     Link monitor
 * Description:     Verifica y lista qué enlaces generan inconvenientes y en qué post fueron detectados.
 * Version:         0.0.1
 * Author:          Francisco Marconi Juárez
 * Author URI:      https://github.com/ArneSaknussemm
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     verifica-enlaces
 * Domain Path:     /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

if ( ! class_exists( 'link_monitor' ) ) {

	/*
	 * main link_monitor class
	 *
	 * @class link_monitor
	 * @since 0.0.1
	 */
	class link_monitor {

		/*
		 * link_monitor plugin version
		 *
		 * @var string
		 */
		public $version = '4.7.5';

		/**
		 * The single instance of the class.
		 *
		 * @var link_monitor
		 * @since 0.0.1
		 */
		protected static $instance = null;

		/**
		 * Main link_monitor instance.
		 *
		 * @since 0.0.1
		 * @static
		 * @return link_monitor - main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * link_monitor class constructor.
		 */
		public function __construct() {
			$this->load_plugin_textdomain();
			$this->define_constants();
			$this->includes();
			$this->define_actions();
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'plugin-name', false, basename( dirname( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Include required core files
		 */
		public function includes() {
            // Example
			//require_once __DIR__ . '/includes/loader.php';

			// Load custom functions and hooks
			require_once __DIR__ . '/includes/includes.php';
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Define link_monitor constants
		 */
		private function define_constants() {
			define( 'LINK_MONITOR_PLUGIN_FILE', __FILE__ );
			define( 'LINK_MONITOR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'LINK_MONITOR_VERSION', $this->version );
			define( 'LINK_MONITOR_PATH', $this->plugin_path() );
			define( 'ERROR_CODES', array(
				array('status_code' => 1, 'name' => 'Enlace inseguro'),
				array('status_code' => 2, 'name' => 'Protocolo no especificado'),
				array('status_code' => 3, 'name' => 'Enlace malformado'),
				array('status_code' => 200, 'name' => 'OK'),
				array('status_code' => 400, 'name' => '400 Bad Request'),
				array('status_code' => 401, 'name' => '401 Unauthorized'),
				array('status_code' => 402, 'name' => '402 Payment Required'),
				array('status_code' => 403, 'name' => '403 Forbidden'),
				array('status_code' => 404, 'name' => '404 Not Found'),
				array('status_code' => 405, 'name' => '405 Method Not Allowed'),
				array('status_code' => 406, 'name' => '406 Not Acceptable'),
				array('status_code' => 408, 'name' => '408 Request Timeout'),
				array('status_code' => 409, 'name' => '409 Conflict'),
				array('status_code' => 410, 'name' => '410 Gone'),
				array('status_code' => 411, 'name' => '411 Length Required'),
				array('status_code' => 412, 'name' => '412 Precondition Failed'),
				array('status_code' => 413, 'name' => '413 Payload Too Large'),
				array('status_code' => 414, 'name' => '414 URI Too Long'),
				array('status_code' => 415, 'name' => '415 Unsupported Media Type'),
				array('status_code' => 416, 'name' => '416 Range Not Satisfiable'),
				array('status_code' => 417, 'name' => '417 Expectation Failed'),
				array('status_code' => 418, 'name' => '418 I\'m a teapot'),
				array('status_code' => 421, 'name' => '421 Misdirected Request'),
				array('status_code' => 422, 'name' => '422 Unprocessable Entity'),
				array('status_code' => 423, 'name' => '423 Locked'),
				array('status_code' => 424, 'name' => '424 Failed Dependency'),
				array('status_code' => 425, 'name' => '425 Too Early'),
				array('status_code' => 426, 'name' => '426 Upgrade Required'),
				array('status_code' => 428, 'name' => '428 Precondition Required'),
				array('status_code' => 429, 'name' => '429 Too Many Requests'),
				array('status_code' => 431, 'name' => '431 Request Header Fields Too Large'),
				array('status_code' => 451, 'name' => '451 Unavailable For Legal Reasons'),
				array('status_code' => 500, 'name' => '500 Internal Server Error'),
				array('status_code' => 501, 'name' => '501 Not Implemented'),
				array('status_code' => 502, 'name' => '502 Bad Gateway'),
				array('status_code' => 503, 'name' => '503 Service Unavailable'),
				array('status_code' => 504, 'name' => '504 Gateway Timeout'),
				array('status_code' => 505, 'name' => '505 HTTP Version Not Supported'),
				array('status_code' => 506, 'name' => '506 Variant Also Negotiates'),
				array('status_code' => 507, 'name' => '507 Insufficient Storage'),
				array('status_code' => 508, 'name' => '508 Loop Detected'),
				array('status_code' => 510, 'name' => '510 Not Extended'),
				array('status_code' => 511, 'name' => '511 Network Authentication Required'),
			));
		}

		/**
		 * Define link_monitor actions
		 */
		public function define_actions() {
			//
		}

		/**
		 * Define link_monitor menus
		 */
		public function define_menus() {
            //
		}
		public function define_cron() {
			if (! wp_next_scheduled ( 'monitor_links' )) {
				wp_schedule_event( time(), 'daily', 'monitor_links' );
			}

		}

		function deactivate_link_monitor(){

			wp_clear_scheduled_hook( 'monitor_links' );
		}
		
		function activate_link_monitor() {

			create_link_monitor_tables();
		}
	}
	
	$link_monitor = new link_monitor();
}

register_activation_hook(__FILE__, array($link_monitor, 'define_cron'));

register_activation_hook(__FILE__, array($link_monitor, 'activate_link_monitor'));

register_deactivation_hook(__FILE__, array($link_monitor, 'deactivate_link_monitor'));
