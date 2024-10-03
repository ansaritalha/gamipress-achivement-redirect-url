<?php
/**
 * Plugin Name: Gamipress Achievement Redirect
 * Description: Redirects users to a specific post URL upon unlocking achievements with GamiPress.
 * Version: 1.0
 * Author: Talha Ansari
 * Author URI:  https://www.fiverr.com/ansari_talha?up_rollout=true
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Enqueue the JavaScript code in the footer
function custom_redirect_on_rank_unlock() {
    // Create a nonce for security
    $nonce = wp_create_nonce('achievement_redirect_nonce');
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $(document).ajaxComplete(function(event, xhr, settings) {
            // Check if the AJAX request is related to unlocking an achievement
            if (settings.data && settings.data.indexOf('action=gamipress_unlock_achievement_with_points') !== -1) {
                var params = new URLSearchParams(settings.data);
                var achievement_id = params.get('achievement_id'); // Capture the achievement_id from the request

                if (achievement_id) {
                    console.log('Achievement ID:', achievement_id);

                    // Send the achievement_id to the server to get the corresponding post URL
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_custom_post_url',
                            achievement_id: achievement_id,
                            nonce: '<?php echo $nonce; ?>' // Pass the nonce for verification
                        },
                        success: function(response) {
                            // Check if a post URL was returned
                            if (response) {
                                // Redirect to the custom post URL
                                window.location.href = response;
                            } else {
                                console.log('No URL returned for the achievement ID.');
                            }
                        },
                        error: function() {
                            console.log('Failed to get the custom post URL.');
                        }
                    });
                }
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'custom_redirect_on_rank_unlock');

// PHP handler to get the custom post URL
function get_custom_post_url() {
    // Check nonce for security
    check_ajax_referer('achievement_redirect_nonce', 'nonce');

    // Check if achievement_id is set
    $achievement_id = isset($_POST['achievement_id']) ? absint($_POST['achievement_id']) : 0;

    // Check if the achievement_id corresponds to a specific post ID
    // If achievement_id directly corresponds to a post ID, do the following:
    $post_id = $achievement_id; // Use achievement_id as post ID

    // Get the permalink of the post
    $post_url = get_permalink($post_id);

    if ($post_url) {
        echo esc_url($post_url); // Return the permalink of the post, escaping the URL
    } else {
        echo ''; // No post found
    }

    wp_die(); // End AJAX processing
}
add_action('wp_ajax_get_custom_post_url', 'get_custom_post_url');
add_action('wp_ajax_nopriv_get_custom_post_url', 'get_custom_post_url');