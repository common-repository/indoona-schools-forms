<?php

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
        exit();

    $options = get_option('wp_indoona_msc');
    if ( isset($options['delete_uninstall']) ) {
        delete_option( 'wp_indoona_msc_version' );
        delete_option( 'wp_indoona_msc' );

        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}indoona_school_forms" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}indoona_school_users" );
    }

?>