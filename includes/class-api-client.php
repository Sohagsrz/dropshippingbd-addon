<?php
/**
 * API Client for Mohasagor.com.bd
 */

if (!defined('ABSPATH')) {
    exit;
}

class DropshippingBD_API_Client {
    
    private $base_url = 'https://mohasagor.com.bd/api/reseller/';
    private $timeout = 30;
    
    public function __construct() {
        // Constructor
    }
    
    /**
     * Fetch products from API
     *
     * @param int $page Page number
     * @param int $per_page Products per page
     * @return array|WP_Error
     */
    public function get_products($page = 1, $per_page = 50) {
        $url = $this->base_url . 'product';
        $args = array(
            'page' => $page,
            'per_page' => $per_page
        );
        
        $url = add_query_arg($args, $url);
        
        $response = wp_remote_get($url, array(
            'timeout' => $this->timeout,
            'headers' => array(
                'User-Agent' => 'DropshippingBD-Addon/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        if (!isset($data['status']) || $data['status'] !== 200) {
            return new WP_Error('api_error', 'API returned error: ' . (isset($data['message']) ? $data['message'] : 'Unknown error'));
        }
        
        return $data;
    }
    
    /**
     * Get single product by ID
     *
     * @param int $product_id
     * @return array|WP_Error
     */
    public function get_product($product_id) {
        $url = $this->base_url . 'product/' . $product_id;
        
        $response = wp_remote_get($url, array(
            'timeout' => $this->timeout,
            'headers' => array(
                'User-Agent' => 'DropshippingBD-Addon/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        return $data;
    }
    
    /**
     * Get total pages available
     *
     * @param int $per_page Products per page
     * @return int|WP_Error
     */
    public function get_total_pages($per_page = 50) {
        $data = $this->get_products(1, $per_page);
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        if (isset($data['pagination']['last_page'])) {
            return intval($data['pagination']['last_page']);
        }
        
        return 1;
    }
    
    /**
     * Get total products count
     *
     * @return int|WP_Error
     */
    public function get_total_products() {
        $data = $this->get_products(1, 1);
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        if (isset($data['pagination']['total'])) {
            return intval($data['pagination']['total']);
        }
        
        return 0;
    }
    
    /**
     * Get all categories from API
     *
     * @return array|WP_Error
     */
    public function get_categories() {
        $url = $this->base_url . 'categories';
        
        $response = wp_remote_get($url, array(
            'timeout' => $this->timeout,
            'headers' => array(
                'User-Agent' => 'DropshippingBD-Addon/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        if (!isset($data['status']) || $data['status'] !== 200) {
            return new WP_Error('api_error', 'API returned error: ' . (isset($data['message']) ? $data['message'] : 'Unknown error'));
        }
        
        return $data;
    }
    
    /**
     * Test API connection
     *
     * @return bool|WP_Error
     */
    public function test_connection() {
        $data = $this->get_products(1, 1);
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        return true;
    }
}
