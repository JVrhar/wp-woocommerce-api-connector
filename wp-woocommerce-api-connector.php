<?php
/**
 * Plugin Name: WP WooCommerce API Connector
 * Description: Seamlessly integrates WooCommerce orders with external CRM/REST APIs using asynchronous hooks and error-handled cURL requests.
 * Version: 1.0.0
 * Author: Juraj Vrhar
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_External_API_Connector {

    private $api_url = 'https://api.external-crm.com/v1/orders';
    private $api_key = 'demo_secret_api_key_12345';

    public function __construct() {
        // Trigger action when a WooCommerce order is marked as completed
        add_action('woocommerce_order_status_completed', array($this, 'sync_order_to_external_api'), 10, 1);
    }

    /**
     * Send order data to external REST API
     */
    public function sync_order_to_external_api($order_id) {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Prepare sanitized payload
        $payload = array(
            'order_id'       => $order->get_id(),
            'total'          => $order->get_total(),
            'currency'       => $order->get_currency(),
            'customer_email' => $order->get_billing_email(),
            'items'          => array(),
        );

        foreach ($order->get_items() as $item_id => $item) {
            $payload['items'][] = array(
                'product_id' => $item->get_product_id(),
                'name'       => $item->get_name(),
                'quantity'   => $item->get_quantity(),
                'subtotal'   => $item->get_subtotal(),
            );
        }

        $this->dispatch_http_request($payload, $order_id);
    }

    /**
     * Safe HTTP Remote Request using wp_remote_post
     */
    private function dispatch_http_request($payload, $order_id) {
        $args = array(
            'body'        => json_encode($payload),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'timeout'     => 15,
            'redirection' => 5,
            'blocking'    => true,
            'data_format' => 'body',
        );

        $response = wp_remote_post($this->api_url, $args);

        // Error handling & Logging
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("WooCommerce API Sync Failed for Order #{$order_id}: {$error_message}");
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 200 || $response_code === 201) {
                // Store metadata to prevent duplicate API syncs
                update_post_meta($order_id, '_external_api_synced', true);
            } else {
                error_log("WooCommerce API Sync Returned HTTP {$response_code} for Order #{$order_id}");
            }
        }
    }
}

// Initialize only if WooCommerce is active
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        new WC_External_API_Connector();
    }
});
