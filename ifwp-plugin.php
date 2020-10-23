<?php
/*
Author: IFWP
Author URI: https://github.com/ifwp
Description: Improvements and Fixes for WordPress.
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network:
Plugin Name: IFWP Plugin
Plugin URI: https://github.com/ifwp/ifwp-plugin
Text Domain: ifwp-plugin
Version: 0.10.23.1
*/

if(!defined('ABSPATH')){
    die("Hi there! I'm just a plugin, not much I can do when called directly.");
}
if(defined('IFWP_Plugin')){
    wp_die("IFWP_Plugin constant already exists.");
}
define('IFWP_PLUGIN', __FILE__);
foreach(glob(plugin_dir_path(IFWP_PLUGIN) . 'extensions/*', GLOB_ONLYDIR) as $dir){
    $file = $dir . '/load.php';
    if(file_exists($file)){
        require_once($file);
    }
}
do_action('ifwp_plugin_loaded');
