<?php

// Catch POST data

// 1. Group ID
// 2. User ID
// 3. Time Stamp
// 4. Session Number
// 5. Type (Step, Complete)

// Record POST data




function my_awesome_func( $data ) {




//    $posts = get_posts( array(
//        'author' => $data['id'],
//    ) );
//
//    if ( empty( $posts ) ) {
//        return null;
//    }
//
//    return $posts[0]->post_title;
}

//add_action( 'rest_api_init', function () {
//    register_rest_route( 'zume/v1', '/step/{id}', array(
//        'methods' => 'GET',
//        'callback' => 'my_awesome_func',
//    ) );
//} );
//
////load script
//wp_enqueue_script( 'my-post-submitter', plugin_dir_url( __FILE__ ) . 'post-submitter.js', array( 'jquery' ) );
//
////localize data for script
//wp_localize_script( 'my-post-submitter', 'POST_SUBMITTER', array(
//        'root' => esc_url_raw( rest_url() ),
//        'nonce' => wp_create_nonce( 'wp_rest' ),
//        'success' => __( 'Thanks for your submission!', 'your-text-domain' ),
//        'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
//        'current_user_id' => get_current_user_id()
//    )
//);

