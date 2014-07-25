<?php
/**
 * uninstall.php
 *
 * @created   7/25/14 9:44 AM
 * @author    Mindshare Studios, Inc.
 * @copyright Copyright (c) 2014
 * @link      http://www.mindsharelabs.com/documentation/
 *
 */

//if uninstall not called from WordPress exit
if(!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

if(!defined('EXAMPLE_PLUGIN_OPTIONS')) {
	define('EXAMPLE_PLUGIN_OPTIONS', 'example_plugin_options');
}

$option_name = EXAMPLE_PLUGIN_OPTIONS;
delete_option($option_name);

// For site options in multisite
//delete_site_option($option_name);

//drop a custom db table
//global $wpdb;
//$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mytable");
