<?php

// Register REQUEST_URI for DEBUG purposes
file_put_contents('log_gcm_origin', $_SERVER['REQUEST_URI']);

require_once('../../../wp-load.php');
require_once('../../../wp-config.php');

/**
 * Rewrite url for simpler call from android.
 * Use https://hlcs.it/gcm/newuser.php instead of 
 * https://hlcs.it/wp-content/plugins/wp_gcm_push/newuser.php
 * 
 * @TODO !!!! NOT WORKING YET !!!!
 */
function wptuts_add_rewrite_rules() {
    add_rewrite_rule('^gcm/(.*)', 'wp-content/plugins/wp_gcm_push/$matches[1]', 'top');
}
add_action('init', 'wptuts_add_rewrite_rules');

$wpdb = $GLOBALS['wpdb'];
$table_name = $wpdb->prefix . 'gcm_users';

if ($_REQUEST['regId'] != '') {
    // Insert new user REGID in database (ignore if duplicate)
    if ($wpdb->query(
    "insert into $table_name (gcm_regid) "
    . "values('".$_REQUEST['regId']."')"
    )) {
        echo 1;
    } else {
        echo 0;
    }
} else if ($_REQUEST['unregId'] != '') {
    // Remove user REGID from database (ignore in case of error)
   if ( $wpdb->query(
            "DELETE FROM $table_name "
            . "WHERE gcm_regid = '" . $_REQUEST['unregId'] . "'"
    )) {
        echo 1;
    } else {
        echo 0;
    }
}	