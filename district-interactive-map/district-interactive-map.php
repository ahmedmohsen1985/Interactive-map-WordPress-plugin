<?php
/**
 * Plugin Name: District Interactive Map
 * Plugin URI:  https://example.com/district-interactive-map
 * Description: A standalone interactive split-screen map plugin for WordPress.
 * Version:     1.0.0
 * Author:      Principal WordPress Plugin Architect
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: district-interactive-map
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'DIM_VERSION', '1.0.0' );
define( 'DIM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DIM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once DIM_PLUGIN_DIR . 'includes/class-dim-settings.php';
require_once DIM_PLUGIN_DIR . 'includes/class-dim-cpt.php';
require_once DIM_PLUGIN_DIR . 'includes/class-dim-metaboxes.php';
require_once DIM_PLUGIN_DIR . 'includes/class-dim-shortcode.php';

/**
 * Plugin activation handler.
 */
function dim_activate_plugin() {
    if ( ! class_exists( 'DIM_CPT' ) ) {
        require_once DIM_PLUGIN_DIR . 'includes/class-dim-cpt.php';
    }

    $cpt = new DIM_CPT();
    $cpt->register();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dim_activate_plugin' );

/**
 * Plugin deactivation handler.
 */
function dim_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'dim_deactivate_plugin' );

/**
 * Boot the plugin.
 */
function dim_init() {
    new DIM_Settings();
    new DIM_CPT();
    new DIM_Metaboxes();
    new DIM_Shortcode();
}
add_action( 'plugins_loaded', 'dim_init' );
