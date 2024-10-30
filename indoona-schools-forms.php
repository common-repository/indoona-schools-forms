<?php
/*
Plugin Name:  indoona schools forms
Plugin URI:  https://wordpress.org/plugins/indoona-schools-forms
Description: indoona plugin for sending and managing school forms
Version:      0.9.1
Author:       Tiscali Italia S.p.A.
Author URI:  http://www.tiscali.it/
Contributors: indoonaopenplatform
Text Domain:  indoona-schools-forms
*/

function indoona_msc_textdomain() {
   load_plugin_textdomain('indoona-schools-forms');
} add_action('plugins_loaded', 'indoona_msc_textdomain');

require 'function/subscribers_bulk_actions.php';
require 'function/callback_endpoint.php';

function indoona_msc_menu()
{
    if ( function_exists ( 'indoona_getsvg') ) {
        add_menu_page( __('School', 'indoona-schools-forms'), __('School', 'indoona-schools-forms'), 'edit_others_posts', 'indoona_schools', 'indoona_msc_main_panel', indoona_getsvg(), 25);
        add_submenu_page('indoona_schools', __('Forms', 'indoona-schools-forms'), __('Forms', 'indoona-schools-forms'), 'edit_others_posts', 'indoona_msc_forms', 'indoona_msc_forms_panel');
        add_submenu_page('indoona_schools', __('Users', 'indoona-schools-forms'), __('Users', 'indoona-schools-forms'), 'edit_others_posts', 'indoona_msc_users', 'indoona_msc_users_panel');
        add_submenu_page('indoona_schools', __('Settings'), __('Settings'), 'manage_options', 'indoona_msc_settings', 'indoona_msc_settings_panel');

        //Ghost page
        add_submenu_page( '', __('Send Message', 'indoona-schools-forms'), __('Send Message', 'indoona-schools-forms'), 'edit_others_posts', 'indoona_msc_send', 'indoona_msc_send_panel');
    } else {
        add_action( 'admin_notices', 'indoona_msc_menu_error' );
    }
} add_action('admin_menu', 'indoona_msc_menu');

function indoona_msc_menu_error() {
	$class = 'notice notice-error';
	$message = __( 'In order to use <b>indoona schools forms</b> you need the <a href="https://wordpress.org/plugins/indoona-connect/" title="indoona connect">indoona plugin</a> installed and active.', 'indoona-schools-forms' );;

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
}

function indoona_msc_main_panel() { require 'menu/main.php'; }
function indoona_msc_forms_panel() { require 'menu/moduli.php'; }
function indoona_msc_users_panel() { require 'menu/personale.php'; }

function indoona_msc_settings_panel() { require 'menu/settings.php'; }
function indoona_msc_send_panel() { require 'menu/send.php'; }

function indoona_msc_activation() { require 'db/firstrun.php'; indoona_msc_create_db(); indoona_msc_forms_array_set_default(); }
register_activation_hook(__FILE__, 'indoona_msc_activation');

function indoona_callback_subscriberslist_user( $value, $id ) {
    $banner = '';
    if ( indoona_msc_user_enabled( $id ) ) {
        $banner = '
        <a href="'.admin_url( 'admin.php?page=indoona_msc_users' ).'" title="Modulistica">
            <img style="float:right; padding-top: 15px;" src="'.plugins_url() .'/indoona-schools-forms/img/modulistica.png" />
        </a>';
        $nonceurl = wp_nonce_url( 'admin.php?page=indoona_msc_users&remove='.$id, 'remove-msc-user_'.$id );
        $after = '<a style="float: right; padding: 0 10px; opacity: 0.8;" href="'.$nonceurl.'" onclick="return confirm(\''.__('Are you sure?', 'indoona-schools-forms').'\')">'.__('Disable forms', 'indoona-schools-forms').'</a>';
    } else {
        $nonceurl = wp_nonce_url( 'admin.php?page=indoona_msc_users&add='.$id, 'add-msc-user_'.$id );
        $after = '<a style="float: right; padding: 0 20px; opacity: 0.8;" href="'.$nonceurl.'" onclick="return confirm(\''.__('Are you sure?', 'indoona-schools-forms').'\')">'.__('Enable forms', 'indoona-schools-forms').'</a>';
    }
    return $banner.$value.$after;
}
add_filter( 'indoona_hook_subscriberslist_user', 'indoona_callback_subscriberslist_user', 10, 2 );

/**
 * Disable managementApi of master plugin
 */
function indoona_template_management_api_callback() {
    return false;
}
add_filter( 'indoona_template_management_api', 'indoona_template_management_api_callback', 10, 1 );

/**
 * Function that action after /redirect db writes.
 * @param string $id id of indoona user
 */
function indoona_user_redirect_after_callback_school ( $id ) {
    if ( indoona_msc_user_enabled( $id ) ) {
        indoona_msc_personale_contact_add( $id );
        indoona_tp_message_send ( $id, $id, indoona_msc_option('wmuser'), true, false, 101 );
    }
} add_action( 'indoona_user_redirect_after', 'indoona_user_redirect_after_callback_school' );

function indoona_msc_user_enabled( $id ) {
    global $wpdb;
    $user_exist = $wpdb->get_var ('SELECT id FROM '.$wpdb->prefix . 'indoona_school_users WHERE id="'.$id.'"');

    return (boolean)$user_exist;
}

function indoons_msc_pin_expired( $id ) {
    global $wpdb;
    $user_last_access = $wpdb->get_var ('SELECT pin_timestamp FROM '.$wpdb->prefix . 'indoona_school_users WHERE id="'.$id.'"');

    if ( strtotime($user_last_access) < ( current_time( 'timestamp' )-5*60) ) {
        return true;
    } else {
        return false;
    }
}

function indoona_msc_pin( $id ) {
    global $wpdb;
    $user_pin = $wpdb->get_var ('SELECT pin FROM '.$wpdb->prefix . 'indoona_school_users WHERE id="'.$id.'"');

    if ( $user_pin ) {
        return $user_pin;
    } else {
        return indoona_msc_pin_default( $id );
    }
}

function indoona_msc_pin_default( $id ) {
    global $wpdb;
    $timestamp = $wpdb->get_var ('SELECT time FROM '.$wpdb->prefix . 'indoona_school_users WHERE id="'.$id.'"');
    return date('sz', strtotime ($timestamp) );
}

function indoona_msc_admin_init() {

    register_setting('wp_indoona_msc_options', 'wp_indoona_msc');
    $arraytbpv = get_plugin_data(__FILE__);
    $nuova_versione = $arraytbpv['Version'];
    //delete_option('wp_indoona_msc_version');
    //delete_option('wp_indoona_msc');
    if ( !get_option('wp_indoona_msc_version') ) {
        require 'defaults.php';
    }
    if ( get_option('wp_indoona_msc_version') < $nuova_versione) {
        update_option('wp_indoona_msc_version', $nuova_versione);
    }
}
add_action('admin_init', 'indoona_msc_admin_init');

function indoona_refresh_pin_timestamp( $user_id ) {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'indoona_school_users',
        array(
            'pin_timestamp' => current_time( 'mysql' )
        ),
        array( 'id' => $user_id ),
        array( '%s' ),
        array( '%s' )
    );
}

add_action( 'wp_ajax_indoona_msc_mng', 'indoona_manage_msc_mng' );
add_action( 'wp_ajax_nopriv_indoona_msc_mng', 'indoona_manage_msc_mng' );

function indoona_manage_msc_mng() {

    if (
        isset($_POST['token']) && isset($_POST['user']) && isset($_POST['val']) && isset($_POST['name']) &&
        $_POST['token'] == md5( $_POST['user'].indoona_option('client_id') )
    ) {

        $token = sanitize_text_field($_POST['token']);
        $user = sanitize_text_field($_POST['user']);
        $val = sanitize_text_field($_POST['val']);
        $field = sanitize_text_field($_POST['name']);

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'indoona_school_users',
            array(
                $field => $val
            ),
            array( 'id' => $user ),
            array( '%s' ),
            array( '%s' )
        );

        echo 'ok';
    } else { echo 'verification failed'; }

	wp_die();
}

function indoona_callback_management_body_before( $user_id ) {

    if ( ! indoona_get_msc_user( $user_id ) ) { return; }

    $user = indoona_get_msc_user( $user_id );
    $url = get_home_url().'/indoona/forms/';

    echo '<div class="iwp-content--featured">
    <a class="iwp-button--featured" id="linkaccount" href="'.$url.'">'.__('Manage', 'indoona-schools-forms').'</a>'.__( 'School forms', 'indoona-schools-forms').'<br><br></div>';
}
add_action('indoona_hook_management_body_before',  'indoona_callback_management_body_before', 10, 1 );


function indoona_scuola_template_redirect( $p ) {
    if ( $p == 'forms' ) {
        require 'interface/modulistica.php';
    }
}
add_action('indoona_hook_template_redirect',  'indoona_scuola_template_redirect', 10, 1 );
/**
 * Get the given user
 * @param  string $id indoona id of user to get
 * @return object db row object accessibile with $return->$field
 */
function indoona_get_msc_user( $id ) {
    global $wpdb;
    return $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'indoona_school_users WHERE id="'.$id.'"');
}

/**
 * Get the state of the given user
 * @param  string $id indoona id of user to get
 * @return object db row object accessibile with $return->$field
 */
function indoona_get_msc_user_state( $id ) {
    global $wpdb;
    return $wpdb->get_row('SELECT state, curr_form FROM '.$wpdb->prefix.'indoona_school_users WHERE id="'.$id.'"');
}

/**
 * Set a new state for the given user
 * @param string $user_id    indoona id of user to update
 * @param integer $state      current fms state
 * @param integer $curr_form current editing form id (primary key of _indoona_school_forms)
 */
function indoona_set_msc_user_state( $user_id, $state, $curr_form ) {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'indoona_school_users',
        array(
            'state' => $state,
            'curr_form' => $curr_form
        ),
        array( 'id' => $user_id )
    );
}

/**
 * Create a new form in the db
 * @param  string $owner indoona id of the owner
 * @return integer auto_increment id of the inserted row
 */
function indoona_msc_create_form( $owner ) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'indoona_school_forms',
        array(
            'owner' => $owner,
            'time' => current_time( 'mysql' )
        )
    );
    return $wpdb->insert_id;
}

function indoona_msc_forms_array( $id = 0 ) {

    $tipi = explode("\n", indoona_msc_option('forms') );

    if ( $id ) {
        return $tipi[$id-1];
    } else {
        return $tipi;
    }
}

function indoona_msc_forms_array_set_default() {

    if ( indoona_msc_option('forms') ) { return; }

    $my_options = get_option('wp_indoona_msc');
    $my_options['forms'] = __('Paid leave for exam', 'indoona-schools-forms').PHP_EOL.
            __('Leave for health reasons', 'indoona-schools-forms').PHP_EOL.
            __('Holidays', 'indoona-schools-forms');
    update_option( 'wp_indoona_msc', $my_options);
}

/**
 * Update a form
 * @param string $form_id if of the form to update
 * @param array $fields  fields to update: array( $field_name => $field_content )
 */
function indoona_msc_set_form( $form_id, $fields ) {
    global $wpdb;

    $wpdb->update(
        $wpdb->prefix . 'indoona_school_forms',
        $fields,
        array( 'id' => $form_id )
    );

}

function indoona_msc_get_form( $form_id ) {
    global $wpdb;

    return $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'indoona_school_forms WHERE id="'.$form_id.'"');
}

/**
 * Get an option
 * @param  string $name option name
 * @return boolean  return the value. False if null
 */
function indoona_msc_option($name)
{
    $options = get_option('wp_indoona_msc');
    if (isset($options[$name])) {
        return $options[$name];
    }
    return false;
}

function indoona_msc_get_readable_state( $int ) {
    switch ( $int ) {
        case 0:
            return __('Not completed', 'indoona-schools-forms');
        case 1:
            return __('Draft', 'indoona-schools-forms');
        case 2:
            return __('Approved', 'indoona-schools-forms');
        case 3:
            return __('Not approved', 'indoona-schools-forms');
        case 4:
            return __('Acknowledged', 'indoona-schools-forms');
    }
}


?>
