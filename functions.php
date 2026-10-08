<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css' );

}

function googlesiteverification() {
    require('includes/google-analytics.php');
}

add_action('wp_head', 'googlesiteverification');

function majalah_komunikasi_tracker() {
    // Check for a single page slug, page ID, or an array of multiple pages
    if ( is_page( array( 'majalah-komunikasi', '2016-2020', '2021-2025') ) ) {
        wp_enqueue_script(
            'majalahkomunikasi-js',
            get_stylesheet_directory_uri() . '/assets/js/majalahkomunikasi.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'majalah_komunikasi_tracker' );

// KARYAMAPS

function karyamaps_javascript() {
    global $post;
    // Check if this page contains the shortcode
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'karyamaps' ) ) {
        wp_enqueue_script(
            'googlemaps-markerclusterer',
            'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js',
            [],
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'karyamaps-js',
            get_stylesheet_directory_uri() . '/assets/js/karyamaps.js',
            ['googlemaps-markerclusterer'],
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'karyamaps_javascript' );

function karyamaps_shortcode_handler( $atts ){
    return '<div id="karyamap"></div>';
}
add_shortcode( 'karyamaps', 'karyamaps_shortcode_handler' );

add_action('rest_api_init', 'karyamaps_register_api_routes');
function karyamaps_register_api_routes() {
    register_rest_route('karyamaps/v1/', '/data', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'karyamaps_get_map_data',
        'permission_callback' => '__return_true',
    ]);
}

function karyamaps_fetch_data_from_file() {
    $file_path = get_stylesheet_directory() . '/assets/tsv/karyamaps.tsv';
    $file_contents = trim(file_get_contents($file_path));

    $line_fn = function($line) {
        $col_fn = function($col) {
            return trim($col);
        };
        return array_map($col_fn, explode("\t", trim($line)));
    };

    $lines = array_map($line_fn, explode("\n", $file_contents));
    $headers = $lines[0];

    $transform_fn = function($cols) use ($headers) {
        $col_count = count($cols);
        $header_count = count($headers);

        assert($col_count !== $header_count);

        $o = [];

        for ($i = 0; $i < $col_count; $i++) {
            $o[$headers[$i]] = $cols[$i];
        }
        if (!empty($o['New Image (1600x900)'])) {
            if (file_exists(get_stylesheet_directory() . '/assets/images/karyamaps/' . $o['New Image (1600x900)'])) {
                $o['image_url'] = get_stylesheet_directory_uri() . '/assets/images/karyamaps/' . $o['New Image (1600x900)'];
            } else {
                $o['image_url'] = get_stylesheet_directory_uri() . '/assets/images/karyamaps/no-image.png';
            }
        } else {
            $o['image_url'] = null;
        }

        return $o;
    };

    $body_lines = array_slice($lines, 1);

    $data = array_map($transform_fn, $body_lines);
    return $data;
}

function karyamaps_get_map_data() {
    $data = karyamaps_fetch_data_from_file();
    return rest_ensure_response([
        'status' => 'success',
        'data' => $data,
    ]);
}
