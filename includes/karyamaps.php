<?php
/**
 * Plugin Name: Google Map Custom Plugin
 * Description: Map Plugin
 * Version: 1.0
 * Author: Keenan A. Leman
 */

if (!defined('WPINC')) {
    die;
}

add_filter( 'query_vars', 'gmap_register_query_vars' );
function gmap_register_query_vars( $vars ) {
    $vars[] = 'map';
    return $vars;
}

add_action( 'init', 'gmap_rewrite_rules' );
function gmap_rewrite_rules() {
    add_rewrite_rule( '^map/?$', 'index.php?map=1', 'top' );
}

add_action( 'template_redirect', 'gmap_render_frontend_page' );
function gmap_render_frontend_page() {
    if ( get_query_var( 'map' ) == 1 ) {
        include(plugin_dir_path(__FILE__) . "./app.php");
        exit;
    }
}

add_action('wp_enqueue_scripts', 'gmap_enqueue_frontend_scripts');
function gmap_enqueue_frontend_scripts() {
    // wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer )
    wp_enqueue_script(
        'app-script',
        plugins_url( '/js/app.js', __FILE__ ),
        array(),
        '1.0.0',
        true
    );
}

register_activation_hook( __FILE__, 'gmap_activate' );
function gmap_activate() {
    gmap_rewrite_rules();
    flush_rewrite_rules();
}
