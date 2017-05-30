<?php 
function my_scripts_method() {
    wp_enqueue_script(
        'custom-script',
        get_stylesheet_directory_uri() . '/js/custom_script.js',
        array( 'jquery' )
    );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );