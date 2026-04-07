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
    register_rest_route('kcs-ratings-server/v1', '/all-reviews-csv', array(
        'methods'  => 'GET',
        'callback' => 'kcs_ratings_server_all_reviews_csv',
        'permission_callback' => '__return_true', // No auth required
    ));
    register_rest_route('kcs-ratings-server/v1', '/all-reviews', array(
        'methods'  => 'GET',
        'callback' => 'kcs_ratings_server_all_reviews',
        'permission_callback' => '__return_true', // No auth required
    ));
});

function kcs_ratings_server_all_reviews( $request ) {
    $api =  new KcsRatingAPIController();
    $response = $api->index();
    return new WP_REST_Response( $response, 200 );
}

function kcs_ratings_server_all_reviews_csv( $request ): void {
    $api =  new KcsRatingAPIController();
    $companies = $api->index();

    // Create a CSV file
    $csv_file = fopen('php://output', 'w');
    // Set the headers for the CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="all_reviews.csv"');
    // Add the header row
    fputcsv($csv_file, array('Company ID', 'Company Name', 'Review Author', 'Review Content', 'Review Date', 'Rating Mean'));
    // Loop through the companies and their reviews
    foreach ($companies as $company) {
        foreach ($company['reviews'] as $review) {
            fputcsv($csv_file, array(
                $company['id'],
                $company['post_title'],
                $review->author,
                $review->content,
                date('Y-m-d H:i:s', $review->date),
                $review->rating_mean
            ));
        }
    }
    // Close the CSV file
    fclose($csv_file);
    // Exit to prevent any additional output
    exit();
}


if( is_admin() )
    $kcs_ratings_server_settings_page = new KcsRatingsServerSettingsPage();