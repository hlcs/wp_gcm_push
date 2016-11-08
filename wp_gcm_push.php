<?php

/**
 * @package wp_gcm_push
 * @version 1.0
 */
/*
  Plugin Name: WP GCM PUSH
  Plugin URI: 
  Description:
  Author: Vincenzo Bruno
  Version: 1.0
  Author URI: http://www.vincenzobruno.it
 */
$wpdb = $GLOBALS['wpdb'];

define('GCM_API_KEY', "");// Put your GCM APY KEY HERE. Obtain a key here https://console.developers.google.com "APIs & auth"->Credentials

// Table name where Android GCM users are stored
define('TABLE_NAME', $wpdb->prefix . 'gcm_users');

// Security check
defined('ABSPATH') or die("No script kiddies please!");

// Call our function when a post gets published the first time
add_action('draft_to_publish', 'wp_gcm_push', 10, 2);
add_action('auto-draft_to_publish', 'wp_gcm_push', 10, 2);
add_action('pending_to_publish', 'wp_gcm_push', 10, 2);

/**
 * Function called by action hook
 * 
 * @param type $ID ID of the post
 * @param type $post Post object
 */
function wp_gcm_push($ID, $post) {
    $link = get_permalink($ID);
    $title = $post->post_title;
    push_gcm($link, $title);
}

/**
 * Push notification to registered users
 * 
 * @global type $wpdb Global object for Wordpress database
 * @param type $link Permalink of the post sent to Android for notification link
 * @param type $title Title of the post sent to Android for notification text
 */ 
function push_gcm($link, $title) {
    global $wpdb;
    $table_name = TABLE_NAME;

    // Replace with real BROWSER API key from Google APIs
    $apiKey = GCM_API_KEY;

    // Seleziona gli utenti
    $users = $wpdb->get_results("SELECT * FROM $table_name LIMIT 999");
    foreach ($users as $u) {
        // Recipient's list
        $registrationIDs[] = $u->gcm_regid;
    }
    // Message to be sent
    $message = $title;

    // Set POST variables
    $url = 'https://android.googleapis.com/gcm/send';

    $fields = array(
        'registration_ids' => $registrationIDs,
        'data' => array("message" => $message, "link" => $link),
    );

    $headers = array(
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    );
    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);

    // Close connection
    curl_close($ch);

    //echo $result." <br>".json_encode( $fields );
}

// Create data structure at installation
register_activation_hook(__FILE__, 'wp_gcm_push_install');

/**
 * Function called at plugin activaction.
 * It creates the GCM users table
 *  
 */
function wp_gcm_push_install() {
    global $wpdb;

    $table_name = TABLE_NAME;

    /*
     * We'll set the default character set and collation for this table.
     * If we don't do this, some characters could end up being converted 
     * to just ?'s when saved in our table.
     */
    $charset_collate = '';

    if (!empty($wpdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if (!empty($wpdb->collate)) {
        $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql = "CREATE TABLE $table_name ( "
            . "gcm_regid varchar(255) NOT NULL, "
            . "created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, "
            . "PRIMARY KEY (gcm_regid) "
            . ") $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}
