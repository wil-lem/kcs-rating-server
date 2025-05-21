<?php

class kcsRatingServer {

    var $company_id;

    var $errors = [];

    var $post;

    var $post_meta;

    var $post_slug;

    var $post_title;

    var $rating_full;

    var $rating_rounded;

    var $review_link;

    var $review_count;
    
    var $base_url;
    
    var $post_type;

    function __construct($id)
    {
        $this->company_id = $id;
        try {
            $this->load();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * Get the post meta value
     * 
     * @deprecated
     * @param string $key
     * @return mixed
     */
    function getMetaValue($key) {
        if(isset($this->post_meta[$key][0])) {
            return $this->post_meta[$key][0];
        }
        return FALSE;
    }

    /**
     * Get the rating mean
     * 
     * @deprecated
     * @return float|int
     */
    function getMetaValueRatingFull() {
        return $this->getMetaValue('rating_full');
    }

    /**
     * Get the rating mean
     * 
     * @deprecated
     * @return float|int
     */
    function getMetaValueRatingMean() {
        return $this->getMetaValue('rating_mean');
    }

    /**
     * Get the rating mean
     * 
     * @deprecated
     * @return float|int
     */
    function getPostID() {
        return $this->post->ID;
    }

    /**
     * Get the post type
     * 
     * @deprecated
     * @return string|bool
     */
    function getPostType() {
        if(isset($this->post->post_type)) {
            return $this->post->post_type;
        }
        return FALSE;
    }

    /**
     * Get the rating mean
     * 
     * @deprecated
     * @return float|int
     */
    function getRatingFull() {
        $rating = 0;
        if($this->getMetaValueRatingFull() && $this->getMetaValueRatingFull() > 0) {
            $rating = $this->getMetaValueRatingFull();
        }
        if($this->getMetaValueRatingMean() && $this->getMetaValueRatingMean() > 0) {
            $rating = $this->getMetaValueRatingMean();
        }
        return $rating;
    }

    /**
     * Get the rating mean
     * 
     * @deprecated
     * @return float|int
     */
    function fixMigratedPost() {
        if($this->getPostType() == 'ait-dir-item') {
            $post_id = $this->get_migrated_post_id();
            if($post_id) {
                $this->post = get_post($post_id);
            }
        }
    }

    /**
     * Get the migrated post id
     * 
     * @deprecated
     * @return int|bool
     */
    function get_migrated_post_id() {
        $meta_values = get_post_meta($this->post->ID);
        if(isset($meta_values['ait_migrated'][0])) {
            $migrate_info = unserialize($meta_values['ait_migrated'][0]);
            $new_id = $migrate_info['newid'];

            return $new_id;
        }
        return false;
    }

    function loadGlsr() {
        // Load the post id based on the migrated post id
        global $wpdb;
        
        $post_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            'kcs_migrate_id',
            $this->company_id
        ));
        
        if(empty($post_id)) {
            // Attempt to load the post directly
            $post = get_post($this->company_id);
            if(! $post || $post->post_type != 'item') {
                $this->errors[] = 'Post not found';
                return;
            }
        } else {
            $post = get_post($post_id);
        }

        if (! $post || $post->post_type != 'item') {
            $this->errors[] = 'Post not found';
            return;
        }
        $this->post = $post;

        $reviews = glsr_get_reviews([
            'assigned_posts' => $post->ID,
            'status' => 'approved',
            'number' => 0,
        ]);

        $count = 0;
        $sum = 0;
        foreach($reviews as $review) {
            $count++;
            $sum += $review->rating;
        }
        if($count > 0) {
            $this->rating_full = number_format($sum / $count, 1);
        } else {
            $this->rating_full = 0;
        }
        $this->rating_rounded = ($this->rating_full * 2) * 10;
        $this->review_count = $count;

        // Load rating data
        $this->post = $post;
        $this->post_meta = get_post_meta($this->getPostID());

        $base_url = get_site_url();

        $this->post_slug = $this->post->post_name;

        $this->post_title = $this->post->post_title;

        $this->review_link = $base_url . '/item/' . $this->post_slug . '/' . '#review';

        $this->base_url = $base_url;

    }

    /**
     * Load the AIT reviews
     * 
     * @deprecated
     * @return void
     * @throws Exception
     */
    function loadAit(): void {
        $this->post = get_post($this->company_id);
        $this->fixMigratedPost();

        if(empty($this->post)) {
            throw new Exception('Company post not found');
        }

        $this->post_meta = get_post_meta($this->getPostID());
        if(empty($this->post_meta)) {
           throw new Exception('Company post meta not found');
        }
        $base_url = get_site_url();

        $this->post_slug = $this->post->post_name;

        $this->post_title = $this->post->post_title;

        $this->rating_rounded = ($this->getMetaValue('_glsr_average') * 2) * 10;

        $this->rating_full = number_format($this->getMetaValue('_glsr_average') * 2, 1);

        $this->review_link = $base_url . '/item/' . $this->post_slug . '/' . '#review';

        $this->review_count = $this->getMetaValue('rating_count');

        $this->base_url = $base_url;
    }

    function load() {
        if(function_exists('glsr')) {
            $this->loadGlsr();
            return;
        }
        $this->loadAit();
    }

    function output() {
        $data = ['success'=>TRUE];
        if(count($this->errors) > 0) {
            $data['success'] = FALSE;
            $data['errors'] = $this->errors;
            return;
        }
        $data['rating']['full'] = $this->rating_full;
        $data['rating']['rounded'] = $this->rating_rounded;
        $data['url'] = $this->review_link;
        $data['review_count'] = $this->review_count;
        $data['base_url'] = $this->base_url;
        $data['title'] = $this->post_title;
        return $data;
    }
}