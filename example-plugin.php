<?php
/*
Plugin Name: Example Plugin
Plugin URI: http://mindsharelabs.com/
Description: Example Plugin
Version: 0.1
Author: Mindshare Studios, Inc.
Author URI: http://mind.sh/are/
License: GNU General Public License
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Text Domain: example-plugin
Domain Path: /lang
*/

/*
 * INSTRUCTIONS:
 *
 * Search and replace the following strings:
 * "EXAMPLE_PLUGIN", "example-plugin", "$example_plugin", "example_plugin", "Example Plugin"
 * with the appropriate alternates for your plugin.
 *
 *
 */

/**
 *
 * Copyright 2014  Mindshare Studios, Inc. (http://mind.sh/are/)
 *
 * Plugin template was forked from the WP Settings Framework by Gilbert Pellegrom http://dev7studios.com
 * and the WordPress Plugin Boilerplate by Christopher Lamm http://www.theantichris.com
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

		/**
		 * @var example_plugin_settings
		 */
		private $settings_framework;

		/**
		 * Initialize the plugin. Set up actions / filters.
		 *
		 */
		public function __construct() {

			// i8n, uncomment for translation support
			//add_action('plugins_loaded', array($this, 'load_textdomain'));

			// Admin scripts
			add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'register_styles'));

			// Plugin action links
			add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);

			// Frontend scripts
			add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'register_styles'));

			// Activation hooks
			register_activation_hook(__FILE__, array($this, 'activate'));
			register_deactivation_hook(__FILE__, array($this, 'deactivate'));

			// Uninstall hook
			register_uninstall_hook(EXAMPLE_PLUGIN_DIR_PATH.'uninstall.php', NULL);

			// Settings Framework
			add_action('admin_menu', array($this, 'admin_menu'), 99);
			require_once(EXAMPLE_PLUGIN_DIR_PATH.'lib/settings-framework/settings-framework.php');
			$this->settings_framework = new example_plugin_settings(EXAMPLE_PLUGIN_DIR_PATH.'views/settings.php', EXAMPLE_PLUGIN_OPTIONS);
			// Add an optional settings validation filter (recommended)
			add_filter($this->settings_framework->get_option_group().'_validate', array($this, 'validate_settings'));

			// uncomment to disable the Uninstall and Reset Default buttons
			//$this->settings_framework->show_reset_button = FALSE;
			//$this->settings_framework->show_uninstall_button = FALSE;

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
			load_plugin_textdomain(EXAMPLE_PLUGIN_PLUGIN_SLUG, FALSE, EXAMPLE_PLUGIN_DIR_PATH.'/lang');
		}

		/**
		 * Activation
		 */
		public function activate() {
		}

		/**
		 * Deactivation
		 */
		public function deactivate() {
		}

		/**
		 * Install
		 */
		public function install() {
		}

		/**
		 * WordPress options page
		 *
		 */
		public function admin_menu() {
			// top level page
			//add_menu_page(__(EXAMPLE_PLUGIN_PLUGIN_NAME, 'example-plugin'), __(EXAMPLE_PLUGIN_PLUGIN_NAME, 'example-plugin'), 'manage_options', EXAMPLE_PLUGIN_PLUGIN_SLUG, array($this,'settings_page'));
			// Settings page
			add_submenu_page('options-general.php', __(EXAMPLE_PLUGIN_PLUGIN_NAME.' Settings', 'example-plugin'), __(EXAMPLE_PLUGIN_PLUGIN_NAME.' Settings', 'example-plugin'), 'manage_options', EXAMPLE_PLUGIN_PLUGIN_SLUG, array(
				$this,
				'settings_page'
			));
		}

		/**
		 *  Settings page
		 *
		 */
		public function settings_page() {

			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php echo EXAMPLE_PLUGIN_PLUGIN_NAME; ?></h2>
				<?php
				// Output settings-framework form
				$this->settings_framework->settings();
				?>
			</div>
			<?php

			// Get settings
			//$settings = $this->get_settings(EXAMPLE_PLUGIN_OPTIONS);
			//echo '<pre>'.print_r($settings, TRUE).'</pre>';

			// Get individual setting
			//$setting = $this->get_setting(EXAMPLE_PLUGIN_OPTIONS, 'general', 'text');
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
		 * Converts the settings-framework filename to option group id
		 *
		 * @param $settings_file string settings-framework file
		 *
		 * @return string option group id
		 */
		public function get_option_group($settings_file) {
			$option_group = preg_replace("/[^a-z0-9]+/i", "", basename($settings_file, '.php'));
			return $option_group;
		}

		/**
		 * Get the settings from a settings-framework file/option group
		 *
		 * @param $option_group string option group id
		 *
		 * @return array settings
		 */
		public function get_settings($option_group) {
			return get_option($option_group);
		}

		/**
		 * Get a setting from an option group
		 *
		 * @param $option_group string option group id
		 * @param $section_id   string section id
		 * @param $field_id     string field id
		 *
		 * @return mixed setting or false if no setting exists
		 */
		public function get_setting($option_group, $section_id, $field_id) {
			$options = get_option($option_group);
			if(isset($options[$option_group.'_'.$section_id.'_'.$field_id])) {
				return $options[$option_group.'_'.$section_id.'_'.$field_id];
			}
			return FALSE;
		}

		/**
		 * Delete all the saved settings from a settings-framework file/option group
		 *
		 * @param $option_group string option group id
		 */
		public function delete_settings($option_group) {
			delete_option($option_group);
		}

		/**
		 * Deletes a setting from an option group
		 *
		 * @param $option_group string option group id
		 * @param $section_id   string section id
		 * @param $field_id     string field id
		 *
		 * @return mixed setting or false if no setting exists
		 */
		public function delete_setting($option_group, $section_id, $field_id) {
			$options = get_option($option_group);
			if(isset($options[$option_group.'_'.$section_id.'_'.$field_id])) {
				$options[$option_group.'_'.$section_id.'_'.$field_id] = NULL;
				return update_option($option_group, $options);
			}
			return FALSE;
		}

		/**
		 *
		 * Add a settings link to plugins page
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 */
		public function plugin_action_links($links, $file) {
			if($file == plugin_basename(__FILE__)) {
				$settings_link = '<a href="options-general.php?page='.EXAMPLE_PLUGIN_PLUGIN_SLUG.'" title="'.__(EXAMPLE_PLUGIN_PLUGIN_NAME, 'example-plugin').'">'.__('Settings', 'example-plugin').'</a>';
				array_unshift($links, $settings_link);
			}
			return $links;
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

$example_plugin = new EXAMPLE_PLUGIN();
