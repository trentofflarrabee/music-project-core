<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Press Quotes custom post type.
 */
function mpc_register_press_quote_post_type() {
    $labels = [
        'name'               => __('Press Quotes', 'music-project-core'),
        'singular_name'      => __('Press Quote', 'music-project-core'),
        'add_new'            => __('Add New Quote', 'music-project-core'),
        'add_new_item'       => __('Add New Press Quote', 'music-project-core'),
        'edit_item'          => __('Edit Press Quote', 'music-project-core'),
        'new_item'           => __('New Press Quote', 'music-project-core'),
        'view_item'          => __('View Press Quote', 'music-project-core'),
        'search_items'       => __('Search Press Quotes', 'music-project-core'),
        'not_found'          => __('No press quotes found.', 'music-project-core'),
        'not_found_in_trash' => __('No press quotes found in Trash.', 'music-project-core'),
        'menu_name'          => __('Press Quotes', 'music-project-core'),
    ];

    register_post_type('mpc_press_quote', [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'mpc-homepage',
        'menu_icon' => 'dashicons-format-quote',
        'supports' => ['title', 'page-attributes'],
        'capability_type' => 'post',
        'hierarchical' => false,
        'has_archive' => false,
        'rewrite' => false,
    ]);
}
add_action('init', 'mpc_register_press_quote_post_type');

/**
 * Add meta box.
 */
function mpc_add_press_quote_meta_boxes() {
    add_meta_box(
        'mpc_press_quote_details',
        __('Press Quote Details', 'music-project-core'),
        'mpc_render_press_quote_meta_box',
        'mpc_press_quote',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mpc_add_press_quote_meta_boxes');

/**
 * Render meta box.
 */
function mpc_render_press_quote_meta_box($post) {
    wp_nonce_field('mpc_save_press_quote_meta', 'mpc_press_quote_nonce');

    $quote_text = get_post_meta($post->ID, '_mpc_press_quote_text', true);
    $source_name = get_post_meta($post->ID, '_mpc_press_quote_source_name', true);
    $source_url = get_post_meta($post->ID, '_mpc_press_quote_source_url', true);
    $featured = get_post_meta($post->ID, '_mpc_press_quote_featured', true);
    ?>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="mpc_press_quote_text">
                    <?php esc_html_e('Quote Text', 'music-project-core'); ?>
                </label>
            </th>
            <td>
                <textarea
                    id="mpc_press_quote_text"
                    name="mpc_press_quote_text"
                    class="large-text"
                    rows="5"
                    placeholder="<?php esc_attr_e('Paste the quote here.', 'music-project-core'); ?>"
                ><?php echo esc_textarea($quote_text); ?></textarea>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpc_press_quote_source_name">
                    <?php esc_html_e('Source Name', 'music-project-core'); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    id="mpc_press_quote_source_name"
                    name="mpc_press_quote_source_name"
                    class="regular-text"
                    value="<?php echo esc_attr($source_name); ?>"
                    placeholder="<?php esc_attr_e('Pitchfork, Stereogum, Local Blog, etc.', 'music-project-core'); ?>"
                >
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mpc_press_quote_source_url">
                    <?php esc_html_e('Source URL', 'music-project-core'); ?>
                </label>
            </th>
            <td>
                <input
                    type="url"
                    id="mpc_press_quote_source_url"
                    name="mpc_press_quote_source_url"
                    class="regular-text"
                    value="<?php echo esc_url($source_url); ?>"
                    placeholder="https://..."
                >
            </td>
        </tr>

        <tr>
            <th scope="row">
                <?php esc_html_e('Homepage Featured Quote', 'music-project-core'); ?>
            </th>
            <td>
                <label>
                    <input
                        type="checkbox"
                        name="mpc_press_quote_featured"
                        value="1"
                        <?php checked('1', $featured); ?>
                    >
                    <?php esc_html_e('Feature this quote on the homepage', 'music-project-core'); ?>
                </label>

                <p class="description">
                    <?php esc_html_e('If multiple quotes are featured, the first by order/date will be used for now.', 'music-project-core'); ?>
                </p>
            </td>
        </tr>
    </table>

    <?php
}

/**
 * Save meta box data.
 */
function mpc_save_press_quote_meta($post_id) {
    if (!isset($_POST['mpc_press_quote_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['mpc_press_quote_nonce'])),
        'mpc_save_press_quote_meta'
    )) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $quote_text = isset($_POST['mpc_press_quote_text'])
        ? sanitize_textarea_field(wp_unslash($_POST['mpc_press_quote_text']))
        : '';

    $source_name = isset($_POST['mpc_press_quote_source_name'])
        ? sanitize_text_field(wp_unslash($_POST['mpc_press_quote_source_name']))
        : '';

    $source_url = isset($_POST['mpc_press_quote_source_url'])
        ? esc_url_raw(wp_unslash($_POST['mpc_press_quote_source_url']))
        : '';

    $featured = !empty($_POST['mpc_press_quote_featured']) ? '1' : '0';

    update_post_meta($post_id, '_mpc_press_quote_text', $quote_text);
    update_post_meta($post_id, '_mpc_press_quote_source_name', $source_name);
    update_post_meta($post_id, '_mpc_press_quote_source_url', $source_url);
    update_post_meta($post_id, '_mpc_press_quote_featured', $featured);
}
add_action('save_post_mpc_press_quote', 'mpc_save_press_quote_meta');

/**
 * Get the featured homepage press quote.
 */
function mpc_get_featured_press_quote() {
    $quotes = get_posts([
        'post_type' => 'mpc_press_quote',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_key' => '_mpc_press_quote_featured',
        'meta_value' => '1',
        'orderby' => [
            'menu_order' => 'ASC',
            'date' => 'DESC',
        ],
        'no_found_rows' => true,
    ]);

    if (empty($quotes)) {
        return null;
    }

    $quote = $quotes[0];

    return [
        'id' => $quote->ID,
        'title' => get_the_title($quote),
        'text' => get_post_meta($quote->ID, '_mpc_press_quote_text', true),
        'source_name' => get_post_meta($quote->ID, '_mpc_press_quote_source_name', true),
        'source_url' => get_post_meta($quote->ID, '_mpc_press_quote_source_url', true),
    ];
}