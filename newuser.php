<?php

file_put_contents('log_vincenzo', $_SERVER['REQUEST_URI']);

require_once('../../../wp-load.php');
require_once('../../../wp-config.php');

function wptuts_add_rewrite_rules() {
    add_rewrite_rule('^gcm/(.*)', 'wp-content/plugins/wp_gcm_push/$matches[1]', 'top');
}

add_action('init', 'wptuts_add_rewrite_rules');

$wpdb = $GLOBALS['wpdb'];
$table_name = $wpdb->prefix . 'gcm_users';

if ($_REQUEST['regId'] != '') {
    // Inserisce dato (se duplicato da errore ma lo ignoriamo)
    if ($wpdb->query(
    "insert into $table_name (gcm_regid) "
    . "values('".$_REQUEST['regId']."')"
    )) {
        echo 1;
    } else {
        echo 0;
    }
} else if ($_REQUEST['unregId'] != '') {
    // rimuove dato (se duplicato da errore ma lo ignoriamo)
   if ( $wpdb->query(
            "DELETE FROM $table_name "
            . "WHERE gcm_regid = '" . $_REQUEST['unregId'] . "'"
    )) {
        echo 1;
    } else {
        echo 0;
    }
}	