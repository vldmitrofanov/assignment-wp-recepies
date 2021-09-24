<?php
    if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
    global $wpdb;
    $wpdb->query( "ALTER TABLE $wpdb->comments DROP COLUMN rating;");
    delete_option("my_plugin_db_version");
