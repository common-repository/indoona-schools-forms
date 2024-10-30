<?php

    if ( ! defined( 'ABSPATH' )  ) exit; // Exit if accessed directly

    if ( ! current_user_can('edit_others_posts') ) {
        die( 'Security check' ); 
    }

    echo '<div class="wrap"><h2>New message</h2>';

    if ( isset($_POST["indoona_send"]) && $_POST["indoona_send"] != '' ) {
        $count_users = $count_groups = 0;
        
        if ( isset($_GET['indoona_id']) ) {
            $subscribers = explode( ',', $_GET['indoona_id']);
            if ( $_GET['indoona_security_check'] != md5($subscribers[0]) ) { die( 'Security check' ); }
            $resources_push = array();
            foreach( $subscribers as $subscriber ) {
                indoona_tp_message_send( $subscriber, $subscriber,  $_POST["indoona_send"], true, false, 101 );
            }
        }
        echo '
        <div class="updated">
            <p>Your message have been sent!</p>
        </div>';
    } else {
        
        if ( isset($_GET['indoona_id']) ) {
            $subscribers = explode( ',', $_GET['indoona_id']);
            if ( $_GET['indoona_security_check'] != md5($subscribers[0]) ) { die( 'Security check' ); }
            echo '
            <div class="notice notice-info">
                <p>This message will be sent to the '.count($subscribers).' choosen subscribers</p>
            </div>';
        }
        if ( isset($_GET["indoona_pre_send"]) ) {
            $pre_text = $_GET["indoona_pre_send"].PHP_EOL;
        }
        echo '<form method="post" id="indoona_send">
        <textarea name="indoona_send" cols="40" rows="5">'.$pre_text.'</textarea>';
        submit_button( 'Send Now', 'primary');
        echo '</form>';
    }
    echo '</div>';

?>