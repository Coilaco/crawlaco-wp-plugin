<?php
/**
 * Custom Functions for Crawlaco Plugin
 * 
 * DEVELOPER ONLY FILE - DO NOT MODIFY UNLESS YOU ARE A DEVELOPER
 * 
 * This file is intended for plugin developers who need to extend or modify
 * the functionality of the Crawlaco plugin. It allows you to add custom code
 * that will be executed in WordPress core.
 * 
 * WARNING: 
 * - This file should only be modified by experienced developers
 * - Any code added here will be executed on every page load
 * - Incorrect code can break your WordPress installation
 * - Always test your code thoroughly before deploying
 * - Keep a backup of this file before making changes
 * 
 * Usage:
 * 1. Add your custom functions, hooks, and filters here
 * 2. Follow WordPress coding standards
 * 3. Use proper error handling
 * 4. Document your code
 */

// Your custom code goes here:

// Add custom meta field to product varation (provider url)
function crawlaco_provider_url_field( $loop, $variation_data, $variation ) {
    if (!class_exists('WooCommerce')) {
        return;
    }
    woocommerce_wp_text_input(
        array(
        'id'            => 'text_field[' . $loop . ']',
        'label'         => 'لینک ارائه دهنده محصول (کرالاکو)',
        'wrapper_class' => 'form-row',
        'placeholder'   => 'لینک ارائه دهنده محصول ...',
        'desc_tip'      => true,
        'description'   => 'لینک محصول در وب‌سایت اصلی ارائه دهنده (مثال: دیجیکالا، آمازون، ترندیول و...)',
        'value'         => get_post_meta( $variation->ID, 'provider_url', true )
        )
    );
}
add_action( 'woocommerce_product_after_variable_attributes', 'crawlaco_provider_url_field', 10, 3 );
  
// Add custom meta field to product varation (is archive)
function crawlaco_product_archive_field( $loop, $variation_data, $variation ) {
    if (!class_exists('WooCommerce')) {
        return;
    }
    woocommerce_wp_checkbox(
        array(
            'id'            => 'is_archived[' . $loop . ']',
            'label'         => 'آرشیو محصول (کرالاکو)',
            'wrapper_class' => 'form-row',
            'desc_tip'      => true,
            'description'   => 'آیا محصول آرشیو شود؟ (در صورت آرشیو شدن، این محصول دیگر توسط کرالاکو بروزرسانی نمی‌شود.) ',
            'value'         => get_post_meta( $variation->ID, 'is_archived', true ),
        )
    );
}
add_action( 'woocommerce_product_after_variable_attributes', 'crawlaco_product_archive_field', 10, 3 );

// Save custom meta field to product variations
function crawlaco_save_variation_meta_fields( $variation_id, $loop ) {
    if (!class_exists('WooCommerce')) {
        return;
    }

    // Verify nonce if it exists
    if (isset($_POST['woocommerce_meta_nonce'])) {
        if (!wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['woocommerce_meta_nonce'])),
            'woocommerce_save_data'
        )) {
            return;
        }
    }

    // Sanitize and validate loop index
    $loop = absint($loop);
    if ($loop < 0) {
        return;
    }

    // Text Field - Sanitize array access and value
    $text_field = '';
    if (isset($_POST['text_field']) && is_array($_POST['text_field']) && isset($_POST['text_field'][$loop])) {
        $text_field = sanitize_text_field(wp_unslash($_POST['text_field'][$loop]));
    }
    update_post_meta($variation_id, 'provider_url', $text_field);
    
    // Checkbox Field - Sanitize array access and value
    $checkbox_field = 'no';
    if (isset($_POST['is_archived']) && is_array($_POST['is_archived']) && isset($_POST['is_archived'][$loop])) {
        $checkbox_field = sanitize_text_field(wp_unslash($_POST['is_archived'][$loop])) === 'yes' ? 'yes' : 'no';
    }
    update_post_meta($variation_id, 'is_archived', $checkbox_field);
}

add_action( 'woocommerce_save_product_variation', 'crawlaco_save_variation_meta_fields', 10, 2 );


// Add custom meta fields to simple products
function crawlaco_add_simple_product_fields() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    global $post;
    
    // Only show these fields for simple products
    if ('product' !== $post->post_type) {
        return;
    }
    
    $product = wc_get_product($post->ID);
    if (!$product || $product->is_type('variable')) {
        return;
    }

    // Provider URL field
    woocommerce_wp_text_input(
        array(
            'id'            => 'provider_url',
            'label'         => 'لینک ارائه دهنده محصول (کرالاکو)',
            'wrapper_class' => 'form-row',
            'placeholder'   => 'لینک ارائه دهنده محصول ...',
            'desc_tip'      => true,
            'description'   => 'لینک محصول در وب‌سایت اصلی ارائه دهنده (مثال: دیجیکالا، آمازون، ترندیول و...)',
            'value'         => get_post_meta($post->ID, 'provider_url', true)
        )
    );

    // Is Archive field
    woocommerce_wp_checkbox(
        array(
            'id'            => 'is_archived',
            'label'         => 'آرشیو محصول (کرالاکو)',
            'wrapper_class' => 'form-row',
            'desc_tip'      => true,
            'description'   => 'آیا محصول آرشیو شود؟ (در صورت آرشیو شدن، این محصول دیگر توسط کرالاکو بروزرسانی نمی‌شود.) ',
            'value'         => get_post_meta($post->ID, 'is_archived', true)
        )
    );
}
add_action('woocommerce_product_options_general_product_data', 'crawlaco_add_simple_product_fields');

// Save custom meta fields for simple products
function crawlaco_save_simple_product_fields($post_id) {
    if (!class_exists('WooCommerce')) {
        return;
    }
    $product = wc_get_product($post_id);
    
    if (!$product || $product->is_type('variable')) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['woocommerce_meta_nonce'])),
        'woocommerce_save_data'
    )) {
        return;
    }

    // Save provider URL
    if (isset($_POST['provider_url'])) {
        update_post_meta($post_id, 'provider_url', sanitize_text_field(wp_unslash($_POST['provider_url'])));
    }

    // Save is_archived
    $is_archived = isset($_POST['is_archived']) ? 'yes' : 'no';
    update_post_meta($post_id, 'is_archived', $is_archived);
}
add_action('woocommerce_process_product_meta', 'crawlaco_save_simple_product_fields');


// Rank Math related code
add_action('rest_api_init', function () {
    register_rest_route('custom-rankmath/v1', '/add-redirect', array(
        'methods' => 'POST',
        'callback' => 'crawlaco_add_rankmath_redirect',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));
});

function crawlaco_add_rankmath_redirect($request) {
    if (!class_exists('RankMath')) {
        return new WP_Error('rank_math_not_found', 'Rank Math is not installed.', array('status' => 404));
    }
    
    global $wpdb;
    
    // Check if the redirections table exists
    $table_name = $wpdb->prefix . 'rank_math_redirections';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return new WP_REST_Response(array(
            'success' => true,
            'message' => "Table does not exist, skipping redirect creation.",
        ), 200);
    }

    // Extract parameters from the POST request
    $params = $request->get_json_params();
    $url = isset($params['url']) ? esc_url_raw($params['url']) : '';
    $status_code = isset($params['status_code']) ? intval($params['status_code']) : 410; // Default to 410 if not provided
    $url_to = isset($params['url_to']) ? esc_url_raw($params['url_to']) : ''; // Target URL for 301 and 302 redirects

    // Validate status code (allow only 301, 302, and 410)
    $allowed_status_codes = array(301, 302, 410);
    if (!in_array($status_code, $allowed_status_codes)) {
        return new WP_Error('invalid_status_code', 'Status code must be 301, 302, or 410.', array('status' => 400));
    }

    if (empty($url)) {
        return new WP_Error('missing_url', 'URL parameter is required.', array('status' => 400));
    }

    // Remove protocol and domain if present, keeping only the path
    $parsed_url = wp_parse_url($url);
    $url_path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $url_path .= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $url_path .= isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

    // If status code is 301 or 302, the `url_to` parameter should not be empty
    if (in_array($status_code, array(301, 302)) && empty($url_to)) {
        return new WP_Error('missing_url_to', 'The target URL (url_to) is required for 301 and 302 redirects.', array('status' => 400));
    }

    // Check if the URL already has a redirect in Rank Math
    $existing_redirect = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rank_math_redirections WHERE sources LIKE %s",
        '%"url":"' . $url_path . '"%'
    ));

    if ($existing_redirect) {
        return new WP_Error('redirect_exists', 'A redirect already exists for this URL.', array('status' => 409));
    }

    // Structure sources data to meet Rank Math's expected format
    $sources = array(array('pattern' => $url_path, 'comparison' => 'exact'));

    // Insert a new redirect into Rank Math's redirect table
    $result = $wpdb->insert(
        "{$wpdb->prefix}rank_math_redirections",
        array(
            'sources' => maybe_serialize($sources),
            'url_to' => $status_code === 410 ? '' : $url_to, // Set `url_to` only if not 410
            'header_code' => $status_code,
            'status' => 'active',
            'hits' => 0,
            'created' => current_time('mysql'),
            'updated' => current_time('mysql')
        ),
        array('%s', '%s', '%d', '%s', '%d', '%s', '%s')
    );

    // Check for any errors in the insertion process
    if ($result === false) {
        $error_message = $wpdb->last_error;
        return new WP_Error('db_insert_error', 'Failed to create redirect in the database. Error: ' . $error_message, array('status' => 500));
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => "{$status_code} redirect added successfully.",
        'data' => array(
            'url' => $url_path,
            'url_to' => $url_to,
            'status_code' => $status_code
        )
    ), 200);
}

// Register Rank Math meta fields
function crawlaco_register_rankmath_meta() {
    register_meta('post', 'rank_math_title', [
        'show_in_rest' => [
            'schema' => [
                'type' => 'string',
                'context' => ['view', 'edit']
            ]
        ],
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
    register_meta('post', 'rank_math_description', [
        'show_in_rest' => [
            'schema' => [
                'type' => 'string',
                'context' => ['view', 'edit']
            ]
        ],
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
    register_meta('post', 'rank_math_focus_keyword', [
        'show_in_rest' => [
            'schema' => [
                'type' => 'string',
                'context' => ['view', 'edit']
            ]
        ],
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('init', 'crawlaco_register_rankmath_meta');


// Yoast SEO related code
// Hook into the REST API to handle meta values for Yoast SEO when creating posts.
add_action('rest_insert_post', function ($post, $request, $creating) {
    // Check if it's a POST request and if meta data exists in the request.
    if ($creating && isset($request['meta'])) {
        $meta = $request['meta'];

        // Validate and sanitize input for Yoast SEO fields.
        $focus_keywords = isset($meta['yoast_seo_focus_keyword']) ? sanitize_text_field($meta['yoast_seo_focus_keyword']) : '';
        $seo_description = isset($meta['yoast_seo_description']) ? sanitize_textarea_field($meta['yoast_seo_description']) : '';
        $seo_title = isset($meta['yoast_seo_title']) ? sanitize_text_field($meta['yoast_seo_title']) : '';

        // Process focus keywords: split by comma and trim whitespace.
        if (!empty($focus_keywords)) {
            $keywords_array = array_map('trim', explode(',', $focus_keywords));

            // Store the first keyword as the primary focus keyword (Yoast default behavior).
            update_post_meta($post->ID, '_yoast_wpseo_focuskw', $keywords_array[0]);

            // Optionally, store all keywords in a custom meta key (for custom handling).
            update_post_meta($post->ID, '_custom_yoast_keywords', $keywords_array);
        }

        // Update the Yoast SEO description and title.
        if (!empty($seo_description)) {
            update_post_meta($post->ID, '_yoast_wpseo_metadesc', $seo_description);
        }
        if (!empty($seo_title)) {
            update_post_meta($post->ID, '_yoast_wpseo_title', $seo_title);
        }
    }
}, 10, 3);

// Hook into the WooCommerce REST API to handle Yoast SEO metadata for products.
add_action('rest_insert_product', function ($product, $request, $creating) {
    // Check if it's a POST request and if meta_data exists in the request.
    if ($creating && isset($request['meta_data'])) {
        $meta_data = $request['meta_data'];

        // Parse meta_data for Yoast SEO fields.
        $focus_keywords = '';
        $seo_description = '';
        $seo_title = '';

        foreach ($meta_data as $meta_entry) {
            if (isset($meta_entry['key']) && isset($meta_entry['value'])) {
                switch ($meta_entry['key']) {
                    case 'yoast_seo_focus_keyword':
                        $focus_keywords = sanitize_text_field($meta_entry['value']);
                        break;
                    case 'yoast_seo_description':
                        $seo_description = sanitize_textarea_field($meta_entry['value']);
                        break;
                    case 'yoast_seo_title':
                        $seo_title = sanitize_text_field($meta_entry['value']);
                        break;
                }
            }
        }

        // Process focus keywords: split by comma and trim whitespace.
        if (!empty($focus_keywords)) {
            $keywords_array = array_map('trim', explode(',', $focus_keywords));

            // Store the first keyword as the primary focus keyword (Yoast default behavior).
            update_post_meta($product->ID, '_yoast_wpseo_focuskw', $keywords_array[0]);

            // Optionally, store all keywords in a custom meta key (for custom handling).
            update_post_meta($product->ID, '_custom_yoast_keywords', $keywords_array);
        }

        // Update the Yoast SEO description and title.
        if (!empty($seo_description)) {
            update_post_meta($product->ID, '_yoast_wpseo_metadesc', $seo_description);
        }
        if (!empty($seo_title)) {
            update_post_meta($product->ID, '_yoast_wpseo_title', $seo_title);
        }
    }
}, 10, 3);
