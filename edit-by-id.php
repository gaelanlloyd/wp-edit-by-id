<?php
/**
 *
 * @author Gaelan Lloyd
 * @copyright 2026 Gaelan Lloyd
 * @license GPL v2 or later
 *
 * Plugin Name: Edit By ID
 * Description: Adds an 'Edit by ID' field to the admin bar to let you instantly edit any post.
 * Version: 1.0
 * Author: Gaelan Lloyd
 * Author URI: https://www.gaelanlloyd.com
 *
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Show for editors and above
define( 'EDIT_BY_ID_CAP', 'edit_others_posts' );

add_action( 'admin_bar_menu', function( $wp_admin_bar ) {

    // Check for user rights

    if ( ! is_admin_bar_showing() || ! current_user_can( EDIT_BY_ID_CAP ) ) {
        return;
    }

    $action = admin_url( 'admin-post.php' );
    $nonce = wp_create_nonce( 'edit_by_id' );

    ob_start();

    ?>

    <form id="edit-by-id" action="<?php echo esc_url( $action ); ?>" method="post" style="position: relative; top: 2px; display: flex; align-items: center; gap: 0; padding: 0; margin: 0;">

        <input type="hidden" name="action" value="edit_by_id_go" />

        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>" />

        <span class="ab-icon dashicons dashicons-edit" aria-hidden="true"></span>

        <span class="screen-reader-text">Edit by ID</span>

        <input name="post_id" type="text" inputmode="numeric" pattern="[0-9]*" placeholder="Edit by ID…" aria-label="Post ID" style="font-size: 12px; width: 64px; min-height: auto; padding: 2px; margin: 0; line-height: 18px;" />

    </form>

    <?php

    $html_form = ob_get_clean();

    $wp_admin_bar->add_menu( [
        'id' => 'edit-by-id',
        'parent' => 'top-secondary',
        'title' => $html_form,
        'meta' => [
            'class' => 'edit-by-id-node',
            'title' => 'Enter a post or page ID and press Enter to edit that item.',
        ],
    ] );

}, 100 );

add_action( 'admin_post_edit_by_id_go', function() {

    if ( ! current_user_can( EDIT_BY_ID_CAP ) ) {
        wp_die( 'Sorry, you are not allowed to do that.' );
    }

    check_admin_referer( 'edit_by_id' );

    if ( isset( $_POST['post_id'] ) ) {
        $id = absint( $_POST['post_id'] );
    } else {
        $id = 0;
    }

    // If ID is invalid or doesn't exist, return user to the dashboard.
    if ( ! $id || ! get_post( $id ) ) {
        wp_safe_redirect( admin_url() );
        exit;
    }

    // Show an error if user doesn't have the rights to edit this specific post.
    if ( ! current_user_can( 'edit_post', $id ) ) {
        wp_die( 'Sorry, you are not allowed to edit this item.' );
    }

    wp_safe_redirect( get_edit_post_link( $id, 'raw' ) );

    exit;

} );
