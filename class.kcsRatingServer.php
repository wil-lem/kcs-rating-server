<?php

class kcsRatingServer {

    var $company_id;

    var $errors = [];

    var $post;

    var $post_meta;

    var $post_slug;

    var $post_title;

    function __construct($id)
    {
        $this->company_id = $id;
        try {
            $this->load();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    function getMetaValue($key) {
        if(isset($this->post_meta[$key][0])) {
            return $this->post_meta[$key][0];
        }
        return FALSE;
    }

    function getMetaValueRatingFull() {
        return $this->getMetaValue('rating_full');
    }

    function getMetaValueRatingMean() {
        return $this->getMetaValue('rating_mean');
    }

    function getPostID() {
        return $this->post->ID;
    }

    function getPostType() {
        if(isset($this->post->post_type)) {
            return $this->post->post_type;
        }
        return FALSE;
    }



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

    function fixMigratedPost() {
        if($this->getPostType() == 'ait-dir-item') {
            $post_id = $this->get_migrated_post_id();
            if($post_id) {
                $this->post = get_post($post_id);
            }
        }
    }

    function get_migrated_post_id() {
        $meta_values = get_post_meta($this->post->ID);
        if(isset($meta_values['ait_migrated'][0])) {
            $migrate_info = unserialize($meta_values['ait_migrated'][0]);
            $new_id = $migrate_info['newid'];

            return $new_id;
          }
          return false;
        }

    function load() {
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

        $this->rating_rounded = ($this->getRatingFull() * 2) * 10;

        $this->rating_full = number_format($this->getRatingFull() * 2, 1);

        $this->review_link = $base_url . '/item/' . $this->post_slug . '/' . '#review';

        $this->review_count = $this->getMetaValue('rating_count');

        $this->base_url = $base_url;

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