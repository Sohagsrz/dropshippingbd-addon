<?php
/**
 * Product Importer for WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

class DropshippingBD_Product_Importer {
    
    private $api_client;
    private $markup_percentage = 20; // 20% markup
    
    public function __construct() {
        $this->api_client = new DropshippingBD_API_Client();
    }
    
    /**
     * Import products from API
     *
     * @param int $page Page number
     * @param int $per_page Products per page
     * @return array
     */
    public function import_products($page = 1, $per_page = 50) {
        $result = array(
            'success' => false,
            'imported' => 0,
            'skipped' => 0,
            'errors' => array(),
            'message' => ''
        );
        
        // Fetch products from API
        $api_data = $this->api_client->get_products($page, $per_page);
        
        if (is_wp_error($api_data)) {
            $result['errors'][] = $api_data->get_error_message();
            $result['message'] = 'Failed to fetch products from API';
            return $result;
        }
        
        if (!isset($api_data['products']) || !is_array($api_data['products'])) {
            $result['message'] = 'No products found in API response';
            return $result;
        }
        
        $products = $api_data['products'];
        
        foreach ($products as $product_data) {
            try {
                $import_result = $this->import_single_product($product_data);
                
                if ($import_result['success']) {
                    $result['imported']++;
                } else {
                    $result['skipped']++;
                    $result['errors'][] = $import_result['message'];
                }
            } catch (Exception $e) {
                $result['skipped']++;
                $result['errors'][] = 'Error importing product ID ' . $product_data['id'] . ': ' . $e->getMessage();
            }
        }
        
        $result['success'] = true;
        $result['message'] = sprintf(
            'Import completed. Imported: %d, Skipped: %d',
            $result['imported'],
            $result['skipped']
        );
        
        return $result;
    }
    
    /**
     * Import single product
     *
     * @param array $product_data Product data from API
     * @return array
     */
    public function import_single_product($product_data) {
        error_log('DropshippingBD Product Importer: Starting import for product ID: ' . ($product_data['id'] ?? 'N/A'));
        
        // Check if WooCommerce is active
        if (!class_exists('WC_Product_Simple') || !class_exists('WC_Product_Variable')) {
            error_log('DropshippingBD Product Importer: WooCommerce is not active or missing required classes');
            return array(
                'success' => false,
                'message' => 'WooCommerce is not active or missing required classes',
                'product_id' => null
            );
        }
        
        $result = array(
            'success' => false,
            'message' => '',
            'product_id' => null
        );
        
        // Check if product already exists
        $existing_product_id = $this->get_existing_product_id($product_data['id']);
        
        if ($existing_product_id) {
            error_log('DropshippingBD Product Importer: Product already exists - WooCommerce ID: ' . $existing_product_id);
            $result['message'] = 'Product already exists (ID: ' . $existing_product_id . ')';
            return $result;
        }
        
        error_log('DropshippingBD Product Importer: Product not found, proceeding with creation...');
        
        // Validate required product data
        if (empty($product_data['name'])) {
            error_log('DropshippingBD Product Importer: Product name is missing');
            $result['message'] = 'Product name is required';
            return $result;
        }
        
        if (empty($product_data['price'])) {
            error_log('DropshippingBD Product Importer: Product price is missing');
            $result['message'] = 'Product price is required';
            return $result;
        }
        
        try {
            // Determine product type based on variants
            $has_variants = !empty($product_data['product_variants']);
            
            if ($has_variants) {
                error_log('DropshippingBD Product Importer: Creating Variable Product (has variants)');
                $product = new WC_Product_Variable();
            } else {
                error_log('DropshippingBD Product Importer: Creating Simple Product (no variants)');
        $product = new WC_Product_Simple();
            }
        
        // Set basic product data
        error_log('DropshippingBD Product Importer: Setting product name: ' . $product_data['name']);
        $product->set_name($product_data['name']);
        
        if (!empty($product_data['details'])) {
        $product->set_description($product_data['details']);
        $product->set_short_description($this->truncate_description($product_data['details']));
        }
        
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_featured(false);
        
        // Set prices with 20% markup (only for simple products)
        if (!$has_variants) {
        $regular_price = $this->apply_markup($product_data['price']);
            error_log('DropshippingBD Product Importer: Setting regular price: ' . $regular_price);
        $product->set_regular_price($regular_price);
        
        if (isset($product_data['sale_price']) && $product_data['sale_price'] > 0) {
            $sale_price = $this->apply_markup($product_data['sale_price']);
                error_log('DropshippingBD Product Importer: Setting sale price: ' . $sale_price);
            $product->set_sale_price($sale_price);
            }
        }
        
        // Set SKU with validation
        try {
            $sku = $this->generate_valid_sku($product_data);
            error_log('DropshippingBD Product Importer: Setting SKU: ' . $sku);
            $product->set_sku($sku);
        } catch (Exception $e) {
            error_log('DropshippingBD Product Importer: SKU error - ' . $e->getMessage());
            // Generate a fallback SKU
            $sku = 'DSBD-' . $product_data['id'] . '-' . time();
            $product->set_sku($sku);
        }
        
        // Set stock status
        $product->set_stock_status('instock');
        $product->set_manage_stock(false);
        
        // Save product
        error_log('DropshippingBD Product Importer: Saving product...');
        $product_id = $product->save();
        
        if (!$product_id) {
            error_log('DropshippingBD Product Importer: Failed to save product - no ID returned');
            $result['message'] = 'Failed to save product';
            return $result;
        }
        
        error_log('DropshippingBD Product Importer: Product saved successfully with ID: ' . $product_id);
        
        // Store original API data in custom fields
        $this->store_original_data($product_id, $product_data);
        
        // Handle product images
        $this->import_product_images($product_id, $product_data);
        
        // Handle product categories
        $this->import_product_categories($product_id, $product_data);
        
        // Handle product variations if any
        if ($has_variants) {
            error_log('DropshippingBD Product Importer: Setting up variable product attributes and variations...');
            $this->setup_variable_product($product_id, $product_data);
        } else {
            error_log('DropshippingBD Product Importer: Simple product created successfully');
        }
        
        // Track imported product
        $this->track_imported_product($product_data['id'], $product_id);
        
        error_log('DropshippingBD Product Importer: Product imported successfully - WooCommerce ID: ' . $product_id);
        
        $result['success'] = true;
        $result['product_id'] = $product_id;
        $result['message'] = 'Product imported successfully';
        
        return $result;
        
        } catch (Exception $e) {
            error_log('DropshippingBD Product Importer: Error during import - ' . $e->getMessage());
            $result['message'] = 'Error during import: ' . $e->getMessage();
        return $result;
        }
    }
    
    /**
     * Apply 20% markup to price
     *
     * @param float $price Original price
     * @return float
     */
    private function apply_markup($price) {
        return $price;
        // return $price * (1 + ($this->markup_percentage / 100));
    }
    
    /**
     * Store original API data in custom fields
     *
     * @param int $product_id WooCommerce product ID
     * @param array $product_data Original API data
     */
    private function store_original_data($product_id, $product_data) {
        // Store complete original data
        update_post_meta($product_id, '_dropshippingbd_original_data', $product_data);
        
        // Store individual fields for easy access
        update_post_meta($product_id, '_dropshippingbd_mohasagor_id', $product_data['id']);
        update_post_meta($product_id, '_dropshippingbd_original_price', $product_data['price']);
        update_post_meta($product_id, '_dropshippingbd_original_sale_price', $product_data['sale_price'] ?? 0);
        update_post_meta($product_id, '_dropshippingbd_product_code', $product_data['product_code']);
        update_post_meta($product_id, '_dropshippingbd_category', $this->get_category_name(
            $product_data['category_id'] ?? null,
            $product_data['category'] ?? null
        ));
        update_post_meta($product_id, '_dropshippingbd_markup_percentage', $this->markup_percentage);
        update_post_meta($product_id, '_dropshippingbd_last_synced', current_time('mysql'));
    }
    
    /**
     * Import product images
     *
     * @param int $product_id WooCommerce product ID
     * @param array $product_data Product data from API
     */
    private function import_product_images($product_id, $product_data) {
        $image_urls = array();
        
        // Add thumbnail image with proper URL prefix
        if (!empty($product_data['thumbnail_img'])) {
            $thumbnail_url = $this->build_image_url($product_data['thumbnail_img']);
            if ($thumbnail_url) {
                $image_urls[] = $thumbnail_url;
            }
        }
        
        // Add additional images
        if (!empty($product_data['product_images']) && is_array($product_data['product_images'])) {
            foreach ($product_data['product_images'] as $image) {
                if (!empty($image['product_image'])) {
                    $image_url = $this->build_image_url($image['product_image']);
                    if ($image_url) {
                        $image_urls[] = $image_url;
                    }
                }
            }
        }
        
        // Debug logging
        error_log('DropshippingBD Image Import: Product ID ' . $product_id . 
                 ', Thumbnail: ' . ($product_data['thumbnail_img'] ?? 'null') . 
                 ', Total Images: ' . count($image_urls));
        
        // Import images
        $gallery_ids = array();
        foreach ($image_urls as $index => $image_url) {
            $attachment_id = $this->import_image($image_url, $product_id);
            
            if ($attachment_id) {
                if ($index === 0) {
                    // Set first image as featured image
                    set_post_thumbnail($product_id, $attachment_id);
                    error_log('DropshippingBD Image Import: Set featured image ' . $attachment_id . ' for product ' . $product_id);
                } else {
                    // Add to gallery
                    $gallery_ids[] = $attachment_id;
                }
            } else {
                error_log('DropshippingBD Image Import: Failed to import image ' . $image_url . ' for product ' . $product_id);
            }
        }
        
        // Set product gallery
        if (!empty($gallery_ids)) {
            update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
            error_log('DropshippingBD Image Import: Set gallery for product ' . $product_id . ' with ' . count($gallery_ids) . ' images');
        }
    }
    
    /**
     * Build proper image URL with prefix
     *
     * @param string $image_path Image path from API
     * @return string|false Full image URL or false if invalid
     */
    private function build_image_url($image_path) {
        if (empty($image_path)) {
            return false;
        }
        
        // If already a full URL, return as is
        if (strpos($image_path, 'http') === 0) {
            return $image_path;
        }
        
        // Add base URL prefix
        return 'https://dropshipping.com.bd/public/storage/' . ltrim($image_path, '/');
    }
    
    /**
     * Import image from URL
     *
     * @param string $image_url Image URL
     * @param int $product_id Product ID
     * @return int|false Attachment ID or false on failure
     */
    private function import_image($image_url, $product_id) {
        error_log('DropshippingBD Image Import: Attempting to import image: ' . $image_url);
        
        // Check if image already exists
        $existing_id = $this->get_attachment_by_url($image_url);
        if ($existing_id) {
            error_log('DropshippingBD Image Import: Image already exists with ID: ' . $existing_id);
            return $existing_id;
        }
        
        // Download image
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_get($image_url);
        
        if (is_wp_error($image_data)) {
            error_log('DropshippingBD Image Import: Failed to download image: ' . $image_data->get_error_message());
            return false;
        }
        
        $image_body = wp_remote_retrieve_body($image_data);
        $filename = basename(parse_url($image_url, PHP_URL_PATH));
        
        if (empty($filename)) {
            $filename = 'dropshippingbd-' . $product_id . '-' . time() . '.jpg';
        }
        
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        // Save image file
        if (file_put_contents($file_path, $image_body) === false) {
            error_log('DropshippingBD Image Import: Failed to save image file: ' . $file_path);
            return false;
        }
        
        error_log('DropshippingBD Image Import: Image file saved successfully: ' . $file_path);
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => wp_check_filetype($filename)['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path, $product_id);
        
        if (is_wp_error($attachment_id)) {
            error_log('DropshippingBD Image Import: Failed to create attachment: ' . $attachment_id->get_error_message());
            return false;
        }
        
        error_log('DropshippingBD Image Import: Attachment created successfully with ID: ' . $attachment_id);
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        error_log('DropshippingBD Image Import: Attachment metadata generated for ID: ' . $attachment_id);
        
        return $attachment_id;
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
    
    /**
     * Get category name from ID or provided name
     *
     * @param int|null $category_id Category ID
     * @param string|null $category_name Category name
     * @return string Category name
     */
    private function get_category_name($category_id, $category_name) {
        $category_map = $this->get_category_map();
        
        if (!empty($category_id) && isset($category_map[$category_id])) {
            return $category_map[$category_id];
        }
        
        return $category_name ?: 'No Category';
    }
    
    /**
     * Import product categories
     *
     * @param int $product_id WooCommerce product ID
     * @param array $product_data Product data from API
     */
    private function import_product_categories($product_id, $product_data) {
        // Get category name using centralized mapping
        $category_name = $this->get_category_name(
            $product_data['category_id'] ?? null,
            $product_data['category'] ?? null
        );
        
        // Debug logging
        error_log('DropshippingBD Category Import: Product ID ' . $product_id . 
                 ', Category ID: ' . ($product_data['category_id'] ?? 'null') . 
                 ', Category Name: ' . ($product_data['category'] ?? 'null') . 
                 ', Mapped Name: ' . $category_name);
        
        if (empty($category_name) || $category_name === 'No Category') {
            error_log('DropshippingBD Category Import: No category to import for product ' . $product_id);
            return;
        }
        
        // Check if category exists
        $term = get_term_by('name', $category_name, 'product_cat');
        
        if (!$term) {
            // Create category
            $term_result = wp_insert_term($category_name, 'product_cat');
            
            if (is_wp_error($term_result)) {
                return;
            }
            
            $term_id = $term_result['term_id'];
        } else {
            $term_id = $term->term_id;
        }
        
        // Assign category to product
        wp_set_object_terms($product_id, $term_id, 'product_cat');
    }
    
    /**
     * Setup variable product with attributes and variations
     *
     * @param int $product_id WooCommerce variable product ID
     * @param array $product_data Product data from API
     */
    private function setup_variable_product($product_id, $product_data) {
        if (empty($product_data['product_variants'])) {
            error_log('DropshippingBD Product Importer: No variants provided for variable product setup');
            return;
        }
        
        // Load the variable product
        $variable_product = wc_get_product($product_id);
        if (!$variable_product) {
            error_log('DropshippingBD Product Importer: Could not load variable product - ID: ' . $product_id);
            return;
        }
        
        error_log('DropshippingBD Product Importer: Setting up variable product attributes and variations for ID: ' . $product_id);
        error_log('DropshippingBD Product Importer: Variants data: ' . print_r($product_data['product_variants'], true));
        
        // Group variants by attribute label
        $attribute_label_to_values = array();
        foreach ($product_data['product_variants'] as $variant) {
            error_log('DropshippingBD Product Importer: Processing variant: ' . print_r($variant, true));
            
            if (empty($variant['attribute']) || empty($variant['variant'])) {
                error_log('DropshippingBD Product Importer: Skipping variant - missing attribute or variant value');
                error_log('DropshippingBD Product Importer: Attribute: "' . ($variant['attribute'] ?? 'EMPTY') . '", Variant: "' . ($variant['variant'] ?? 'EMPTY') . '"');
                continue;
            }
            $attribute_label = trim($variant['attribute']);
            $attribute_value = trim($variant['variant']);
            
            error_log('DropshippingBD Product Importer: Processing - Attribute: "' . $attribute_label . '", Value: "' . $attribute_value . '"');
            
            if (!isset($attribute_label_to_values[$attribute_label])) {
                $attribute_label_to_values[$attribute_label] = array();
            }
            if (!in_array($attribute_value, $attribute_label_to_values[$attribute_label], true)) {
                $attribute_label_to_values[$attribute_label][] = $attribute_value;
                error_log('DropshippingBD Product Importer: Added "' . $attribute_value . '" to "' . $attribute_label . '" attribute');
            } else {
                error_log('DropshippingBD Product Importer: Value "' . $attribute_value . '" already exists for "' . $attribute_label . '" attribute');
            }
        }
        
        error_log('DropshippingBD Product Importer: Grouped attributes: ' . print_r($attribute_label_to_values, true));
        
        if (empty($attribute_label_to_values)) {
            error_log('DropshippingBD Product Importer: No valid attributes found, skipping variable product setup');
            return;
        }
        
        // Create custom (non-global) attributes using WC_Product_Attribute
        $product_attributes = array();
        foreach ($attribute_label_to_values as $attribute_label => $values) {
            error_log('DropshippingBD Product Importer: Creating custom attribute: ' . $attribute_label . ' with values: ' . print_r($values, true));
            
            // Create custom attribute (non-taxonomy)
            $attribute = new WC_Product_Attribute();
            $attribute->set_id(0); // 0 for custom (non-taxonomy) attribute
            $attribute->set_name($attribute_label); // Keep the display name
            $attribute->set_options($values); // Array of strings
            $attribute->set_visible(true);
            $attribute->set_variation(true);
            
            $product_attributes[] = $attribute;
            error_log('DropshippingBD Product Importer: Created custom attribute: ' . $attribute_label);
        }
        
        error_log('DropshippingBD Product Importer: Saving ' . count($product_attributes) . ' custom attributes to product');
        
        // Set attributes on product
            $product = wc_get_product($product_id);
            $product->set_attributes($product_attributes);
            $product->save();
        
        error_log('DropshippingBD Product Importer: Custom attributes saved successfully');
        
        // Create variations using custom attribute approach
        $this->create_variations_from_custom_attributes($product_id, $attribute_label_to_values, $product_data);
        
        error_log('DropshippingBD Product Importer: Variable product setup completed');
    }
    
    /**
     * Create variations using custom attributes approach
     *
     * @param int $product_id Product ID
     * @param array $attribute_label_to_values Array of attribute labels to values
     * @param array $product_data Product data from API
     */
    private function create_variations_from_custom_attributes($product_id, $attribute_label_to_values, $product_data) {
        error_log('DropshippingBD Product Importer: Creating variations from custom attributes for product ID: ' . $product_id);
        error_log('DropshippingBD Product Importer: Attribute label to values: ' . print_r($attribute_label_to_values, true));
        
        if (empty($attribute_label_to_values)) {
            error_log('DropshippingBD Product Importer: No attributes provided for variation creation');
            return;
        }
        
        // Build cartesian product of all attribute combinations
        $combinations = $this->cartesian_product_of_custom_attributes($attribute_label_to_values);
        error_log('DropshippingBD Product Importer: Generated combinations: ' . print_r($combinations, true));
        
        // Get existing variations to avoid duplicates
        $existing_attribute_maps = array();
        $children = wc_get_products(array(
            'type' => 'variation',
            'parent' => $product_id,
            'limit' => -1,
            'return' => 'ids',
        ));
        
        foreach ($children as $child_id) {
            $var = wc_get_product($child_id);
            if ($var) {
                $existing_attribute_maps[] = $var->get_attributes();
            }
        }
        
        error_log('DropshippingBD Product Importer: Found ' . count($existing_attribute_maps) . ' existing variations');
        
        foreach ($combinations as $attributes_map) {
            error_log('DropshippingBD Product Importer: Processing combination: ' . print_r($attributes_map, true));
            
            // Check if this combination already exists
            $already_exists = false;
            foreach ($existing_attribute_maps as $existing) {
                if ($existing == $attributes_map) {
                    $already_exists = true;
                    error_log('DropshippingBD Product Importer: Combination already exists, skipping');
                    break;
                }
            }
            
            if ($already_exists) {
                continue;
            }
            
            // Create variation
            $variation_id = $this->create_single_variation($product_id, $attributes_map, $product_data);
            
            if ($variation_id) {
                error_log('DropshippingBD Product Importer: Created variation ID: ' . $variation_id);
            } else {
                error_log('DropshippingBD Product Importer: Failed to create variation for combination: ' . print_r($attributes_map, true));
            }
        }
        
        // Sync variation data up to the parent
        WC_Product_Variable::sync($product_id);
        WC_Product_Variable::sync_attributes($product_id);
        
        error_log('DropshippingBD Product Importer: Variation creation completed and synced');
    }
    
    /**
     * Build cartesian product of custom attribute combinations
     *
     * @param array $attribute_label_to_values Array of attribute labels to values
     * @return array Array of attribute combinations
     */
    private function cartesian_product_of_custom_attributes($attribute_label_to_values) {
        error_log('DropshippingBD Product Importer: Creating cartesian product from: ' . print_r($attribute_label_to_values, true));
        
        $result = array(array());
        foreach ($attribute_label_to_values as $attribute_label => $values) {
            $append = array();
            foreach ($result as $product) {
                foreach ($values as $value) {
                    $product_copy = $product;
                    $product_copy[$attribute_label] = $value;
                    $append[] = $product_copy;
                }
            }
            $result = $append;
        }
        
        error_log('DropshippingBD Product Importer: Final cartesian product result: ' . print_r($result, true));
        return $result;
    }
    
    /**
     * Create a single variation with custom attributes
     *
     * @param int $product_id Parent product ID
     * @param array $attributes_map Array of attribute => value pairs
     * @param array $product_data Product data from API
     * @return int|false Variation ID or false on failure
     */
    private function create_single_variation($product_id, $attributes_map, $product_data) {
        error_log('DropshippingBD Product Importer: Creating single variation with attributes: ' . print_r($attributes_map, true));
        
        // Create the variation post
        $variation_id = wp_insert_post(array(
            'post_title'  => sprintf('Variation for product #%d', $product_id),
            'post_name'   => 'product-' . $product_id . '-variation-' . sanitize_title(implode('-', $attributes_map)),
            'post_status' => 'publish',
            'post_parent' => $product_id,
            'post_type'   => 'product_variation',
            'menu_order'  => 0,
        ));
        
        if (is_wp_error($variation_id) || !$variation_id) {
            error_log('DropshippingBD Product Importer: Failed to create variation post');
            return false;
        }
        
        error_log('DropshippingBD Product Importer: Created variation post with ID: ' . $variation_id);
        
        // Set variation attributes using custom attribute meta keys
        foreach ($attributes_map as $attribute_label => $value) {
            $attr_key = 'attribute_' . sanitize_title($attribute_label);
            update_post_meta($variation_id, $attr_key, $value);
            error_log('DropshippingBD Product Importer: Set ' . $attr_key . ' = ' . $value);
        }
        
        // Set prices with 20% markup
        $base_price = $product_data['price'] ?? 0;
        $regular_price = $this->apply_markup($base_price);
        
        update_post_meta($variation_id, '_regular_price', $regular_price);
        update_post_meta($variation_id, '_price', $regular_price);
        
        // Set stock status
        update_post_meta($variation_id, '_manage_stock', 'no');
        update_post_meta($variation_id, '_stock_status', 'instock');
        
        // Set SKU
        $sku = $this->generate_valid_sku($product_data) . '-' . sanitize_title(implode('-', $attributes_map));
        update_post_meta($variation_id, '_sku', $sku);
        
        // Set other meta
        update_post_meta($variation_id, '_virtual', 'no');
        update_post_meta($variation_id, '_downloadable', 'no');
        
        error_log('DropshippingBD Product Importer: Variation created successfully with ID: ' . $variation_id);
        
        return $variation_id;
    }
    
    /**
     * Update existing product with new data from API
     *
     * @param array $product_data Product data from API
     * @return array Result with success status and message
     */
    public function update_existing_product($product_data) {
        error_log('DropshippingBD Product Importer: Updating existing product - ID: ' . ($product_data['id'] ?? 'N/A'));
        
        // Check if WooCommerce is active
        if (!class_exists('WC_Product_Simple') || !class_exists('WC_Product_Variable')) {
            error_log('DropshippingBD Product Importer: WooCommerce is not active or missing required classes');
            return array(
                'success' => false,
                'message' => 'WooCommerce is not active or missing required classes',
                'product_id' => null
            );
        }
        
        // Find existing product
        $existing_product_id = $this->get_existing_product_id($product_data['id']);
        
        if (!$existing_product_id) {
            error_log('DropshippingBD Product Importer: Product not found for update - ID: ' . $product_data['id']);
            return array(
                'success' => false,
                'message' => 'Product not found for update',
                'product_id' => null
            );
        }
        
        error_log('DropshippingBD Product Importer: Found existing product - WooCommerce ID: ' . $existing_product_id);
        
        try {
            $product = wc_get_product($existing_product_id);
            
            if (!$product) {
                error_log('DropshippingBD Product Importer: Could not load WooCommerce product - ID: ' . $existing_product_id);
                return array(
                    'success' => false,
                    'message' => 'Could not load WooCommerce product',
                    'product_id' => null
                );
            }
            
            // Update basic product information
            if (!empty($product_data['name'])) {
                $product->set_name($product_data['name']);
                error_log('DropshippingBD Product Importer: Updated product name: ' . $product_data['name']);
            }
            
            if (!empty($product_data['details'])) {
                $product->set_description($product_data['details']);
                $product->set_short_description($this->truncate_description($product_data['details']));
                error_log('DropshippingBD Product Importer: Updated product description');
            }
            
            // Update prices (only for simple products)
            if ($product->is_type('simple')) {
                if (!empty($product_data['price'])) {
                    $regular_price = $this->apply_markup($product_data['price']);
                    $product->set_regular_price($regular_price);
                    error_log('DropshippingBD Product Importer: Updated regular price: ' . $regular_price);
                }
                
                if (isset($product_data['sale_price']) && $product_data['sale_price'] > 0) {
                    $sale_price = $this->apply_markup($product_data['sale_price']);
                    $product->set_sale_price($sale_price);
                    error_log('DropshippingBD Product Importer: Updated sale price: ' . $sale_price);
                }
            }
            
            // Update SKU if needed
            if (!empty($product_data['product_code'])) {
                try {
                    $sku = $this->generate_valid_sku($product_data);
                    $product->set_sku($sku);
                    error_log('DropshippingBD Product Importer: Updated SKU: ' . $sku);
                } catch (Exception $e) {
                    error_log('DropshippingBD Product Importer: SKU update error - ' . $e->getMessage());
                }
            }
            
            // Save product
            $product->save();
            error_log('DropshippingBD Product Importer: Product updated successfully');
            
            // Update custom fields
            $this->store_original_data($existing_product_id, $product_data);
            
            // Update images if needed
            $this->import_product_images($existing_product_id, $product_data);
            
            // Update categories
            $this->import_product_categories($existing_product_id, $product_data);
            
            error_log('DropshippingBD Product Importer: Product update completed successfully - WooCommerce ID: ' . $existing_product_id);
            
            return array(
                'success' => true,
                'product_id' => $existing_product_id,
                'message' => 'Product updated successfully'
            );
            
        } catch (Exception $e) {
            error_log('DropshippingBD Product Importer: Error during update - ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Error during update: ' . $e->getMessage(),
                'product_id' => null
            );
        }
    }
    
    /**
     * Track import progress
     *
     * @param int $current_page Current page being imported
     * @param int $current_index Current product index on the page
     * @param int $total_pages Total pages to import
     * @param int $total_products Total products to import
     */
    private function track_import_progress($current_page, $current_index, $total_pages, $total_products) {
        $progress_data = array(
            'current_page' => $current_page,
            'current_index' => $current_index,
            'total_pages' => $total_pages,
            'total_products' => $total_products,
            'last_updated' => current_time('mysql'),
            'status' => 'in_progress'
        );
        
        update_option('dropshippingbd_import_progress', $progress_data);
        error_log('DropshippingBD Product Importer: Tracked progress - Page: ' . $current_page . ', Index: ' . $current_index . ', Total: ' . $total_products);
    }
    
    /**
     * Get last import progress
     *
     * @return array|false Progress data or false if none exists
     */
    public function get_last_import_progress() {
        $progress = get_option('dropshippingbd_import_progress', false);
        
        if ($progress && isset($progress['status']) && $progress['status'] === 'in_progress') {
            error_log('DropshippingBD Product Importer: Found last import progress - Page: ' . $progress['current_page'] . ', Index: ' . $progress['current_index']);
            return $progress;
        }
        
        return false;
    }
    
    /**
     * Complete import progress
     */
    private function complete_import_progress() {
        $progress_data = array(
            'status' => 'completed',
            'completed_at' => current_time('mysql')
        );
        
        update_option('dropshippingbd_import_progress', $progress_data);
        error_log('DropshippingBD Product Importer: Import progress marked as completed');
    }
    
    /**
     * Clear import progress
     */
    public function clear_import_progress() {
        delete_option('dropshippingbd_import_progress');
        error_log('DropshippingBD Product Importer: Import progress cleared');
    }
    
    /**
     * Get count of imported products
     *
     * @return int Number of imported products
     */
    public function get_imported_products_count() {
        global $wpdb;
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dropshippingbd_products");
        
        error_log('DropshippingBD Product Importer: Found ' . $count . ' imported products');
        
        return (int) $count;
    }
    
    /**
     * Delete all imported products
     *
     * @return array Result with success status and count of deleted products
     */
    public function delete_all_imported_products() {
        error_log('DropshippingBD Product Importer: Starting deletion of all imported products');
        
        global $wpdb;
        
        // Get all imported product IDs
        $imported_products = $wpdb->get_results("SELECT woo_product_id FROM {$wpdb->prefix}dropshippingbd_products");
        
        $deleted_count = 0;
        $errors = array();
        
        foreach ($imported_products as $imported_product) {
            $product_id = $imported_product->woo_product_id;
            
            // Delete WooCommerce product (this will also delete variations)
            $deleted = wp_delete_post($product_id, true);
            
            if ($deleted) {
                $deleted_count++;
                error_log('DropshippingBD Product Importer: Deleted product ID: ' . $product_id);
            } else {
                $errors[] = 'Failed to delete product ID: ' . $product_id;
                error_log('DropshippingBD Product Importer: Failed to delete product ID: ' . $product_id);
            }
        }
        
        // Clear the tracking table
        $wpdb->query("DELETE FROM {$wpdb->prefix}dropshippingbd_products");
        
        // Clear import progress
        $this->clear_import_progress();
        
        error_log('DropshippingBD Product Importer: Deleted ' . $deleted_count . ' products, ' . count($errors) . ' errors');
        
        return array(
            'success' => true,
            'deleted_count' => $deleted_count,
            'errors' => $errors,
            'message' => 'Deleted ' . $deleted_count . ' products successfully'
        );
    }
    
    /**
     * Create product attribute
     *
     * @param int $product_id Product ID
     * @param string $attribute_name Attribute name
     * @param array $values Attribute values
     */
    private function create_product_attribute($product_id, $attribute_name, $values) {
        // Deprecated in favor of taxonomy-based implementation in convert_to_variable_product()
        return;
    }
    
    /**
     * Create product variation
     *
     * @param int $product_id Product ID
     * @param array $variant Variant data
     * @param array $product_data Product data from API
     */
    private function create_product_variation($product_id, $attributes_map, $product_data) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        
        // Attributes must use taxonomy slugs for taxonomy-based attributes
        $variation->set_attributes($attributes_map);
        
        // Set prices with markup
        $regular_price = $this->apply_markup($product_data['price']);
        $variation->set_regular_price($regular_price);
        
        if (isset($product_data['sale_price']) && $product_data['sale_price'] > 0) {
            $sale_price = $this->apply_markup($product_data['sale_price']);
            $variation->set_sale_price($sale_price);
        }
        
        // Set stock
        $variation->set_stock_status('instock');
        $variation->set_manage_stock(false);
        
        $variation->save();
    }

    /**
     * Ensure a global attribute taxonomy exists; create it if missing.
     * Returns the taxonomy name, e.g., pa_size
     */
    private function ensure_attribute_taxonomy($attribute_label) {
        error_log('DropshippingBD Product Importer: Ensuring attribute taxonomy for: ' . $attribute_label);
        
        if (empty($attribute_label)) {
            $attribute_label = 'attribute';
        }
        $slug = wc_sanitize_taxonomy_name(sanitize_title($attribute_label));
        if (strpos($slug, 'pa_') !== 0) {
            $slug = 'pa_' . $slug;
        }
        
        error_log('DropshippingBD Product Importer: Generated slug: ' . $slug);
        
        // Check if taxonomy already registered in this request
        if (taxonomy_exists($slug)) {
            error_log('DropshippingBD Product Importer: Taxonomy already exists: ' . $slug);
            return $slug;
        }
        
        // Check if attribute taxonomy exists in WooCommerce DB
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $exists = false;
        if (!empty($attribute_taxonomies)) {
            foreach ($attribute_taxonomies as $tax) {
                if (!empty($tax->attribute_name) && ('pa_' . $tax->attribute_name) === $slug) {
                    $exists = true;
                    break;
                }
            }
        }
        
        if (!$exists) {
            // Create attribute taxonomy
            $result = wc_create_attribute(array(
                'name' => $attribute_label,
                'slug' => substr($slug, 3), // Woo expects without pa_
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => false,
            ));
            
            if (is_wp_error($result)) {
                // Fallback to ephemeral taxonomy for this request
                register_taxonomy($slug, apply_filters('woocommerce_taxonomy_objects_' . $slug, array('product')), apply_filters('woocommerce_taxonomy_args_' . $slug, array(
                    'hierarchical' => false,
                    'show_ui' => false,
                    'query_var' => true,
                    'rewrite' => false,
                )));
                return $slug;
            }
            // Refresh attribute taxonomies list and register
            unregister_taxonomy($slug);
        }
        
        // Ensure taxonomy is registered for this request
        register_taxonomy($slug, apply_filters('woocommerce_taxonomy_objects_' . $slug, array('product')), apply_filters('woocommerce_taxonomy_args_' . $slug, array(
            'hierarchical' => false,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => false,
        )));
        
        return $slug;
    }

    /**
     * Ensure terms exist for the given taxonomy. Returns array of WP_Term keyed by slug
     */
    private function ensure_terms($taxonomy, $values) {
        error_log('DropshippingBD Product Importer: Ensuring terms for taxonomy: ' . $taxonomy . ' with values: ' . print_r($values, true));
        
        $terms = array();
        foreach ($values as $value) {
            $value = trim(wp_strip_all_tags((string) $value));
            if ($value === '') { 
                error_log('DropshippingBD Product Importer: Skipping empty value');
                continue; 
            }
            
            $slug = sanitize_title($value);
            error_log('DropshippingBD Product Importer: Processing value: "' . $value . '" with slug: "' . $slug . '"');
            
            $term = get_term_by('slug', $slug, $taxonomy);
            if (!$term) {
                error_log('DropshippingBD Product Importer: Term not found, creating new term: "' . $value . '"');
                $created = wp_insert_term($value, $taxonomy, array('slug' => $slug));
                if (!is_wp_error($created)) {
                    $term = get_term($created['term_id']);
                    error_log('DropshippingBD Product Importer: Created term ID: ' . $created['term_id'] . ' for value: "' . $value . '"');
                } else {
                    error_log('DropshippingBD Product Importer: Error creating term: ' . $created->get_error_message());
                }
            } else {
                error_log('DropshippingBD Product Importer: Found existing term ID: ' . $term->term_id . ' for value: "' . $value . '"');
            }
            
            if ($term && !is_wp_error($term)) {
                $terms[$slug] = $term;
                error_log('DropshippingBD Product Importer: Added term to array - Slug: "' . $slug . '", ID: ' . $term->term_id . ', Name: "' . $term->name . '"');
            }
        }
        
        error_log('DropshippingBD Product Importer: Final terms array: ' . print_r($terms, true));
        return $terms;
    }

    /**
     * Create variations for all combinations of provided attribute terms
     */
    private function create_variations_from_terms($product_id, $attribute_taxonomy_to_terms, $product_data) {
        error_log('DropshippingBD Product Importer: Creating variations from terms for product ID: ' . $product_id);
        error_log('DropshippingBD Product Importer: Attribute taxonomy to terms: ' . print_r($attribute_taxonomy_to_terms, true));
        
        if (empty($attribute_taxonomy_to_terms)) {
            error_log('DropshippingBD Product Importer: No attribute taxonomy to terms provided');
            return;
        }
        
        // Build list of arrays of [taxonomy => term_slug]
        $combinations = $this->cartesian_product_of_terms($attribute_taxonomy_to_terms);
        error_log('DropshippingBD Product Importer: Generated combinations: ' . print_r($combinations, true));
        
        // Get existing variations' attribute maps to avoid duplicates
        $existing_attribute_maps = array();
        $children = wc_get_products(array(
            'type' => 'variation',
            'parent' => $product_id,
            'limit' => -1,
            'return' => 'ids',
        ));
        foreach ($children as $child_id) {
            $var = wc_get_product($child_id);
            if ($var) {
                $existing_attribute_maps[] = $var->get_attributes();
            }
        }
        
        foreach ($combinations as $attributes_map) {
            // attributes_map uses taxonomy => term_slug; WC expects keys without 'attribute_' prefix for set_attributes()
            $already_exists = false;
            foreach ($existing_attribute_maps as $existing) {
                if ($existing == $attributes_map) {
                    $already_exists = true;
                    break;
                }
            }
            if ($already_exists) {
                continue;
            }
            $this->create_product_variation($product_id, $attributes_map, $product_data);
        }
    }

    /**
     * Build cartesian product of term slugs per taxonomy
     */
    private function cartesian_product_of_terms($taxonomy_to_terms) {
        error_log('DropshippingBD Product Importer: Creating cartesian product from: ' . print_r($taxonomy_to_terms, true));
        
        // Convert WP_Term arrays to slug arrays
        $taxonomy_to_slugs = array();
        foreach ($taxonomy_to_terms as $taxonomy => $terms) {
            $taxonomy_to_slugs[$taxonomy] = array_keys($terms); // keys are slugs
            error_log('DropshippingBD Product Importer: Taxonomy "' . $taxonomy . '" has slugs: ' . print_r(array_keys($terms), true));
        }
        
        error_log('DropshippingBD Product Importer: Taxonomy to slugs: ' . print_r($taxonomy_to_slugs, true));
        
        // Recursive cartesian product
        $result = array(array());
        foreach ($taxonomy_to_slugs as $taxonomy => $slugs) {
            $append = array();
            foreach ($result as $product) {
                foreach ($slugs as $slug) {
                    $product_copy = $product;
                    $product_copy[$taxonomy] = $slug; // e.g., 'pa_size' => 'm'
                    $append[] = $product_copy;
                }
            }
            $result = $append;
        }
        
        error_log('DropshippingBD Product Importer: Final cartesian product result: ' . print_r($result, true));
        return $result;
    }
    
    /**
     * Get existing product ID by Mohasagor ID
     *
     * @param int $mohasagor_id Mohasagor product ID
     * @return int|false
     */
    private function get_existing_product_id($mohasagor_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dropshippingbd_products';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT woo_product_id FROM $table_name WHERE mohasagor_id = %d",
            $mohasagor_id
        ));
        
        return $result ? intval($result) : false;
    }
    
    /**
     * Track imported product
     *
     * @param int $mohasagor_id Mohasagor product ID
     * @param int $woo_product_id WooCommerce product ID
     */
    private function track_imported_product($mohasagor_id, $woo_product_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dropshippingbd_products';
        
        $wpdb->insert(
            $table_name,
            array(
                'mohasagor_id' => $mohasagor_id,
                'woo_product_id' => $woo_product_id,
                'last_synced' => current_time('mysql'),
                'status' => 'active'
            ),
            array('%d', '%d', '%s', '%s')
        );
    }
    
    /**
     * Sync existing products
     *
     * @return array
     */
    public function sync_existing_products() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dropshippingbd_products';
        
        $imported_products = $wpdb->get_results(
            "SELECT mohasagor_id, woo_product_id FROM $table_name WHERE status = 'active'"
        );
        
        $result = array(
            'success' => false,
            'synced' => 0,
            'errors' => array(),
            'message' => ''
        );
        
        foreach ($imported_products as $product) {
            try {
                $api_data = $this->api_client->get_product($product->mohasagor_id);
                
                if (is_wp_error($api_data)) {
                    $result['errors'][] = 'Failed to fetch product ' . $product->mohasagor_id . ': ' . $api_data->get_error_message();
                    continue;
                }
                
                $this->update_product($product->woo_product_id, $api_data);
                $result['synced']++;
                
            } catch (Exception $e) {
                $result['errors'][] = 'Error syncing product ' . $product->mohasagor_id . ': ' . $e->getMessage();
            }
        }
        
        $result['success'] = true;
        $result['message'] = 'Sync completed. Synced: ' . $result['synced'] . ' products';
        
        return $result;
    }
    
    /**
     * Update existing product
     *
     * @param int $product_id WooCommerce product ID
     * @param array $product_data Updated product data from API
     */
    private function update_product($product_id, $product_data) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        // Update prices with markup
        $regular_price = $this->apply_markup($product_data['price']);
        $product->set_regular_price($regular_price);
        
        if (isset($product_data['sale_price']) && $product_data['sale_price'] > 0) {
            $sale_price = $this->apply_markup($product_data['sale_price']);
            $product->set_sale_price($sale_price);
        }
        
        $product->save();
        
        // Update custom fields
        update_post_meta($product_id, '_dropshippingbd_original_data', $product_data);
        update_post_meta($product_id, '_dropshippingbd_original_price', $product_data['price']);
        update_post_meta($product_id, '_dropshippingbd_original_sale_price', $product_data['sale_price'] ?? 0);
        update_post_meta($product_id, '_dropshippingbd_last_synced', current_time('mysql'));
        
        // Update tracking table
        global $wpdb;
        $table_name = $wpdb->prefix . 'dropshippingbd_products';
        
        $wpdb->update(
            $table_name,
            array('last_synced' => current_time('mysql')),
            array('woo_product_id' => $product_id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Truncate description for short description
     *
     * @param string $description Full description
     * @return string
     */
    private function truncate_description($description) {
        return wp_trim_words($description, 20, '...');
    }
    
    /**
     * Get attachment by URL
     *
     * @param string $url Image URL
     * @return int|false
     */
    private function get_attachment_by_url($url) {
        global $wpdb;
        
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid = %s",
            $url
        ));
        
        return $attachment_id ? intval($attachment_id) : false;
    }
    
    /**
     * Import categories from API
     *
     * @return array
     */
    public function import_categories() {
        $result = array(
            'success' => false,
            'imported' => 0,
            'skipped' => 0,
            'errors' => array(),
            'message' => ''
        );
        
        // Fetch categories from API
        $api_data = $this->api_client->get_categories();
        
        if (is_wp_error($api_data)) {
            $result['errors'][] = $api_data->get_error_message();
            $result['message'] = 'Failed to fetch categories from API';
            return $result;
        }
        
        if (!isset($api_data['categories']) || !is_array($api_data['categories'])) {
            $result['message'] = 'No categories found in API response';
            return $result;
        }
        
        $categories = $api_data['categories'];
        
        foreach ($categories as $category_data) {
            try {
                $import_result = $this->import_single_category($category_data);
                
                if ($import_result['success']) {
                    $result['imported']++;
                } else {
                    $result['skipped']++;
                    $result['errors'][] = $import_result['message'];
                }
            } catch (Exception $e) {
                $result['skipped']++;
                $result['errors'][] = 'Error importing category ID ' . $category_data['id'] . ': ' . $e->getMessage();
            }
        }
        
        $result['success'] = true;
        $result['message'] = sprintf(
            'Category import completed. Imported: %d, Skipped: %d',
            $result['imported'],
            $result['skipped']
        );
        
        return $result;
    }
    
    /**
     * Import single category
     *
     * @param array $category_data Category data from API
     * @return array
     */
    private function import_single_category($category_data) {
        $result = array(
            'success' => false,
            'message' => '',
            'category_id' => null
        );
        
        if (empty($category_data['name'])) {
            $result['message'] = 'Category name is required';
            return $result;
        }
        
        $category_name = $category_data['name'];
        $category_slug = isset($category_data['slug']) ? $category_data['slug'] : sanitize_title($category_name);
        
        // Check if category already exists
        $existing_term = get_term_by('slug', $category_slug, 'product_cat');
        
        if ($existing_term) {
            $result['message'] = 'Category already exists (ID: ' . $existing_term->term_id . ')';
            return $result;
        }
        
        // Create category
        $term_data = array(
            'name' => $category_name,
            'slug' => $category_slug,
            'description' => isset($category_data['description']) ? $category_data['description'] : '',
        );
        
        // Handle parent category if exists
        if (!empty($category_data['parent_id'])) {
            $parent_term = $this->get_category_by_api_id($category_data['parent_id']);
            if ($parent_term) {
                $term_data['parent'] = $parent_term->term_id;
            }
        }
        
        $term_result = wp_insert_term($category_name, 'product_cat', $term_data);
        
        if (is_wp_error($term_result)) {
            $result['message'] = 'Failed to create category: ' . $term_result->get_error_message();
            return $result;
        }
        
        $term_id = $term_result['term_id'];
        
        // Store original API data in term meta
        update_term_meta($term_id, '_dropshippingbd_api_id', $category_data['id']);
        update_term_meta($term_id, '_dropshippingbd_original_data', $category_data);
        update_term_meta($term_id, '_dropshippingbd_last_synced', current_time('mysql'));
        
        // Handle category image if exists
        if (!empty($category_data['image'])) {
            $this->import_category_image($term_id, $category_data['image']);
        }
        
        $result['success'] = true;
        $result['category_id'] = $term_id;
        $result['message'] = 'Category imported successfully';
        
        return $result;
    }
    
    /**
     * Get category by API ID
     *
     * @param int $api_id API category ID
     * @return WP_Term|false
     */
    private function get_category_by_api_id($api_id) {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => '_dropshippingbd_api_id',
                    'value' => $api_id,
                    'compare' => '='
                )
            )
        ));
        
        return !empty($terms) && !is_wp_error($terms) ? $terms[0] : false;
    }
    
    /**
     * Import category image
     *
     * @param int $term_id Category term ID
     * @param string $image_url Image URL
     */
    private function import_category_image($term_id, $image_url) {
        // Download image
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_get($image_url);
        
        if (is_wp_error($image_data)) {
            return;
        }
        
        $image_body = wp_remote_retrieve_body($image_data);
        $filename = basename(parse_url($image_url, PHP_URL_PATH));
        
        if (empty($filename)) {
            $filename = 'category-' . $term_id . '-' . time() . '.jpg';
        }
        
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        // Save image file
        if (file_put_contents($file_path, $image_body) === false) {
            return;
        }
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => wp_check_filetype($filename)['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (is_wp_error($attachment_id)) {
            return;
        }
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set as category thumbnail
        update_term_meta($term_id, 'thumbnail_id', $attachment_id);
    }
    
    /**
     * Generate a valid SKU for the product
     *
     * @param array $product_data Product data from API
     * @return string Valid SKU
     */
    private function generate_valid_sku($product_data) {
        $sku = '';
        
        // Try to use product_code first
        if (isset($product_data['product_code']) && !empty($product_data['product_code'])) {
            $sku = sanitize_text_field($product_data['product_code']);
            
            // Remove invalid characters (only allow alphanumeric, hyphens, underscores)
            $sku = preg_replace('/[^a-zA-Z0-9\-_]/', '', $sku);
            
            // Ensure SKU is not empty after sanitization
            if (empty($sku)) {
                $sku = 'DSBD-' . $product_data['id'];
            }
        } else {
            // Generate SKU from product ID
            $sku = 'DSBD-' . $product_data['id'];
        }
        
        // Ensure SKU is unique
        $original_sku = $sku;
        $counter = 1;
        
        while (wc_get_product_id_by_sku($sku)) {
            $sku = $original_sku . '-' . $counter;
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 1000) {
                $sku = 'DSBD-' . $product_data['id'] . '-' . time();
                break;
            }
        }
        
        // Ensure SKU length is within limits (max 64 characters)
        if (strlen($sku) > 64) {
            $sku = substr($sku, 0, 64);
        }
        
        error_log('DropshippingBD Product Importer: Generated SKU: ' . $sku);
        return $sku;
    }
}
