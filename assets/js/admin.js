jQuery(document).ready(function($) {
    'use strict';
    
    console.log('DropshippingBD Admin JS loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('WordPress AJAX object:', typeof dropshippingbd_ajax !== 'undefined' ? dropshippingbd_ajax : 'NOT DEFINED');
    
    // Test if any buttons are clickable
    $(document).on('click', 'button', function() {
        console.log('Any button clicked:', $(this).attr('id'), $(this).text());
    });
    
    // Check for importer buttons after page load
    setTimeout(function() {
        console.log('🔍 Checking for importer buttons after page load...');
        console.log('🔍 Fetch button:', $('#fetch-products-btn').length);
        console.log('🔍 Start button:', $('#start-import-btn').length);
        console.log('🔍 Per page dropdown:', $('#importer-per-page').length);
        console.log('🔍 Per page dropdown value:', $('#importer-per-page').val());
        console.log('🔍 Per page dropdown options:', $('#importer-per-page option').map(function() { return $(this).val(); }).get());
    }, 2000);
    
    // Cache object for storing API responses
    const cache = {
        data: {},
        timestamps: {},
        duration: 5 * 60 * 1000, // 5 minutes in milliseconds
        
        get: function(key) {
            const now = Date.now();
            if (this.data[key] && this.timestamps[key] && (now - this.timestamps[key]) < this.duration) {
                return this.data[key];
            }
            return null;
        },
        
        set: function(key, value) {
            this.data[key] = value;
            this.timestamps[key] = Date.now();
        },
        
        clear: function() {
            this.data = {};
            this.timestamps = {};
        }
    };
    
    // Theme switcher functionality
    const themeToggle = $('#theme-toggle');
    const body = $('body');
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('dropshippingbd-theme') || 'light';
    body.attr('data-theme', currentTheme);
    updateThemeButton(currentTheme);
    
    themeToggle.on('click', function() {
        const currentTheme = body.attr('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        body.attr('data-theme', newTheme);
        localStorage.setItem('dropshippingbd-theme', newTheme);
        updateThemeButton(newTheme);
    });
    
    function updateThemeButton(theme) {
        const icon = themeToggle.find('.theme-icon');
        const text = themeToggle.find('.theme-text');
        
        if (theme === 'dark') {
            icon.text('☀️');
            text.text('Light Mode');
        } else {
            icon.text('🌙');
            text.text('Dark Mode');
        }
    }
    
    // Tab functionality
    $('.tab-link').on('click', function(e) {
        e.preventDefault();
        
        const targetTab = $(this).data('tab');
        console.log('Tab clicked:', targetTab);
        console.log('Tab element:', $(this));
        console.log('Tab text:', $(this).text());
        
        // Remove active class from all tabs and panels
        $('.tab-link').removeClass('active');
        $('.tab-panel').removeClass('active');
        
        // Add active class to clicked tab and corresponding panel
        $(this).addClass('active');
        const targetPanel = $(`#${targetTab}-tab`);
        
        if (targetPanel.length) {
            targetPanel.addClass('active');
            console.log('Tab switched to:', targetTab);
            
            // Add visual indicator for importer tab
            if (targetTab === 'importer') {
                console.log('IMPORTER TAB ACTIVATED!');
                targetPanel.css('border', '3px solid red');
                setTimeout(function() {
                    targetPanel.css('border', '');
                }, 3000);
            }
        } else {
            console.error('Target panel not found:', `#${targetTab}-tab`);
        }
        
        // Update URL hash
        window.location.hash = targetTab;
        
        // Load tab-specific data
        loadTabData(targetTab);
    });
    
    // Debug: Check if tab elements exist
    console.log('Tab links found:', $('.tab-link').length);
    console.log('Tab panels found:', $('.tab-panel').length);
    
    // Handle initial tab from URL hash
    const hash = window.location.hash.substring(1);
    console.log('URL hash:', hash);
    
    if (hash && $(`#${hash}-tab`).length) {
        console.log('Loading tab from hash:', hash);
        $('.tab-link').removeClass('active');
        $('.tab-panel').removeClass('active');
        $(`.tab-link[data-tab="${hash}"]`).addClass('active');
        $(`#${hash}-tab`).addClass('active');
        loadTabData(hash);
    } else {
        // Default to dashboard tab
        console.log('Loading default dashboard tab');
        loadTabData('dashboard');
    }
    
    // Load tab-specific data
    function loadTabData(tab) {
        console.log('loadTabData called with tab:', tab);
        switch(tab) {
            case 'dashboard':
                console.log('Loading dashboard data...');
                loadDashboardData();
                break;
            case 'products':
                console.log('Loading products data...');
                loadProductsData();
                break;
            case 'importer':
                console.log('Loading importer data...');
                loadImporterData();
                break;
            case 'orders':
                // Orders data loaded on demand
                break;
            case 'customers':
                // Customers data loaded on demand
                break;
            case 'finance':
                loadFinanceData();
                break;
            case 'settings':
                loadSettingsData();
                break;
        }
    }
    
    // Dashboard Tab Functions
    function loadDashboardData() {
        // Load cached statistics if available
        const cachedStats = cache.get('dashboard_stats');
        if (cachedStats) {
            displayDashboardStats(cachedStats);
            return;
        }
        
        // Load initial product stats
        loadProductStats();
    }
    
    function loadProductStats() {
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_total_products',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#available-products').text(response.data.total_products || 0);
                    $('#total-pages').text(response.data.total_pages || 0);
                }
            }
        });
    }
    
    // Dashboard Authentication
    $('#dashboard-auth-form').on('submit', function(e) {
        e.preventDefault();
        
        const phone = $('#dashboard-phone').val();
        const password = $('#dashboard-password').val();
        
        if (!phone || !password) {
            showMessage('Please enter both phone number and password.', 'error');
            return;
        }
        
        loginToDashboard(phone, password);
    });
    
    function loginToDashboard(phone, password) {
        const loginBtn = $('#login-btn');
        const originalText = loginBtn.html();
        
        loginBtn.prop('disabled', true);
        loginBtn.html('<span class="btn-icon">⏳</span>Logging in...');
        
        addLogEntry('Attempting to login to dashboard...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_set_dashboard_credentials',
                nonce: dropshippingbd_ajax.nonce,
                phone: phone,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage('Successfully logged in!', 'success');
                    
                    // Show auth status
                    $('#auth-status').show();
                    $('#login-btn').hide();
                    $('#logout-btn').show();
                    
                    // Load dashboard statistics
                    loadDashboardStatistics();
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Login failed. Please check your credentials.', 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred during login.', 'error');
            },
            complete: function() {
                loginBtn.prop('disabled', false);
                loginBtn.html(originalText);
            }
        });
    }
    
    function loadDashboardStatistics() {
        const cachedStats = cache.get('dashboard_stats');
        if (cachedStats) {
            displayDashboardStats(cachedStats);
            return;
        }
        
        const refreshBtn = $('#refresh-stats-btn');
        const originalText = refreshBtn.html();
        
        refreshBtn.prop('disabled', true);
        refreshBtn.html('<span class="btn-icon">⏳</span>Loading...');
        
        addLogEntry('Loading dashboard statistics...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_dashboard_data',
                nonce: dropshippingbd_ajax.nonce,
                start_date: '',
                end_date: ''
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    
                    // Cache the statistics
                    cache.set('dashboard_stats', response.data);
                    
                    // Display statistics
                    displayDashboardStats(response.data);
                    
                    // Show dashboard stats section
                    $('#dashboard-stats').show();
                    
                    showMessage('Dashboard statistics loaded successfully!', 'success');
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Failed to load dashboard statistics.', 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred while loading statistics.', 'error');
            },
            complete: function() {
                refreshBtn.prop('disabled', false);
                refreshBtn.html(originalText);
            }
        });
    }
    
    function displayDashboardStats(data) {
        if (!data) return;
        
        // Update statistics cards
        $('#total-balance').text(formatCurrency(data.total_balance || 0));
        $('#total-products').text(data.total_products || 0);
        $('#total-orders').text(data.total_orders || 0);
        $('#total-customers').text(data.total_customers || 0);
    }
    
    // Refresh statistics button
    $('#refresh-stats-btn').on('click', function() {
        cache.clear(); // Clear cache to force refresh
        loadDashboardStatistics();
    });
    
    // Logout button
    $('#logout-btn').on('click', function() {
        logoutFromDashboard();
    });
    
    function logoutFromDashboard() {
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_dashboard_logout',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Successfully logged out!', 'success');
                    
                    // Hide auth status and dashboard stats
                    $('#auth-status').hide();
                    $('#dashboard-stats').hide();
                    $('#login-btn').show();
                    $('#logout-btn').hide();
                    
                    // Clear form
                    $('#dashboard-auth-form')[0].reset();
                    
                    // Clear cache
                    cache.clear();
                }
            }
        });
    }
    
    // Products Tab Functions
    function loadProductsData() {
        loadProductStats();
    }
    
    // Product Importer Tab Functions
    function loadImporterData() {
        console.log('🚀 LOADING PRODUCT IMPORTER DATA - FUNCTION CALLED!');
        
        // Wait a bit for DOM to be ready
        setTimeout(function() {
            console.log('⏰ Setting up importer after timeout...');
            
            // Set up event handlers
            setupImporterHandlers();
            
            // Load categories for filter
            loadCategoriesForImporter();
            
            // Load import history
            loadImportHistory();
            
            // Load last import progress
            loadLastImportProgress();
            
            // Get imported products count
            getImportedProductsCount();
            
            console.log('✅ Product Importer data loading complete!');
        }, 100);
    }
    
    function setupImporterHandlers() {
        console.log('🔧 SETTING UP IMPORTER HANDLERS...');
        
        // Check if elements exist
        console.log('🔍 Fetch button exists:', $('#fetch-products-btn').length);
        console.log('🔍 Start button exists:', $('#start-import-btn').length);
        console.log('🔍 Pause button exists:', $('#pause-import-btn').length);
        console.log('🔍 Stop button exists:', $('#stop-import-btn').length);
        
        // Use event delegation to ensure handlers work even if elements are added later
        $(document).off('click', '#fetch-products-btn').on('click', '#fetch-products-btn', function(e) {
            e.preventDefault();
            console.log('🔍 Fetch products button clicked!');
            fetchProductsInfo();
        });
        
        $(document).off('click', '#start-import-btn').on('click', '#start-import-btn', function(e) {
            e.preventDefault();
            console.log('🚀 Start import button clicked!');
            startBulkImport();
        });
        
        $(document).off('click', '#pause-import-btn').on('click', '#pause-import-btn', function(e) {
            e.preventDefault();
            console.log('Pause import button clicked!');
            pauseImport();
        });
        
        $(document).off('click', '#stop-import-btn').on('click', '#stop-import-btn', function(e) {
            e.preventDefault();
            console.log('Stop import button clicked!');
            stopImport();
        });
        
        $(document).off('click', '#delete-all-products-btn').on('click', '#delete-all-products-btn', function(e) {
            e.preventDefault();
            console.log('🗑️ Delete all products button clicked!');
            deleteAllImportedProducts();
        });
        
        $(document).off('click', '#clear-progress-btn').on('click', '#clear-progress-btn', function(e) {
            e.preventDefault();
            console.log('🗑️ Clear progress button clicked!');
            clearImportProgress();
        });
        
        // Search functionality
        $(document).off('click', '#search-products-btn').on('click', '#search-products-btn', function(e) {
            e.preventDefault();
            console.log('🔍 Search products button clicked!');
            searchProducts();
        });
        
        $(document).off('keypress', '#product-search-input').on('keypress', '#product-search-input', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                console.log('🔍 Search triggered by Enter key!');
                searchProducts();
            }
        });
        
        // Product action buttons (Import/Update)
        $(document).off('click', '.product-action-btn').on('click', '.product-action-btn', function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            const productId = $(this).data('product-id');
            
            console.log('🔘 Product action clicked:', action, productId);
            
            if (action === 'importProduct') {
                importProduct(productId);
            } else if (action === 'updateProduct') {
                updateProduct(productId);
            }
        });
        
        // View product button
        $(document).off('click', '.view-product-btn').on('click', '.view-product-btn', function(e) {
            e.preventDefault();
            const wooProductId = $(this).data('woo-product-id');
            
            console.log('👁️ View product clicked:', wooProductId);
            
            if (wooProductId) {
                // Open WooCommerce product edit page in new tab
                const editUrl = `${dropshippingbd_ajax.admin_url}post.php?post=${wooProductId}&action=edit`;
                window.open(editUrl, '_blank');
            }
        });
        
        $(document).off('click', '#clear-log-btn').on('click', '#clear-log-btn', function(e) {
            e.preventDefault();
            console.log('Clear log button clicked!');
            clearImportLog();
        });
        
        $(document).off('click', '#export-log-btn').on('click', '#export-log-btn', function(e) {
            e.preventDefault();
            console.log('Export log button clicked!');
            exportImportLog();
        });
        
        // Form validation
        $(document).off('input', '#import-start-page, #import-end-page').on('input', '#import-start-page, #import-end-page', function() {
            validatePageRange();
        });
        
        // Debug per page dropdown changes
        $(document).off('change', '#importer-per-page').on('change', '#importer-per-page', function() {
            console.log('Per page dropdown changed to:', $(this).val());
        });
        
        console.log('✅ IMPORTER HANDLERS SETUP COMPLETE!');
        console.log('🎯 All buttons should now be clickable!');
    }
    
    function fetchProductsInfo() {
        console.log('fetchProductsInfo function called!');
        
        const fetchBtn = $('#fetch-products-btn');
        const originalText = fetchBtn.html();
        
        console.log('Fetch button found:', fetchBtn.length);
        
        fetchBtn.prop('disabled', true);
        fetchBtn.html('<span class="btn-icon">⏳</span>Fetching...');
        
        const config = {
            start_page: parseInt($('#import-start-page').val()) || 1,
            end_page: parseInt($('#import-end-page').val()) || 10,
            per_page: parseInt($('#importer-per-page').val()) || 20,
            category: $('#import-category').val() || '',
            status: $('#import-status').val() || 'active'
        };
        
        console.log('Config:', config);
        console.log('Per page element value:', $('#importer-per-page').val());
        console.log('Per page element found:', $('#importer-per-page').length);
        console.log('AJAX URL:', dropshippingbd_ajax.ajax_url);
        console.log('Nonce:', dropshippingbd_ajax.nonce);
        
        addImportLog('🔍 Fetching products information...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_fetch_products_info',
                nonce: dropshippingbd_ajax.nonce,
                config: config
            },
            success: function(response) {
                console.log('Products info response:', response);
                if (response.success) {
                    displayProductsInfo(response.data);
                    addImportLog(`✅ Found ${response.data.total_products} products across ${response.data.total_pages} pages`, 'success');
                } else {
                    addImportLog(`❌ Failed to fetch products: ${response.message}`, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching products info:', error);
                addImportLog(`❌ Error fetching products: ${error}`, 'error');
            },
            complete: function() {
                fetchBtn.prop('disabled', false);
                fetchBtn.html(originalText);
            }
        });
    }
    
    function displayProductsInfo(data) {
        // Update statistics
        $('#total-products-found').text(data.total_products || 0);
        $('#total-pages').text(data.total_pages || 0);
        $('#already-imported').text(data.already_imported || 0);
        $('#new-products').text(data.new_products || 0);
        
        // Show statistics section
        $('#product-stats-section').show();
        
        // Enable start import button if there are new products
        if (data.new_products > 0) {
            $('#start-import-btn').prop('disabled', false);
        } else {
            $('#start-import-btn').prop('disabled', true);
            addImportLog('⚠️ No new products to import', 'warning');
        }
    }
    
    function startBulkImport() {
        const startBtn = $('#start-import-btn');
        const originalText = startBtn.html();
        
        startBtn.prop('disabled', true);
        startBtn.html('<span class="btn-icon">⏳</span>Starting...');
        
        const config = {
            start_page: parseInt($('#import-start-page').val()) || 1,
            end_page: parseInt($('#import-end-page').val()) || 10,
            per_page: parseInt($('#importer-per-page').val()) || 20,
            delay: parseInt($('#import-delay').val()) || 2,
            category: $('#import-category').val() || '',
            status: $('#import-status').val() || 'active'
        };
        
        console.log('Bulk Import Config:', config);
        console.log('Bulk Import - Per page element value:', $('#importer-per-page').val());
        
        // Initialize import state
        window.importState = {
            isRunning: true,
            isPaused: false,
            currentPage: config.start_page,
            currentProduct: 0,
            imported: 0,
            skipped: 0,
            errors: 0,
            totalProducts: parseInt($('#total-products-found').text()) || 0,
            config: config
        };
        
        addImportLog('🚀 Starting bulk import...', 'info');
        
        // Show progress section
        $('#import-progress-section').show();
        
        // Enable pause/stop buttons
        $('#pause-import-btn').prop('disabled', false);
        $('#stop-import-btn').prop('disabled', false);
        
        // Start the import process
        processImportPage();
        
        startBtn.html(originalText);
    }
    
    function processImportPage() {
        if (!window.importState.isRunning || window.importState.isPaused) {
            return;
        }
        
        const state = window.importState;
        
        addImportLog(`📄 Processing page ${state.currentPage}...`, 'info');
        updateProgress();
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_products_page',
                nonce: dropshippingbd_ajax.nonce,
                page: state.currentPage,
                per_page: state.config.per_page,
                category: state.config.category,
                status: state.config.status
            },
            success: function(response) {
                if (response.success && response.data.products) {
                    processProductsOnPage(response.data.products);
                } else {
                    addImportLog(`❌ Failed to fetch page ${state.currentPage}: ${response.message}`, 'error');
                    state.errors++;
                    nextPageOrComplete();
                }
            },
            error: function(xhr, status, error) {
                addImportLog(`❌ Error fetching page ${state.currentPage}: ${error}`, 'error');
                state.errors++;
                nextPageOrComplete();
            }
        });
    }
    
    function processProductsOnPage(products) {
        const state = window.importState;
        state.currentProduct = 0;
        
        if (products.length === 0) {
            addImportLog(`📄 No products found on page ${state.currentPage}`, 'warning');
            nextPageOrComplete();
            return;
        }
        
        addImportLog(`📦 Found ${products.length} products on page ${state.currentPage}`, 'info');
        
        // Process products one by one
        processNextProduct(products);
    }
    
    function processNextProduct(products) {
        const state = window.importState;
        
        if (!state.isRunning || state.isPaused || state.currentProduct >= products.length) {
            if (state.currentProduct >= products.length) {
                nextPageOrComplete();
            }
            return;
        }
        
        const product = products[state.currentProduct];
        state.currentProduct++;
        
        addImportLog(`🔄 Importing product: ${product.name || 'Unknown'} (ID: ${product.id})`, 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_import_single_product',
                nonce: dropshippingbd_ajax.nonce,
                product: product,
                current_page: state.currentPage,
                current_index: state.currentProduct,
                total_pages: state.totalPages,
                total_products: state.totalProducts
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.is_duplicate) {
                        state.skipped++;
                        addImportLog(`⏭️ Skipped duplicate: ${product.name}`, 'warning');
                    } else {
                        state.imported++;
                        addImportLog(`✅ Imported: ${product.name}`, 'success');
                    }
                } else {
                    state.errors++;
                    addImportLog(`❌ Failed to import ${product.name}: ${response.message}`, 'error');
                }
                
                updateProgress();
                
                // Add delay to prevent server overload
                setTimeout(function() {
                    processNextProduct(products);
                }, state.config.delay * 1000);
            },
            error: function(xhr, status, error) {
                state.errors++;
                addImportLog(`❌ Error importing ${product.name}: ${error}`, 'error');
                updateProgress();
                
                setTimeout(function() {
                    processNextProduct(products);
                }, state.config.delay * 1000);
            }
        });
    }
    
    function nextPageOrComplete() {
        const state = window.importState;
        
        if (state.currentPage < state.config.end_page) {
            state.currentPage++;
            setTimeout(function() {
                processImportPage();
            }, state.config.delay * 1000);
        } else {
            completeImport();
        }
    }
    
    function completeImport() {
        const state = window.importState;
        state.isRunning = false;
        
        addImportLog('🎉 Import completed!', 'success');
        addImportLog(`📊 Final Stats: ${state.imported} imported, ${state.skipped} skipped, ${state.errors} errors`, 'info');
        
        // Disable control buttons
        $('#pause-import-btn').prop('disabled', true);
        $('#stop-import-btn').prop('disabled', true);
        $('#start-import-btn').prop('disabled', false);
        
        // Update progress
        updateProgress();
        
        // Save import history
        saveImportHistory(state);
        
        // Mark import progress as completed
        markImportProgressCompleted();
        
        // Refresh statistics
        setTimeout(function() {
            fetchProductsInfo();
        }, 2000);
    }
    
    function pauseImport() {
        const state = window.importState;
        state.isPaused = !state.isPaused;
        
        const pauseBtn = $('#pause-import-btn');
        if (state.isPaused) {
            pauseBtn.html('<span class="btn-icon">▶️</span>Resume');
            addImportLog('⏸️ Import paused', 'warning');
        } else {
            pauseBtn.html('<span class="btn-icon">⏸️</span>Pause');
            addImportLog('▶️ Import resumed', 'info');
            // Resume processing
            if (state.currentProduct > 0) {
                // Continue with current page
                setTimeout(function() {
                    processImportPage();
                }, 1000);
            }
        }
    }
    
    function stopImport() {
        const state = window.importState;
        state.isRunning = false;
        
        addImportLog('⏹️ Import stopped by user', 'warning');
        
        // Disable control buttons
        $('#pause-import-btn').prop('disabled', true);
        $('#stop-import-btn').prop('disabled', true);
        $('#start-import-btn').prop('disabled', false);
        
        // Reset pause button
        $('#pause-import-btn').html('<span class="btn-icon">⏸️</span>Pause');
        
        updateProgress();
    }
    
    function updateProgress() {
        const state = window.importState;
        if (!state) return;
        
        const total = state.totalProducts;
        const processed = state.imported + state.skipped + state.errors;
        const percentage = total > 0 ? Math.round((processed / total) * 100) : 0;
        
        // Update progress bar
        $('#progress-fill').css('width', percentage + '%');
        $('#progress-percentage').text(percentage + '%');
        
        // Update progress text
        if (state.isPaused) {
            $('#progress-text').text('Import paused...');
        } else if (state.isRunning) {
            $('#progress-text').text('Importing products...');
        } else {
            $('#progress-text').text('Import completed');
        }
        
        // Update details
        $('#current-page').text(state.currentPage);
        $('#current-product').text(state.currentProduct);
        $('#imported-count').text(state.imported);
        $('#skipped-count').text(state.skipped);
        $('#error-count').text(state.errors);
    }
    
    function addImportLog(message, type) {
        const timestamp = new Date().toLocaleTimeString();
        const logClass = type === 'error' ? 'log-error' : 
                       type === 'success' ? 'log-success' : 
                       type === 'warning' ? 'log-warning' : 'log-info';
        
        const logHtml = `
            <div class="log-entry ${logClass}">
                <span class="log-time">[${timestamp}]</span>
                <span class="log-message">${message}</span>
            </div>
        `;
        
        // Remove placeholder if it exists
        $('.log-placeholder').remove();
        
        // Add to log
        $('#import-log').prepend(logHtml);
        
        // Auto-scroll to top
        $('#import-log').scrollTop(0);
        
        // Limit log entries to prevent memory issues
        const logEntries = $('#import-log .log-entry');
        if (logEntries.length > 100) {
            logEntries.slice(100).remove();
        }
    }
    
    function clearImportLog() {
        $('#import-log').html(`
            <div class="log-placeholder">
                <div class="log-icon">📝</div>
                <div class="log-text">Import log cleared...</div>
            </div>
        `);
    }
    
    function exportImportLog() {
        const logEntries = $('#import-log .log-entry').map(function() {
            return $(this).text();
        }).get().reverse().join('\n');
        
        const blob = new Blob([logEntries], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `import-log-${new Date().toISOString().split('T')[0]}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    function loadCategoriesForImporter() {
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_categories',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    const categorySelect = $('#import-category');
                    categorySelect.find('option:not(:first)').remove();
                    
                    response.data.forEach(function(category) {
                        categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                    });
                }
            }
        });
    }
    
    function loadImportHistory() {
        // Load from localStorage for now
        const history = JSON.parse(localStorage.getItem('dropshippingbd_import_history') || '[]');
        
        if (history.length > 0) {
            const historyHtml = history.map(function(entry) {
                return `
                    <div class="history-item">
                        <div class="history-date">${entry.date}</div>
                        <div class="history-details">Imported: ${entry.imported}, Skipped: ${entry.skipped}, Errors: ${entry.errors}</div>
                    </div>
                `;
            }).join('');
            
            $('.import-history').html(historyHtml);
        }
    }
    
    function saveImportHistory(state) {
        const history = JSON.parse(localStorage.getItem('dropshippingbd_import_history') || '[]');
        
        history.unshift({
            date: new Date().toLocaleString(),
            imported: state.imported,
            skipped: state.skipped,
            errors: state.errors,
            total: state.totalProducts
        });
        
        // Keep only last 10 entries
        if (history.length > 10) {
            history.splice(10);
        }
        
        localStorage.setItem('dropshippingbd_import_history', JSON.stringify(history));
        loadImportHistory();
    }
    
    function validatePageRange() {
        const startPage = parseInt($('#import-start-page').val()) || 1;
        const endPage = parseInt($('#import-end-page').val()) || 10;
        
        if (endPage < startPage) {
            $('#import-end-page').val(startPage);
        }
    }
    
    // Finance Tab Functions
    function loadFinanceData() {
        // Set up account info button handler
        $('#fetch-account-btn').on('click', function() {
            fetchAccountInfo();
        });
    }
    
    // Settings Tab Functions
    function loadSettingsData() {
        console.log('Loading settings data...');
        
        // Load current settings
        loadCurrentSettings();
        
        // Load statistics
        loadSettingsStatistics();
        
        // Set up form handlers
        setupSettingsHandlers();
    }
    
    function loadCurrentSettings() {
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_settings',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Settings loaded:', response.data);
                    
                    // Populate form fields
                    $('#price-markup').val(response.data.price_markup || 20);
                    $('#auto-sync').val(response.data.auto_sync || 'disabled');
                    $('#cache-duration').val(response.data.cache_duration || 5);
                    
                    // Add visual feedback
                    showMessage('Settings loaded successfully!', 'success');
                } else {
                    console.error('Failed to load settings:', response.message);
                    showMessage('Failed to load settings: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading settings:', error);
                showMessage('An error occurred while loading settings.', 'error');
            }
        });
    }
    
    function loadSettingsStatistics() {
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_statistics',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Statistics loaded:', response.data);
                    
                    // Update statistics display
                    $('#imported-products').text(response.data.imported_products || 0);
                    $('#imported-categories').text(response.data.imported_categories || 0);
                    $('#last-sync').text(response.data.last_sync || 'Never');
                } else {
                    console.error('Failed to load statistics:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading statistics:', error);
            }
        });
    }
    
    function setupSettingsHandlers() {
        // Settings form submission
        $('#settings-form').on('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });
        
        // Clear cache button
        $('#clear-cache-btn').on('click', function() {
            clearCache();
        });
        
        // Reset plugin button
        $('#reset-plugin-btn').on('click', function() {
            resetPlugin();
        });
        
        // Auto-save on field changes (with debounce)
        let saveTimeout;
        $('#settings-form input, #settings-form select').on('change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                autoSaveSettings();
            }, 2000); // Auto-save after 2 seconds of inactivity
        });
        
        // Real-time validation
        $('#price-markup').on('input', function() {
            validatePriceMarkup();
        });
        
        $('#cache-duration').on('input', function() {
            validateCacheDuration();
        });
    }
    
    function saveSettings() {
        const saveBtn = $('#save-settings-btn');
        const originalText = saveBtn.html();
        
        // Validate form
        if (!validateSettingsForm()) {
            return;
        }
        
        saveBtn.prop('disabled', true);
        saveBtn.html('<span class="btn-icon">⏳</span>Saving...');
        
        const settings = {
            price_markup: parseInt($('#price-markup').val()),
            auto_sync: $('#auto-sync').val(),
            cache_duration: parseInt($('#cache-duration').val())
        };
        
        console.log('Saving settings:', settings);
        addLogEntry('Saving settings...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_save_settings',
                nonce: dropshippingbd_ajax.nonce,
                settings: settings
            },
            success: function(response) {
                console.log('Save settings response:', response);
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage('Settings saved successfully!', 'success');
                    
                    // Update cache with new settings
                    cache.set('settings', settings);
                    
                    // Refresh statistics after settings change
                    setTimeout(function() {
                        loadSettingsStatistics();
                    }, 1000);
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Failed to save settings: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error saving settings:', error, xhr.responseText);
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred while saving settings.', 'error');
            },
            complete: function() {
                saveBtn.prop('disabled', false);
                saveBtn.html(originalText);
            }
        });
    }
    
    function autoSaveSettings() {
        console.log('Auto-saving settings...');
        
        const settings = {
            price_markup: parseInt($('#price-markup').val()),
            auto_sync: $('#auto-sync').val(),
            cache_duration: parseInt($('#cache-duration').val())
        };
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_save_settings',
                nonce: dropshippingbd_ajax.nonce,
                settings: settings
            },
            success: function(response) {
                if (response.success) {
                    console.log('Settings auto-saved successfully');
                    // Show subtle notification
                    showAutoSaveNotification();
                }
            },
            error: function(xhr, status, error) {
                console.error('Auto-save failed:', error);
            }
        });
    }
    
    function clearCache() {
        const clearBtn = $('#clear-cache-btn');
        const originalText = clearBtn.html();
        
        clearBtn.prop('disabled', true);
        clearBtn.html('<span class="btn-icon">⏳</span>Clearing...');
        
        addLogEntry('Clearing cache...', 'info');
        
        // Clear local cache
        cache.clear();
        
        console.log('Clearing WordPress cache...');
        
        // Clear WordPress transients
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_clear_cache',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                console.log('Clear cache response:', response);
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage('Cache cleared successfully!', 'success');
                    
                    // Refresh statistics
                    loadSettingsStatistics();
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Failed to clear cache: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error clearing cache:', error, xhr.responseText);
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred while clearing cache.', 'error');
            },
            complete: function() {
                clearBtn.prop('disabled', false);
                clearBtn.html(originalText);
            }
        });
    }
    
    function resetPlugin() {
        if (!confirm('Are you sure you want to reset the plugin? This will delete all imported products and settings. This action cannot be undone.')) {
            return;
        }
        
        const resetBtn = $('#reset-plugin-btn');
        const originalText = resetBtn.html();
        
        resetBtn.prop('disabled', true);
        resetBtn.html('<span class="btn-icon">⏳</span>Resetting...');
        
        addLogEntry('Resetting plugin...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_reset_plugin',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage('Plugin reset successfully!', 'success');
                    
                    // Reset form to defaults
                    $('#price-markup').val(20);
                    $('#auto-sync').val('disabled');
                    $('#cache-duration').val(5);
                    
                    // Clear local cache
                    cache.clear();
                    
                    // Refresh statistics
                    setTimeout(function() {
                        loadSettingsStatistics();
                    }, 1000);
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Failed to reset plugin: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred while resetting plugin.', 'error');
            },
            complete: function() {
                resetBtn.prop('disabled', false);
                resetBtn.html(originalText);
            }
        });
    }
    
    function validateSettingsForm() {
        let isValid = true;
        
        // Validate price markup
        const priceMarkup = parseInt($('#price-markup').val());
        if (isNaN(priceMarkup) || priceMarkup < 0 || priceMarkup > 100) {
            showFieldError('#price-markup', 'Price markup must be between 0 and 100');
            isValid = false;
        } else {
            clearFieldError('#price-markup');
        }
        
        // Validate cache duration
        const cacheDuration = parseInt($('#cache-duration').val());
        if (isNaN(cacheDuration) || cacheDuration < 1 || cacheDuration > 60) {
            showFieldError('#cache-duration', 'Cache duration must be between 1 and 60 minutes');
            isValid = false;
        } else {
            clearFieldError('#cache-duration');
        }
        
        return isValid;
    }
    
    function validatePriceMarkup() {
        const priceMarkup = parseInt($('#price-markup').val());
        if (isNaN(priceMarkup) || priceMarkup < 0 || priceMarkup > 100) {
            showFieldError('#price-markup', 'Must be between 0 and 100');
        } else {
            clearFieldError('#price-markup');
        }
    }
    
    function validateCacheDuration() {
        const cacheDuration = parseInt($('#cache-duration').val());
        if (isNaN(cacheDuration) || cacheDuration < 1 || cacheDuration > 60) {
            showFieldError('#cache-duration', 'Must be between 1 and 60 minutes');
        } else {
            clearFieldError('#cache-duration');
        }
    }
    
    function showFieldError(fieldId, message) {
        const field = $(fieldId);
        field.addClass('field-error');
        
        // Remove existing error message
        field.siblings('.field-error-message').remove();
        
        // Add error message
        field.after(`<div class="field-error-message">${message}</div>`);
    }
    
    function clearFieldError(fieldId) {
        const field = $(fieldId);
        field.removeClass('field-error');
        field.siblings('.field-error-message').remove();
    }
    
    function showAutoSaveNotification() {
        // Create subtle auto-save notification
        const notification = $(`
            <div class="auto-save-notification">
                <span class="notification-icon">💾</span>
                <span class="notification-text">Settings auto-saved</span>
            </div>
        `);
        
        $('.dropshippingbd-admin').append(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                notification.remove();
            });
        }, 3000);
    }
    
    function fetchAccountInfo() {
        const fetchBtn = $('#fetch-account-btn');
        const originalText = fetchBtn.html();
        
        fetchBtn.prop('disabled', true);
        fetchBtn.html('<span class="btn-icon">⏳</span>Fetching...');
        
        addLogEntry('Fetching account information...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_account_info',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    displayAccountInfo(response.data);
                    showMessage('Account information loaded successfully!', 'success');
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Failed to load account information.', 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred while loading account information.', 'error');
            },
            complete: function() {
                fetchBtn.prop('disabled', false);
                fetchBtn.html(originalText);
            }
        });
    }
    
    function displayAccountInfo(data) {
        if (!data) return;
        
        // Display account balance
        $('#account-balance').text(formatCurrency(data.wallet_value || 0));
        
        // Display payment methods
        const paymentMethods = data.reseller_payment_method;
        let paymentHtml = '';
        
        if (paymentMethods) {
            paymentHtml += '<div class="payment-method-list">';
            
            if (paymentMethods.bkash_no) {
                paymentHtml += `
                    <div class="payment-method-item">
                        <div class="payment-icon">📱</div>
                        <div class="payment-details">
                            <div class="payment-name">bKash</div>
                            <div class="payment-number">${paymentMethods.bkash_no}</div>
                        </div>
                    </div>
                `;
            }
            
            if (paymentMethods.nagad_no) {
                paymentHtml += `
                    <div class="payment-method-item">
                        <div class="payment-icon">💳</div>
                        <div class="payment-details">
                            <div class="payment-name">Nagad</div>
                            <div class="payment-number">${paymentMethods.nagad_no}</div>
                        </div>
                    </div>
                `;
            }
            
            if (paymentMethods.rocket_no) {
                paymentHtml += `
                    <div class="payment-method-item">
                        <div class="payment-icon">🚀</div>
                        <div class="payment-details">
                            <div class="payment-name">Rocket</div>
                            <div class="payment-number">${paymentMethods.rocket_no}</div>
                        </div>
                    </div>
                `;
            }
            
            if (paymentMethods.bank_account_no) {
                paymentHtml += `
                    <div class="payment-method-item">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-details">
                            <div class="payment-name">Bank Account</div>
                            <div class="payment-number">${paymentMethods.bank_account_no}</div>
                            ${paymentMethods.bank_account_name ? `<div class="payment-holder">${paymentMethods.bank_account_name}</div>` : ''}
                            ${paymentMethods.bank_branch_name ? `<div class="payment-branch">${paymentMethods.bank_branch_name}</div>` : ''}
                        </div>
                    </div>
                `;
            }
            
            if (!paymentMethods.bkash_no && !paymentMethods.nagad_no && !paymentMethods.rocket_no && !paymentMethods.bank_account_no) {
                paymentHtml += '<div class="no-payment-methods">No payment methods configured</div>';
            }
            
            paymentHtml += '</div>';
        } else {
            paymentHtml = '<div class="no-payment-methods">No payment methods available</div>';
        }
        
        $('#payment-methods').html(paymentHtml);
        
        // Show the account display section
        $('#account-display').show();
    }
    
    // Bulk import form
    $('#bulk-import-form').on('submit', function(e) {
        e.preventDefault();
        
        const page = $('#import-page').val();
        const perPage = $('#import-per-page').val();
        
        if (!page || !perPage) {
            showMessage('Please fill in all required fields.', 'error');
            return;
        }
        
        importProducts(page, perPage);
    });
    
    function importProducts(page, perPage) {
        const importBtn = $('#bulk-import-btn');
        const originalText = importBtn.html();
        
        importBtn.prop('disabled', true);
        importBtn.html('<span class="btn-icon">⏳</span>Importing...');
        
        addLogEntry(`Starting bulk import from page ${page}...`, 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_import_products',
                nonce: dropshippingbd_ajax.nonce,
                page: page,
                per_page: perPage
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage(`Successfully imported ${response.data.imported_count} products!`, 'success');
                    
                    // Refresh product stats
                    loadProductStats();
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Import failed. Check the log for details.', 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred during import.', 'error');
            },
            complete: function() {
                importBtn.prop('disabled', false);
                importBtn.html(originalText);
            }
        });
    }
    
    // Import categories button
    $('#import-categories-btn').on('click', function() {
        importCategories();
    });
    
    function importCategories() {
        const importBtn = $('#import-categories-btn');
        const originalText = importBtn.html();
        
        importBtn.prop('disabled', true);
        importBtn.html('<span class="btn-icon">⏳</span>Importing...');
        
        addLogEntry('Starting category import...', 'info');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_import_categories',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(`✅ ${response.message}`, 'success');
                    showMessage(`Successfully imported ${response.data.imported_count} categories!`, 'success');
                } else {
                    addLogEntry(`❌ ${response.message}`, 'error');
                    showMessage('Category import failed. Check the log for details.', 'error');
                }
            },
            error: function(xhr, status, error) {
                addLogEntry(`❌ AJAX Error: ${error}`, 'error');
                showMessage('An error occurred during category import.', 'error');
            },
            complete: function() {
                importBtn.prop('disabled', false);
                importBtn.html(originalText);
            }
        });
    }
    
    // Utility Functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-BD', {
            style: 'currency',
            currency: 'BDT',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(amount);
    }
    
    function showMessage(message, type) {
        const messageClass = type === 'error' ? 'notice-error' : 'notice-success';
        const messageHtml = `
            <div class="notice ${messageClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        $('.dropshippingbd-admin').prepend(messageHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.notice').fadeOut();
        }, 5000);
        
        // Handle dismiss button
        $('.notice-dismiss').on('click', function() {
            $(this).parent().fadeOut();
        });
    }
    
    function addLogEntry(message, type) {
        const timestamp = new Date().toLocaleTimeString();
        const logClass = type === 'error' ? 'log-error' : type === 'success' ? 'log-success' : 'log-info';
        
        const logHtml = `
            <div class="log-entry ${logClass}">
                <span class="log-time">[${timestamp}]</span>
                <span class="log-message">${message}</span>
            </div>
        `;
        
        // Add to log if log container exists
        if ($('#log-container').length) {
            $('#log-container').prepend(logHtml);
        }
        
        // Also log to console for debugging
        console.log(`[${timestamp}] ${message}`);
    }
    
    // ===== NEW IMPORTER FEATURES =====
    
    /**
     * Load last import progress
     */
    function loadLastImportProgress() {
        console.log('📍 Loading last import progress...');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_last_import_progress',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                console.log('📍 Last import progress response:', response);
                
                if (response.success && response.data) {
                    displayLastImportProgress(response.data);
                } else {
                    console.log('📍 No last import progress found');
                    $('#last-import-progress-section').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('📍 Error loading last import progress:', error);
            }
        });
    }
    
    /**
     * Display last import progress
     */
    function displayLastImportProgress(progress) {
        console.log('📍 Displaying last import progress:', progress);
        
        $('#last-page-info').text(`Page ${progress.current_page}, Product ${progress.current_index}`);
        $('#last-total-info').text(`${progress.current_index} of ${progress.total_products} products`);
        $('#last-updated-info').text(progress.last_updated || 'Unknown');
        
        $('#last-import-progress-section').show();
        
        // Pre-fill form with last position if resume is checked
        if ($('#resume-from-last').is(':checked')) {
            $('#import-start-page').val(progress.current_page);
            console.log('📍 Pre-filled start page with last position:', progress.current_page);
        }
    }
    
    /**
     * Delete all imported products
     */
    function deleteAllImportedProducts() {
        if (!confirm('⚠️ Are you sure you want to delete ALL imported products? This action cannot be undone!')) {
            return;
        }
        
        console.log('🗑️ Starting deletion of all imported products...');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_delete_all_imported_products',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                console.log('🗑️ Delete all products response:', response);
                
                if (response.success) {
                    addImportLog(`✅ Deleted ${response.deleted_count} products successfully`, 'success');
                    
                    // Clear progress and stats
                    $('#product-stats-section').hide();
                    $('#last-import-progress-section').hide();
                    
                    // Reset form
                    $('#import-start-page').val(1);
                    $('#import-end-page').val(10);
                    $('#start-import-btn').prop('disabled', true);
                    
                    alert(`✅ Successfully deleted ${response.deleted_count} products!`);
                } else {
                    addImportLog(`❌ Failed to delete products: ${response.message}`, 'error');
                    alert(`❌ Failed to delete products: ${response.message}`);
                }
            },
            error: function(xhr, status, error) {
                console.error('🗑️ Error deleting products:', error);
                addImportLog(`❌ Error deleting products: ${error}`, 'error');
                alert(`❌ Error deleting products: ${error}`);
            }
        });
    }
    
    /**
     * Clear import progress
     */
    function clearImportProgress() {
        if (!confirm('⚠️ Are you sure you want to clear the import progress? This will reset the last import position.')) {
            return;
        }
        
        console.log('🗑️ Clearing import progress...');
        
        // Clear the progress display
        $('#last-import-progress-section').hide();
        
        // Reset form to default values
        $('#import-start-page').val(1);
        $('#import-end-page').val(10);
        $('#resume-from-last').prop('checked', false);
        
        addImportLog('✅ Import progress cleared', 'success');
        
        // Note: We don't need to call an AJAX endpoint to clear progress
        // as it will be automatically cleared when a new import starts
    }
    
    /**
     * Get imported products count
     */
    function getImportedProductsCount() {
        console.log('📊 Getting imported products count...');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_get_imported_count',
                nonce: dropshippingbd_ajax.nonce
            },
            success: function(response) {
                console.log('📊 Imported count response:', response);
                
                if (response.success) {
                    // Update the already imported count in stats
                    $('#already-imported').text(response.count);
                    console.log('📊 Updated imported count:', response.count);
                }
            },
            error: function(xhr, status, error) {
                console.error('📊 Error getting imported count:', error);
            }
        });
    }
    
    /**
     * Mark import progress as completed
     */
    function markImportProgressCompleted() {
        console.log('✅ Marking import progress as completed...');
        
        // This will be handled by the backend when the import completes
        // We don't need to make an AJAX call here since the progress
        // will be automatically marked as completed when the import finishes
    }
    
    // ===== SEARCH FUNCTIONALITY =====
    
    // Category mapping for displaying proper category names
    const categoryMap = {
        1: "Men's Fashion",
        2: "Women's Fashion", 
        3: "Home & Lifestyle",
        4: "Gadgets",
        5: "Winter",
        6: "Year Closing Offer",
        7: "Other's",
        9: "Watch",
        10: "Islamic Item",
        11: "Kids Zone",
        12: "Customize Item",
        13: "Customize & Gift",
        14: "Rain item",
        15: "Gadgets & Electronics",
        16: "OFFER"
    };
    
    /**
     * Get category name from ID or return the provided name
     */
    function getCategoryName(categoryId, categoryName) {
        if (categoryId && categoryMap[categoryId]) {
            return categoryMap[categoryId];
        }
        return categoryName || 'No Category';
    }
    
    /**
     * Search products
     */
    function searchProducts() {
        const keyword = $('#product-search-input').val().trim();
        
        if (!keyword) {
            alert('Please enter a search keyword');
            return;
        }
        
        console.log('🔍 Searching for products with keyword:', keyword);
        
        $('#search-products-btn').prop('disabled', true).html('<span class="btn-icon">⏳</span>Searching...');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_search_products',
                nonce: dropshippingbd_ajax.nonce,
                keyword: keyword,
                page: 1,
                per_page: 30
            },
            success: function(response) {
                console.log('🔍 Search response:', response);
                
                if (response.success) {
                    displaySearchResults(response.data, response.imported_products);
                } else {
                    alert('Search failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('🔍 Search error:', error);
                alert('Search failed: ' + error);
            },
            complete: function() {
                $('#search-products-btn').prop('disabled', false).html('<span class="btn-icon">🔍</span>Search');
            }
        });
    }
    
    /**
     * Display search results
     */
    function displaySearchResults(data, importedProducts) {
        console.log('🔍 Displaying search results:', data);
        
        // The response structure is data.products.data, not data.data
        const products = data.products?.data || [];
        
        if (products.length === 0) {
            $('#search-results-grid').html('<div class="no-results">No products found matching your search.</div>');
        } else {
            let html = '';
            
            products.forEach(function(product) {
                const isImported = importedProducts[product.id] ? true : false;
                const importedBadge = isImported ? '<span class="imported-badge">✅ Imported</span>' : '';
                const buttonText = isImported ? 'Update' : 'Import';
                const buttonClass = isImported ? 'btn-warning' : 'btn-success';
                const buttonAction = isImported ? 'updateProduct' : 'importProduct';
                const wooProductId = isImported ? importedProducts[product.id] : null;
                
                // Build image URL with proper prefix
                let imageUrl = '';
                if (product.thumbnail_img) {
                    // If image already has full URL, use it as is
                    if (product.thumbnail_img.startsWith('http')) {
                        imageUrl = product.thumbnail_img;
                    } else {
                        // Add the base URL prefix
                        imageUrl = `https://dropshipping.com.bd/public/storage/${product.thumbnail_img}`;
                    }
                }
                
                // Escape the product data for safe HTML attribute usage
                const escapedProductData = JSON.stringify(product).replace(/"/g, '&quot;');
                
                html += `
                    <div class="product-card" data-product-id="${product.id}" data-product-data="${escapedProductData}">
                        <div class="product-image">
                            ${imageUrl ? `<img src="${imageUrl}" alt="${product.name}" loading="lazy">` : '<div class="no-image">No Image</div>'}
                        </div>
                        <div class="product-info">
                            <h4 class="product-name">${product.name || 'Unknown Product'}</h4>
                            <div class="product-pricing">
                                ${product.price ? `<div class="regular-price">Regular: ৳${product.price}</div>` : ''}
                                ${product.sale_price && product.sale_price > 0 ? `<div class="sale-price">Sale: ৳${product.sale_price}</div>` : ''}
                                ${product.price && product.sale_price && product.sale_price > 0 ? 
                                    `<div class="profit-amount">Profit: ৳${(product.price - product.sale_price).toFixed(2)}</div>` : 
                                    (product.price ? `<div class="profit-amount">Profit: ৳${product.price}</div>` : '')
                                }
                            </div>
                            <div class="product-category">${getCategoryName(product.category_id, product.category)}</div>
                            <div class="product-code">Code: ${product.product_code || 'N/A'}</div>
                            ${importedBadge}
                        </div>
                        <div class="product-actions">
                            <button type="button" class="btn ${buttonClass} btn-sm product-action-btn" data-action="${buttonAction}" data-product-id="${product.id}">
                                ${buttonText}
                            </button>
                            ${isImported && wooProductId ? 
                                `<button type="button" class="btn btn-info btn-sm view-product-btn" data-woo-product-id="${wooProductId}">
                                    <span class="btn-icon">👁️</span>View Product
                                </button>` : ''
                            }
                        </div>
                    </div>
                `;
            });
            
            $('#search-results-grid').html(html);
        }
        
        $('#search-results-section').show();
        
        // Add pagination if available
        if (data.products && data.products.last_page > 1) {
            displaySearchPagination(data.products);
        }
    }
    
    /**
     * Display search pagination
     */
    function displaySearchPagination(pagination) {
        let html = '<div class="pagination">';
        
        if (pagination.current_page > 1) {
            html += `<button type="button" class="btn btn-sm" onclick="searchProductsPage(${pagination.current_page - 1})">Previous</button>`;
        }
        
        html += `<span class="page-info">Page ${pagination.current_page} of ${pagination.last_page}</span>`;
        
        if (pagination.current_page < pagination.last_page) {
            html += `<button type="button" class="btn btn-sm" onclick="searchProductsPage(${pagination.current_page + 1})">Next</button>`;
        }
        
        html += '</div>';
        $('#search-pagination').html(html);
    }
    
    /**
     * Search products for specific page
     */
    function searchProductsPage(page) {
        const keyword = $('#product-search-input').val().trim();
        
        if (!keyword) {
            return;
        }
        
        console.log('🔍 Searching page:', page);
        
        $('#search-products-btn').prop('disabled', true).html('<span class="btn-icon">⏳</span>Searching...');
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_search_products',
                nonce: dropshippingbd_ajax.nonce,
                keyword: keyword,
                page: page,
                per_page: 30
            },
            success: function(response) {
                console.log('🔍 Search page response:', response);
                
                if (response.success) {
                    displaySearchResults(response.data, response.imported_products);
                } else {
                    alert('Search failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('🔍 Search page error:', error);
                alert('Search failed: ' + error);
            },
            complete: function() {
                $('#search-products-btn').prop('disabled', false).html('<span class="btn-icon">🔍</span>Search');
            }
        });
    }
    
    /**
     * Import product from search results
     */
    function importProduct(productId) {
        console.log('📦 Importing product:', productId);
        
        const productCard = $(`.product-card[data-product-id="${productId}"]`);
        const productName = productCard.find('.product-name').text();
        
        if (!confirm(`Import "${productName}"?`)) {
            return;
        }
        
        const productData = productCard.data('product-data');
        
        if (!productData) {
            alert('Product data not found. Please search again.');
            return;
        }
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_import_single_product',
                nonce: dropshippingbd_ajax.nonce,
                product: productData
            },
            success: function(response) {
                console.log('📦 Import response:', response);
                
                if (response.success) {
                    alert('Product imported successfully!');
                    updateProductCardButton(productId, true);
                } else {
                    alert('Import failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('📦 Import error:', error);
                alert('Import failed: ' + error);
            }
        });
    }
    
    /**
     * Update existing product
     */
    function updateProduct(productId) {
        console.log('🔄 Updating product:', productId);
        
        const productCard = $(`.product-card[data-product-id="${productId}"]`);
        const productName = productCard.find('.product-name').text();
        
        if (!confirm(`Update "${productName}" with latest information?`)) {
            return;
        }
        
        const productData = productCard.data('product-data');
        
        if (!productData) {
            alert('Product data not found. Please search again.');
            return;
        }
        
        $.ajax({
            url: dropshippingbd_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dropshippingbd_update_product',
                nonce: dropshippingbd_ajax.nonce,
                product: productData
            },
            success: function(response) {
                console.log('🔄 Update response:', response);
                
                if (response.success) {
                    alert('Product updated successfully!');
                } else {
                    alert('Update failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('🔄 Update error:', error);
                alert('Update failed: ' + error);
            }
        });
    }
    
    /**
     * Update product card button after import
     */
    function updateProductCardButton(productId, isImported) {
        const productCard = $(`.product-card[data-product-id="${productId}"]`);
        const button = productCard.find('.product-actions button');
        
        if (isImported) {
            button.removeClass('btn-success').addClass('btn-warning').text('Update');
            button.attr('data-action', 'updateProduct');
            
            if (!productCard.find('.imported-badge').length) {
                productCard.find('.product-info').append('<span class="imported-badge">✅ Imported</span>');
            }
        }
    }
});
