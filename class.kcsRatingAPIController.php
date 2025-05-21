<?php
if(!function_exists('add_action')) {
    exit;
}


class KcsRatingAPIController
{
    
    public function __construct()
    {
    }

    /**
     * Get all reviews for a company
     * 
     * @return array
     */
    public function index(): array
    {
      return $this->getCompanies(); 
    }

    /**
     * Get all companies
     * 
     * @return array
     */
    public function getCompanies(): array
    {  
        // Get all posts from the database 
        $args = array(
            'post_type' => 'ait-item',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        $posts = $query->posts;
        $commpanies = array();
        foreach ($posts as $post) {
            $commpanies[] = $this->getCompanyData($post);
        }

        // Dot the same for the ait-dir-item post type
        $args = array(
            'post_type' => 'ait-dir-item',
            'posts_per_page' => 10,
        );
        $query = new WP_Query($args);
        $posts = $query->posts;
        foreach ($posts as $post) {
            $commpanies[] = $this->getCompanyData($post);
        }

        return $commpanies;
    }

    private function getCompanyData($post): array {
        $reviews = new kcsReviewsServer($post->ID);
        $company = $reviews->output();
        $company['id'] = $post->ID;
        $company['post_title'] = $post->post_title;
        $company['post_slug'] = $post->post_name;
        $company['post_type'] = $post->post_type;
        return $company;
    }
    
  }