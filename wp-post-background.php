<?php
/**
 * Plugin Name: WP Post Background
 * Plugin URI: https://github.com/jcchavezs/wp-post-background
 * Description: This plugin allows you to add and image as background of posts, pages or custom post type in general (in the same way as a thumbnail).
 * Author: José Carlos Chávez <jcchavezs@gmail.com>
 * Author URI: http://github.com/jcchavezs
 * Github Plugin URI: https://github.com/jcchavezs/wp-post-background
 * Github Branch: master
 * Version: 1.0.0
 * Tags: wordpress, post, background, thumbnail, attachment, plugin
 */

add_action( 'plugins_loaded', 'wppb_load_textdomain' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function wppb_load_textdomain() {
  load_plugin_textdomain( 'wp-post-background', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action('admin_init', 'wppb_admin_init');

/**
 * Returns all the public post types but the 'attachment'.
 * @return array
 */
function wppb_get_public_post_types()
{
    return array_diff(get_post_types(array('public' => true), 'names'), array('attachment'));
}

/**
 * Adds the metaboxes for all the public post types.
 */
function wppb_admin_init()
{
    $post_types = wppb_get_public_post_types();

    foreach ($post_types as $post_type) {
        add_meta_box('wp-post-background', __('Background Image', 'wp-post-background'), 'wppb_render_meta_box', $post_type, 'side', 'low');
    }
}

/**
 * Creates a namespaced nonce.
 * @param  string $action
 * @return string
 */
function wppb_create_nonce($action)
{
    return wp_create_nonce('wp-post-background-' . $action);
}

/**
 * Verifies a namespaced nonce
 * @param  string $nonce
 * @param  string $action
 * @return bool
 */
function wppb_verify_nonce( $nonce, $action )
{
    return wp_verify_nonce( $nonce, 'wp-post-background-' . $action );
}

/**
 * Renders the metabox for the post background.
 */
function wppb_render_meta_box()
{
    global $post;

    $post_id = $post->ID;

    $attachment_id = get_post_meta($post->ID, '_background_id', true);

    $remove_background_nonce = wppb_create_nonce('remove-background-nonce-' . $post_id);
    ?>
    <?php if ($attachment_id): ?>
        <p class="hide-if-no-js"><a class="thickbox" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=image&amp;tab=type&amp;TB_iframe=1&amp;width=640&amp;height=310" id="set-post-background"><?php echo wp_get_attachment_image($attachment_id); ?></a></p>
        <p class="hide-if-no-js"><a onclick="WPPBRemoveBackground(<?php echo $post_id; ?>,'<?php echo $remove_background_nonce; ?>');return false;" id="remove-post-background" href="#"><?php _e('Remove the background', 'wp-post-background'); ?></a></p>
    <?php else: ?>
        <p class="hide-if-no-js"><a class="thickbox" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=image&amp;tab=type&amp;TB_iframe=1&amp;width=640&amp;height=310" id="set-post-background"><?php _e('Set background image', 'wp-post-background'); ?></a></p>
    <?php endif; ?>
    <?php
}

add_action('admin_head', 'wppb_admin_enqueue_scripts');

/**
 * Registers and enqueues the needed javascript file for the plugin.
 * @return [type] [description]
 */
function wppb_admin_enqueue_scripts() {
    wp_register_script('wp-post-background', plugins_url( 'wp-post-background.js', __FILE__ ), array('jquery', 'media-upload', 'thickbox'));
    wp_enqueue_script('wp-post-background');
}

add_filter('attachment_fields_to_edit', 'wppb_attachment_fields_to_edit', 999, 2);

/**
 * Filter the 'form_fields' variable for adding the link 'Use as post background'.
 * @param  array $form_fields   The array containing the form fields.
 * @param  WP_Post $post        The post that will attach the background image.
 * @return array                The array containing the form fields filtered.
 */
function wppb_attachment_fields_to_edit($form_fields, $post) {
    if (!isset($form_fields['image-size']) || !isset($post->ID)) {
        return $form_fields;
    }

    $attachment_id = $post->ID;

    $set_as_background_nonce = wppb_create_nonce('set-as-background-nonce-'. $attachment_id);

    $post_id = $_GET['post_id'];

    $output = "<a
                onclick='WPPBSetAsBackground({$post_id}, {$attachment_id},\"{$set_as_background_nonce}\"); return false;'
                href='#'
                id='wp-post-background-{$attachment_id}'
                class='wp-post-background'>"
                . __('Use as post background', 'wp-post-background')
                . "</a>";

    $form_fields['image-size']['extra_rows']['wp-post-background']['html'] = $output;

    return $form_fields;
}

add_action( 'wp_ajax_wppb_add_post_background', 'wppb_add_post_background' );

/**
 * Renders the output of the action of adding a background image to a post.
 */
function wppb_add_post_background()
{
    $post_id = $_POST['post_id'];
    $attachment_id = $_POST['attachment_id'];
    $set_as_background_nonce = $_POST['nonce'];

    if(!wppb_verify_nonce($set_as_background_nonce, 'set-as-background-nonce-' . $attachment_id)) {
        wp_die();
    }

    $remove_background_nonce = wppb_create_nonce('remove-background-nonce-' . $post_id);

    if (!update_post_meta($post_id, '_background_id', $attachment_id)) {
        add_post_meta($post_id, '_background_id', $attachment_id, true);
    }

    $ajax_nonce = wppb_create_nonce($post->ID);

    ?>
    <p class="hide-if-no-js"><a class="thickbox" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=image&amp;tab=type&amp;TB_iframe=1&amp;width=640&amp;height=310" id="set-post-background"><?php echo wp_get_attachment_image($attachment_id); ?></a></p>
    <p class="hide-if-no-js"><a onclick="WPPBRemoveBackground(<?php echo $post_id; ?>,'<?php echo $remove_background_nonce; ?>');return false;" id="remove-post-background" href="#"><?php _e('Remove the background','wp-post-background'); ?></a></p>
    <?php
    wp_die();
}

add_action('wp_ajax_wppb_remove_post_background', 'wppb_remove_post_background');

/**
 * Renders the output of the action of removing a background image.
 * @return [type] [description]
 */
function wppb_remove_post_background()
{
    $post_id = $_POST['post_id'];
    $remove_background_nonce = $_POST['nonce'];

    if(!wppb_verify_nonce($remove_background_nonce, 'remove-background-nonce-'. $post_id)) {
        wp_die('a');
    }

    delete_post_meta($post_id, '_background_id');
    ?>
    <p class="hide-if-no-js"><a class="thickbox" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=image&amp;tab=type&amp;TB_iframe=1&amp;width=640&amp;height=310" id="set-post-background"><?php _e('Set background image', 'wp-post-background'); ?></a></p>
    <?php
    wp_die();
}

/**
 * Gets the post background attachment ID.
 * @param  int $post_id [optional] The ID of the post.
 * @return int|null                The ID of the background attachment.
 */
function get_post_background_id($post_id = null) {
    if (!isset($post_id)) {
        global $post;
        $post_id = $post->ID;
    }

    return get_post_meta($post_id, '_background_id', true);
}

/**
 * Checks whether a post has a background attachment or not.
 * @param  int|null  $post_id [optional]   The ID of the post.
 * @return boolean
 */
function has_post_background($post_id = null) {
    if (!isset($post_id)) {
        global $post;
        $post_id = $post->ID;
    }

    return (bool) get_post_background_id($post_id);
}

/**
 * Echoes the markup for the background attachment.
 * @param  string|array $size The preferred size for the background image.
 * @param  string|array $attr The attributes for the image markup.
 * @return string             The markup of the background attachment as an image.
 */
function the_post_background($size = 'fullsize', $attr = '') {
    echo get_the_post_background(null, $size, $attr);
}

/**
 * Returns the markup for the background attachment.
 * @param  string|array $size The preferred size for the background image.
 * @param  string|array $attr The attributes for the image markup.
 * @return string             The markup of the background attachment as an image.
 */
function get_the_post_background($post_id = null, $size = 'fullsize', $attr = '') {
    if(is_array($attr)) {
        $attr['class'] = 'wp-post-background';
    } else {
        $attr .= '&class=wp-post-background';
    }

    return wp_get_attachment_image(get_post_background_id($post_id), $size, false, $attr);
}

/**
 * Returns the URL for the background attachment image.
 * @param  int     $post_id [optional] The ID of the post.
 * @param  string  $size    The preferred size for the background image.
 * @param  boolean $inherit Whether to use the parent's background in case the current post does not have a post background.
 * @return string           The url of the background image.
 */
function get_the_post_background_src($post_id = null, $size = 'fullsize', $inherit = false) {
    if (!isset($post_id)) {
        global $post;
        $post_id = $post->ID;
    }

    $attachment_id = get_post_background_id($post_id);

    if (($attachment_id == '') && $inherit) {

        $ancestors = get_post_ancestors($post_id);

        foreach ($ancestors as $ancestor) {
            $attachment_id = get_post_background_id($ancestor);

            if ($attachment_id) {
                break;
            }
        }
    }

    list($src, $width, $height) = wp_get_attachment_image_src($attachment_id, $size);

    return $src;
}