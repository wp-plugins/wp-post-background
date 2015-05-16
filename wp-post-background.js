/**
 * Calls the action for setting a background attachment to a post.
 * @param {int} post_id       The ID of the post.
 * @param {int} attachment_id The ID of the background attachment.
 * @param {string} nonce      The nonce for the AJAX action.
 */
function WPPBSetAsBackground(post_id, attachment_id, nonce){
    jQuery.ajax({
        type:'POST',
        url: ajaxurl,
        data:{
            'action': 'wppb_add_post_background',
            'post_id': post_id,
            'nonce': nonce,
            'attachment_id': attachment_id
        },
        dataType:'html',
        success: function(data){
            jQuery('#wp-post-background .inside', window.parent.document).html(data);

            window.parent.tb_remove();
        }
    });
}

/**
 * Calls the action for removing a background attachment from a post.
 * @param {int} post_id     The ID of the post.
 * @param {string} nonce    The nonce for the AJAX action.
 */
function WPPBRemoveBackground(post_id, nonce){
    jQuery.ajax({
        type:'POST',
        url: ajaxurl,
        data:{
            'action': 'wppb_remove_post_background',
            'post_id': post_id,
            'nonce': nonce
        },
        dataType:'html',
        success: function(data){
            jQuery('#wp-post-background .inside', window.parent.document).html(data);
        }
    });
}