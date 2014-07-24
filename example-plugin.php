<?php
/*
Plugin Name: WP Settings Framework Example
Plugin URI: http://mindsharelabs.com/products/
Description: A WordPress plugin...
Version: 0.1
Author: Mindshare Studios, Inc.
Author URI: http://mind.sh/are/
License: GNU General Public License
License URI: license.txt
Text Domain: example-plugin
Domain Path: /lang
*/

/**
 *
 * Copyright 2014  Mindshare Studios, Inc. (http://mind.sh/are/)
 *
 * Based on the WP Settings Framework by Gilbert Pellegrom http://dev7studios.com
 * and on the WordPress Plugin Boilerplate by Christopher Lamm http://www.theantichris.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// deny direct access
if(!function_exists('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if(!defined('EXAMPLE_PLUGIN_VERSION')) {
	define('EXAMPLE_PLUGIN_VERSION', '0.1');
}

if(!defined('EXAMPLE_PLUGIN_MIN_WP_VERSION')) {
	define('EXAMPLE_PLUGIN_MIN_WP_VERSION', '3.9');
}

if(!defined('EXAMPLE_PLUGIN_PLUGIN_NAME')) {
	define('EXAMPLE_PLUGIN_PLUGIN_NAME', 'Example Plugin');
}

if(!defined('EXAMPLE_PLUGIN_PLUGIN_SLUG')) {
	define('EXAMPLE_PLUGIN_PLUGIN_SLUG', dirname(plugin_basename(__FILE__))); // plugin-slug
}

if(!defined('EXAMPLE_PLUGIN_DIR_PATH')) {
	define('EXAMPLE_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

if(!defined('EXAMPLE_PLUGIN_DIR_URL')) {
	define('EXAMPLE_PLUGIN_DIR_URL', trailingslashit(plugins_url(NULL, __FILE__)));
}

if(!defined('EXAMPLE_PLUGIN_OPTIONS')) {
	define('EXAMPLE_PLUGIN_OPTIONS', 'example_plugin_options');
}

if(!defined('EXAMPLE_PLUGIN_TEMPLATE_PATH')) {
	define('EXAMPLE_PLUGIN_TEMPLATE_PATH', trailingslashit(get_template_directory()).trailingslashit(EXAMPLE_PLUGIN_PLUGIN_SLUG));
	// e.g. /wp-content/themes/__ACTIVE_THEME__/plugin-slug
}

// check WordPress version
global $wp_version;
if(version_compare($wp_version, EXAMPLE_PLUGIN_MIN_WP_VERSION, "<")) {
	exit(EXAMPLE_PLUGIN_PLUGIN_NAME.' requires WordPress '.EXAMPLE_PLUGIN_MIN_WP_VERSION.' or newer.');
}

if(!class_exists('EXAMPLE_PLUGIN')) :
	/**
	 * Class EXAMPLE_PLUGIN
	 */
	class EXAMPLE_PLUGIN {

		private $settings_framework;

		private static $instance = NULL;

		/**
		 * @var $options - holds all plugin options
		 */
		public $options;

		/**
		 * Creates or returns an instance of this class.
		 * (Singleton design pattern)
		 */
		public static function get_instance() {
			if(NULL == self::$instance) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Initialize the plugin. Set up actions / filters.
		 *
		 */
		private function __construct() {

			// Admin scripts
			add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'register_styles'));

			// Frontend scripts
			add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'register_styles'));

			// Activation hooks
			register_activation_hook(__FILE__, array($this, 'activate'));
			register_deactivation_hook(__FILE__, array($this, 'deactivate'));

			// Setting framework
			add_action('admin_menu', array($this, 'admin_menu'), 99);
			require_once(EXAMPLE_PLUGIN_DIR_PATH.'settings/settings-framework.php');
			$this->settings_framework = new WordPressSettingsFramework(EXAMPLE_PLUGIN_DIR_PATH.'settings/example-settings.php', EXAMPLE_PLUGIN_OPTIONS);

			// Add an optional settings validation filter (recommended)
			add_filter($this->settings_framework->get_option_group().'_settings_validate', array($this, 'validate_settings'));

			// Run the plugin
			$this->run();
		}

		/**
		 * Returns the class name and version.
		 *
		 * @return string
		 */
		public function __toString() {
			return get_class($this).' '.$this->get_version();
		}

		/**
		 * Returns the plugin version number.
		 *
		 * @return string
		 */
		public function get_version() {
			return EXAMPLE_PLUGIN_VERSION;
		}

		/**
		 * @return string
		 */
		public function get_plugin_url() {
			return EXAMPLE_PLUGIN_DIR_URL;
		}

		/**
		 * @return string
		 */
		public function get_plugin_path() {
			return EXAMPLE_PLUGIN_DIR_PATH;
		}

		/**
		 * Register the plugin text domain for translation
		 *
		 */
		public function load_textdomain() {
			load_plugin_textdomain('subscribr', FALSE, EXAMPLE_PLUGIN_DIR_PATH.'/lang');
		}

		/**
		 * Activation code
		 */
		public function activate() {
		}

		/**
		 * Deactivation code
		 */
		public function deactivate() {
		}

		/**
		 * WordPress options page
		 *
		 */
		public function admin_menu() {
			add_menu_page(__('WPSF', 'wp-settings-framework'), __('WPSF', 'wp-settings-framework'), 'update_core', 'wpsf', array($this, 'settings_page'));
			add_submenu_page('wpsf', __('Settings', 'wp-settings-framework'), __('Settings', 'wp-settings-framework'), 'update_core', 'wpsf', array($this, 'settings_page'));
		}

		/**
		 *  Settings page
		 *
		 */
		public function settings_page() {

			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2>Example Plugin</h2>
				<?php
				// Output settings form
				$this->settings_framework->settings();
				?>
			</div>
			<?php

			// Get settings
			$settings = wpsf_get_settings('my_example_settings');
			//echo '<pre>'.print_r($settings, TRUE).'</pre>';

			// Get individual setting
			$setting = wpsf_get_setting('my_example_settings', 'general', 'text');
			//var_dump($setting);
		}

		/**
		 * Settings validation
		 *
		 * @see $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
		 *
		 * @param $input
		 *
		 * @return mixed
		 */
		public function validate_settings($input) {
			return $input;
		}

		/**
		 * Enqueue and register JavaScript
		 */
		public function register_scripts() {
		}

		/**
		 * Enqueue and register CSS
		 */
		public function register_styles() {
		}

		/**
		 * Run
		 */
		private function run() {
		}
	}

endif;

EXAMPLE_PLUGIN::get_instance();
