<?php
/**
 * Plugin Name: Lost Password - WP REST API
 * Description: A simple plugin to handle lost password requests via email.
 * Version: 1.2
 * Author: Kenan İLGÜN
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register the endpoints
add_action('rest_api_init', function () {
    register_rest_route('lostpassword/v1', '/request', array(
        'methods' => 'POST',
        'callback' => 'handle_lost_password_request',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('lostpassword/v1', '/validate', array(
        'methods' => 'POST',
        'callback' => 'validate_reset_code',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('lostpassword/v1', '/reset', array(
        'methods' => 'POST',
        'callback' => 'reset_user_password',
        'permission_callback' => '__return_true',
    ));
});

// Handle the lost password request
function handle_lost_password_request(WP_REST_Request $request) {
    $user_email = $request->get_param('email');

    if (email_exists($user_email)) {
        // Generate a 6-digit numeric code
        $reset_code = mt_rand(100000, 999999); // Generates a random number between 100000 and 999999

        // Store the code and the timestamp in user meta for later verification
        $user = get_user_by('email', $user_email);
        update_user_meta($user->ID, 'lost_password_code', $reset_code);
        update_user_meta($user->ID, 'lost_password_code_timestamp', time()); // Store current timestamp

        // Send the code via email
        $subject = 'Your Password Reset Code';
        $message = "Your password reset code is: $reset_code";

        wp_mail($user_email, $subject, $message);

        // Return a JSON response with the reset code
        return new WP_REST_Response(array('reset_code' => $reset_code), 200);
    } else {
        return new WP_REST_Response(array('error' => 'Email not found.'), 404);
    }
}

// Validate the reset code
function validate_reset_code(WP_REST_Request $request) {
    $user_email = $request->get_param('email');
    $submitted_code = $request->get_param('code');

    if (email_exists($user_email)) {
        $user = get_user_by('email', $user_email);
        $stored_code = get_user_meta($user->ID, 'lost_password_code', true);
        $timestamp = get_user_meta($user->ID, 'lost_password_code_timestamp', true);

        // Check if the code is valid and if it's within the 10-minute limit
        if ($stored_code && $stored_code == $submitted_code) {
            if (time() - $timestamp <= 600) { // 600 seconds = 10 minutes
                return new WP_REST_Response(array('status' => 'approved'), 200);
            } else {
                return new WP_REST_Response(array('status' => 'not approved', 'error' => 'Code expired.'), 403);
            }
        } else {
            return new WP_REST_Response(array('status' => 'not approved'), 403);
        }
    } else {
        return new WP_REST_Response(array('error' => 'Email not found.'), 404);
    }
}

// Reset the user's password
function reset_user_password(WP_REST_Request $request) {
    $user_email = $request->get_param('email');
    $submitted_code = $request->get_param('code');
    $new_password = $request->get_param('new_password');

    if (email_exists($user_email)) {
        $user = get_user_by('email', $user_email);
        $stored_code = get_user_meta($user->ID, 'lost_password_code', true);
        $timestamp = get_user_meta($user->ID, 'lost_password_code_timestamp', true);

        // Validate the reset code
        if ($stored_code && $stored_code == $submitted_code) {
            if (time() - $timestamp <= 600) { // 600 seconds = 10 minutes
                // Update the user's password
                wp_set_password($new_password, $user->ID);
                return new WP_REST_Response(array('status' => 'password reset successfully'), 200);
            } else {
                return new WP_REST_Response(array('status' => 'not approved', 'error' => 'Code expired.'), 403);
            }
        } else {
            return new WP_REST_Response(array('status' => 'not approved'), 403);
        }
    } else {
        return new WP_REST_Response(array('error' => 'Email not found.'), 404);
    }
}