<?php

class kcsReviewsServer extends kcsRatingServer
{

    var $reviews = [];
    var $ratings_data = null;


    function load()
    {
        parent::load();

        $this->loadReviews();
        $this->loadRatings();
    }

    function loadReviews() {
        if (!function_exists('glsr_get_reviews')) {
            return;
        }
        
        $args = [
            'assigned_posts' => $this->getPostID(),
            'status' => 'approved',
            'display' => 9999, // Get all reviews
        ];
        
        $reviews = glsr_get_reviews($args);
        if ($reviews) {
            $this->reviews = $reviews->reviews;
        }
    }
    
    function loadRatings() {
        if (!function_exists('glsr_get_ratings')) {
            return;
        }
        
        $args = [
            'assigned_posts' => $this->getPostID(),
        ];
        
        $this->ratings_data = glsr_get_ratings($args);
    }

    function output()
    {
        $data = parent::output();

        // Override parent ratings with site-reviews data if available
        if ($this->ratings_data) {
            $data['rating']['full'] = number_format($this->ratings_data->average, 1);
            $data['rating']['rounded'] = ($this->ratings_data->average * 2) * 10;
            $data['review_count'] = $this->ratings_data->reviews;
        }

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

        // Use site-reviews review object properties
        $output->author = $review->author;
        $output->content = $review->content;
        $output->date = strtotime($review->date);
        $output->rating = $review->rating;
        $output->title = $review->title;
        
        return $output;
    }
}
