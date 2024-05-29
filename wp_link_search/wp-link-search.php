<?php
/**
 * Plugin Name: WP Link Search
 * Description: Search for pages linking to a specific URL.
 * Version: 1.0
 * Author: Richard Sinka
 */

// Enqueue the CSS and JS files for the frontend
function wp_link_search_enqueue_scripts() {
    wp_enqueue_style('wp-link-search-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('wp-link-search-script', plugin_dir_url(__FILE__) . 'js/wp-link-search.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'wp_link_search_enqueue_scripts');

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main plugin class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-link-search.php';

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'WP_Link_Search', 'init' ) );
