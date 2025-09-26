<?php

/**
 * Dashboard Client for DropshippingBD
 * Handles authenticated API calls to dropshipping.com.bd dashboard
 */

class DropshippingBD_Dashboard_Client {
    
    private $base_url = 'https://www.dropshipping.com.bd/api/';
    private $timeout = 30;
    public $cookie_jar_path;
    private $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36';
    public $xsrf_token;
    
    public function __construct() {
        // Set cookie jar path in WordPress uploads directory
        $upload_dir = wp_upload_dir();
        $this->cookie_jar_path = $upload_dir['basedir'] . '/dropshippingbd_cookies.txt';
        
        // Ensure the uploads directory exists
        if (!file_exists($upload_dir['basedir'])) {
            wp_mkdir_p($upload_dir['basedir']);
        }
        //delete options
        delete_option('dropshippingbd_dashboard_xsrf_token');
        delete_option('dropshippingbd_dashboard_xsrf_token_expires');

    }
    
    /**
     * Set credentials for authentication
     *
     * @param string $phone Phone number
     * @param string $password Password
     * @return bool|WP_Error
     */
    public function set_credentials($phone, $password) {
        if (empty($phone) || empty($password)) {
            return new WP_Error('missing_credentials', 'Phone and password are required');
        }
        
        // Store credentials securely
        update_option('dropshippingbd_dashboard_phone', $phone);
        update_option('dropshippingbd_dashboard_password', base64_encode($password));
        
        return true;
    }

    // request with csrf token
    public function request_with_csrf_token($endpoint, $method = 'GET', $data = array(), $token = null) {
        if($token) {
            //from option
            $this->xsrf_token = $token; 
        } else {
            $this->xsrf_token = get_option('dropshippingbd_dashboard_xsrf_token');
            if($this->xsrf_token_expires < time()) {
                // $this->xsrf_token = $this->get_token_from_fresh_request();
                //login 
                $this->login();
                $this->xsrf_token = $this->get_token_from_cookie_jar();
            }
            update_option('dropshippingbd_dashboard_xsrf_token', $this->xsrf_token);
            update_option('dropshippingbd_dashboard_xsrf_token_expires', time() + 5 * 60);

        }
        $url = $this->base_url . ltrim($endpoint, '/');
       
        $ch = curl_init();
      
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_COOKIEJAR => $this->cookie_jar_path,
            CURLOPT_COOKIEFILE => $this->cookie_jar_path,
            CURLOPT_CUSTOMREQUEST => $method, 
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json, text/plain, */*',
                'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
                'X-Requested-With: XMLHttpRequest',
                'X-XSRF-TOKEN: ' . $this->xsrf_token
            )
        ); 
        if($method === 'POST') {
            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = $data;
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = $method;
        }
 
        curl_setopt_array($ch, $curl_options);
        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'body' => $body,
            'http_code' => $http_code
        );

    }
    // get token from cookie jar
    public function get_token_from_cookie_jar() {
        $cookie_content = file_get_contents($this->cookie_jar_path);
        if (preg_match('/XSRF-TOKEN\s+([^\s]+)/', $cookie_content, $matches)) {
            return $matches[1];
        }
    }




    // get token  from new freshg request
    public function get_token_from_fresh_request($force = false) {
        if($force) {
            $this->xsrf_token = null;
        }else{
            $this->xsrf_token = get_option('dropshippingbd_dashboard_xsrf_token', null);
            $this->xsrf_token_expires = get_option('dropshippingbd_dashboard_xsrf_token_expires', 0);

            if($this->xsrf_token && $this->xsrf_token_expires > time()) {
                return $this->xsrf_token;
            }
        } 
 
         

        $login_page_url = 'https://www.dropshipping.com.bd/dropshipper/login';
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $login_page_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_COOKIEJAR => $this->cookie_jar_path,
            CURLOPT_COOKIEFILE => $this->cookie_jar_path,
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
                'Sec-Ch-Ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "macOS"',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Sec-Fetch-User: ?1',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'
            )
        ));
        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 200) {
            // extract token from cookie jar
            $cookie_content = file_get_contents($this->cookie_jar_path);
            if (preg_match('/XSRF-TOKEN\s+([^\s]+)/', $cookie_content, $matches)) {
                //url decode
                $matches[1] = urldecode($matches[1]);
                $this->xsrf_token = $matches[1];
                $this->xsrf_token_expires = time() + 5 * 60;
                update_option('dropshippingbd_dashboard_xsrf_token', $this->xsrf_token);
                update_option('dropshippingbd_dashboard_xsrf_token_expires', $this->xsrf_token_expires);
                return $matches[1];
            }
            return null;
        }
        return null;
    }       
    /**
     * Perform login to dashboard
     *
     * @return bool|WP_Error
     */
    public function login() {
        $phone = get_option('dropshippingbd_dashboard_phone');
        $password = base64_decode(get_option('dropshippingbd_dashboard_password'));
      
        
        if (empty($phone) || empty($password)) {
            return new WP_Error('no_credentials', 'No credentials found. Please set credentials first.');
        }
        
        $this->xsrf_token = $this->get_token_from_fresh_request();
      
        if (empty($this->xsrf_token)) {
            return new WP_Error('csrf_extraction_failed', 'Failed to extract XSRF-TOKEN from cookies');
        }
        //https://www.dropshipping.com.bd/api/reseller/login


        $response = $this->request_with_csrf_token('reseller/login', 'POST', array(
            'phone' => $phone,
            'password' => $password
        ), $this->xsrf_token);
        

        if ($response['http_code'] !== 200) {
            return new WP_Error('login_failed', 'Login failed with HTTP code: ' . $response['http_code']);
        }else{
            // store the token for 20 minutes
            update_option('dropshippingbd_dashboard_xsrf_token', $this->xsrf_token);
            update_option('dropshippingbd_dashboard_xsrf_token_expires', time() + 5 * 60);
            return true;
        }
    

        return true;
        


        // error_log('DropshippingBD: Starting login process for phone: ' . $phone);
        
        // // Step 1: Visit login page to get fresh cookies
        // $login_page_url = 'https://www.dropshipping.com.bd/dropshipper/login';
        // $ch1 = curl_init();
        
        // curl_setopt_array($ch1, array(
        //     CURLOPT_URL => $login_page_url,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_TIMEOUT => $this->timeout,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_SSL_VERIFYPEER => false,
        //     CURLOPT_SSL_VERIFYHOST => false,
        //     CURLOPT_USERAGENT => $this->user_agent,
        //     CURLOPT_COOKIEJAR => $this->cookie_jar_path,
        //     CURLOPT_COOKIEFILE => $this->cookie_jar_path,
        //     CURLOPT_HTTPHEADER => array(
        //         'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        //         'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
        //         'Sec-Ch-Ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
        //         'Sec-Ch-Ua-Mobile: ?0',
        //         'Sec-Ch-Ua-Platform: "macOS"',
        //         'Sec-Fetch-Dest: document',
        //         'Sec-Fetch-Mode: navigate',
        //         'Sec-Fetch-Site: none',
        //         'Sec-Fetch-User: ?1',
        //         'Upgrade-Insecure-Requests: 1',
        //         'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'
        //     )
        // ));
        
        // $page_body = curl_exec($ch1);
        // $page_http_code = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
        // curl_close($ch1);
        
        // if ($page_http_code !== 200) {
        //     error_log('DropshippingBD: Failed to get login page. HTTP code: ' . $page_http_code);
        //     return new WP_Error('login_page_failed', 'Failed to get login page. HTTP code: ' . $page_http_code);
        // }
        
        // // Step 2: Extract XSRF-TOKEN from cookies
        // $this->xsrf_token = $this->extract_csrf_from_cookies();
        // if (empty($this->xsrf_token)) {
        //     error_log('DropshippingBD: Failed to extract XSRF-TOKEN from cookies');
        //     return new WP_Error('csrf_extraction_failed', 'Failed to extract XSRF-TOKEN from cookies');
        // }
        
        // error_log('DropshippingBD: Extracted XSRF-TOKEN: ' . $this->xsrf_token);
        
        // // Step 3: Perform login with XSRF-TOKEN in header
        // $login_data = array(
        //     'phone' => $phone,
        //     'password' => $password
        // );
        
        // error_log('DropshippingBD: Attempting login with phone: ' . $phone);
        // error_log('DropshippingBD: CSRF token being used: ' . ($this->xsrf_token ? $this->xsrf_token : 'NULL'));
        // error_log('DropshippingBD: Login data: ' . json_encode($login_data));
        
        // // Make direct POST request to the login endpoint
        // $login_url = 'https://www.dropshipping.com.bd/api/reseller/login';
        // $ch = curl_init();
        
        // // Build headers array with XSRF-TOKEN in header
        // $headers = array(
        //     'Content-Type: application/json;charset=UTF-8',
        //     'Accept: application/json, text/plain, */*',
        //     'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
        //     'Origin: https://www.dropshipping.com.bd',
        //     'Referer: https://www.dropshipping.com.bd/dropshipper/login',
        //     'X-Requested-With: XMLHttpRequest',
        //     'X-XSRF-TOKEN: ' . $this->xsrf_token
        // );
        
        // curl_setopt_array($ch, array(
        //     CURLOPT_URL => $login_url,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_TIMEOUT => $this->timeout,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_SSL_VERIFYPEER => false,
        //     CURLOPT_SSL_VERIFYHOST => false,
        //     CURLOPT_USERAGENT => $this->user_agent,
        //     CURLOPT_COOKIEJAR => $this->cookie_jar_path,
        //     CURLOPT_COOKIEFILE => $this->cookie_jar_path,
        //     CURLOPT_POST => true,
        //     CURLOPT_POSTFIELDS => json_encode($login_data),
        //     CURLOPT_HTTPHEADER => $headers
        // ));
        
        // $body = curl_exec($ch);
        // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $error = curl_error($ch);
        
        // curl_close($ch);
        
        // if ($error) {
        //     error_log('DropshippingBD: Login cURL error: ' . $error);
        //     return new WP_Error('curl_error', 'cURL error: ' . $error);
        // }
        
        // $response = array(
        //     'body' => $body,
        //     'http_code' => $http_code
        // );
        
        // if (is_wp_error($response)) {
        //     error_log('DropshippingBD: Login request failed: ' . $response->get_error_message());
        //     return $response;
        // }
        
        // $response_code = $response['http_code'];
        // $body = $response['body'];
        
        // error_log('DropshippingBD: Login response code: ' . $response_code);
        // error_log('DropshippingBD: Login response body: ' . $body);
        
        // // Always try to parse JSON first for explicit fail/success
        // $data = json_decode($body, true);
        // if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        //     // If API explicitly says fail, return that message
        //     if ((isset($data['status']) && strtolower($data['status']) === 'fail') || (isset($data['success']) && $data['success'] === false)) {
        //         $msg = isset($data['message']) ? $data['message'] : 'Login failed';
        //         error_log('DropshippingBD: Login explicit fail: ' . $msg);
        //         return new WP_Error('login_failed', $msg);
        //     }
        //     // If API explicitly says success, treat as success (and rely on cookies for session)
        //     if ((isset($data['status']) && strtolower($data['status']) === 'success') || (isset($data['success']) && $data['success'] === true)) {
        //         // Ensure cookies got written
        //         if (file_exists($this->cookie_jar_path)) {
        //             error_log('DropshippingBD: Login success via JSON with cookies present');
        //             return true;
        //         }
        //         return new WP_Error('login_failed', 'Login response OK but no cookies were saved.');
        //     }
        // }

        // // Fallback: if HTTP 200 and cookies exist, consider success
        // if ($response_code === 200) {
        //     if (file_exists($this->cookie_jar_path)) {
        //         error_log('DropshippingBD: Login fallback success (200 + cookies)');
        //         return true;
        //     }
        //     return new WP_Error('login_failed', 'Login returned 200 but no cookies were saved.');
        // }

        // error_log('DropshippingBD: Login failed with response code: ' . $response_code);
        // return new WP_Error('login_failed', 'Login failed with HTTP code: ' . $response_code . ' - Response: ' . $body);
    }
    
    /**
     * Extract XSRF-TOKEN from cookies in cookie jar
     *
     * @return string|null
     */
    private function extract_csrf_from_cookies() {
        if (!file_exists($this->cookie_jar_path)) {
            error_log('DropshippingBD: Cookie jar file does not exist');
            return null;
        }
        
        $cookie_content = file_get_contents($this->cookie_jar_path);
        error_log('DropshippingBD: Cookie jar content: ' . $cookie_content);
        
        // Look for XSRF-TOKEN in cookie jar
        if (preg_match('/XSRF-TOKEN\s+([^\s]+)/', $cookie_content, $matches)) {
            $token = trim($matches[1]);
            error_log('DropshippingBD: Found XSRF-TOKEN in cookies: ' . $token);
            return $token;
        }
        
        error_log('DropshippingBD: XSRF-TOKEN not found in cookies');
        return null;
    }
    
    /**
     * Get CSRF token and initial session cookies
     *
     * @return bool
     */
    public function get_csrf_token() {
        $this->xsrf_token = $this->get_token_from_fresh_request();
        return $this->xsrf_token;
    }
    
    /**
     * Check session status
     *
     * @return bool|WP_Error
     */
    public function check_session() {
        error_log('DropshippingBD: Checking session...');
        
        if (!file_exists($this->cookie_jar_path)) {
            error_log('DropshippingBD: No cookie jar found, session invalid');
            return new WP_Error('no_session', 'No active session found');
        }
        
        // $response = $this->make_curl_request('login/session/check', 'GET');
        $response = $this->request_with_csrf_token('reseller/login/session/check', 'GET', array());
        
        if (is_wp_error($response)) {
            error_log('DropshippingBD: Session check request error: ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = $response['http_code'];
        $body = $response['body'];
        
        error_log('DropshippingBD: Session check response code: ' . $response_code);
        error_log('DropshippingBD: Session check response body: ' . $body);
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DropshippingBD: Session check JSON decode error: ' . json_last_error_msg());
            return new WP_Error('json_decode_error', 'Failed to decode session check response');
        }
        
        // Check for authentication status in response
        error_log('DropshippingBD: Session check data: ' . json_encode($data));
        
        if (isset($data['authenticated']) && $data['authenticated'] === true) {
            error_log('DropshippingBD: Session valid via authenticated field');
            return true;
        } elseif (isset($data['status']) && $data['status'] === 'success') {
            error_log('DropshippingBD: Session valid via status field');
            return true;
        } elseif (isset($data['logged_in']) && $data['logged_in'] === true) {
            error_log('DropshippingBD: Session valid via logged_in field');
            return true;
        } elseif ($response_code === 200 && empty($data)) {
            error_log('DropshippingBD: Session valid via empty response with 200 status');
            return true;
        } else {
            error_log('DropshippingBD: Session invalid - no valid pattern found');
            return new WP_Error('session_invalid', 'Session is invalid or expired');
        }
    }
    
    /**
     * Make authenticated API request using cURL
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array|WP_Error
     */
    public function make_request($endpoint, $method = 'GET', $data = array()) {
        return $this->request_with_csrf_token($endpoint, $method, $data); 
    }
    
    /**
     * Make cURL request with cookie jar
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array|WP_Error
     */
    public function make_curl_request($endpoint, $method = 'GET', $data = array()) {
        return $this->request_with_csrf_token($endpoint, $method, $data);
    }
    

    
    /**
     * Get dashboard data with date range
     *
     * @param string $start_date Start date (Y-m-d format)
     * @param string $end_date End date (Y-m-d format)
     * @return array|WP_Error
     */
    public function get_dashboard_data($start_date = '', $end_date = '') {
        $data = array();
        if (!empty($start_date)) {
            $data['start_date'] = $start_date;
        }
        if (!empty($end_date)) {
            $data['end_date'] = $end_date;
        }
        //    /https://www.dropshipping.com.bd/api/get/reseller/dashboard/data?start_date=&end_date=
     
        $datas=  $this->make_request('get/reseller/dashboard/data', 'GET', $data);
        //body json decode
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
    }
    
    /**
     * Get reseller account information and payment methods
     *
     * @return array|WP_Error
     */
    public function get_account_info() {
        $datas = $this->make_request('current/reseller/account/number', 'GET');
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('current/reseller/account/number', 'GET');
    }
    
    /**
     * Get orders list with filters
     *
     * @param int $page Page number
     * @param int $item Items per page
     * @param string $status_code Order status filter
     * @param string $start_date Start date filter
     * @param string $end_date End date filter
     * @return array|WP_Error
     */
    public function get_orders($page = 1, $item = 50, $status_code = 'all', $start_date = '', $end_date = '') {
        $data = array(
            'page' => $page,
            'item' => $item,
            'status_code' => $status_code
        );
        
        if (!empty($start_date)) {
            $data['start_date'] = $start_date;
        }
        if (!empty($end_date)) {
            $data['end_date'] = $end_date;
        }
        $datas = $this->make_request('reseller/order', 'GET', $data);
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('reseller/order', 'GET', $data); 
    }
    
    /**
     * Get dashboard products list with filters
     *
     * @param int $page Page number
     * @param int $item Items per page
     * @param string $status Product status filter
     * @param string|int $category_id Category ID
     * @param string|int $sub_category_id Sub-category ID
     * @param string|int $sub_sub_category_id Sub-sub-category ID
     * @param string $type Type filter (e.g., all)
     * @return array|WP_Error
     */
    public function get_dashboard_products(
        $page = 1,
        $item = 30,
        $status = '',
        $category_id = '',
        $sub_category_id = '',
        $sub_sub_category_id = '',
        $type = 'all'
    ) {
        $data = array(
            'page' => $page,
            'item' => $item,
            'type' => $type
        );
        
        if (!empty($status)) {
            $data['status'] = $status;
        }
        if (!empty($category_id)) {
            $data['category_id'] = $category_id;
        }
        if (!empty($sub_category_id)) {
            $data['sub_category_id'] = $sub_category_id;
        }
        if (!empty($sub_sub_category_id)) {
            $data['sub_sub_category_id'] = $sub_sub_category_id;
        }
        ////    /https://www.dropshipping.com.bd/api/get/reseller/dashboard/data?start_date=&end_date=
        
        // return $this->make_request('reseller/show/product/list', 'GET', $data);
        $datas = $this->make_request('reseller/show/product/list', 'GET', $data);
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
    }
    
    /**
     * Get images for a product by product ID
     * Endpoint: https://www.dropshipping.com.bd/api/product/wise/images/{productId}
     *
     * @param int $product_id
     * @return array|WP_Error
     */
    public function get_product_images($product_id) {
        $product_id = intval($product_id);
        if ($product_id <= 0) {
            return new WP_Error('invalid_product_id', 'Invalid product ID');
        }
        
        // This endpoint is not under /api/reseller, so call it via absolute URL
        $url = 'https://www.dropshipping.com.bd/api/product/wise/images/' . $product_id;
        
        // Ensure we have a valid session/cookies
        $session_check = $this->check_session();
        if (is_wp_error($session_check)) {
            $login_result = $this->login();
            if (is_wp_error($login_result)) {
                return $login_result;
            }
        }
        $response = $this->request_with_csrf_token('product/wise/images/' . $product_id, 'GET', array());
        
        if ($response['http_code'] !== 200) {
            return new WP_Error('curl_error', 'cURL error: ' . $response['body']);
        }
        
        $data = json_decode($response['body'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        
        return $data;
    }
    
    /**
     * Get dashboard categories
     *
     * @return array|WP_Error
     */
    public function get_categories() {
        $datas = $this->make_request('reseller/categories', 'GET');
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('reseller/categories', 'GET');
    }
    
    /**
     * Get cashbook data (expenses/income transactions)
     *
     * @param int $page Page number
     * @param int $item Items per page
     * @param string $start_date Start date filter
     * @param string $end_date End date filter
     * @return array|WP_Error
     */
    public function get_cashbook($page = 1, $item = 50, $start_date = '', $end_date = '') {
        $data = array(
            'page' => $page,
            'item' => $item
        );
        
        if (!empty($start_date)) {
            $data['start_date'] = $start_date;
        }
        if (!empty($end_date)) {
            $data['end_date'] = $end_date;
        }
        
        $datas = $this->make_request('reseller/cashbook', 'GET', $data);
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('reseller/cashbook', 'GET', $data);
    }
    
    /**
     * Get withdraw transaction history
     *
     * @param int $page Page number
     * @param int $item Items per page
     * @return array|WP_Error
     */
    public function get_withdraw_transactions($page = 1, $item = 50) {
        $data = array(
            'page' => $page,
            'item' => $item
        );
        
        $datas = $this->make_request('reseller/withdraw/transaction', 'GET', $data);
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('reseller/withdraw/transaction', 'GET', $data);
    }
    
    /**
     * Get customer list
     *
     * @param int $page Page number
     * @return array|WP_Error
     */
    public function get_customers($page = 1) {
        $data = array(
            'page' => $page
        );
        
        $datas = $this->make_request('reseller/customer/list', 'GET', $data);
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
        // return $this->make_request('reseller/customer/list', 'GET', $data);
    }
    
    //test_connection
    public function test_connection() {
        // /https://www.dropshipping.com.bd/api/reseller/login/session/check
        $datas = $this->make_request('reseller/login/session/check', 'GET');
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        return $datas;
    }
    /**
     * Clear session and cookies
     *
     * @return bool
     */
    public function logout() {
        if (file_exists($this->cookie_jar_path)) {
            unlink($this->cookie_jar_path);
        }
        
        // Clear stored credentials
        delete_option('dropshippingbd_dashboard_phone');
        delete_option('dropshippingbd_dashboard_password');
        
        return true;
    }

     /**
     * Get category mapping array
     *
     * @return array Category ID to name mapping
     */
    private function get_category_map() {
        return array(
            1 => "Men's Fashion",
            2 => "Women's Fashion", 
            3 => "Home & Lifestyle",
            4 => "Gadgets",
            5 => "Winter",
            6 => "Year Closing Offer",
            7 => "Other's",
            9 => "Watch",
            10 => "Islamic Item",
            11 => "Kids Zone",
            12 => "Customize Item",
            13 => "Customize & Gift",
            14 => "Rain item",
            15 => "Gadgets & Electronics",
            16 => "OFFER"
        );
    }

    //search product
    public function search_product($keyword, $page = 1, $per_page = 30) {
        // https://www.dropshipping.com.bd/api/search/product/hoco?page=1&item=30
        $datas = $this->make_request('search/product/' . $keyword, 'GET', array('page' => $page, 'item' => $per_page));
        $datas = json_decode($datas['body'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode API response');
        }
        $produucts = ($datas['products']['data']);
        $newProducts= [];
        foreach($produucts as $product){
            $product['price'] =  $product['reselling_price'];


            $product['sale_price'] =  $product['purchase_price'];
            $product['product_images'] = $product['product_image'];
            //product_variants
            $product['product_variants'] = $product['product_variant'];
            //category , id b y name
            $catName = $this->get_category_map()[$product['category_id']];
            if(!$catName){
                $catName = "others";
            }
            $product['category'] =  $catName;

            unset($product['product_image']);
            unset($product['product_variant']);
            $newProducts[] = $product;
        } 
        $datas['products']['data'] = $newProducts;

        return $datas;
    }
}