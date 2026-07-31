<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Quotes / Testimonials custom post type.
 */
function mpc_register_press_quote_post_type() {
$labels = [
    'name'                  => __('Quotes / Testimonials', 'music-project-core'),
    'singular_name'         => __('Quote / Testimonial', 'music-project-core'),
    'menu_name'             => __('Quotes / Testimonials', 'music-project-core'),
    'name_admin_bar'        => __('Quote / Testimonial', 'music-project-core'),
    'add_new'               => __('Add New', 'music-project-core'),
    'add_new_item'          => __('Add New Quote / Testimonial', 'music-project-core'),
    'new_item'              => __('New Quote / Testimonial', 'music-project-core'),
    'edit_item'             => __('Edit Quote / Testimonial', 'music-project-core'),
    'view_item'             => __('View Quote / Testimonial', 'music-project-core'),
    'all_items'             => __('All Quotes / Testimonials', 'music-project-core'),
    'search_items'          => __('Search Quotes / Testimonials', 'music-project-core'),
    'parent_item_colon'     => __('Parent Quotes / Testimonials:', 'music-project-core'),
    'not_found'             => __('No quotes or testimonials found.', 'music-project-core'),
    'not_found_in_trash'    => __('No quotes or testimonials found in Trash.', 'music-project-core'),
    'featured_image'        => __('Quote / Testimonial Image', 'music-project-core'),
    'set_featured_image'    => __('Set quote/testimonial image', 'music-project-core'),
    'remove_featured_image' => __('Remove quote/testimonial image', 'music-project-core'),
    'use_featured_image'    => __('Use as quote/testimonial image', 'music-project-core'),
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
        __('Quote / Testimonial Details', 'music-project-core'),
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
                    <?php esc_html_e('Quote / Testimonial', 'music-project-core'); ?>
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
                    <?php esc_html_e('Source / Client', 'music-project-core'); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    id="mpc_press_quote_source_name"
                    name="mpc_press_quote_source_name"
                    class="regular-text"
                    value="<?php echo esc_attr($source_name); ?>"
placeholder="<?php esc_attr_e('Client name, venue, publication, blog, etc.', 'music-project-core'); ?>"                >
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
                <?php esc_html_e('Featured Quote / Testimonial', 'music-project-core'); ?>
            </th>
            <td>
                <label>
                    <input
                        type="checkbox"
                        name="mpc_press_quote_featured"
                        value="1"
                        <?php checked('1', $featured); ?>
                    >
<?php esc_html_e('Feature this quote/testimonial on the homepage', 'music-project-core'); ?>                </label>

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
 * Get the first non-empty quote meta value from a list of keys.
 *
 * This supports current and legacy meta keys without modifying stored data.
 *
 * @param int      $post_id Quote post ID.
 * @param string[] $keys    Meta keys in priority order.
 * @return mixed
 */
function mpc_get_press_quote_meta_value($post_id, $keys) {
    foreach ($keys as $key) {
        $value = get_post_meta($post_id, $key, true);

        if ($value !== '' && $value !== null) {
            return $value;
        }
    }

    return '';
}

/**
 * Get normalized data for a quote/testimonial.
 *
 * Current meta keys are checked first, followed by known legacy keys.
 *
 * @param int|WP_Post $quote Quote post ID or post object.
 * @return array|null Normalized quote data, or null for an invalid post.
 */
function mpc_get_press_quote_data($quote) {
    $quote = get_post($quote);

    if (!$quote instanceof WP_Post || $quote->post_type !== 'mpc_press_quote') {
        return null;
    }

    $text = mpc_get_press_quote_meta_value(
        $quote->ID,
        [
            '_mpc_press_quote_text',
            '_mpc_quote_text',
        ]
    );

    if ($text === '') {
        $text = get_the_excerpt($quote);

        if ($text === '') {
            $text = wp_strip_all_tags($quote->post_content);
        }
    }

    $source_name = mpc_get_press_quote_meta_value(
        $quote->ID,
        [
            '_mpc_press_quote_source_name',
            '_mpc_press_quote_source',
            '_mpc_press_quote_client',
            '_mpc_quote_source',
        ]
    );

    $context = mpc_get_press_quote_meta_value(
        $quote->ID,
        [
            '_mpc_press_quote_context',
            '_mpc_press_quote_publication',
            '_mpc_press_quote_role',
            '_mpc_quote_context',
        ]
    );

    $featured_value = mpc_get_press_quote_meta_value(
        $quote->ID,
        [
            '_mpc_press_quote_featured',
        ]
    );

    $featured = in_array(
        strtolower((string) $featured_value),
        ['1', 'yes', 'true', 'on'],
        true
    );

    return [
        'id'          => $quote->ID,
        'title'       => get_the_title($quote),
        'text'        => (string) $text,
        'source_name' => (string) $source_name,
        'source_url'  => (string) get_post_meta(
            $quote->ID,
            '_mpc_press_quote_source_url',
            true
        ),
        'context'     => (string) $context,
        'featured'    => $featured,
    ];
}

/**
 * Get the featured homepage quote/testimonial.
 */
function mpc_get_featured_press_quote() {
    $quotes = get_posts([
        'post_type'      => 'mpc_press_quote',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_mpc_press_quote_featured',
        'meta_value'     => '1',
        'orderby'        => [
            'menu_order' => 'ASC',
            'date'       => 'DESC',
        ],
        'no_found_rows'  => true,
    ]);

    if (empty($quotes)) {
        return null;
    }

    return mpc_get_press_quote_data($quotes[0]);
}

/**
 * Press Quote admin columns.
 */

function mpc_press_quote_admin_columns($columns) {
    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
$new_columns['title'] = __('Title', 'music-project-core');
$new_columns['quote_text'] = __('Quote / Testimonial', 'music-project-core');
$new_columns['quote_source'] = __('Source / Client', 'music-project-core');
$new_columns['quote_featured'] = __('Featured', 'music-project-core');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_mpc_press_quote_posts_columns', 'mpc_press_quote_admin_columns');

function mpc_press_quote_admin_column_content($column, $post_id) {
    if ($column === 'quote_text') {
        $quote = get_post_meta($post_id, '_mpc_press_quote_text', true);

        if ($quote) {
            echo esc_html(wp_trim_words($quote, 18));
        } else {
            echo '<span aria-hidden="true">—</span>';
        }
    }

    if ($column === 'quote_source') {
        $source_name = get_post_meta($post_id, '_mpc_press_quote_source_name', true);
        $source_url = get_post_meta($post_id, '_mpc_press_quote_source_url', true);

        if ($source_name && $source_url) {
            printf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($source_url),
                esc_html($source_name)
            );
        } elseif ($source_name) {
            echo esc_html($source_name);
        } else {
            echo '<span aria-hidden="true">—</span>';
        }
    }

    if ($column === 'quote_featured') {
        $featured = get_post_meta(
            $post_id,
            '_mpc_press_quote_featured',
            true
        );

        if ($featured) {
            printf(
                '<strong style="color:#008a20;">%s</strong>',
                esc_html__(
                    'Yes',
                    'music-project-core'
                )
            );
        } else {
            printf(
                '<span style="color:#646970;">%s</span>',
                esc_html__(
                    'No',
                    'music-project-core'
                )
            );
        }
    }
}
add_action(
    'manage_mpc_press_quote_posts_custom_column',
    'mpc_press_quote_admin_column_content',
    10,
    2
);
/**
 * Press Quote featured filter.
 */
function mpc_press_quote_admin_filters($post_type) {
    if ($post_type !== 'mpc_press_quote') {
        return;
    }

    $current = isset($_GET['mpc_quote_featured_filter'])
        ? sanitize_key($_GET['mpc_quote_featured_filter'])
        : '';
    ?>
    <select name="mpc_quote_featured_filter">
<option value=""><?php esc_html_e('All Quotes / Testimonials', 'music-project-core'); ?></option>
<option value="featured" <?php selected($current, 'featured'); ?>>
    <?php esc_html_e('Featured Only', 'music-project-core'); ?>
</option>
<option value="not_featured" <?php selected($current, 'not_featured'); ?>>
    <?php esc_html_e('Not Featured', 'music-project-core'); ?>
</option>    </select>
    <?php
}
add_action('restrict_manage_posts', 'mpc_press_quote_admin_filters');

function mpc_press_quote_admin_filter_query($query) {
    global $pagenow;

    if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query()) {
        return;
    }

    $post_type = $query->get('post_type');

    if ($post_type !== 'mpc_press_quote') {
        return;
    }

    $filter = isset($_GET['mpc_quote_featured_filter'])
        ? sanitize_key($_GET['mpc_quote_featured_filter'])
        : '';

    if ($filter === 'featured') {
        $query->set('meta_query', [
            [
                'key' => '_mpc_press_quote_featured',
                'value' => '1',
                'compare' => '=',
            ],
        ]);
    }

    if ($filter === 'not_featured') {
        $query->set('meta_query', [
            'relation' => 'OR',
            [
                'key' => '_mpc_press_quote_featured',
                'value' => '1',
                'compare' => '!=',
            ],
            [
                'key' => '_mpc_press_quote_featured',
                'compare' => 'NOT EXISTS',
            ],
        ]);
    }
}
add_action('pre_get_posts', 'mpc_press_quote_admin_filter_query');

/**
 * Auto-fill quote title when left blank.
 */
function mpc_press_quote_auto_title($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ($post->post_type !== 'mpc_press_quote') {
        return;
    }

    if ($post->post_status === 'auto-draft') {
        return;
    }

    if (trim($post->post_title) !== '') {
        return;
    }

    $quote = get_post_meta($post_id, '_mpc_press_quote_text', true);
    $source = get_post_meta($post_id, '_mpc_press_quote_source_name', true);

    $title_parts = [];

    if ($quote) {
        $title_parts[] = wp_trim_words($quote, 8, '…');
    }

    if ($source) {
        $title_parts[] = $source;
    }

    $new_title = $title_parts
        ? implode(' — ', $title_parts)
        : sprintf(
            /* translators: %d is the quote or testimonial post ID. */
            __(
                'Quote / Testimonial #%d',
                'music-project-core'
            ),
            $post_id
        );

    remove_action('save_post_mpc_press_quote', 'mpc_press_quote_auto_title', 20);

    wp_update_post([
        'ID' => $post_id,
        'post_title' => $new_title,
    ]);

    add_action('save_post_mpc_press_quote', 'mpc_press_quote_auto_title', 20, 3);
}
add_action('save_post_mpc_press_quote', 'mpc_press_quote_auto_title', 20, 3);