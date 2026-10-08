<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css' );

}

function googlesiteverification()
{
    require('includes/google-analytics.php');
}

add_action('wp_head', 'googlesiteverification');

function majalah_komunikasi_tracker() {
    // Check for a single page slug, page ID, or an array of multiple pages
    if ( is_page( array( 'majalah-komunikasi', '2016-2020', '2021-2025') ) ) {
        wp_enqueue_script(
            'majalahkomunikasi-js', // Unique handle name
            get_stylesheet_directory_uri() . '/assets/js/majalahkomunikasi.js', // Path to file
            array('jquery'), // Dependencies (e.g., array('jquery') if needed)
            '1.0.0', // Version number
            true // Load in footer (true) or header (false)
        );
    }
}
add_action( 'wp_enqueue_scripts', 'majalah_komunikasi_tracker' );