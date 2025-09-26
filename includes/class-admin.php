<?php
/**
 * Admin Interface for DropshippingBD Addon
 */

if (!defined('ABSPATH')) {
    exit;
}

class DropshippingBD_Admin {
    
    public function __construct() {
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
    
    /**
     * Display admin page
     */
    public function display_page() {
        ?>
        <!-- DropshippingBD Admin Page Loaded -->
        <script>
        console.log('DropshippingBD Admin Page HTML loaded');
        console.log('Plugin URL:', '<?php echo DROPSHIPPINGBD_PLUGIN_URL; ?>');
        console.log('Admin JS URL:', '<?php echo DROPSHIPPINGBD_PLUGIN_URL . 'assets/js/admin.js'; ?>');
        </script>
        <div class="wrap dropshippingbd-admin">
            <!-- Theme Switcher -->
            <div class="theme-switcher">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <span class="theme-icon">🌙</span>
                    <span class="theme-text">Dark Mode</span>
                </button>
            </div>
            
            <div class="dropshippingbd-header">
                <h1 class="dropshippingbd-title">
                    <span class="dropshippingbd-icon">📦</span>
                    DropshippingBD Management
                </h1>
                <p class="dropshippingbd-subtitle">Complete dropshipping solution for WooCommerce</p>
            </div>
            
            <!-- Tab Navigation -->
            <div class="dropshippingbd-tabs">
                <nav class="tab-nav">
                    <a href="#dashboard" class="tab-link active" data-tab="dashboard">
                        <span class="tab-icon">📊</span>
                        <span class="tab-text">Dashboard</span>
                    </a>
                    <a href="#products" class="tab-link" data-tab="products">
                        <span class="tab-icon">📦</span>
                        <span class="tab-text">Products</span>
                    </a>
                    <a href="#importer" class="tab-link" data-tab="importer">
                        <span class="tab-icon">🚀</span>
                        <span class="tab-text">Product Importer</span>
                    </a>
                    <a href="#orders" class="tab-link" data-tab="orders">
                        <span class="tab-icon">📋</span>
                        <span class="tab-text">Orders</span>
                    </a>
                    <a href="#customers" class="tab-link" data-tab="customers">
                        <span class="tab-icon">👥</span>
                        <span class="tab-text">Customers</span>
                    </a>
                    <a href="#finance" class="tab-link" data-tab="finance">
                        <span class="tab-icon">💰</span>
                        <span class="tab-text">Finance</span>
                    </a>
                    <a href="#settings" class="tab-link" data-tab="settings">
                        <span class="tab-icon">⚙️</span>
                        <span class="tab-text">Settings</span>
                    </a>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div id="dashboard-tab" class="tab-panel active">
                    <?php $this->display_dashboard_tab(); ?>
                </div>
                
                <!-- Products Tab -->
                <div id="products-tab" class="tab-panel">
                    <?php $this->display_products_tab(); ?>
                </div>
                
                <!-- Product Importer Tab -->
                <div id="importer-tab" class="tab-panel">
                    <?php $this->display_importer_tab(); ?>
                </div>
                
                <!-- Orders Tab -->
                <div id="orders-tab" class="tab-panel">
                    <?php $this->display_orders_tab(); ?>
                </div>
                
                <!-- Customers Tab -->
                <div id="customers-tab" class="tab-panel">
                    <?php $this->display_customers_tab(); ?>
                </div>
                
                <!-- Finance Tab -->
                <div id="finance-tab" class="tab-panel">
                    <?php $this->display_finance_tab(); ?>
                </div>
                
                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-panel">
                    <?php $this->display_settings_tab(); ?>
                </div>
            </div>
        </div>
        
        <style>
        /* Fallback styles in case CSS file doesn't load */
        .dropshippingbd-admin {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #1d2327;
            background: #f0f0f1;
            min-height: 100vh;
            padding: 20px;
        }
        
        .dropshippingbd-tabs {
            margin: 20px 0;
            background: #ffffff;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .tab-nav {
            display: flex;
            gap: 4px;
            overflow-x: auto;
            padding: 4px;
        }
        
        .tab-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #646970;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
            min-width: fit-content;
            cursor: pointer;
        }
        
        .tab-link:hover {
            background: #f0f0f1;
            color: #1d2327;
        }
        
        .tab-link.active {
            background: #007cba;
            color: white;
            box-shadow: 0 2px 8px rgba(0,123,255,0.3);
        }
        
        .tab-panel {
            display: none !important;
        }
        
        .tab-panel.active {
            display: block !important;
        }
        
        .dropshippingbd-card {
            background: #ffffff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #c3c4c7;
            background: #f0f0f1;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #1d2327;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            background: #ffffff;
            color: #1d2327;
            font-size: 14px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #007cba;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005a87;
        }
        
        /* Auth Status */
        .auth-status {
            margin-top: 15px;
            padding: 10px 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            color: #155724;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Saved Credentials */
        .saved-credentials {
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        
        .credentials-info h4 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        
        .credential-item {
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #fff;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        
        .credential-item strong {
            color: #333;
            margin-right: 8px;
        }
        
        .credential-item span {
            color: #666;
            font-family: monospace;
        }
        
        .credential-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .credential-actions .btn {
            font-size: 14px;
            padding: 8px 16px;
        }
        
        /* Dashboard Stats */
        .dashboard-stats {
            margin-top: 20px;
        }
        
        .loading-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Dashboard Sections */
        .stats-section {
            margin: 20px 0;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .stats-section h4 {
            margin: 0 0 15px 0;
            color: var(--text-color);
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Order Statistics Grid */
        .order-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .order-stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .order-stat-item .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-align: center;
        }
        
        .order-stat-item .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        /* Financial Summary Grid */
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .financial-card {
            display: flex;
            align-items: center;
            padding: 20px;
            background: var(--bg-color);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease;
        }
        
        .financial-card:hover {
            transform: translateY(-2px);
        }
        
        .financial-icon {
            font-size: 32px;
            margin-right: 15px;
        }
        
        .financial-content {
            flex: 1;
        }
        
        .financial-amount {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .financial-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        /* Order Status Chart */
        .status-chart-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .status-bar {
            flex: 1;
            height: 20px;
            background: var(--border-color);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .status-fill {
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }
        
        .status-info {
            display: flex;
            justify-content: space-between;
            min-width: 150px;
        }
        
        .status-label {
            font-size: 14px;
            color: var(--text-color);
        }
        
        .status-count {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        /* Monthly Profit Chart */
        .profit-chart-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 10px;
            align-items: end;
            height: 200px;
            padding: 20px 0;
        }
        
        .profit-month {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }
        
        .profit-bar {
            flex: 1;
            width: 100%;
            background: var(--border-color);
            border-radius: 4px 4px 0 0;
            position: relative;
            margin-bottom: 10px;
        }
        
        .profit-fill {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: linear-gradient(to top, var(--primary-color), var(--accent-color));
            border-radius: 4px 4px 0 0;
            transition: height 0.3s ease;
        }
        
        .profit-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .profit-month-name {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        
        .profit-amount {
            font-size: 10px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 2px;
        }
        
        .profit-percentage {
            font-size: 10px;
            color: var(--text-muted);
        }
        
        /* Tables */
        .orders-table, .customers-table, .finance-table {
            margin-top: 20px;
        }
        
        .orders-table table, .customers-table table, .finance-table table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .orders-table th, .customers-table th, .finance-table th,
        .orders-table td, .customers-table td, .finance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .orders-table th, .customers-table th, .finance-table th {
            background: var(--bg-color);
            font-weight: 600;
            color: var(--text-color);
        }
        
        .orders-table td, .customers-table td, .finance-table td {
            color: var(--text-color);
        }
        
        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pending { background: #F6BE00; color: #000; }
        .status-approved { background: #90EE90; color: #000; }
        .status-delivered { background: #4CAF50; color: #fff; }
        .status-cancelled { background: #FF474C; color: #fff; }
        .status-new { background: #2196F3; color: #fff; }
        .status-shipment { background: #efc371; color: #000; }
        .status-return { background: #000; color: #fff; }
        .status-packaging { background: #72839A; color: #fff; }
        .status-returned { background: #f44336; color: #fff; }
        
        /* Transaction Types */
        .transaction-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .transaction-type.credit { background: #4CAF50; color: #fff; }
        .transaction-type.debit { background: #f44336; color: #fff; }
        .transaction-type.income { background: #2196F3; color: #fff; }
        .transaction-type.expense { background: #FF9800; color: #fff; }
        
        /* Orders Display */
        .orders-display {
            margin-top: 20px;
        }
        
        .orders-summary {
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .summary-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .summary-content {
            flex: 1;
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .orders-container h3 {
            margin: 0 0 15px 0;
            color: var(--text-color);
            font-size: 18px;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        
        .table-placeholder {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        
        .placeholder-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        /* Finance Tab Styles */
        .account-display, .cashbook-display, .withdraw-display {
            margin-top: 20px;
        }
        
        .account-summary, .cashbook-summary {
            margin-bottom: 20px;
        }
        
        .payment-methods-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .method-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .method-info {
            flex: 1;
        }
        
        .method-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        
        .method-details {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .no-payment-methods, .no-transactions, .no-withdrawals {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        
        .cashbook-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .pagination-info {
            color: var(--text-color);
            font-weight: 500;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #ffffff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007cba;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #646970;
            font-size: 0.9rem;
        }
        
        .stats-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .cache-info {
            color: #646970;
            font-size: 0.8rem;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            console.log('Fallback JS loaded');
            console.log('AJAX URL:', ajaxurl || '/wp-admin/admin-ajax.php');
            console.log('Nonce:', '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>');
            
            // Tab functionality
            $('.tab-link').on('click', function(e) {
                e.preventDefault();
                
                const targetTab = $(this).data('tab');
                console.log('Tab clicked:', targetTab);
                
                // Remove active class from all tabs and panels
                $('.tab-link').removeClass('active');
                $('.tab-panel').removeClass('active');
                
                // Add active class to clicked tab and corresponding panel
                $(this).addClass('active');
                const targetPanel = $(`#${targetTab}-tab`);
                
                if (targetPanel.length) {
                    targetPanel.addClass('active');
                    console.log('Tab switched to:', targetTab);
                } else {
                    console.error('Target panel not found:', `#${targetTab}-tab`);
                }
                
                // Update URL hash
                window.location.hash = targetTab;
                
                // Load tab-specific data
                loadTabData(targetTab);
            });
            
            function loadTabData(tabName) {
                console.log('Loading data for tab:', tabName);
                
                switch(tabName) {
                    case 'products':
                        loadProductStats();
                        break;
                    case 'orders':
                        loadOrders();
                        break;
                    case 'customers':
                        loadCustomers();
                        break;
                    case 'finance':
                        loadFinanceData();
                        break;
                    case 'settings':
                        loadSettings();
                        break;
                    case 'dashboard':
                        // Dashboard data is loaded automatically on page load
                        break;
                    default:
                        console.log('No specific data loading for tab:', tabName);
                }
            }
            
            // Debug: Check if tab elements exist
            console.log('Tab links found:', $('.tab-link').length);
            console.log('Tab panels found:', $('.tab-panel').length);
            
            // Load data for initial tab (if hash exists)
            const initialHash = window.location.hash.substring(1);
            if (initialHash) {
                console.log('Initial hash found:', initialHash);
                const initialTab = $(`.tab-link[data-tab="${initialHash}"]`);
                if (initialTab.length) {
                    initialTab.click();
                }
            } else {
                // Default to dashboard tab
                $('.tab-link[data-tab="dashboard"]').click();
            }
            
            
            // Dashboard Authentication
            $('#dashboard-auth-form').on('submit', function(e) {
                e.preventDefault();
                
                const phone = $('#dashboard-phone').val();
                const password = $('#dashboard-password').val();
                
                if (!phone || !password) {
                    alert('Please enter both phone number and password.');
                    return;
                }
                
                loginToDashboard(phone, password);
            });
            
            function loginToDashboard(phone, password) {
                const loginBtn = $('#login-btn');
                const originalText = loginBtn.html();
                
                loginBtn.prop('disabled', true);
                loginBtn.html('<span class="btn-icon">⏳</span>Logging in...');
                
                console.log('Attempting to login to dashboard...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_set_dashboard_credentials',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        phone: phone,
                        password: password
                    },
                    success: function(response) {
                        console.log('Login response:', response);
                        
                        if (response.success) {
                            console.log('Login successful:', response.message);
                            alert('Successfully logged in!');
                            
                            // Show auth status
                            $('#auth-status').show();
                            $('#login-btn').hide();
                            $('#logout-btn').show();
                            
                            // Hide form and show saved credentials
                            $('#dashboard-auth-form').hide();
                            loadSavedCredentials();
                            
                            // Load dashboard statistics
                            loadDashboardStatistics();
                        } else {
                            console.log('Login failed:', response.message);
                            alert('Login failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error);
                        alert('An error occurred during login: ' + error);
                    },
                    complete: function() {
                        loginBtn.prop('disabled', false);
                        loginBtn.html(originalText);
                    }
                });
            }
            
            function loadDashboardStatistics() {
                console.log('Loading dashboard statistics...');
                
                // Show loading indicator
                $('#dashboard-loading').show();
                $('#dashboard-stats').show();
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_dashboard_data',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        start_date: '',
                        end_date: ''
                    },
                    success: function(response) {
                        console.log('Dashboard data response:', response);
                        
                        if (response.success) {
                            console.log('Dashboard statistics loaded successfully');
                            
                            // Hide loading indicator
                            $('#dashboard-loading').hide();
                            
                            // Display statistics
                            displayDashboardStats(response.data);
                            
                            // Show dashboard stats section
                            $('#dashboard-stats').show();
                            
                            // Don't show alert for auto-load
                            if (!window.autoLoading) {
                                alert('Dashboard statistics loaded successfully!');
                            } else {
                                console.log('Dashboard statistics loaded automatically (no alert shown)');
                            }
                        } else {
                            console.log('Failed to load dashboard statistics:', response.message);
                            $('#dashboard-loading').hide();
                            alert('Failed to load dashboard statistics: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error loading dashboard data:', error);
                        $('#dashboard-loading').hide();
                        alert('An error occurred while loading statistics: ' + error);
                    },
                    complete: function() {
                        // Reset auto-loading flag after request completes
                        window.autoLoading = false;
                    }
                });
            }
            
            function displayDashboardStats(data) {
                if (!data) return;
                
                console.log('Displaying dashboard stats:', data);
                
                // Update main statistics cards
                $('#total-balance').text(formatCurrency(data.wallet_value || 0));
                $('#total-products').text(data.total_data?.total_orders || 0);
                $('#total-orders').text(data.orders?.total_order || 0);
                $('#total-customers').text(data.total_data?.total_orders || 0);
                
                // Update order statistics
                updateOrderStats(data.orders);
                
                // Update financial summary
                updateFinancialSummary(data);
                
                // Update order status distribution
                updateOrderStatusChart(data.order_status_counts);
                
                // Update monthly profit chart
                updateMonthlyProfitChart(data.monthly_profit);
            }
            
            function updateOrderStats(orders) {
                if (!orders) return;
                
                // Create order stats HTML if it doesn't exist
                if ($('#order-stats-section').length === 0) {
                    $('#dashboard-stats').append(`
                        <div id="order-stats-section" class="stats-section">
                            <h4>📋 Order Statistics</h4>
                            <div class="order-stats-grid">
                                <div class="order-stat-item">
                                    <span class="stat-label">Today's Orders</span>
                                    <span class="stat-value" id="today-orders">0</span>
                                </div>
                                <div class="order-stat-item">
                                    <span class="stat-label">New Orders</span>
                                    <span class="stat-value" id="new-orders">0</span>
                                </div>
                                <div class="order-stat-item">
                                    <span class="stat-label">Pending Orders</span>
                                    <span class="stat-value" id="pending-orders">0</span>
                                </div>
                                <div class="order-stat-item">
                                    <span class="stat-label">Approved Orders</span>
                                    <span class="stat-value" id="approved-orders">0</span>
                                </div>
                                <div class="order-stat-item">
                                    <span class="stat-label">Delivered Orders</span>
                                    <span class="stat-value" id="delivered-orders">0</span>
                                </div>
                                <div class="order-stat-item">
                                    <span class="stat-label">Cancelled Orders</span>
                                    <span class="stat-value" id="cancelled-orders">0</span>
                                </div>
                            </div>
                        </div>
                    `);
                }
                
                // Update order values
                $('#today-orders').text(orders.today_order || 0);
                $('#new-orders').text(orders.new_order || 0);
                $('#pending-orders').text(orders.pending_order || 0);
                $('#approved-orders').text(orders.approved_order || 0);
                $('#delivered-orders').text(orders.total_delivered_order || 0);
                $('#cancelled-orders').text(orders.cancel_order || 0);
            }
            
            function updateFinancialSummary(data) {
                if (!$('#financial-summary-section').length) {
                    $('#dashboard-stats').append(`
                        <div id="financial-summary-section" class="stats-section">
                            <h4>💰 Financial Summary</h4>
                            <div class="financial-grid">
                                <div class="financial-card">
                                    <div class="financial-icon">💳</div>
                                    <div class="financial-content">
                                        <div class="financial-amount" id="wallet-value">0</div>
                                        <div class="financial-label">Wallet Balance</div>
                                    </div>
                                </div>
                                <div class="financial-card">
                                    <div class="financial-icon">📈</div>
                                    <div class="financial-content">
                                        <div class="financial-amount" id="total-income">0</div>
                                        <div class="financial-label">Total Income</div>
                                    </div>
                                </div>
                                <div class="financial-card">
                                    <div class="financial-icon">💸</div>
                                    <div class="financial-content">
                                        <div class="financial-amount" id="total-payoff">0</div>
                                        <div class="financial-label">Total Payoff</div>
                                    </div>
                                </div>
                                <div class="financial-card">
                                    <div class="financial-icon">📊</div>
                                    <div class="financial-content">
                                        <div class="financial-amount" id="total-profit">0</div>
                                        <div class="financial-label">Total Profit</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }
                
                $('#wallet-value').text(formatCurrency(data.wallet_value || 0));
                $('#total-income').text(formatCurrency(data.total_income || 0));
                $('#total-payoff').text(formatCurrency(data.total_pay_off || 0));
                $('#total-profit').text(formatCurrency(data.total_data?.total_profit || 0));
            }
            
            function updateOrderStatusChart(orderStatusCounts) {
                if (!$('#order-status-chart-section').length) {
                    $('#dashboard-stats').append(`
                        <div id="order-status-chart-section" class="stats-section">
                            <h4>📊 Order Status Distribution</h4>
                            <div id="order-status-chart" class="status-chart"></div>
                        </div>
                    `);
                }
                
                let chartHtml = '<div class="status-chart-grid">';
                orderStatusCounts.forEach(function(item) {
                    const percentage = item.value > 0 ? (item.value / orderStatusCounts.reduce((sum, i) => sum + i.value, 0)) * 100 : 0;
                    chartHtml += `
                        <div class="status-item">
                            <div class="status-bar">
                                <div class="status-fill" style="width: ${percentage}%; background-color: ${item.color};"></div>
                            </div>
                            <div class="status-info">
                                <span class="status-label">${item.label}</span>
                                <span class="status-count">${item.value}</span>
                            </div>
                        </div>
                    `;
                });
                chartHtml += '</div>';
                
                $('#order-status-chart').html(chartHtml);
            }
            
            function updateMonthlyProfitChart(monthlyProfit) {
                if (!$('#monthly-profit-chart-section').length) {
                    $('#dashboard-stats').append(`
                        <div id="monthly-profit-chart-section" class="stats-section">
                            <h4>📈 Monthly Profit Trend</h4>
                            <div id="monthly-profit-chart" class="profit-chart"></div>
                        </div>
                    `);
                }
                
                let chartHtml = '<div class="profit-chart-grid">';
                monthlyProfit.forEach(function(item) {
                    const maxProfit = Math.max(...monthlyProfit.map(m => m.total_profit));
                    const height = maxProfit > 0 ? (item.total_profit / maxProfit) * 100 : 0;
                    chartHtml += `
                        <div class="profit-month">
                            <div class="profit-bar">
                                <div class="profit-fill" style="height: ${height}%;"></div>
                            </div>
                            <div class="profit-info">
                                <span class="profit-month-name">${item.month_name.substring(0, 3)}</span>
                                <span class="profit-amount">${formatCurrency(item.total_profit)}</span>
                                <span class="profit-percentage">${item.profit_percentage}%</span>
                            </div>
                        </div>
                    `;
                });
                chartHtml += '</div>';
                
                $('#monthly-profit-chart').html(chartHtml);
            }
            
            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-BD', {
                    style: 'currency',
                    currency: 'BDT',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }).format(amount);
            }
            
            function loadProductStats() {
                console.log('Loading product statistics...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_total_products',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Product stats response:', response);
                        
                        if (response.success) {
                            $('#available-products').text(response.data.total_products || 0);
                            $('#total-pages').text(response.data.total_pages || 0);
                        } else {
                            console.log('Failed to load product stats:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error loading product stats:', error);
                    }
                });
            }
            
            function startBulkImport() {
                const page = $('#import-page').val();
                const perPage = $('#import-per-page').val();
                const importBtn = $('#bulk-import-btn');
                const originalText = importBtn.html();
                
                console.log('Starting bulk import:', { page, perPage });
                
                // Disable button and show loading
                importBtn.prop('disabled', true);
                importBtn.html('<span class="btn-icon">⏳</span> Importing...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_import_products',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        page: page,
                        per_page: perPage
                    },
                    success: function(response) {
                        console.log('Bulk import response:', response);
                        
                        if (response.success) {
                            alert('Bulk import completed successfully!\n\n' +
                                  'Imported: ' + (response.data.imported || 0) + ' products\n' +
                                  'Skipped: ' + (response.data.skipped || 0) + ' products\n' +
                                  'Errors: ' + (response.data.errors || 0) + ' products');
                            
                            // Reload product stats
                            loadProductStats();
                        } else {
                            alert('Bulk import failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error during bulk import:', error);
                        alert('An error occurred during bulk import: ' + error);
                    },
                    complete: function() {
                        // Re-enable button
                        importBtn.prop('disabled', false);
                        importBtn.html(originalText);
                    }
                });
            }
            
            function importCategories() {
                const importBtn = $('#import-categories-btn');
                const originalText = importBtn.html();
                
                console.log('Starting category import...');
                
                // Disable button and show loading
                importBtn.prop('disabled', true);
                importBtn.html('<span class="btn-icon">⏳</span> Importing Categories...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_import_categories',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Category import response:', response);
                        
                        if (response.success) {
                            alert('Category import completed successfully!\n\n' +
                                  'Imported: ' + (response.data.imported_categories || 0) + ' categories');
                        } else {
                            alert('Category import failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error during category import:', error);
                        alert('An error occurred during category import: ' + error);
                    },
                    complete: function() {
                        // Re-enable button
                        importBtn.prop('disabled', false);
                        importBtn.html(originalText);
                    }
                });
            }
            
            function loadOrders() {
                console.log('loadOrders function called');
                
                const page = $('#orders-page').val();
                const item = $('#orders-item').val();
                const status = $('#orders-status').val();
                const startDate = $('#orders-start-date').val();
                const endDate = $('#orders-end-date').val();
                
                console.log('Loading orders with params:', { page, item, status, startDate, endDate });
                console.log('AJAX URL:', ajaxurl || '/wp-admin/admin-ajax.php');
                console.log('Nonce:', '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_orders',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        page: page,
                        item: item,
                        status_code: status,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        console.log('Orders AJAX success response:', response);
                        
                        if (response.success) {
                            console.log('Orders data received:', response.data);
                            displayOrders(response.data);
                        } else {
                            console.log('Orders request failed:', response.message);
                            alert('Failed to load orders: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Orders AJAX error:', { xhr, status, error });
                        console.log('Response text:', xhr.responseText);
                        alert('An error occurred while loading orders: ' + error);
                    }
                });
            }
            
            function displayOrders(data) {
                console.log('Displaying orders data:', data);
                
                // Show the orders display section
                $('#orders-display').show();
                
                // Update total orders count
                $('#total-orders-count').text(data.total_orders || 0);
                
                let ordersHtml = '';
                if (data.orders && data.orders.length > 0) {
                    ordersHtml += '<table><thead><tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th></tr></thead><tbody>';
                    data.orders.forEach(function(order) {
                        ordersHtml += `
                            <tr>
                                <td>${order.id || 'N/A'}</td>
                                <td>${order.customer_name || 'N/A'}</td>
                                <td><span class="status-badge status-${order.status || 'unknown'}">${order.status || 'N/A'}</span></td>
                                <td>${formatCurrency(order.total || 0)}</td>
                                <td>${order.created_at || 'N/A'}</td>
                            </tr>
                        `;
                    });
                    ordersHtml += '</tbody></table>';
                } else {
                    ordersHtml += '<div class="no-orders"><p>No orders found.</p></div>';
                }
                
                $('#orders-table').html(ordersHtml);
            }
            
            function loadCustomers() {
                const page = $('#customers-page').val();
                const item = $('#customers-item').val();
                
                console.log('Loading customers:', { page, item });
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_customers',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        page: page,
                        item: item
                    },
                    success: function(response) {
                        console.log('Customers response:', response);
                        
                        if (response.success) {
                            displayCustomers(response.data);
                        } else {
                            alert('Failed to load customers: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error loading customers:', error);
                        alert('An error occurred while loading customers: ' + error);
                    }
                });
            }
            
            function displayCustomers(data) {
                if (!$('#customers-list').length) {
                    $('#customers-tab .card-body').append('<div id="customers-list" class="customers-list"></div>');
                }
                
                let customersHtml = '<div class="customers-table">';
                if (data.customers && data.customers.length > 0) {
                    customersHtml += '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th></tr></thead><tbody>';
                    data.customers.forEach(function(customer) {
                        customersHtml += `
                            <tr>
                                <td>${customer.id || 'N/A'}</td>
                                <td>${customer.name || 'N/A'}</td>
                                <td>${customer.email || 'N/A'}</td>
                                <td>${customer.phone || 'N/A'}</td>
                                <td>${customer.total_orders || 0}</td>
                            </tr>
                        `;
                    });
                    customersHtml += '</tbody></table>';
                } else {
                    customersHtml += '<p>No customers found.</p>';
                }
                customersHtml += '</div>';
                
                $('#customers-list').html(customersHtml);
            }
            
            function loadFinanceData() {
                console.log('Finance tab activated - loading initial data');
                // Account info loading is handled by external JS file
            }
            
            
            
            function loadCashbookData() {
                console.log('Loading cashbook data...');
                
                const type = $('#cashbook-type').val();
                const page = $('#cashbook-page').val();
                const item = $('#cashbook-item').val();
                const startDate = $('#cashbook-start-date').val();
                const endDate = $('#cashbook-end-date').val();
                
                console.log('Cashbook params:', { type, page, item, startDate, endDate });
                
                const fetchBtn = $('#fetch-cashbook-btn');
                const originalText = fetchBtn.html();
                
                // Show loading state
                fetchBtn.prop('disabled', true);
                fetchBtn.html('<span class="btn-icon">⏳</span> Loading...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_cashbook',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        page: page,
                        item: item,
                        is_income: type,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        console.log('Cashbook response:', response);
                        
                        if (response.success) {
                            displayCashbookData(response.data);
                        } else {
                            alert('Failed to load cashbook data: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error loading cashbook:', error);
                        alert('An error occurred while loading cashbook data: ' + error);
                    },
                    complete: function() {
                        // Re-enable button
                        fetchBtn.prop('disabled', false);
                        fetchBtn.html(originalText);
                    }
                });
            }
            
            function displayCashbookData(data) {
                console.log('Displaying cashbook data:', data);
                
                // Show the cashbook display section
                $('#cashbook-display').show();
                
                // Update summary
                $('#total-balance-cashbook').text(formatCurrency(data.total_balance || 0));
                $('#total-transactions').text(data.total_transactions || 0);
                
                // Display transactions
                let transactionsHtml = '';
                if (data.transactions && data.transactions.length > 0) {
                    transactionsHtml += '<table><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead><tbody>';
                    data.transactions.forEach(function(transaction) {
                        const typeClass = transaction.is_income == 1 ? 'income' : 'expense';
                        const typeText = transaction.is_income == 1 ? 'Income' : 'Expense';
                        transactionsHtml += `
                            <tr>
                                <td>${transaction.date || 'N/A'}</td>
                                <td><span class="transaction-type ${typeClass}">${typeText}</span></td>
                                <td>${formatCurrency(transaction.amount || 0)}</td>
                                <td>${transaction.description || 'N/A'}</td>
                            </tr>
                        `;
                    });
                    transactionsHtml += '</tbody></table>';
                } else {
                    transactionsHtml = '<div class="no-transactions"><p>No transactions found.</p></div>';
                }
                
                $('#cashbook-table').html(transactionsHtml);
            }
            
            function loadWithdrawData() {
                console.log('Loading withdraw data...');
                
                const page = $('#withdraw-page').val();
                const item = $('#withdraw-item').val();
                
                console.log('Withdraw params:', { page, item });
                
                const fetchBtn = $('#fetch-withdraw-btn');
                const originalText = fetchBtn.html();
                
                // Show loading state
                fetchBtn.prop('disabled', true);
                fetchBtn.html('<span class="btn-icon">⏳</span> Loading...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_withdraw_transactions',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>',
                        page: page,
                        item: item
                    },
                    success: function(response) {
                        console.log('Withdraw response:', response);
                        
                        if (response.success) {
                            displayWithdrawData(response.data);
                        } else {
                            alert('Failed to load withdraw data: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error loading withdraw data:', error);
                        alert('An error occurred while loading withdraw data: ' + error);
                    },
                    complete: function() {
                        // Re-enable button
                        fetchBtn.prop('disabled', false);
                        fetchBtn.html(originalText);
                    }
                });
            }
            
            function displayWithdrawData(data) {
                console.log('Displaying withdraw data:', data);
                
                // Show the withdraw display section
                $('#withdraw-display').show();
                
                // Display withdraw transactions
                let withdrawHtml = '';
                if (data.transactions && data.transactions.length > 0) {
                    withdrawHtml += '<table><thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Method</th></tr></thead><tbody>';
                    data.transactions.forEach(function(transaction) {
                        withdrawHtml += `
                            <tr>
                                <td>${transaction.date || 'N/A'}</td>
                                <td>${formatCurrency(transaction.amount || 0)}</td>
                                <td><span class="status-badge status-${transaction.status || 'unknown'}">${transaction.status || 'N/A'}</span></td>
                                <td>${transaction.method || 'N/A'}</td>
                            </tr>
                        `;
                    });
                    withdrawHtml += '</tbody></table>';
                } else {
                    withdrawHtml = '<div class="no-withdrawals"><p>No withdrawal transactions found.</p></div>';
                }
                
                $('#withdraw-table').html(withdrawHtml);
            }
            
            
            
            // Logout button
            $('#logout-btn').on('click', function() {
                logoutFromDashboard();
            });
            
            // Test CSRF button
            $('#test-csrf-btn').on('click', function() {
                testCSRFToken();
            });
            
            // Test Dashboard button
            $('#test-dashboard-btn').on('click', function() {
                testDashboard();
            });
            
            // Test Login Page button
            $('#test-login-page-btn').on('click', function() {
                testLoginPage();
            });
            
            // Test Main Page button
            $('#test-main-page-btn').on('click', function() {
                testMainPage();
            });
            
            // Test Exact Endpoint button
            $('#test-exact-endpoint-btn').on('click', function() {
                testExactEndpoint();
            });
            
            // Test Login Page button
            $('#test-login-page-btn').on('click', function() {
                testLoginPage();
            });
            
            // Test Login No CSRF button
            $('#test-login-no-csrf-btn').on('click', function() {
                testLoginNoCsrf();
            });
            
            // Test Login With Cookies button
            $('#test-login-with-cookies-btn').on('click', function() {
                testLoginWithCookies();
            });
            
            // Test Fresh Login button
            $('#test-fresh-login-btn').on('click', function() {
                testFreshLogin();
            });
            
            // Edit credentials button
            $('#edit-credentials-btn').on('click', function() {
                showCredentialsForm();
            });
            
            // Login with saved credentials button
            $('#login-with-saved-btn').on('click', function() {
                loginWithSavedCredentials();
            });
            
            // Load saved credentials on page load
            loadSavedCredentials();
            
            // Auto-load dashboard statistics if authenticated
            autoLoadDashboardStats();
            
            // Products tab functionality
            $('#bulk-import-form').on('submit', function(e) {
                e.preventDefault();
                startBulkImport();
            });
            
            $('#import-categories-btn').on('click', function() {
                importCategories();
            });
            
            // Form submissions (keep these as they're different from tab clicks)
            $('#orders-filter-form').on('submit', function(e) {
                e.preventDefault();
                loadOrders();
            });
            
            $('#customers-filter-form').on('submit', function(e) {
                e.preventDefault();
                loadCustomers();
            });
            
            // Settings form handled by external JS file
            
            // Finance tab functionality - handled by external JS file
            
            $('#cashbook-filter-form').on('submit', function(e) {
                e.preventDefault();
                loadCashbookData();
            });
            
            $('#withdraw-filter-form').on('submit', function(e) {
                e.preventDefault();
                loadWithdrawData();
            });
            
            function logoutFromDashboard() {
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_logout_dashboard',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Logout response:', response);
                        
                        if (response.success) {
                            alert('Successfully logged out!');
                            
                            // Hide auth status and dashboard stats
                            $('#auth-status').hide();
                            $('#dashboard-stats').hide();
                            $('#login-btn').show();
                            $('#logout-btn').hide();
                            
                            // Clear form
                            $('#dashboard-auth-form')[0].reset();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Logout error:', error);
                        alert('Error during logout: ' + error);
                    }
                });
            }
            
            function testCSRFToken() {
                const testBtn = $('#test-csrf-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing CSRF token...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_csrf',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('CSRF test response:', response);
                        
                        if (response.success) {
                            console.log('CSRF test data:', response.data);
                            alert('CSRF test completed. Check console for details.');
                        } else {
                            console.log('CSRF test failed:', response.message);
                            alert('CSRF test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('CSRF test error:', error);
                        alert('CSRF test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testDashboard() {
                const testBtn = $('#test-dashboard-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing dashboard data...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_dashboard',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Dashboard test response:', response);
                        
                        if (response.success) {
                            console.log('Dashboard test data:', response.data);
                            alert('Dashboard test completed. Check console for details.');
                        } else {
                            console.log('Dashboard test failed:', response.message);
                            alert('Dashboard test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Dashboard test error:', error);
                        alert('Dashboard test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testLoginPage() {
                const testBtn = $('#test-login-page-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing login page...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_login_page',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Login page test response:', response);
                        
                        if (response.success) {
                            console.log('Login page test data:', response.data);
                            alert('Login page test completed. Check console for details.');
                        } else {
                            console.log('Login page test failed:', response.message);
                            alert('Login page test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Login page test error:', error);
                        alert('Login page test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function loadSavedCredentials() {
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_saved_credentials',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.phone) {
                            $('#saved-phone').text(response.data.phone);
                            $('#saved-credentials').show();
                            $('#dashboard-auth-form').hide();
                        } else {
                            $('#saved-credentials').hide();
                            $('#dashboard-auth-form').show();
                        }
                    },
                    error: function() {
                        $('#saved-credentials').hide();
                        $('#dashboard-auth-form').show();
                    }
                });
            }
            
            function autoLoadDashboardStats() {
                // Check if we have saved credentials
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_get_saved_credentials',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.phone) {
                            console.log('Credentials found, attempting auto-login...');
                            
                            // Try to login with saved credentials
                            $.ajax({
                                url: ajaxurl || '/wp-admin/admin-ajax.php',
                                type: 'POST',
                                data: {
                                    action: 'dropshippingbd_login_with_saved',
                                    nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                                },
                                success: function(loginResponse) {
                                    if (loginResponse.success) {
                                        console.log('Auto-login successful, loading dashboard statistics...');
                                        
                                        // Show saved credentials UI
                                        $('#saved-phone').text(response.data.phone);
                                        $('#saved-credentials').show();
                                        $('#dashboard-auth-form').hide();
                                        
                                        // Load dashboard statistics
                                        window.autoLoading = true;
                                        loadDashboardStatistics();
                                    } else {
                                        console.log('Auto-login failed:', loginResponse.message);
                                        // Show login form if auto-login fails
                                        $('#saved-credentials').hide();
                                        $('#dashboard-auth-form').show();
                                    }
                                },
                                error: function() {
                                    console.log('Auto-login error occurred');
                                    $('#saved-credentials').hide();
                                    $('#dashboard-auth-form').show();
                                }
                            });
                        } else {
                            console.log('No saved credentials found');
                            $('#saved-credentials').hide();
                            $('#dashboard-auth-form').show();
                        }
                    },
                    error: function() {
                        console.log('Error checking saved credentials');
                        $('#saved-credentials').hide();
                        $('#dashboard-auth-form').show();
                    }
                });
            }
            
            function showCredentialsForm() {
                $('#saved-credentials').hide();
                $('#dashboard-auth-form').show();
            }
            
            function loginWithSavedCredentials() {
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_login_with_saved',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Successfully logged in with saved credentials!');
                            
                            // Show auth status
                            $('#auth-status').show();
                            $('#login-btn').hide();
                            $('#logout-btn').show();
                            
                            // Load dashboard statistics
                            loadDashboardStatistics();
                        } else {
                            alert('Login failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred during login: ' + error);
                    }
                });
            }
            
            function testMainPage() {
                const testBtn = $('#test-main-page-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing main dashboard page...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_main_page',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Main page test response:', response);
                        
                        if (response.success) {
                            console.log('Main page test data:', response.data);
                            alert('Main page test completed. Check console for details.');
                        } else {
                            console.log('Main page test failed:', response.message);
                            alert('Main page test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Main page test error:', error);
                        alert('Main page test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testExactEndpoint() {
                const testBtn = $('#test-exact-endpoint-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing exact endpoint...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_exact_endpoint',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Exact endpoint test response:', response);
                        
                        if (response.success) {
                            console.log('Exact endpoint test data:', response.data);
                            alert('Exact endpoint test completed. Check console for details.');
                        } else {
                            console.log('Exact endpoint test failed:', response.message);
                            alert('Exact endpoint test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Exact endpoint test error:', error);
                        alert('Exact endpoint test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testLoginPage() {
                const testBtn = $('#test-login-page-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing login page...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_login_page',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Login page test response:', response);
                        
                        if (response.success) {
                            console.log('Login page test data:', response.data);
                            alert('Login page test completed. Check console for details.');
                        } else {
                            console.log('Login page test failed:', response.message);
                            alert('Login page test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Login page test error:', error);
                        alert('Login page test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testLoginNoCsrf() {
                const testBtn = $('#test-login-no-csrf-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing login without CSRF...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_login_no_csrf',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Login no CSRF test response:', response);
                        
                        if (response.success) {
                            console.log('Login no CSRF test data:', response.data);
                            alert('Login test without CSRF completed. Check console for details.');
                        } else {
                            console.log('Login no CSRF test failed:', response.message);
                            alert('Login test without CSRF failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Login no CSRF test error:', error);
                        alert('Login test without CSRF error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testLoginWithCookies() {
                const testBtn = $('#test-login-with-cookies-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing login with exact cookies...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_login_with_cookies',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Login with cookies test response:', response);
                        
                        if (response.success) {
                            console.log('Login with cookies test data:', response.data);
                            alert('Login test with exact cookies completed. Check console for details.');
                        } else {
                            console.log('Login with cookies test failed:', response.message);
                            alert('Login test with exact cookies failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Login with cookies test error:', error);
                        alert('Login test with exact cookies error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
            
            function testFreshLogin() {
                const testBtn = $('#test-fresh-login-btn');
                const originalText = testBtn.html();
                
                testBtn.prop('disabled', true);
                testBtn.html('<span class="btn-icon">⏳</span>Testing...');
                
                console.log('Testing fresh login...');
                
                $.ajax({
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'dropshippingbd_test_fresh_login',
                        nonce: '<?php echo wp_create_nonce('dropshippingbd_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Fresh login test response:', response);
                        
                        if (response.success) {
                            console.log('Fresh login test data:', response.data);
                            alert('Fresh login test completed. Check console for details.');
                        } else {
                            console.log('Fresh login test failed:', response.message);
                            alert('Fresh login test failed: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Fresh login test error:', error);
                        alert('Fresh login test error: ' + error);
                    },
                    complete: function() {
                        testBtn.prop('disabled', false);
                        testBtn.html(originalText);
                    }
                });
            }
        });
        </script>
        
        <?php
    }
    
    /**
     * Display Dashboard Tab
     */
    private function display_dashboard_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>📊 Dashboard Overview</h2>
                <p>Real-time statistics and performance metrics</p>
            </div>
            
            <div class="card-body">
                <!-- Authentication Section -->
                <div class="auth-section">
                    <h3>🔐 Account Authentication</h3>
                    
                    <!-- Saved Credentials Display -->
                    <div id="saved-credentials" class="saved-credentials" style="display: none;">
                        <div class="credentials-info">
                            <h4>📋 Saved Credentials</h4>
                            <div class="credential-item">
                                <strong>Phone:</strong> <span id="saved-phone"></span>
                            </div>
                            <div class="credential-item">
                                <strong>Password:</strong> <span id="saved-password">••••••••</span>
                            </div>
                            <div class="credential-actions">
                                <button type="button" class="btn btn-sm btn-outline" id="edit-credentials-btn">
                                    <span class="btn-icon">✏️</span>
                                    Edit Credentials
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="login-with-saved-btn">
                                    <span class="btn-icon">🔑</span>
                                    Login with Saved Credentials
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credentials Form -->
                    <form id="dashboard-auth-form" class="auth-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dashboard-phone">Phone Number</label>
                                <input type="tel" id="dashboard-phone" name="phone" placeholder="0171213456789" required>
                            </div>
                            <div class="form-group">
                                <label for="dashboard-password">Password</label>
                                <input type="password" id="dashboard-password" name="password" placeholder="Your password" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="login-btn">
                                <span class="btn-icon">🔑</span>
                                Login to Dashboard
                            </button>
                            <button type="button" class="btn btn-secondary" id="logout-btn" style="display: none;">
                                <span class="btn-icon">🚪</span>
                                Logout
                            </button>
                            <button type="button" class="btn btn-outline" id="test-csrf-btn">
                                <span class="btn-icon">🔍</span>
                                Test CSRF Token
                            </button>
                            <button type="button" class="btn btn-outline" id="test-dashboard-btn">
                                <span class="btn-icon">📊</span>
                                Test Dashboard
                            </button>
                            <button type="button" class="btn btn-outline" id="test-login-page-btn">
                                <span class="btn-icon">🔍</span>
                                Test Login Page
                            </button>
                            <button type="button" class="btn btn-outline" id="test-main-page-btn">
                                <span class="btn-icon">🏠</span>
                                Test Main Page
                            </button>
                            <button type="button" class="btn btn-outline" id="test-exact-endpoint-btn">
                                <span class="btn-icon">🎯</span>
                                Test Exact Endpoint
                            </button>
                            <button type="button" class="btn btn-outline" id="test-login-page-btn">
                                <span class="btn-icon">🔍</span>
                                Debug Login Page
                            </button>
                            <button type="button" class="btn btn-outline" id="test-login-no-csrf-btn">
                                <span class="btn-icon">🚫</span>
                                Test Login No CSRF
                            </button>
                            <button type="button" class="btn btn-outline" id="test-login-with-cookies-btn">
                                <span class="btn-icon">🍪</span>
                                Test Login With Cookies
                            </button>
                            <button type="button" class="btn btn-outline" id="test-fresh-login-btn">
                                <span class="btn-icon">🔄</span>
                                Test Fresh Login
                            </button>
                        </div>
                    </form>
                    
                    <div id="auth-status" class="auth-status" style="display: none;">
                        <div class="status-indicator">
                            <span class="status-icon">✅</span>
                            <span class="status-text">Connected to dropshipping.com.bd</span>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Section -->
                <div id="dashboard-stats" class="dashboard-stats" style="display: none;">
                    <h3>📈 Key Statistics</h3>
                    <div id="dashboard-loading" class="loading-indicator" style="display: none;">
                        <div class="loading-spinner"></div>
                        <span>Loading dashboard statistics...</span>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-balance">0</div>
                                <div class="stat-label">Account Balance</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-products">0</div>
                                <div class="stat-label">Total Products</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📋</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-orders">0</div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-customers">0</div>
                                <div class="stat-label">Total Customers</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Refresh Button -->
                    <div class="stats-actions">
                        <button type="button" class="btn btn-outline" id="refresh-stats-btn">
                            <span class="btn-icon">🔄</span>
                            Refresh Statistics
                        </button>
                        <div class="cache-info">
                            <small>Data cached for 5 minutes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Products Tab
     */
    private function display_products_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>📦 Product Management</h2>
                <p>Import and manage products from Mohasagor.com.bd</p>
            </div>
            
            <div class="card-body">
                <div class="product-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-content">
                            <div class="stat-number" id="available-products">0</div>
                            <div class="stat-label">Available Products</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📄</div>
                        <div class="stat-content">
                            <div class="stat-number" id="total-pages">0</div>
                            <div class="stat-label">Total Pages</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-number">20%</div>
                            <div class="stat-label">Price Markup</div>
                        </div>
                    </div>
                </div>
                
                <div class="import-section">
                    <h3>🚀 Bulk Import</h3>
                    <form id="bulk-import-form" class="import-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="import-page">Start Page</label>
                                <input type="number" id="import-page" name="page" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <label for="import-per-page">Products per Page</label>
                                <select id="import-per-page" name="per_page">
                                    <option value="10">10 products</option>
                                    <option value="20" selected>20 products</option>
                                    <option value="50">50 products</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="bulk-import-btn">
                                <span class="btn-icon">📥</span>
                                Start Bulk Import
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="category-section">
                    <h3>📂 Category Management</h3>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="import-categories-btn">
                            <span class="btn-icon">📂</span>
                            Import Categories
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Orders Tab
     */
    private function display_orders_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>📋 Order Management</h2>
                <p>View and manage orders from dropshipping.com.bd</p>
            </div>
            
            <div class="card-body">
                <div class="orders-filters">
                    <form id="orders-filter-form" class="orders-filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="orders-page">Page</label>
                                <input type="number" id="orders-page" name="page" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <label for="orders-item">Items per Page</label>
                                <select id="orders-item" name="item">
                                    <option value="10">10 orders</option>
                                    <option value="25">25 orders</option>
                                    <option value="50" selected>50 orders</option>
                                    <option value="100">100 orders</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="orders-status">Status</label>
                                <select id="orders-status" name="status_code">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="orders-start-date">Start Date</label>
                                <input type="date" id="orders-start-date" name="start_date">
                            </div>
                            <div class="form-group">
                                <label for="orders-end-date">End Date</label>
                                <input type="date" id="orders-end-date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-info" id="fetch-orders-btn">
                                <span class="btn-icon">📋</span>
                                Fetch Orders
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="orders-display" class="orders-display" style="display: none;">
                    <div class="orders-summary">
                        <div class="summary-card">
                            <div class="summary-icon">📊</div>
                            <div class="summary-content">
                                <div class="summary-number" id="total-orders-count">0</div>
                                <div class="summary-label">Total Orders</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="orders-container">
                        <h3>Order List</h3>
                        <div id="orders-table" class="orders-table">
                            <div class="table-placeholder">
                                <div class="placeholder-icon">📋</div>
                                <p>Orders will appear here...</p>
                            </div>
                        </div>
                        
                        <div id="orders-pagination" class="orders-pagination" style="display: none;">
                            <button type="button" class="btn btn-outline" id="prev-orders-btn">Previous</button>
                            <span id="orders-pagination-info" class="pagination-info"></span>
                            <button type="button" class="btn btn-outline" id="next-orders-btn">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Customers Tab
     */
    private function display_customers_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>👥 Customer Management</h2>
                <p>View and manage customers from dropshipping.com.bd</p>
            </div>
            
            <div class="card-body">
                <div class="customers-filters">
                    <form id="customers-filter-form" class="customers-filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customers-page">Page</label>
                                <input type="number" id="customers-page" name="page" value="1" min="1">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-info" id="fetch-customers-btn">
                                <span class="btn-icon">👥</span>
                                Fetch Customers
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="customers-display" class="customers-display" style="display: none;">
                    <div class="customers-summary">
                        <div class="summary-card">
                            <div class="summary-icon">👥</div>
                            <div class="summary-content">
                                <div class="summary-number" id="total-customers-count">0</div>
                                <div class="summary-label">Total Customers</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="customers-container">
                        <h3>Customer List</h3>
                        <div id="customers-table" class="customers-table">
                            <div class="table-placeholder">
                                <div class="placeholder-icon">👥</div>
                                <p>Customers will appear here...</p>
                            </div>
                        </div>
                        
                        <div id="customers-pagination" class="customers-pagination" style="display: none;">
                            <button type="button" class="btn btn-outline" id="prev-customers-btn">Previous</button>
                            <span id="customers-pagination-info" class="pagination-info"></span>
                            <button type="button" class="btn btn-outline" id="next-customers-btn">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Finance Tab
     */
    private function display_finance_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>💰 Financial Management</h2>
                <p>Track expenses, income, and withdrawal history</p>
            </div>
            
            <div class="card-body">
                <!-- Account Information -->
                <div class="account-section">
                    <h3>🏦 Account Information</h3>
                    <div class="form-actions">
                        <button type="button" class="btn btn-info" id="fetch-account-btn">
                            <span class="btn-icon">🏦</span>
                            Fetch Account Info
                        </button>
                    </div>
                    
                    <div id="account-display" class="account-display" style="display: none;">
                        <div class="account-summary">
                            <div class="summary-card">
                                <div class="summary-icon">💰</div>
                                <div class="summary-content">
                                    <div class="summary-number" id="account-balance">0</div>
                                    <div class="summary-label">Account Balance</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="account-details">
                            <h4>Payment Methods</h4>
                            <div id="payment-methods" class="payment-methods">
                                <!-- Payment methods will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cashbook Section -->
                <div class="cashbook-section">
                    <h3>📊 Cashbook & Expenses</h3>
                    <form id="cashbook-filter-form" class="cashbook-filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cashbook-type">Transaction Type</label>
                                <select id="cashbook-type" name="is_income">
                                    <option value="0" selected>Expenses (Debit)</option>
                                    <option value="1">Income (Credit)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cashbook-page">Page</label>
                                <input type="number" id="cashbook-page" name="page" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <label for="cashbook-item">Items per Page</label>
                                <select id="cashbook-item" name="item">
                                    <option value="10">10 transactions</option>
                                    <option value="25">25 transactions</option>
                                    <option value="50" selected>50 transactions</option>
                                    <option value="100">100 transactions</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cashbook-start-date">Start Date</label>
                                <input type="date" id="cashbook-start-date" name="start_date">
                            </div>
                            <div class="form-group">
                                <label for="cashbook-end-date">End Date</label>
                                <input type="date" id="cashbook-end-date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-info" id="fetch-cashbook-btn">
                                <span class="btn-icon">💰</span>
                                Fetch Cashbook Data
                            </button>
                        </div>
                    </form>
                    
                    <div id="cashbook-display" class="cashbook-display" style="display: none;">
                        <div class="cashbook-summary">
                            <div class="summary-card">
                                <div class="summary-icon">💰</div>
                                <div class="summary-content">
                                    <div class="summary-number" id="total-balance-cashbook">0</div>
                                    <div class="summary-label">Total Balance</div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon">📊</div>
                                <div class="summary-content">
                                    <div class="summary-number" id="total-transactions">0</div>
                                    <div class="summary-label">Total Transactions</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="cashbook-container">
                            <h4>Transaction History</h4>
                            <div id="cashbook-table" class="cashbook-table">
                                <div class="table-placeholder">
                                    <div class="placeholder-icon">💰</div>
                                    <p>Transactions will appear here...</p>
                                </div>
                            </div>
                            
                            <div id="cashbook-pagination" class="cashbook-pagination" style="display: none;">
                                <button type="button" class="btn btn-outline" id="prev-cashbook-btn">Previous</button>
                                <span id="cashbook-pagination-info" class="pagination-info"></span>
                                <button type="button" class="btn btn-outline" id="next-cashbook-btn">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Withdraw History Section -->
                <div class="withdraw-section">
                    <h3>💸 Withdraw History</h3>
                    <form id="withdraw-filter-form" class="withdraw-filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="withdraw-page">Page</label>
                                <input type="number" id="withdraw-page" name="page" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <label for="withdraw-item">Items per Page</label>
                                <select id="withdraw-item" name="item">
                                    <option value="10">10 transactions</option>
                                    <option value="25">25 transactions</option>
                                    <option value="50" selected>50 transactions</option>
                                    <option value="100">100 transactions</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-info" id="fetch-withdraw-btn">
                                <span class="btn-icon">💸</span>
                                Fetch Withdraw History
                            </button>
                        </div>
                    </form>
                    
                    <div id="withdraw-display" class="withdraw-display" style="display: none;">
                        <div class="withdraw-summary">
                            <div class="summary-card">
                                <div class="summary-icon">📊</div>
                                <div class="summary-content">
                                    <div class="summary-number" id="total-withdraw-transactions">0</div>
                                    <div class="summary-label">Total Withdrawals</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="withdraw-container">
                            <h4>Withdrawal History</h4>
                            <div id="withdraw-table" class="withdraw-table">
                                <div class="table-placeholder">
                                    <div class="placeholder-icon">💸</div>
                                    <p>Withdrawal transactions will appear here...</p>
                                </div>
                            </div>
                            
                            <div id="withdraw-pagination" class="withdraw-pagination" style="display: none;">
                                <button type="button" class="btn btn-outline" id="prev-withdraw-btn">Previous</button>
                                <span id="withdraw-pagination-info" class="pagination-info"></span>
                                <button type="button" class="btn btn-outline" id="next-withdraw-btn">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Settings Tab
     */
    private function display_settings_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>⚙️ Plugin Settings</h2>
                <p>Configure plugin behavior and preferences</p>
            </div>
            
            <div class="card-body">
                <div class="settings-section">
                    <h3>🔧 General Settings</h3>
                    <form id="settings-form" class="settings-form">
                        <div class="form-group">
                            <label for="price-markup">Price Markup Percentage</label>
                            <input type="number" id="price-markup" name="price_markup" value="20" min="0" max="100">
                            <small>Default markup applied to all imported products</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="auto-sync">Auto Sync Products</label>
                            <select id="auto-sync" name="auto_sync">
                                <option value="disabled">Disabled</option>
                                <option value="hourly">Every Hour</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                            <small>Automatically sync product data from the API</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="cache-duration">Cache Duration (minutes)</label>
                            <input type="number" id="cache-duration" name="cache_duration" value="5" min="1" max="60">
                            <small>How long to cache API responses</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="save-settings-btn">
                                <span class="btn-icon">💾</span>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="settings-section">
                    <h3>📊 Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-content">
                                <div class="stat-number" id="imported-products">0</div>
                                <div class="stat-label">Imported Products</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📂</div>
                            <div class="stat-content">
                                <div class="stat-number" id="imported-categories">0</div>
                                <div class="stat-label">Imported Categories</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🔄</div>
                            <div class="stat-content">
                                <div class="stat-number" id="last-sync">Never</div>
                                <div class="stat-label">Last Sync</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>🧹 Maintenance</h3>
                    <div class="maintenance-section">
                        <p>Use these tools to maintain your plugin and clear cached data.</p>
                        <div class="maintenance-actions">
                            <button type="button" class="btn btn-warning" id="clear-cache-btn">
                                <span class="btn-icon">🗑️</span>
                                Clear Cache
                            </button>
                            <button type="button" class="btn btn-danger" id="reset-plugin-btn">
                                <span class="btn-icon">🔄</span>
                                Reset Plugin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Product Importer Tab
     */
    private function display_importer_tab() {
        ?>
        <div class="dropshippingbd-card">
            <div class="card-header">
                <h2>🚀 Product Importer</h2>
                <p>Advanced bulk import system with progress tracking and duplicate prevention</p>
            </div>
            
            <div class="card-body">
                <!-- Import Configuration -->
                <div class="importer-section">
                    <h3>⚙️ Import Configuration</h3>
                    <form id="importer-config-form" class="importer-config-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="import-start-page">Start Page</label>
                                <input type="number" id="import-start-page" name="start_page" value="1" min="1">
                                <small>Page number to start importing from</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="import-end-page">End Page</label>
                                <input type="number" id="import-end-page" name="end_page" value="10" min="1">
                                <small>Page number to stop importing (leave empty for all)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="importer-per-page">Products Per Page</label>
                                <select id="importer-per-page" name="per_page">
                                    <option value="10">10 products</option>
                                    <option value="20" selected>20 products</option>
                                    <option value="50">50 products</option>
                                    <option value="100">100 products</option>
                                </select>
                                <small>Number of products to fetch per API call</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="import-delay">Import Delay (seconds)</label>
                                <input type="number" id="import-delay" name="delay" value="2" min="1" max="10">
                                <small>Delay between each product import to prevent server overload</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="import-category">Category Filter</label>
                                <select id="import-category" name="category">
                                    <option value="">All Categories</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                                <small>Filter products by category</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="import-status">Product Status</label>
                                <select id="import-status" name="status">
                                    <option value="active" selected>Active Only</option>
                                    <option value="inactive">Inactive Only</option>
                                    <option value="all">All Status</option>
                                </select>
                                <small>Import products with specific status</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="resume-from-last" name="resume_from_last">
                                    <span class="checkmark"></span>
                                    Resume from last import position
                                </label>
                                <small>Continue importing from where the last import stopped</small>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-info" id="fetch-products-btn">
                                <span class="btn-icon">🔍</span>
                                Fetch Products Info
                            </button>
                            <button type="button" class="btn btn-primary" id="start-import-btn" disabled>
                                <span class="btn-icon">🚀</span>
                                Start Bulk Import
                            </button>
                            <button type="button" class="btn btn-danger" id="delete-all-products-btn">
                                <span class="btn-icon">🗑️</span>
                                Delete All Imported Products
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Product Search -->
                <div class="importer-section">
                    <h3>🔍 Search & Import Products</h3>
                    <div class="search-form">
                        <div class="form-row">
                            <div class="form-group search-group">
                                <input type="text" id="product-search-input" placeholder="Search products by name, category, or keyword..." class="search-input">
                                <button type="button" id="search-products-btn" class="btn btn-primary">
                                    <span class="btn-icon">🔍</span>
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="search-results-section" style="display: none;">
                        <div class="search-results-header">
                            <h4>Search Results</h4>
                            <div class="search-pagination" id="search-pagination"></div>
                        </div>
                        <div class="search-results-grid" id="search-results-grid">
                            <!-- Search results will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <!-- Last Import Progress -->
                <div class="importer-section" id="last-import-progress-section" style="display: none;">
                    <h3>📍 Last Import Progress</h3>
                    <div class="progress-info">
                        <div class="progress-item">
                            <strong>Last Position:</strong> 
                            <span id="last-page-info">Page 0, Product 0</span>
                        </div>
                        <div class="progress-item">
                            <strong>Total Progress:</strong> 
                            <span id="last-total-info">0 of 0 products</span>
                        </div>
                        <div class="progress-item">
                            <strong>Last Updated:</strong> 
                            <span id="last-updated-info">Never</span>
                        </div>
                    </div>
                    <div class="progress-actions">
                        <button type="button" class="btn btn-warning" id="clear-progress-btn">
                            <span class="btn-icon">🗑️</span>
                            Clear Progress
                        </button>
                    </div>
                </div>
                
                <!-- Product Statistics -->
                <div class="importer-section" id="product-stats-section" style="display: none;">
                    <h3>📊 Product Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-products-found">0</div>
                                <div class="stat-label">Total Products Found</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📄</div>
                            <div class="stat-content">
                                <div class="stat-number" id="total-pages">0</div>
                                <div class="stat-label">Total Pages</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-content">
                                <div class="stat-number" id="already-imported">0</div>
                                <div class="stat-label">Already Imported</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🆕</div>
                            <div class="stat-content">
                                <div class="stat-number" id="new-products">0</div>
                                <div class="stat-label">New Products</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Import Progress -->
                <div class="importer-section" id="import-progress-section" style="display: none;">
                    <h3>📈 Import Progress</h3>
                    <div class="progress-container">
                        <div class="progress-header">
                            <div class="progress-info">
                                <span id="progress-text">Ready to start...</span>
                                <span id="progress-percentage">0%</span>
                            </div>
                            <div class="progress-controls">
                                <button type="button" class="btn btn-warning btn-sm" id="pause-import-btn" disabled>
                                    <span class="btn-icon">⏸️</span>
                                    Pause
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="stop-import-btn" disabled>
                                    <span class="btn-icon">⏹️</span>
                                    Stop
                                </button>
                            </div>
                        </div>
                        
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                        </div>
                        
                        <div class="progress-details">
                            <div class="progress-item">
                                <span class="progress-label">Current Page:</span>
                                <span class="progress-value" id="current-page">-</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">Current Product:</span>
                                <span class="progress-value" id="current-product">-</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">Imported:</span>
                                <span class="progress-value" id="imported-count">0</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">Skipped:</span>
                                <span class="progress-value" id="skipped-count">0</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">Errors:</span>
                                <span class="progress-value" id="error-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Import Log -->
                <div class="importer-section">
                    <h3>📝 Import Log</h3>
                    <div class="import-log-container">
                        <div class="log-controls">
                            <button type="button" class="btn btn-secondary btn-sm" id="clear-log-btn">
                                <span class="btn-icon">🗑️</span>
                                Clear Log
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="export-log-btn">
                                <span class="btn-icon">📤</span>
                                Export Log
                            </button>
                        </div>
                        <div id="import-log" class="import-log">
                            <div class="log-placeholder">
                                <div class="log-icon">📝</div>
                                <div class="log-text">Import log will appear here...</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Import History -->
                <div class="importer-section">
                    <h3>📚 Import History</h3>
                    <div class="import-history">
                        <div class="history-item">
                            <div class="history-date">No imports yet</div>
                            <div class="history-details">Start your first import to see history here</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}