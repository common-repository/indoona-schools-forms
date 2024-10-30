<?php

    add_action( 'indoona_hook_subscribers_bulk', 'indoona_msc_subscribers_bulk' );

    function indoona_msc_subscribers_bulk( $action ) {
        if ( $action == 'indoona_modulistica_on' ) {
            foreach( $_GET['subscriber'] as $subscriber ) {
                indoona_msc_personale_add( $subscriber );
                indoona_msc_personale_contact_add( $subscriber );
                indoona_tp_message_send ( $subscriber, $subscriber, indoona_msc_option('wmuser'), true, false, 101 );
            }
        } else if ( $action == 'indoona_modulistica_off' ) {
            foreach( $_GET['subscriber'] as $subscriber ) {
                indoona_msc_personale_remove( $subscriber );
                indoona_tp_message_send( $subscriber, $subscriber, __( 'You have been disabled from using this service', 'indoona-schools-forms'), true, false, 101 );
            }
        }
        return;
    }

    function indoona_msc_personale_add( $id ) {
        global $wpdb;
        
         
        $indoona_user = indoona_get_user( $id );
        
        $wpdb->insert(
            $wpdb->prefix . 'indoona_school_users',
            array(
                'id' => $id,
                'time' => current_time( 'mysql' ),
                'pin' => date('sz', strtotime( current_time( 'mysql' )) ),
                'name' => $indoona_user->profile_name,
                'surname' => $indoona_user->profile_surname,
            ),
            array( '%s', '%s', '%s', '%s', '%s')
        );
    }

    function indoona_msc_personale_contact_add( $id,  $retry = false ) {
    
        $sdk = new Indoona\OpenSDK();
        $apiProvider = $sdk->getApiProvider();
        $appToken = Indoona\OpenPlatform\Sdk\Model\AppAccessToken::fromJson(
            indoona_tp_token()
        );
        
        $contact = new Indoona\OpenPlatform\Sdk\Model\Contact(
            101,
            indoona_msc_option('display_name'),
            plugins_url() . '/indoona-schools-forms/img/default-avatar.png',
            array('interactive')
        );

        try {
            $apiProvider->invokeContactAddApiWithAppAccessToken(
                $appToken,
                $id,
                $contact
            );
        } catch (Indoona\OpenPlatform\Sdk\Provider\Exceptions\ApiException $exc) {
            $code = $exc->getCode();
            indoona_log('ERR', 'contact_add', 'Indoona MSC error '.$code.' for '.$id);
            if ( $code == 401 && !$retry ) {
                indoona_tp_token( true ); //Refresh the app-token
                indoona_msc_personale_contact_add( $id, true );
            }
        }
        
        indoona_log('MSC', 'contact_add', 'Contact added for user '.$id);
    }
        
    function indoona_msc_personale_remove( $id ) {
        
        global $wpdb;
        $wpdb->delete(
            $table_name = $wpdb->prefix . 'indoona_school_users',
            array( 'id' => $id ),
            array( '%s' )
        );
        
    }

?>