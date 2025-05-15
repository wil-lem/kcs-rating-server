<?php

add_action('parse_request', 'kcs_ratings_server_parse_request');

function kcs_ratings_server_parse_request($wp) {
    if(isset($wp->query_vars['kcs_ratings_server']) && isset($wp->query_vars['company_id'])) {
        $server = new kcsRatingServer($wp->query_vars['company_id']);
        echo json_encode($server->output());
        exit();
    } else if(isset($wp->query_vars['kcs_reviews_server']) && isset($wp->query_vars['company_id'])) {
        $server = new kcsReviewsServer($wp->query_vars['company_id']);
        echo json_encode($server->output());
        exit();
    }
}

function kcs_ratings_server_query_vars($vars) {
    // add KCS_RATINGS_SERVER to the valid list of variables
    $new_vars = array('kcs_ratings_server','company_id','kcs_reviews_server');
    $vars = $new_vars + $vars;
    return $vars;
}


// flush_rules() if our rules are not yet included
function _kcs_ratings_server_flush_rules(){
    $rules = get_option( 'rewrite_rules' );
    if ( ! isset( $rules[KCS_RATINGS_SERVER__BASE_ROUTE] )
        || !isset( $rules[KCS_RATINGS_SERVER__BASE_REVIEWS_ROUTE] )
    ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}

// Adding a new rule
function _kcs_ratings_server_insert_rewrite_rules( $rules )
{
    $newrules = array();
    $newrules[KCS_RATINGS_SERVER__BASE_ROUTE] = 'index.php?kcs_ratings_server=1&company_id=$matches[1]';
    $newrules[KCS_RATINGS_SERVER__BASE_REVIEWS_ROUTE] = 'index.php?kcs_reviews_server=1&company_id=$matches[1]';
    return $newrules + $rules;
}

add_filter('query_vars', 'kcs_ratings_server_query_vars');
add_filter( 'rewrite_rules_array','_kcs_ratings_server_insert_rewrite_rules' );
add_action( 'wp_loaded','_kcs_ratings_server_flush_rules' );

