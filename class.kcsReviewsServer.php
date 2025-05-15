<?php

class kcsReviewsServer extends kcsRatingServer
{

    var $reviews = [];


    function load()
    {
        parent::load();

        $this->loadReviews();
    }

    function loadReviews() {
        $args = [
            'meta_query' => [
                [
                    'key' => 'post_id',
                    'value' => $this->getPostID(),
//                    'compare' => '=',
                ]
            ],
            'post_status'   => 'publish',
            'post_type'     =>'ait-review',
        ];
        $query = new WP_Query($args);

        if(isset($query->posts)) {
            $this->reviews = $query->posts;
        }
    }

    function output()
    {
        $data = parent::output();

        $data['reviews'] = [];
        if(count($this->reviews)) {
            foreach ($this->reviews AS $review) {
                $data['reviews'][] = $this->outputReview($review);
            }
        }
        return $data;
    }

    function outputReview($review) {
        $output = new stdClass();

        $review_meta = get_post_meta($review->ID);

        $output->author = $review->post_title;
        $output->content = $review->post_content;
        $output->date = strtotime($review->post_date);
        $output->ratings = NULL;
        $output->rating_mean = NULL;
        $output->rating_mean_rounded = NULL;

        if(isset($review_meta['ratings'][0])) {
            $output->ratings = json_decode($review_meta['ratings'][0]);
        }
        if(isset($review_meta['rating_mean'][0])) {
            $output->rating_mean = $review_meta['rating_mean'][0];
        }
        if(isset($review_meta['rating_mean_rounded'][0])) {
            $output->rating_mean_rounded = $review_meta['rating_mean_rounded'][0];
        }
        return $output;
    }
}
