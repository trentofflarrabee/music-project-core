<?php
/**
 * Page Title Presentation.
 *
 * Core owns the per-Page presentation override and resolution logic.
 * Frontend markup and styling belong to the active theme.
 *
 * @package MusicProjectCore
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the supported Page Title presentation styles.
 *
 * @return array
 */
function mpc_get_page_title_style_options()
{
    return [
        'standard'        => __('Standard', 'music-project-core'),
        'editorial-panel' => __('Editorial Panel', 'music-project-core'),
        'minimal-overlay' => __('Minimal Overlay', 'music-project-core'),
    ];
}

/**
 * Determine whether a Page is excluded from Page Title Presentation.
 *
 * The static front page, Posts page, and assigned Link Hub Page have their
 * own presentation/routing responsibilities.
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function mpc_is_page_title_presentation_excluded($page_id)
{
    $page_id = absint($page_id);

    if (!$page_id || 'page' !== get_post_type($page_id)) {
        return true;
    }

    $front_page_id = absint(get_option('page_on_front'));
    $posts_page_id = absint(get_option('page_for_posts'));

    if ($front_page_id && $page_id === $front_page_id) {
        return true;
    }

    if ($posts_page_id && $page_id === $posts_page_id) {
        return true;
    }

    if (function_exists('mpc_get_link_hub_page_id')) {
        $link_hub_page_id = absint(mpc_get_link_hub_page_id());

        if ($link_hub_page_id && $page_id === $link_hub_page_id) {
            return true;
        }
    }

    return false;
}

/**
 * Get a Page's stored Page Title style override.
 *
 * Returns "default" when no explicit valid override exists.
 *
 * @param int $page_id Page ID.
 * @return string
 */
function mpc_get_page_title_style_override($page_id)
{
    $page_id = absint($page_id);

    if (!$page_id || 'page' !== get_post_type($page_id)) {
        return 'default';
    }

    $value = sanitize_key(
        (string) get_post_meta(
            $page_id,
            '_mpc_page_title_style',
            true
        )
    );

    $allowed = array_keys(mpc_get_page_title_style_options());

    return in_array($value, $allowed, true)
        ? $value
        : 'default';
}

/**
 * Resolve the effective Page Title presentation style.
 *
 * Resolution order:
 * 1. Special Pages always resolve to Standard.
 * 2. Explicit per-Page override.
 * 3. Global Theme Style setting.
 * 4. Standard fallback.
 *
 * @param int $page_id Page ID.
 * @return string
 */
function mpc_get_page_title_style($page_id)
{
    $page_id = absint($page_id);
    $allowed = array_keys(mpc_get_page_title_style_options());

    if (
        !$page_id
        || 'page' !== get_post_type($page_id)
        || mpc_is_page_title_presentation_excluded($page_id)
    ) {
        return 'standard';
    }

    $override = mpc_get_page_title_style_override($page_id);

    if ('default' !== $override) {
        return $override;
    }

    $global_style = 'standard';

    if (function_exists('mpc_get_theme_style_setting')) {
        $global_style = sanitize_key(
            (string) mpc_get_theme_style_setting(
                'page_title_style',
                'standard'
            )
        );
    }

    return in_array($global_style, $allowed, true)
        ? $global_style
        : 'standard';
}

/**
 * Register the Page Title Presentation meta box.
 *
 * @param WP_Post $post Current Page.
 * @return void
 */
function mpc_add_page_title_presentation_meta_box($post)
{
    if (
        !($post instanceof WP_Post)
        || mpc_is_page_title_presentation_excluded($post->ID)
    ) {
        return;
    }

    add_meta_box(
        'mpc-page-title-presentation',
        __('Page Title Presentation', 'music-project-core'),
        'mpc_render_page_title_presentation_meta_box',
        'page',
        'side',
        'default',
        [
            '__block_editor_compatible_meta_box' => true,
        ]
    );
}
add_action(
    'add_meta_boxes_page',
    'mpc_add_page_title_presentation_meta_box'
);

/**
 * Render the Page Title Presentation meta box.
 *
 * @param WP_Post $post Current Page.
 * @return void
 */
function mpc_render_page_title_presentation_meta_box($post)
{
    $override = mpc_get_page_title_style_override($post->ID);
    $styles   = mpc_get_page_title_style_options();

    wp_nonce_field(
        'mpc_save_page_title_presentation',
        'mpc_page_title_presentation_nonce'
    );
    ?>
    <p>
        <label for="mpc_page_title_style">
            <strong>
                <?php esc_html_e('Title Style', 'music-project-core'); ?>
            </strong>
        </label>
    </p>

    <p>
        <select
            name="mpc_page_title_style"
            id="mpc_page_title_style"
            class="widefat"
        >
            <option
                value="default"
                <?php selected($override, 'default'); ?>
            >
                <?php esc_html_e('Use Theme Default', 'music-project-core'); ?>
            </option>

            <?php foreach ($styles as $value => $label) : ?>
                <option
                    value="<?php echo esc_attr($value); ?>"
                    <?php selected($override, $value); ?>
                >
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p class="description">
        <?php
        esc_html_e(
            'Overrides only the title style for this Page. Panel tone, strength, and title size continue to use the global Theme Style settings.',
            'music-project-core'
        );
        ?>
    </p>
    <?php
}

/**
 * Save the per-Page Page Title presentation override.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Page object.
 * @param bool    $update  Whether this is an existing post being updated.
 * @return void
 */
function mpc_save_page_title_presentation_meta(
    $post_id,
    $post,
    $update
) {
    unset($update);

    if (!($post instanceof WP_Post) || 'page' !== $post->post_type) {
        return;
    }

    if (
        defined('DOING_AUTOSAVE')
        && DOING_AUTOSAVE
    ) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (
        !isset($_POST['mpc_page_title_presentation_nonce'])
        || !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash(
                    $_POST['mpc_page_title_presentation_nonce']
                )
            ),
            'mpc_save_page_title_presentation'
        )
    ) {
        return;
    }

    /*
     * Special Pages intentionally ignore Page Title overrides.
     * Preserve any previously stored value in case the Page later stops
     * serving a special WordPress role.
     */
    if (mpc_is_page_title_presentation_excluded($post_id)) {
        return;
    }

    $value = isset($_POST['mpc_page_title_style'])
        ? sanitize_key(
            wp_unslash($_POST['mpc_page_title_style'])
        )
        : 'default';

    if ('default' === $value) {
        delete_post_meta(
            $post_id,
            '_mpc_page_title_style'
        );

        return;
    }

    $allowed = array_keys(mpc_get_page_title_style_options());

    if (!in_array($value, $allowed, true)) {
        delete_post_meta(
            $post_id,
            '_mpc_page_title_style'
        );

        return;
    }

    update_post_meta(
        $post_id,
        '_mpc_page_title_style',
        $value
    );
}
add_action(
    'save_post_page',
    'mpc_save_page_title_presentation_meta',
    10,
    3
);