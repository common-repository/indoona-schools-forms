<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    if ( isset($_GET['add']) ) {
        check_admin_referer( 'add-msc-user_'.$_GET['add'] );

        indoona_msc_personale_add( $_GET['add'] );
        indoona_msc_personale_contact_add( $_GET['add'] );
        indoona_tp_message_send ( $_GET['add'], $_GET['add'], indoona_msc_option('wmuser'), true, false, 101 );

    } else if ( isset($_GET['remove']) ) {
        check_admin_referer( 'remove-msc-user_'.$_GET['remove'] );

        indoona_msc_personale_remove( $_GET['remove'] );
        indoona_tp_message_send( $_GET['remove'], $_GET['remove'], __( 'You have been disabled from using this service', 'indoona-schools-forms'), true, false, 101 );

    } else if ( isset($_GET['restore']) ) {
        check_admin_referer( 'restore-msc-user_'.$_GET['restore'] );
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'indoona_school_users',
            array(
                'pin' => indoona_msc_pin_default( $_GET['restore'] )
            ),
            array( 'id' => $_GET['restore'] )
        );
        indoona_tp_message_send( $_GET['restore'], $_GET['restore'], __('Your pin has been reset by the administrator', 'indoona-schools-forms'), true, false, 101 );
    }

    require 'personale-class.php';

    $usersListTable = new Indoona_Subscribers_List_Table();

    $usersListTable->prepare_items();
    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php _e('Users', 'indoona-schools-forms'); ?></h2>
        <?php echo __('You can add or remove staff members from', 'indoona-schools-forms').' <a href="'.admin_url( 'admin.php?page=indoona_subscribers' ).'">'. __('indoona subscribers', 'indoona-schools-forms').'</a>'; ?>

        <style>
            .wp-list-table .column-id { width: 18%; }
            .wp-list-table .column-activate { width: 15%; }
        </style>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="subscribers-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $usersListTable->display() ?>
        </form>
        <?php _e('Users can change their pin with the command <strong>/pin newpin newpin</strong>', 'indoona-schools-forms'); ?>
