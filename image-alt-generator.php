<?php

/**
 * Plugin Name: Image Alt Generator
 * Plugin URI: https://github.com/Fahadahmed23/wp-image-alt-generator
 * Description: Automatically generate alt tags for images using TensorFlow.js and MobileNet
 * Version: 1.0.0
 * Author: Fahad Ahmed
 * Author URI: https://www.linkedin.com/in/fahad-ahmed-optimist/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-image-alt-generator
 * Domain Path: /languages
 *
 * @package WPImageAltGenerator
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin constants
define('WPIAG_VERSION', '1.0.0');
define('WPIAG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPIAG_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WP_Image_Alt_Generator
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_generate_alt_tag', [$this, 'ajax_generate_alt_tag']);

        // Add hooks for media upload handling
        add_filter('attachment_fields_to_edit', [$this, 'add_generate_button'], 10, 2);
        add_action('add_attachment', [$this, 'handle_new_upload']);
        add_action('wp_ajax_check_new_upload', [$this, 'check_new_upload']);
    }

    /**
     * Enqueue plugin assets
     */
    public function enqueue_assets($hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php', 'upload.php', 'media-new.php'])) {
            return;
        }

        wp_enqueue_style(
            'wp-image-alt-generator',
            WPIAG_PLUGIN_URL . 'assets/style.css',
            [],
            WPIAG_VERSION
        );

        wp_enqueue_script('tensorflow', 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs');
        wp_enqueue_script('mobilenet', 'https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet');

        wp_enqueue_script(
            'wp-image-alt-generator',
            WPIAG_PLUGIN_URL . 'assets/script.js',
            ['jquery', 'media-upload'],
            '1.0.0',
            true
        );

        wp_localize_script('wp-image-alt-generator', 'wpiagData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpiag_nonce'),
            'generateText' => __('Generate Alt Tag', 'wp-image-alt-generator'),
            'skipText' => __('Skip', 'wp-image-alt-generator'),
            'confirmText' => __('Would you like to generate an AI-powered alt tag for this image?', 'wp-image-alt-generator')
        ]);
    }

    public function handle_new_upload($attachment_id)
    {
        if (wp_attachment_is_image($attachment_id)) {
            set_transient('wpiag_new_upload_' . get_current_user_id(), $attachment_id, 30);
        }
    }

    public function check_new_upload()
    {
        check_ajax_referer('wpiag_nonce', 'nonce');

        $attachment_id = get_transient('wpiag_new_upload_' . get_current_user_id());

        if ($attachment_id) {
            delete_transient('wpiag_new_upload_' . get_current_user_id());
            $image_url = wp_get_attachment_url($attachment_id);
            wp_send_json_success([
                'id' => $attachment_id,
                'url' => $image_url
            ]);
        } else {
            wp_send_json_error();
        }
    }

    public function add_generate_button($form_fields, $post)
    {
        if (wp_attachment_is_image($post->ID)) {
            $form_fields['generate_alt'] = [
                'label' => '',
                'input' => 'html',
                'html' => sprintf(
                    '<button type="button" class="button button-primary wpiag-generate" 
                            data-id="%d" 
                            data-url="%s">
                        %s
                    </button>',
                    esc_attr($post->ID),
                    esc_url(wp_get_attachment_url($post->ID)),
                    esc_html__('Generate Alt Tag', 'wp-image-alt-generator')
                )
            ];
        }
        return $form_fields;
    }

    /**
     * AJAX handler for alt tag generation
     */
    public function ajax_generate_alt_tag(): void
    {
        check_ajax_referer('wpiag_nonce', 'nonce');

        $attachment_id = intval($_POST['attachment_id']);
        $alt_text = sanitize_text_field($_POST['alt_text']);

        if (!current_user_can('edit_post', $attachment_id)) {
            wp_send_json_error('Permission denied');
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        wp_send_json_success();
    }
}

// Initialize the plugin
new WP_Image_Alt_Generator();
