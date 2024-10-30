<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Indoona_Subscribers_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => __('Subscriber', 'indoona-schools-forms'),
            'plural' => __('Subscribers', 'indoona-schools-forms'),
            'ajax' => false
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'user':
                $profile_avatar_url = ($item->profile_avatar_url)?$item->profile_avatar_url:plugins_url() .'/indoona-connect/img/user_avatar.png';
                $nonceurl = wp_nonce_url( 'admin.php?page=indoona_msc_users&remove='.$item->id, 'remove-msc-user_'.$item->id );
                ( $item->profile_name || $item->profile_surname ) ? $dn = '<big>'.$item->profile_name.' '.$item->profile_surname.'</big>' : $dn = $item->name.' '.$item->surname.'<br><small>('.__('Disconnected', 'indoona-schools-forms').')</small>';
                $after = '<a href="'.$nonceurl.'" onclick="return confirm(\''.__('Are you sure?', 'indoona-schools-forms').'\')">'.__('Remove', 'indoona-schools-forms').'</a>';
                return '<img style="float:left;width: 50px;border-radius: 50px;padding:15px;" src="'.$profile_avatar_url.'"/>
                <br>'.$dn.'<br>'.$after;
            case 'profilo':
                return $item->name.' '.$item->surname.'<br><small>'.__('Born in', 'indoona-schools-forms').' </small>'.$item->born_place.'<small> '.__('on', 'indoona-schools-forms').' </small>'.$item->born_date.'<br><small>'.__('Venue', 'indoona-schools-forms').': </small>'.$item->venue.'<br><small>'.__('Role', 'indoona-schools-forms').': </small>'.$item->role.'<br><small>Email: </small>'.$item->email;
            case 'pin':
                if ( $item->pin == indoona_msc_pin_default( $item->id) ) {
                    return $item->pin;
                } else {
                    $nonceurl = wp_nonce_url( 'admin.php?page=indoona_msc_users&restore='.$item->id, 'restore-msc-user_'.$item->id );
                    return '('.__('pin changed', 'indoona-schools-forms').')<br><small><a href="'.$nonceurl.'" onclick="return confirm(\''.__('Are you sure to reset this pin?', 'indoona-schools-forms').' ('.$item->profile_name.')\')">'.__('Reset', 'indoona-schools-forms').'</a></small>';
                }
            case 'last_access':
                return strtotime( $item->pin_timestamp ) > 0 ? date_i18n( get_option( 'date_format' ), strtotime( $item->pin_timestamp ) ).'<br>'.date_i18n( get_option( 'time_format' ), strtotime( $item->pin_timestamp ) ) : __('Never', 'indoona-schools-forms');
            case 'activate':
                return date_i18n( get_option( 'date_format' ), strtotime( $item->time ) ).'<br>'.date_i18n( get_option( 'time_format' ), strtotime( $item->time ) );
            default:
                return $item->$column_name; //print_r($item, true);
        }
    }

    function get_columns() {
        $columns = array(
            'user' => __('User', 'indoona-schools-forms'),
            'profilo' => __('Profile', 'indoona-schools-forms'),
            'pin' => 'Pin',
            'last_access' => __('Last access', 'indoona-schools-forms'),
            'activate' => __('Activation date', 'indoona-schools-forms')
        );
        if ( !WP_DEBUG ) {
            unset($columns['id']);
        }
        return $columns;
    }

    function prepare_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'indoona_school_users';
        $table_name_users = $wpdb->prefix . 'indoona_users';

        $query = "SELECT $table_name.*, $table_name_users.profile_name, $table_name_users.profile_surname, $table_name_users.profile_avatar_url, name, surname, venue, born_date, born_place, role FROM $table_name LEFT JOIN $table_name_users ON $table_name.id=$table_name_users.id";

        if ( isset( $_GET['orderby']) && !empty($_GET["orderby"]) && isset( $_GET['order']) && !empty($_GET["order"]) ) {
            $query.=' ORDER BY ' . $_GET["orderby"] . ' ' . $_GET["order"];
        }

        $totalitems = $wpdb->query($query);

        $perpage = $this->get_items_per_page( 'subscribers_per_page', 10 );
        $paged = $this->get_pagenum();

        $totalpages = ceil($totalitems / $perpage);

        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query.=' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array( 'activate'  => array( 'time', false),  'user'  => array( 'profile_name', false)  );

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
    }

}
 ?>
