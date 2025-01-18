<?php
/**
 * Plugin Name: Image Classifier Alt Tag
 * Description: Logs image information to the console when an image is uploaded to the media library.
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue JavaScript file for the media library.
 */
function icat_enqueue_scripts() {
    echo '<div>KKKK</div>';
    wp_enqueue_script(
        'icat-index-script',
        plugin_dir_url(__FILE__) . 'src/index.js',
        ['jquery'],
        '1.0.0',
        true
    );

    wp_localize_script('icat-index-script', 'icatAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('icat_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'icat_enqueue_scripts');

/**
 * Inject JavaScript for handling the image recognition.
 */
function icat_inject_script($post_ID, $image_url) {
    echo '<div>AAAA</div>';

    echo '<script>
        console.log("Inline script executed for post ID: ' . esc_js($post_ID) . '");
        if (typeof runImageRecognition === "function") {
            runImageRecognition("' . esc_js($image_url) . '", ' . esc_js($post_ID) . ');
        } else {
            console.error("runImageRecognition function is not defined.");
        }
    </script>';
}

/**
 * Hook into media upload and ensure compatibility.
 */
function icat_process_image($post_ID) {

        echo '<div>BBBB</div>';

    // Validate post ID
    if (!is_numeric($post_ID) || !$post_ID) {
        error_log('Invalid post ID in icat_process_image: ' . print_r($post_ID, true));
        return;
    }

    // Get attachment details
    $attachment = get_post($post_ID);
    if (!$attachment) {
        error_log('Attachment not found for Post ID: ' . $post_ID);
        return;
    }

    $mime_type = get_post_mime_type($post_ID);
    if (!$mime_type || strpos($mime_type, 'image/') === false) {
        error_log('Not an image or invalid MIME type for Post ID: ' . $post_ID);
        return;
    }

    // Get the image URL
    $image_url = wp_get_attachment_url($post_ID);
    if (!$image_url) {
        error_log('Could not retrieve URL for Post ID: ' . $post_ID);
        return;
    }

    // Inject JavaScript for handling the image recognition
    add_action('wp_enqueue_media', function () use ($post_ID, $image_url) {
        icat_inject_script($post_ID, $image_url);
    });
    
}
add_action('add_attachment', 'icat_process_image');