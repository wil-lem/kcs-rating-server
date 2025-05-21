<?php

/*
Plugin Name: KCS Rating server
Plugin URI: https://kenniscentrumsteen.nl/
Description: Exposes AIT Ratings using json api
Version: 0.1
Author: KCS
Author URI: https://kenniscentrumsteen.nl/
License: Non-Free
Text Domain: kcs_ratings_server
*/
define( 'KCS_RATINGS_SERVER__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KCS_RATINGS_SERVER__BASE_ROUTE', 'kcs-ratings-server/([0-9]+)' );
define( 'KCS_RATINGS_SERVER__BASE_REVIEWS_ROUTE', 'kcs-reviews-server/([0-9]+)' );


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}

require_once( KCS_RATINGS_SERVER__PLUGIN_DIR . 'class.kcsRatingServer.php' );
require_once( KCS_RATINGS_SERVER__PLUGIN_DIR . 'class.kcsReviewsServer.php' );
require_once( KCS_RATINGS_SERVER__PLUGIN_DIR . 'kcs_ratings_settings.php' );
require_once( KCS_RATINGS_SERVER__PLUGIN_DIR . 'kcs_ratings_server_routing.php' );
require_once( KCS_RATINGS_SERVER__PLUGIN_DIR . 'class.kcsRatingAPIController.php' );


add_action('rest_api_init', function () {
    register_rest_route('kcs-ratings-server/v1', '/all-reviews', array(
        'methods'  => 'GET',
        'callback' => 'kcs_ratings_server_all_reviews',
        'permission_callback' => '__return_true', // No auth required
    ));
});

function kcs_ratings_server_all_reviews( $request ) {
    if(function_exists('glsr')) {
        // The api does not work with the glsr plugin
        return new WP_Error( 'glsr_error', 'The glsr plugin is not supported.', array( 'status' => 500 ) );
    }
    $api =  new KcsRatingAPIController();
    $response = $api->index();
    return new WP_REST_Response( $response, 200 );
}



if( is_admin() )
    $kcs_ratings_server_settings_page = new KcsRatingsServerSettingsPage();