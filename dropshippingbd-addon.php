<?php
/**
 * Plugin Name: DropshippingBD Addon
 * Plugin URI: https://github.com/sohagsrz/dropshippingbd-addon
 * Description: Import products from DropshippingBD.com.bd API to WooCommerce with custom markup and custom field storage
 * Version: 1.0.0
 * Author: Sohag Srz
 * License: GPL v2 or later
 * Text Domain: dropshippingbd-addon
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants©∫
define('DROPSHIPPINGBD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DROPSHIPPINGBD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DROPSHIPPINGBD_VERSION', '1.0.0');

// Include required files
require_once DROPSHIPPINGBD_PLUGIN_PATH . 'includes/class-api-client.php';
require_once DROPSHIPPINGBD_PLUGIN_PATH . 'includes/class-dashboard-client.php';
require_once DROPSHIPPINGBD_PLUGIN_PATH . 'includes/class-product-importer.php';
require_once DROPSHIPPINGBD_PLUGIN_PATH . 'includes/class-admin.php';

/**
 * Main plugin class
 */
class DropshippingBD_Addon {
    
    public function __construct() { 
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_dropshippingbd_import_products', array($this, 'ajax_import_products'));
        add_action('wp_ajax_dropshippingbd_sync_products', array($this, 'ajax_sync_products'));
        add_action('wp_ajax_dropshippingbd_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_dropshippingbd_import_categories', array($this, 'ajax_import_categories'));
        add_action('wp_ajax_dropshippingbd_set_dashboard_credentials', array($this, 'ajax_set_dashboard_credentials'));
        add_action('wp_ajax_dropshippingbd_test_dashboard_connection', array($this, 'ajax_test_dashboard_connection'));
        add_action('wp_ajax_dropshippingbd_dashboard_logout', array($this, 'ajax_dashboard_logout'));
        add_action('wp_ajax_dropshippingbd_set_dashboard_cookies', array($this, 'ajax_set_dashboard_cookies'));
        add_action('wp_ajax_dropshippingbd_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_dropshippingbd_get_account_info', array($this, 'ajax_get_account_info'));
        add_action('wp_ajax_dropshippingbd_get_orders', array($this, 'ajax_get_orders'));
        add_action('wp_ajax_dropshippingbd_get_dashboard_products', array($this, 'ajax_get_dashboard_products'));
        add_action('wp_ajax_dropshippingbd_get_dashboard_categories', array($this, 'ajax_get_dashboard_categories'));
        add_action('wp_ajax_dropshippingbd_get_dashboard_sub_categories', array($this, 'ajax_get_dashboard_sub_categories'));
        add_action('wp_ajax_dropshippingbd_get_dashboard_sub_sub_categories', array($this, 'ajax_get_dashboard_sub_sub_categories'));
        add_action('wp_ajax_dropshippingbd_get_cashbook', array($this, 'ajax_get_cashbook'));
        add_action('wp_ajax_dropshippingbd_get_withdraw_transactions', array($this, 'ajax_get_withdraw_transactions'));
        add_action('wp_ajax_dropshippingbd_get_customers', array($this, 'ajax_get_customers'));
        add_action('wp_ajax_dropshippingbd_get_total_products', array($this, 'ajax_get_total_products'));
        add_action('wp_ajax_dropshippingbd_get_settings', array($this, 'ajax_get_settings'));
        add_action('wp_ajax_dropshippingbd_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_dropshippingbd_get_statistics', array($this, 'ajax_get_statistics'));
        add_action('wp_ajax_dropshippingbd_reset_plugin', array($this, 'ajax_reset_plugin'));
        add_action('wp_ajax_dropshippingbd_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_dropshippingbd_fetch_products_info', array($this, 'ajax_fetch_products_info'));
        add_action('wp_ajax_dropshippingbd_get_products_page', array($this, 'ajax_get_products_page'));
        add_action('wp_ajax_dropshippingbd_import_single_product', array($this, 'ajax_import_single_product'));
        
        // Get last import progress
        add_action('wp_ajax_dropshippingbd_get_last_import_progress', array($this, 'ajax_get_last_import_progress'));
        
        // Delete all imported products
        add_action('wp_ajax_dropshippingbd_delete_all_imported_products', array($this, 'ajax_delete_all_imported_products'));
        
        // Get imported products count
        add_action('wp_ajax_dropshippingbd_get_imported_count', array($this, 'ajax_get_imported_count'));
        
        // Search products
        add_action('wp_ajax_dropshippingbd_search_products', array($this, 'ajax_search_products'));
        
        // Update existing product
        add_action('wp_ajax_dropshippingbd_update_product', array($this, 'ajax_update_product'));
        
        add_action('wp_ajax_dropshippingbd_test_csrf', array($this, 'ajax_test_csrf'));
        add_action('wp_ajax_dropshippingbd_test_dashboard', array($this, 'ajax_test_dashboard'));
        add_action('wp_ajax_dropshippingbd_logout_dashboard', array($this, 'ajax_logout_dashboard'));
        add_action('wp_ajax_dropshippingbd_test_login_page', array($this, 'ajax_test_login_page'));
        add_action('wp_ajax_dropshippingbd_get_saved_credentials', array($this, 'ajax_get_saved_credentials'));
        add_action('wp_ajax_dropshippingbd_login_with_saved', array($this, 'ajax_login_with_saved'));
        add_action('wp_ajax_dropshippingbd_test_main_page', array($this, 'ajax_test_main_page'));
        add_action('wp_ajax_dropshippingbd_test_exact_endpoint', array($this, 'ajax_test_exact_endpoint'));
        add_action('wp_ajax_dropshippingbd_test_login_no_csrf', array($this, 'ajax_test_login_no_csrf'));
        add_action('wp_ajax_dropshippingbd_test_login_with_cookies', array($this, 'ajax_test_login_with_cookies'));
        add_action('wp_ajax_dropshippingbd_test_fresh_login', array($this, 'ajax_test_fresh_login'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));


        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

    }

     
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
       
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('dropshippingbd-admin', DROPSHIPPINGBD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), DROPSHIPPINGBD_VERSION, true);
        wp_enqueue_style('dropshippingbd-admin', DROPSHIPPINGBD_PLUGIN_URL . 'assets/css/admin.css', array(), DROPSHIPPINGBD_VERSION);
        
        wp_localize_script('dropshippingbd-admin', 'dropshippingbd_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dropshippingbd_nonce'),
            'strings' => array(
                'importing' => __('Importing products...', 'dropshippingbd-addon'),
                'syncing' => __('Syncing products...', 'dropshippingbd-addon'),
                'success' => __('Operation completed successfully!', 'dropshippingbd-addon'),
                'error' => __('An error occurred. Please try again.', 'dropshippingbd-addon')
            )
        ));
    }
    
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('dropshippingbd-addon', false, dirname(plugin_basename(__FILE__)) . '/languages');
        // $this->remove_all_transients();
    }
    public function remove_all_transients() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'DropshippingBD Import',
            'DropshippingBD',
            'manage_options',
            'dropshippingbd-import',
            array($this, 'admin_page'),
            'dashicons-download',
            30
        );
    }
    
    public function admin_page() {
        error_log('DropshippingBD: admin_page() method called');
        $admin = new DropshippingBD_Admin();
        $admin->display_page();
    }
    
    public function ajax_import_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->import_products($page, $per_page);
        
        wp_send_json($result);
    }
    
    public function ajax_sync_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->sync_existing_products();
        
        wp_send_json($result);
    }
    
    public function ajax_test_connection() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $api_client = new DropshippingBD_API_Client();
        $result = $api_client->test_connection();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'API connection successful'
            ));
        }
    }
    
    public function ajax_import_categories() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->import_categories();
        
        wp_send_json($result);
    }
    
    public function ajax_set_dashboard_credentials() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        $password = $_POST['password'];
        
        if (empty($phone) || empty($password)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Phone and password are required'
            ));
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->set_credentials($phone, $password);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Credentials saved successfully'
            ));
        }
    }
    
    public function ajax_test_dashboard_connection() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->test_connection();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard connection successful'
            ));
        }
    }
    
    public function ajax_dashboard_logout() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->logout();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Logged out successfully'
            ));
        }
    }
    
    public function ajax_set_dashboard_cookies() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $xsrf_token = sanitize_text_field($_POST['xsrf_token']);
        $laravel_session = sanitize_text_field($_POST['laravel_session']);
        
        if (empty($xsrf_token) || empty($laravel_session)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'XSRF token and Laravel session are required'
            ));
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $dashboard_client->set_cookies_manually($xsrf_token, $laravel_session);
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Cookies set successfully'
        ));
    }
    
    public function ajax_get_dashboard_data() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
            
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        // cache key
        $cache_key = 'dropshippingbd_dashboard_data_' . $start_date . '_' . $end_date;
        $cached_data = get_transient($cache_key);
        if ($cached_data) {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => $cached_data
            ));
        }

        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_dashboard_data($start_date, $end_date);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            set_transient($cache_key, $result, 60 * 60 * 24);
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_account_info() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        // cache key
        $cache_key = 'dropshippingbd_account_info';
        $cached_data = get_transient($cache_key);
        if ($cached_data) {
            wp_send_json(array(
                'success' => true,
                'message' => 'Account information retrieved successfully',
                'data' => $cached_data
            ));
        }

        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_account_info();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            set_transient($cache_key, $result, 60 * 60 * 24);
            wp_send_json(array(
                'success' => true,
                'message' => 'Account information retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_orders() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        } 
        $page = intval($_POST['page'] ?? 1);
        $item = intval($_POST['item'] ?? 50);
        $status_code = sanitize_text_field($_POST['status_code'] ?? 'all');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        //cache
        $cache_key = 'dropshippingbd_orders_' . $page . '_' . $item . '_' . $status_code . '_' . $start_date . '_' . $end_date;
        $cached_data = get_transient($cache_key);
        if ($cached_data) {
            wp_send_json(array(
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $cached_data
            ));
        }

        

        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_orders($page, $item, $status_code, $start_date, $end_date);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            set_transient($cache_key, $result, 60 * 60 * 24);
            wp_send_json(array(
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_dashboard_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        $item = intval($_POST['item'] ?? 30);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $category_id = sanitize_text_field($_POST['category_id'] ?? '');
        $sub_category_id = sanitize_text_field($_POST['sub_category_id'] ?? '');
        $sub_sub_category_id = sanitize_text_field($_POST['sub_sub_category_id'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'all');
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_dashboard_products($page, $item, $status, $category_id, $sub_category_id, $sub_sub_category_id, $type);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard products retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_dashboard_categories() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_dashboard_categories();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard categories retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_dashboard_sub_categories() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $category_id = intval($_POST['category_id'] ?? 0);
        
        if (empty($category_id)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Category ID is required',
                'data' => null
            ));
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_dashboard_sub_categories($category_id);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard sub-categories retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_dashboard_sub_sub_categories() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $sub_category_id = intval($_POST['sub_category_id'] ?? 0);
        
        if (empty($sub_category_id)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Sub-category ID is required',
                'data' => null
            ));
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_dashboard_sub_sub_categories($sub_category_id);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Dashboard sub-sub-categories retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_cashbook() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        $item = intval($_POST['item'] ?? 50);
        $is_income = intval($_POST['is_income'] ?? 0);
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_cashbook($page, $item, $is_income, $start_date, $end_date);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Cashbook data retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_withdraw_transactions() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        $item = intval($_POST['item'] ?? 50);
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_withdraw_transactions($page, $item);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Withdraw transactions retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_customers() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->get_customers($page);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'data' => null
            ));
        } else {
            wp_send_json(array(
                'success' => true,
                'message' => 'Customers retrieved successfully',
                'data' => $result
            ));
        }
    }
    
    public function ajax_get_total_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        // cache key
        $cache_key = 'dropshippingbd_total_products';
        $cached_data = get_transient($cache_key);
        if ($cached_data) {
            wp_send_json(array(
                'success' => true,
                'message' => 'Total products retrieved successfully',
                'data' => $cached_data
            ));
        }
        
        $api_client = new DropshippingBD_API_Client();
        $total_products = $api_client->get_total_products();
        $total_pages = $api_client->get_total_pages();
        
        if (is_wp_error($total_products)) {
            $total_products = 0;
        }
        
        if (is_wp_error($total_pages)) {
            $total_pages = 0;
        }
        
        set_transient($cache_key, array(
            'total_products' => $total_products,
            'total_pages' => $total_pages
        ), 60 * 60 * 24);
        wp_send_json(array(
            'success' => true,
            'data' => array(
                'total_products' => $total_products,
                'total_pages' => $total_pages
            )
        ));
    }
    
    public function ajax_get_settings() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = array(
            'price_markup' => get_option('dropshippingbd_price_markup', 20),
            'auto_sync' => get_option('dropshippingbd_auto_sync', 'disabled'),
            'cache_duration' => get_option('dropshippingbd_cache_duration', 5)
        );
        
        wp_send_json(array(
            'success' => true,
            'data' => $settings
        ));
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Handle both formats: settings object and individual fields
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = $_POST['settings'];
        } else {
            // Fallback to individual fields
            $settings = array(
                'price_markup' => $_POST['price_markup'] ?? 20,
                'auto_sync' => $_POST['auto_sync'] ?? 'disabled',
                'cache_duration' => $_POST['cache_duration'] ?? 5
            );
        }
        
        // Validate and sanitize settings
        $price_markup = intval($settings['price_markup']);
        $auto_sync = sanitize_text_field($settings['auto_sync']);
        $cache_duration = intval($settings['cache_duration']);
        
        // Validate ranges
        if ($price_markup < 0 || $price_markup > 100) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Price markup must be between 0 and 100'
            ));
        }
        
        if ($cache_duration < 1 || $cache_duration > 60) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Cache duration must be between 1 and 60 minutes'
            ));
        }
        
        // Save settings
        update_option('dropshippingbd_price_markup', $price_markup);
        update_option('dropshippingbd_auto_sync', $auto_sync);
        update_option('dropshippingbd_cache_duration', $cache_duration);
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Settings saved successfully'
        ));
    }
    
    public function ajax_get_statistics() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        // Get imported products count
        $imported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dropshippingbd_products");
        
        // Get imported categories count
        $imported_categories = wp_count_terms('product_cat');
        
        // Get last sync time
        $last_sync = get_option('dropshippingbd_last_sync', 'Never');
        if ($last_sync !== 'Never') {
            $last_sync = date('Y-m-d H:i:s', $last_sync);
        }
        
        wp_send_json(array(
            'success' => true,
            'data' => array(
                'imported_products' => intval($imported_products),
                'imported_categories' => intval($imported_categories),
                'last_sync' => $last_sync
            )
        ));
    }
    
    public function ajax_reset_plugin() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        // Clear imported products table
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}dropshippingbd_products");
        
        // Clear options
        delete_option('dropshippingbd_price_markup');
        delete_option('dropshippingbd_auto_sync');
        delete_option('dropshippingbd_cache_duration');
        delete_option('dropshippingbd_last_sync');
        delete_option('dropshippingbd_dashboard_phone');
        delete_option('dropshippingbd_dashboard_password');
        delete_option('dropshippingbd_dashboard_cookies');
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Plugin reset successfully'
        ));
    }
    
    public function ajax_clear_cache() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        // Clear all transients related to dropshippingbd
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_dropshippingbd_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_dropshippingbd_%'");
        
        // Clear any cached data
        wp_cache_flush();
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Cache cleared successfully'
        ));
    }
    
    public function ajax_fetch_products_info() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $config = $_POST['config'];
        $start_page = intval($config['start_page'] ?? 1);
        $end_page = intval($config['end_page'] ?? 10);
        $per_page = intval($config['per_page'] ?? 20);
        
        $api_client = new DropshippingBD_API_Client();
        
        // Get total products and pages
        $total_products = $api_client->get_total_products();
        $total_pages = $api_client->get_total_pages($per_page);
        
        if (is_wp_error($total_products) || is_wp_error($total_pages)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to fetch product information'
            ));
        }
        
        // Calculate range
        $actual_end_page = min($end_page, $total_pages);
        $total_products_in_range = ($actual_end_page - $start_page + 1) * $per_page;
        
        // Check how many are already imported
        global $wpdb;
        $already_imported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dropshippingbd_products");
        
        $new_products = max(0, $total_products_in_range - $already_imported);
        
        wp_send_json(array(
            'success' => true,
            'data' => array(
                'total_products' => $total_products,
                'total_pages' => $total_pages,
                'products_in_range' => $total_products_in_range,
                'already_imported' => intval($already_imported),
                'new_products' => $new_products,
                'start_page' => $start_page,
                'end_page' => $actual_end_page
            )
        ));
    }
    
    public function ajax_get_products_page() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);
        $category = sanitize_text_field($_POST['category'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        
        $api_client = new DropshippingBD_API_Client();
        $result = $api_client->get_products($page, $per_page);
     
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        }
        
        $products = $result['products'] ?? array();
        
        // Filter by category if specified
        // if (!empty($category)) {
        //     $products = array_filter($products, function($product) use ($category) {
        //         return isset($product['category_id']) && $product['category_id'] == $category;
        //     });
        // }
        
        // Filter by status if specified
        // if ($status !== 'all') {
        //     $products = array_filter($products, function($product) use ($status) {
        //         $product_status = $product['status'] ?? 'active';
        //         return $product_status === $status;
        //     });
        // }
        
        wp_send_json(array(
            'success' => true,
            'data' => array(
                'products' =>  ($products),
                'page' => $page,
                'per_page' => $per_page,
                'total' => count($products)
            )
        ));
    }
    
    public function ajax_import_single_product() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $product_data = $_POST['product'];
        
        // Debug logging
        error_log('DropshippingBD: Importing single product - ID: ' . ($product_data['id'] ?? 'N/A'));
        error_log('DropshippingBD: Product data: ' . print_r($product_data, true));
        
        if (empty($product_data) || !isset($product_data['id'])) {
            error_log('DropshippingBD: Invalid product data received');
            wp_send_json(array(
                'success' => false,
                'message' => 'Invalid product data'
            ));
        }
        
        global $wpdb;
        
        // Check if product is already imported
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dropshippingbd_products WHERE mohasagor_id = %d",
            $product_data['id']
        ));
        
        if ($existing) {
            error_log('DropshippingBD: Product already exists - ID: ' . $product_data['id']);
            wp_send_json(array(
                'success' => true,
                'data' => array(
                    'is_duplicate' => true,
                    'message' => 'Product already imported'
                )
            ));
        }
        
        error_log('DropshippingBD: Product not found in database, proceeding with import...');
        
        // Import the product using the product importer
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->import_single_product($product_data);
        
        error_log('DropshippingBD: Import result: ' . print_r($result, true));
        
        // Track import progress if this is part of a bulk import
        if (isset($_POST['current_page']) && isset($_POST['current_index']) && isset($_POST['total_pages']) && isset($_POST['total_products'])) {
            $importer->track_import_progress(
                (int) $_POST['current_page'],
                (int) $_POST['current_index'],
                (int) $_POST['total_pages'],
                (int) $_POST['total_products']
            );
        }
        
        if ($result['success']) {
            error_log('DropshippingBD: Product imported successfully - WooCommerce ID: ' . $result['product_id']);
            wp_send_json(array(
                'success' => true,
                'data' => array(
                    'is_duplicate' => false,
                    'woo_product_id' => $result['product_id'],
                    'message' => 'Product imported successfully'
                )
            ));
        } else {
            error_log('DropshippingBD: Product import failed - Error: ' . ($result['message'] ?? 'Unknown error'));
            wp_send_json(array(
                'success' => false,
                'message' => $result['message'] ?? 'Failed to import product'
            ));
        }
    }
    
    /**
     * Get last import progress
     */
    public function ajax_get_last_import_progress() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $progress = $importer->get_last_import_progress();
        
        if ($progress) {
            wp_send_json(array(
                'success' => true,
                'data' => $progress
            ));
        } else {
            wp_send_json(array(
                'success' => false,
                'message' => 'No import progress found'
            ));
        }
    }
    
    /**
     * Delete all imported products
     */
    public function ajax_delete_all_imported_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->delete_all_imported_products();
        
        wp_send_json($result);
    }
    
    /**
     * Get imported products count
     */
    public function ajax_get_imported_count() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $count = $importer->get_imported_products_count();
        
        wp_send_json(array(
            'success' => true,
            'count' => $count
        ));
    }
    
    /**
     * Search products
     */
    public function ajax_search_products() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $keyword = sanitize_text_field($_POST['keyword'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 30);
        
        if (empty($keyword)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Search keyword is required'
            ));
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->search_product($keyword, $page, $per_page);
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => $result->get_error_message()
            ));
        }
        
        // Check which products are already imported
        // The dashboard client returns data in result.products.data structure
        $products = $result['products']['data'] ?? array();
        $imported_products = array();
        
        if (!empty($products)) {
            global $wpdb;
            $mohasagor_ids = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($mohasagor_ids), '%d'));
            
            $imported = $wpdb->get_results($wpdb->prepare(
                "SELECT mohasagor_id, woo_product_id FROM {$wpdb->prefix}dropshippingbd_products WHERE mohasagor_id IN ($placeholders)",
                $mohasagor_ids
            ));
            
            foreach ($imported as $import) {
                $imported_products[$import->mohasagor_id] = $import->woo_product_id;
            }
        }
        
        wp_send_json(array(
            'success' => true,
            'data' => $result,
            'imported_products' => $imported_products
        ));
    }
    
    /**
     * Update existing product
     */
    public function ajax_update_product() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $product_data = $_POST['product'];
        
        if (empty($product_data) || !isset($product_data['id'])) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Invalid product data'
            ));
        }
        
        $importer = new DropshippingBD_Product_Importer();
        $result = $importer->update_existing_product($product_data);
        
        wp_send_json($result);
    }
    
    /**
     * Get category mapping array
     *
     * @return array Category ID to name mapping
     */
    public function get_category_map() {
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
    
    /**
     * Get category name from ID or provided name
     *
     * @param int|null $category_id Category ID
     * @param string|null $category_name Category name
     * @return string Category name
     */
    public function get_category_name($category_id, $category_name) {
        $category_map = $this->get_category_map();
        
        if (!empty($category_id) && isset($category_map[$category_id])) {
            return $category_map[$category_id];
        }
        
        return $category_name ?: 'No Category';
    }
    
    public function ajax_test_csrf() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        
        // Try to get CSRF token using the new cURL method
        $result = $dashboard_client->get_csrf_token();
        
        if ($result) {
            wp_send_json(array(
                'success' => true,
                'message' => 'CSRF token test completed',
                'data' => array(
                    'csrf_token_found' => true,
                    'csrf_token' => $dashboard_client->xsrf_token ?? 'Not available'
                )
            ));
        } else {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to get CSRF token'
            ));
        }
    }
    
    public function ajax_test_dashboard() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        
        // Try to get dashboard data
        $result = $dashboard_client->get_dashboard_data();
        
        if (is_wp_error($result)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Dashboard test failed: ' . $result->get_error_message()
            ));
        }
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Dashboard test completed',
            'data' => $result
        ));
    }
    
    public function ajax_logout_dashboard() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        $result = $dashboard_client->logout();
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Logged out successfully'
        ));
    }
    
    public function ajax_test_login_page() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        
        // Test the CSRF token extraction method directly
        $csrf_result = $dashboard_client->get_csrf_token();
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Login page test completed',
            'data' => array(
                'csrf_result' => $csrf_result,
                'csrf_token' => $dashboard_client->xsrf_token,
                'cookie_jar_exists' => file_exists($dashboard_client->cookie_jar_path),
                'cookie_jar_path' => $dashboard_client->cookie_jar_path,
                'cookie_content' => file_exists($dashboard_client->cookie_jar_path) ? file_get_contents($dashboard_client->cookie_jar_path) : 'No cookie jar'
            )
        ));
    }
    
    public function ajax_test_login_no_csrf() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Test login without CSRF token
        $phone = get_option('dropshippingbd_dashboard_phone');
        $password = base64_decode(get_option('dropshippingbd_dashboard_password'));

        if (empty($phone) || empty($password)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'No credentials found. Please set credentials first.'
            ));
        }

        $login_url = 'https://www.dropshipping.com.bd/api/reseller/login';
        $login_data = array(
            'phone' => $phone,
            'password' => $password
        );

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $login_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($login_data),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json, text/plain, */*',
                'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
                'Content-Type: application/json;charset=UTF-8',
                'Origin: https://www.dropshipping.com.bd',
                'Priority: u=1, i',
                'Referer: https://www.dropshipping.com.bd/dropshipper/login',
                'Sec-Ch-Ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "macOS"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'X-Requested-With: XMLHttpRequest'
            )
        ));

        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to connect: ' . $error
            ));
        }

        wp_send_json(array(
            'success' => true,
            'message' => 'Login test without CSRF completed',
            'data' => array(
                'http_code' => $http_code,
                'response_body' => substr($body, 0, 2000),
                'url_tested' => $login_url
            )
        ));
    }
    
    public function ajax_test_login_with_cookies() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Test login with the exact cookies from your working curl request
        $phone = get_option('dropshippingbd_dashboard_phone');
        $password = base64_decode(get_option('dropshippingbd_dashboard_password'));

        if (empty($phone) || empty($password)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'No credentials found. Please set credentials first.'
            ));
        }

        $login_url = 'https://www.dropshipping.com.bd/api/reseller/login';
        $login_data = array(
            'phone' => $phone,
            'password' => $password
        );

        // Use the exact cookies from your working curl request
        $cookies = 'XSRF-TOKEN=eyJpdiI6IlZKZ3JlT05Wd1hlUWJnbitLTkFwWUE9PSIsInZhbHVlIjoiRjYzajRRU3FzcDNrZ2xiS3AzNnFOWUJUZ0IyNHptUDZHcXpZeGNDS2N3RVhvZ2ZhNExreVZneHk2UFNRc1dGZkVZdHRuSlZ2ZEkrKys3NjBEMVptak13bEY3ME16bHpIS1M5L3EzWmhUTWg5a09COGhYczRzVStGRlN3aFV1V0QiLCJtYWMiOiIzODg2NjFmYzk4OTEzYTRlMzA1ZWJjMWNmYTk1Y2E5ZWFmZWEyN2Y1M2JjMDA5NjU3NmI1M2EzZTRjNjA3YjQyIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6Im1IdVQ0bG9DZmdUeFl4dDROSXdSdXc9PSIsInZhbHVlIjoiOTlaNVJETWVQaWFmMk5WTTBzQnNEZGxUK0FPTEZPay8rSE1BRnUwWWNtaTNha1pFVVRZMURDRjhRbmx0SkhpUUxRUHlWTCtGTWRqei8xMHdjaWMrSGw5UW9DL1FhenRNSXdoTksxdmRrQXNFa0Y3SVh5ZWJleGZWeUlsbTQ3MEkiLCJtYWMiOiI4ZGQ4NzkxZjZjMmVmMDU1Y2IzMGNkOWJkYTNkZjEyNjIwN2FlZGQ3ZjI3YTZkYWI5MGEzZGE2ZjUwMDA5YzU0IiwidGFnIjoiIn0%3D';
        
        $xsrf_token = 'eyJpdiI6IlZKZ3JlT05Wd1hlUWJnbitLTkFwWUE9PSIsInZhbHVlIjoiRjYzajRRU3FzcDNrZ2xiS3AzNnFOWUJUZ0IyNHptUDZHcXpZeGNDS2N3RVhvZ2ZhNExreVZneHk2UFNRc1dGZkVZdHRuSlZ2ZEkrKys3NjBEMVptak13bEY3ME16bHpIS1M5L3EzWmhUTWg5a09COGhYczRzVStGRlN3aFV1V0QiLCJtYWMiOiIzODg2NjFmYzk4OTEzYTRlMzA1ZWJjMWNmYTk1Y2E5ZWFmZWEyN2Y1M2JjMDA5NjU3NmI1M2EzZTRjNjA3YjQyIiwidGFnIjoiIn0=';

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $login_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($login_data),
            CURLOPT_COOKIE => $cookies,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json, text/plain, */*',
                'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
                'Content-Type: application/json;charset=UTF-8',
                'Origin: https://www.dropshipping.com.bd',
                'Priority: u=1, i',
                'Referer: https://www.dropshipping.com.bd/dropshipper/login',
                'Sec-Ch-Ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "macOS"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'X-Requested-With: XMLHttpRequest',
                'X-XSRF-TOKEN: ' . $xsrf_token
            )
        ));

        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to connect: ' . $error
            ));
        }

        wp_send_json(array(
            'success' => true,
            'message' => 'Login test with exact cookies completed',
            'data' => array(
                'http_code' => $http_code,
                'response_body' => substr($body, 0, 2000),
                'url_tested' => $login_url,
                'cookies_used' => $cookies,
                'xsrf_token_used' => $xsrf_token
            )
        ));
    }
    
    public function ajax_test_fresh_login() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Test fresh login - get new cookies and try login
        $phone = get_option('dropshippingbd_dashboard_phone');
        $password = base64_decode(get_option('dropshippingbd_dashboard_password'));

        if (empty($phone) || empty($password)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'No credentials found. Please set credentials first.'
            ));
        }

        // Step 1: Get fresh cookies from login page
        $login_page_url = 'https://www.dropshipping.com.bd/dropshipper/login';
        $cookie_jar = tempnam(sys_get_temp_dir(), 'dropshipping_cookies');
        
        $ch1 = curl_init();
        curl_setopt_array($ch1, array(
            CURLOPT_URL => $login_page_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            CURLOPT_COOKIEJAR => $cookie_jar,
            CURLOPT_COOKIEFILE => $cookie_jar,
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
                'Upgrade-Insecure-Requests: 1'
            )
        ));
        
        $page_body = curl_exec($ch1);
        $page_http_code = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
        curl_close($ch1);
        
        if ($page_http_code !== 200) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to get login page. HTTP code: ' . $page_http_code
            ));
        }
        
        // Step 2: Extract CSRF token from the page
        $csrf_token = null;
        if (preg_match('/name="_token" value="([^"]+)"/', $page_body, $matches)) {
            $csrf_token = $matches[1];
        } elseif (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $page_body, $matches)) {
            $csrf_token = $matches[1];
        }
        
        // Step 3: Extract cookies from cookie jar and format them properly
        $cookie_content = file_get_contents($cookie_jar);
        $cookie_header = '';
        
        // Extract cookies from cookie jar and format as single Cookie header
        if (preg_match_all('/([^=]+)=([^;]+);/', $cookie_content, $matches, PREG_SET_ORDER)) {
            $cookies = array();
            foreach ($matches as $match) {
                $cookies[] = trim($match[1]) . '=' . trim($match[2]);
            }
            $cookie_header = implode('; ', $cookies);
        }
        
        // Step 4: Try login with fresh cookies and CSRF token
        $login_url = 'https://www.dropshipping.com.bd/api/reseller/login';
        $login_data = array(
            'phone' => $phone,
            'password' => $password
        );
        
        if ($csrf_token) {
            $login_data['_token'] = $csrf_token;
        }

        // Build headers array matching your PHP example
        $headers = array(
            'Content-Type: application/json;charset=UTF-8',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
            'Origin: https://www.dropshipping.com.bd',
            'Referer: https://www.dropshipping.com.bd/dropshipper/login',
            'X-Requested-With: XMLHttpRequest'
        );
        
        // Add Cookie header if we have cookies
        if ($cookie_header) {
            $headers[] = 'Cookie: ' . $cookie_header;
        }
        
        // Add X-XSRF-TOKEN header if we have a token
        if ($csrf_token) {
            $headers[] = 'x-xsrf-token: ' . $csrf_token;
        }

        $ch2 = curl_init();
        curl_setopt_array($ch2, array(
            CURLOPT_URL => $login_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($login_data),
            CURLOPT_HTTPHEADER => $headers
        ));

        $login_body = curl_exec($ch2);
        $login_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        $error = curl_error($ch2);
        curl_close($ch2);
        
        // Clean up temp file
        unlink($cookie_jar);

        if ($error) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to connect: ' . $error
            ));
        }

        wp_send_json(array(
            'success' => true,
            'message' => 'Fresh login test completed',
            'data' => array(
                'page_http_code' => $page_http_code,
                'csrf_token_found' => $csrf_token ? 'Yes' : 'No',
                'csrf_token' => $csrf_token,
                'cookie_header' => $cookie_header,
                'login_http_code' => $login_http_code,
                'login_response_body' => substr($login_body, 0, 2000),
                'login_data_sent' => $login_data
            )
        ));
    }
    
    public function ajax_get_saved_credentials() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $phone = get_option('dropshippingbd_dashboard_phone');
        
        if (!empty($phone)) {
            wp_send_json(array(
                'success' => true,
                'data' => array(
                    'phone' => $phone,
                    'has_credentials' => true
                )
            ));
        } else {
            wp_send_json(array(
                'success' => false,
                'data' => array(
                    'has_credentials' => false
                )
            ));
        }
    }
    
    public function ajax_login_with_saved() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $phone = get_option('dropshippingbd_dashboard_phone');
        $password = base64_decode(get_option('dropshippingbd_dashboard_password'));
        
        if (empty($phone) || empty($password)) {
            wp_send_json(array(
                'success' => false,
                'message' => 'No saved credentials found'
            ));
        }
        if(!empty($phone) && !empty($password)) {
            wp_send_json(array(
                'success' => true,
                'message' => 'Login successful'
            ));
        }
        
        // $dashboard_client = new DropshippingBD_Dashboard_Client();
        // $result = $dashboard_client->login();
        
        // if (is_wp_error($result)) {
        //     wp_send_json(array(
        //         'success' => false,
        //         'message' => $result->get_error_message()
        //     ));
        // } else {
        //     wp_send_json(array(
        //         'success' => true,
        //         'message' => 'Login successful'
        //     ));
        // }
    }
    
    public function ajax_test_main_page() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $dashboard_client = new DropshippingBD_Dashboard_Client();
        
        // Test the main dashboard page
        $dashboard_url = 'https://www.dropshipping.com.bd/dropshipper/dashboard';
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $dashboard_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_COOKIEJAR => $dashboard_client->cookie_jar_path,
            CURLOPT_COOKIEFILE => $dashboard_client->cookie_jar_path,
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9,bn;q=0.8,cs;q=0.7',
                'Referer: https://www.dropshipping.com.bd/'
            )
        ));
        
        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            wp_send_json(array(
                'success' => false,
                'message' => 'Failed to connect: ' . $error
            ));
        }
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Main page test completed',
            'data' => array(
                'http_code' => $http_code,
                'response_body' => substr($body, 0, 2000),
                'cookie_jar_exists' => file_exists($dashboard_client->cookie_jar_path),
                'cookie_jar_path' => $dashboard_client->cookie_jar_path,
                'url_tested' => $dashboard_url
            )
        ));
    }
    
    public function ajax_test_exact_endpoint() {
        check_ajax_referer('dropshippingbd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        
        wp_send_json(array(
            'success' => true,
            'message' => 'Exact endpoint test completed',
            'data' => array(
                'http_code' => $http_code,
                'response_body' => substr($body, 0, 2000),
                'url_tested' => $login_url
            )
        ));
    }
    
    public function activate() {
        // Create custom table for tracking imported products
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dropshippingbd_products';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            mohasagor_id int(11) NOT NULL,
            woo_product_id int(11) NOT NULL,
            last_synced datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY (id),
            UNIQUE KEY mohasagor_id (mohasagor_id),
            KEY woo_product_id (woo_product_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
}

// Initialize the plugin
new DropshippingBD_Addon();

 
/**
 * Create a Variable product with custom (non-global) attribute + variations.
 * Tested on WooCommerce 8/9+ (HPOS and classic posts).
 */
add_action('admin_initx', function () {
    // Safety: only run for admins and only once per request.
    if ( ! current_user_can('manage_woocommerce') ) return;

    $product_sku = 'VAR-CUSTOM-001';

    // If it already exists, bail.
    if (wc_get_product_id_by_sku($product_sku)) {
        // error_log('Product already exists, skipping.');
        return;
    }

    // 1) Create the parent Variable product
    $product_id = wp_insert_post([
        'post_title'   => 'Custom Variable Tee',
        'post_content' => 'Programmatically created variable product with custom attribute.',
        'post_status'  => 'publish',
        'post_type'    => 'product',
    ]);

    if (is_wp_error($product_id) || ! $product_id) return;

    // Mark it variable + basic data
    wp_set_object_terms($product_id, 'variable', 'product_type');
    update_post_meta($product_id, '_sku', $product_sku);
    update_post_meta($product_id, '_visibility', 'visible'); // legacy
    update_post_meta($product_id, '_tax_status', 'taxable');
    update_post_meta($product_id, '_manage_stock', 'no');

    // 2) Define a CUSTOM (non-global) attribute "Color" with options
    //    These are product-level, not "pa_*" taxonomy terms.
    $attr_name   = 'Color';                  // Display name
    $options     = ['Red', 'Blue', 'Green']; // Your custom values

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(0);                   // 0 for custom (non-taxonomy) attribute
    $attribute->set_name($attr_name);        // Keep the display name; WC will sanitize internally
    $attribute->set_options($options);       // Array of strings
    $attribute->set_visible(true);
    $attribute->set_variation(true);

    $product = new WC_Product_Variable($product_id);
    $product->set_regular_price(''); // parent price blank for variable
    $product->set_attributes([$attribute]);
    $product->save(); // must save before adding variations

    // 3) Create variations for each option
    //    For custom attributes, variation meta keys use 'attribute_' . sanitize_title( $attr_name )
    $attr_key = 'attribute_' . sanitize_title($attr_name);

    $variation_data = [
        // option => data
        'Red' =>  [
            'regular_price' => '19.99',
            'sale_price'    => '16.99',
            'sku'           => 'VAR-CUSTOM-001-RED',
            'stock_qty'     => 25,
        ],
        'Blue' => [
            'regular_price' => '21.99',
            'sale_price'    => '',
            'sku'           => 'VAR-CUSTOM-001-BLU',
            'stock_qty'     => 10,
        ],
        'Green' => [
            'regular_price' => '18.99',
            'sale_price'    => '',
            'sku'           => 'VAR-CUSTOM-001-GRN',
            'stock_qty'     => 0, // will be out of stock
        ],
    ];

    foreach ($variation_data as $option_value => $vdata) {
        // Create the variation post
        $variation_id = wp_insert_post([
            'post_title'  => sprintf('Variation for product #%d - %s', $product_id, $option_value),
            'post_name'   => 'product-' . $product_id . '-variation-' . sanitize_title($option_value),
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type'   => 'product_variation',
            'menu_order'  => 0,
        ]);

        if (is_wp_error($variation_id) || ! $variation_id) continue;

        // Set the variation's attribute value (exact string for custom attribute)
        update_post_meta($variation_id, $attr_key, $option_value);

        // Price
        update_post_meta($variation_id, '_regular_price', $vdata['regular_price']);
        if (!empty($vdata['sale_price'])) {
            update_post_meta($variation_id, '_sale_price', $vdata['sale_price']);
            update_post_meta($variation_id, '_price', $vdata['sale_price']);
        } else {
            update_post_meta($variation_id, '_price', $vdata['regular_price']);
        }

        // Stock
        update_post_meta($variation_id, '_manage_stock', 'yes');
        update_post_meta($variation_id, '_stock', intval($vdata['stock_qty']));
        update_post_meta(
            $variation_id,
            '_stock_status',
            intval($vdata['stock_qty']) > 0 ? 'instock' : 'outofstock'
        );

        // SKU
        update_post_meta($variation_id, '_sku', $vdata['sku']);

        // Optional flags
        update_post_meta($variation_id, '_virtual', 'no');
        update_post_meta($variation_id, '_downloadable', 'no');
    }

    // 4) (Optional) Set default variation selection on product page
    $product->set_default_attributes([
        sanitize_title($attr_name) => 'Red', // default must match an option exactly (for custom attributes)
    ]);
    $product->save();

    // 5) Sync variation data up to the parent (prices, stock range, etc.)
    WC_Product_Variable::sync($product_id);
    WC_Product_Variable::sync_attributes($product_id);

    // Done!
    // error_log('Custom variable product created: ID ' . $product_id);
});