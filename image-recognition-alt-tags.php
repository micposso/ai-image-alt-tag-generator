<?php
/**
 * Plugin Name: Image Recognition Alt Tags
 * Plugin URI: https://example.com
 * Description: Automatically adds alt tags to images using TensorFlow.js for image recognition.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: MIT
 * Text Domain: image-recognition-alt-tags
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue JavaScript and dependencies
function irat_enqueue_scripts() {
    // TensorFlow.js
    wp_enqueue_script(
        'tensorflow',
        'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.0.0/dist/tf.min.js',
        [],
        null,
        true
    );

    // Plugin's custom JS
    wp_enqueue_script(
        'irat-script',
        plugin_dir_url(__FILE__) . 'js/image-recognition.js',
        ['tensorflow', 'jquery'],
        '1.0.0',
        true
    );

    // Localize script to pass data to JS
    wp_localize_script('irat-script', 'irat_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('irat_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'irat_enqueue_scripts');

// Hook into media upload
function irat_process_image($post_ID) {
    $attachment = get_post($post_ID);
    $mime_type = get_post_mime_type($post_ID);

    // Process only images
    if (strpos($mime_type, 'image/') !== false) {
        // Pass image URL and ID to the JavaScript
        echo '<script>
            if (typeof runImageRecognition === "function") {
                runImageRecognition("' . esc_url(wp_get_attachment_url($post_ID)) . '", ' . esc_js($post_ID) . ');
            }
        </script>';
    }
}
add_action('add_attachment', 'irat_process_image');

// AJAX handler to save alt tags
function irat_save_alt_text() {
    // Verify nonce for security
    check_ajax_referer('irat_nonce', 'security');

    $post_id = intval($_POST['post_id']);
    $alt_text = sanitize_text_field($_POST['alt_text']);

    if ($post_id && $alt_text) {
        // Update the alt tag
        update_post_meta($post_id, '_wp_attachment_image_alt', $alt_text);
        wp_send_json_success(['message' => 'Alt text updated successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update alt text.']);
    }
}
add_action('wp_ajax_irat_save_alt_text', 'irat_save_alt_text');

?>
