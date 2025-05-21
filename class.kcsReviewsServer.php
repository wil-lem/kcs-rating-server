<?php

class kcsReviewsServer extends kcsRatingServer
{

    /**
     * @var array 
     * @deprecated
     */
    private array $reviews = [];

    /**
     * @var GeminiLabs\SiteReviews\Reviews
     */
    private GeminiLabs\SiteReviews\Reviews $glsr_reviews;

    function load()
    {
        parent::load();

        $this->loadReviews();
    }

    function loadReviews(): void {
        if(function_exists('glsr')) {
            $this->loadGlsrReviews();
            return;
        }
        $this->loadAitReviews();
    }

    function loadGlsrReviews(): void {

        $this->glsr_reviews = glsr_get_reviews([
            'post_id' => $this->post->ID,
            'status' => 'approved',
            'number' => 0,
        ]);        
    }

    /**
     * Load the AIT reviews
     * 
     * @deprecated
     * @return void
     * @throws Exception
     */
    function loadAitReviews() {

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
        if(function_exists('glsr')) {
            $data['reviews'] = $this->outputGlsrReviews();
        } else {
            $data['reviews'] = $this->outputAitReviews();
        }
        return $data;
    }



    /**
     * Output the AIT reviews
     * 
     * @deprecated
     * @return array
     */
    function outputAitReviews(): array {
        if(count($this->reviews)) {
            foreach ($this->reviews AS $review) {
                $data['reviews'][] = $this->outputReview($review);
            }
        }
        return $data;
    }

    /**
     * Output the GLSR reviews
     * 
     * @return array
     */
    function outputGlsrReviews(): array {
        $data = [];
        foreach ($this->glsr_reviews AS $review) {
            $data[] = $this->outputGlsrReview($review);
        }
        
        return $data;
    }

    /**
     * Output the GLSR review data
     * 
     * @param GeminiLabs\SiteReviews\Review $review
     * @return stdClass
     */
    function outputGlsrReview(GeminiLabs\SiteReviews\Review $review): stdClass {
        $output = new stdClass();

        $output->author = $review->get('title');
        $output->content = $review->get('content');
        $output->date = strtotime($review->get('date'));
        $output->ratings = [];
        $output->rating_mean = $review->get('rating');
        $output->rating_mean_rounded = $review->get('rating');

        $postMeta = get_post_meta($review->ID, 'kcs_migrate_review_metadata', true);
        if(!empty($postMeta['ratings'])) {
            $output->ratings = $postMeta['ratings'];
        } 
        return $output;
    }


    /**
     * Output the review data
     * 
     * @deprecated
     * @param WP_Post $review
     * @return stdClass
     */
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
