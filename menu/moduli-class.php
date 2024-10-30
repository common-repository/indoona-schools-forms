<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Indoona_Moduli_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => __('Form', 'indoona-schools-forms'),
            'plural' => __('Forms', 'indoona-schools-forms'),
            'ajax' => false
        ));
    }

    function get_bulk_actions() {
        return array(
            'indoonasend' => __('Send message', 'indoona-schools-forms'),
            'stato_approva' => __('Approve request', 'indoona-schools-forms'),
            'stato_atto' => __('Approve request (acknowledge)', 'indoona-schools-forms'),
            'stato_nega' => __('Deny request', 'indoona-schools-forms'),
        );
    }

    function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }
        $from_email = 'wordpress@' . $sitename;
        $headers = 'From: indoona plugin for schools <'.$from_email.'>' . "\r\n";

        switch ( $action ) {
            case 'indoonasend':
                $queryarray = array();
                $count = 0;
                foreach( $_GET['modulo'] as $modulo ) {
                    $queryarray[] = $owner = indoona_msc_get_form( $modulo )->owner;
                    if ( $count == 0 ) { $nonce_id = $owner; }
                    $count++;
                }
                $querystring = implode( ',', $queryarray);
                if ( $count == 1 ) {
                    $oggetto = indoona_msc_get_form( $modulo )->subject;
                    $data = indoona_msc_get_form( $modulo )->date;
                    $giorni = indoona_msc_get_form( $modulo )->days;
                    $user = indoona_get_msc_user( indoona_msc_get_form( $modulo )->owner );
                    $name = $user->name.' '.$user->surname;
                    $indoona_pre_send = urlencode ($name.PHP_EOL.$oggetto.PHP_EOL.$giorni.' days from '.$data);
                }

                if( $querystring ) {
                    $url = add_query_arg( 'indoona_pre_send', $indoona_pre_send,  admin_url('admin.php?page=indoona_msc_send&indoona_id='.$querystring) );
                    $url = add_query_arg( 'indoona_security_check', md5($owner), $url );
                    echo '
                    <script type="text/javascript">
                        window.location = "'.$url.'"
                    </script>';
                    exit;
                }
                break;

            case 'stato_approva':

                foreach( $_GET['modulo'] as $modulo ) {
                    $owner = indoona_msc_get_form( $modulo )->owner;
                    $stato = indoona_msc_get_form( $modulo )->state;
                    $oggetto = indoona_msc_get_form( $modulo )->subject;
                    $data = indoona_msc_get_form( $modulo )->date;
                    $giorni = indoona_msc_get_form( $modulo )->days;
                    $user = indoona_get_msc_user( $owner  );
                    $name = $user->name.' '.$user->surname;
                    $message = __('REQUEST APPROVED', 'indoona-schools-forms').PHP_EOL.PHP_EOL.$name.PHP_EOL.$oggetto.PHP_EOL.$giorni.' days from '.$data;

                    if ( $stato == 1 ) {
                        $form_fields = array( 'state' => 2, 'state_user' => get_current_user_id (), 'state_time' => current_time( 'mysql' ) );
                        indoona_msc_set_form( $modulo, $form_fields );
                        indoona_tp_message_send( $owner, $owner, $message, true, false, 101 );

                        wp_mail(
                            $user->email,
                            __('[FORMS] Request update for', 'indoona-schools-forms').'"'.$oggetto.'"',
                            $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                            $headers
                        );

                        $email_scuola = indoona_msc_option('email');
                        $email_scuola = explode( ' ', $email_scuola);
                        foreach( $email_scuola as $email ) {
                            wp_mail(
                                $email,
                                __('[FORMS] Request update for', 'indoona-schools-forms').' '.$name.' - "'.$oggetto.'"',
                                $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                                $headers
                            );
                        }
                    }
                }
                break;

            case 'stato_nega':

                foreach( $_GET['modulo'] as $modulo ) {
                    $owner = indoona_msc_get_form( $modulo )->owner;
                    $stato = indoona_msc_get_form( $modulo )->state;
                    $oggetto = indoona_msc_get_form( $modulo )->subject;
                    $data = indoona_msc_get_form( $modulo )->date;
                    $giorni = indoona_msc_get_form( $modulo )->days;
                    $user = indoona_get_msc_user( $owner  );
                    $name = $user->name.' '.$user->surname;
                    $message = __('REQUEST NOT APPROVED', 'indoona-schools-forms').PHP_EOL.PHP_EOL.$name.PHP_EOL.$oggetto.PHP_EOL.$giorni.' days from '.$data;

                    if ( $stato == 1 ) {
                        $form_fields = array( 'state' => 3, 'state_user' => get_current_user_id (), 'state_time' => current_time( 'mysql' ) );
                        indoona_msc_set_form( $modulo, $form_fields );
                        indoona_tp_message_send( $owner, $owner, $message, true, false, 101 );

                        wp_mail(
                            $user->email,
                            __('[FORMS] Request update for', 'indoona-schools-forms').'"'.$oggetto.'"',
                            $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                            $headers
                        );

                        $email_scuola = indoona_msc_option('email');
                        $email_scuola = explode( ' ', $email_scuola);
                        foreach( $email_scuola as $email ) {
                            wp_mail(
                                $email,
                                __('[FORMS] Request update for', 'indoona-schools-forms').' '.$name.' - "'.$oggetto.'"',
                                $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                                $headers
                            );
                        }
                    }
                }
                break;

             case 'stato_atto':

                foreach( $_GET['modulo'] as $modulo ) {
                    $owner = indoona_msc_get_form( $modulo )->owner;
                    $stato = indoona_msc_get_form( $modulo )->state;
                    $oggetto = indoona_msc_get_form( $modulo )->subject;
                    $data = indoona_msc_get_form( $modulo )->date;
                    $giorni = indoona_msc_get_form( $modulo )->days;
                    $user = indoona_get_msc_user( $owner  );
                    $name = $user->name.' '.$user->surname;
                    $message = __('REQUEST APPROVED (ACKNOWLEDGED)', 'indoona-schools-forms').PHP_EOL.PHP_EOL.$name.PHP_EOL.$oggetto.PHP_EOL.$giorni.' days from '.$data;

                    if ( $stato == 1 ) {
                        $form_fields = array( 'state' => 4, 'state_user' => get_current_user_id (), 'state_time' => current_time( 'mysql' ) );
                        indoona_msc_set_form( $modulo, $form_fields );
                        indoona_tp_message_send( $owner, $owner, $message, true, false, 101 );

                        wp_mail(
                            $user->email,
                            __('[FORMS] Request update for', 'indoona-schools-forms').'"'.$oggetto.'"',
                            $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                            $headers
                        );

                        $email_scuola = indoona_msc_option('email');
                        $email_scuola = explode( ' ', $email_scuola);
                        foreach( $email_scuola as $email ) {
                            wp_mail(
                                $email,
                                __('[FORMS] Request update for', 'indoona-schools-forms').' '.$name.' - "'.$oggetto.'"',
                                $message.PHP_EOL.PHP_EOL.'Kind regards,'.PHP_EOL.get_bloginfo( 'name' ),
                                $headers
                            );
                        }
                    }
                }
                break;

            default:
                return;
                break;
        }

        return;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="modulo[]" value="%s" />', $item->id
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'user':
                $ret = '<p>
                <details>
                <summary><big>'.$item->owner_name.'</big></summary>
                <small>';
                if ( $item->name && $item->venue ) {
                  $ret .= __('Venue', 'indoona-schools-forms').': '.$item->venue.'
                  <br>'.__('Role', 'indoona-schools-forms').': '.$item->role.'
                  <br>'.__('Date of birth', 'indoona-schools-forms').': '.$item->born_date.'
                  <br>'.__('Birthplace', 'indoona-schools-forms').': '.$item->born_place;
                } else {
                  $ret .= __('User removed', 'indoona-schools-forms');
                }
                $ret .= '</small>
                </details>
                </p>';
                return $ret;
            case 'time':
                $return = __('Started:', 'indoona-schools-forms').'<br>'.date_i18n( get_option( 'date_format' ), strtotime( $item->time ) ).' '.date_i18n( get_option( 'time_format' ), strtotime( $item->time ) );
                $return .= '<br>'.__('Confirmed:', 'indoona-schools-forms');
                if ( $item->time_conf != "0000-00-00 00:00:00" ) {
                    $return .= '<br>'.date_i18n( get_option( 'date_format' ), strtotime( $item->time_conf ) ).' '.date_i18n( get_option( 'time_format' ), strtotime( $item->time_conf ) );
                } else {
                    $return .= '<br><span style="color:red;">'.__('Not confirmed', 'indoona-schools-forms').'</span>';
                }
                return $return;
            case 'details':
                $return = __('Subject', 'indoona-schools-forms').': <strong>'.$item->subject.'</strong>';
                $return .= '<br><strong>'.$item->days.'</strong> '.__('days starting from', 'indoona-schools-forms').' <strong>'.$item->date.'</strong>';
                $return .= '<br>'.__('Notes', 'indoona-schools-forms').': <strong>'.$item->note.'</strong>';
                return $return;
            case 'state':
                $color='none';
                if ( $item->state > 0 ) {
                    switch( $item->state ) {
                        case 1:
                            $color = 'yellow'; break;
                        case 2:
                        case 4:
                            $color = 'lightgreen'; break;
                        case 3:
                            $color = 'lightcoral'; break;
                    }
                }
                $ret = '<span style="padding: 0px 30px 2px 10px;background-color:'.$color.';">'.indoona_msc_get_readable_state( $item->state ).'</span>';

                if ( $item->state > 1 ) {
                    $ret .= '<br><small>'.__('Validated by', 'indoona-schools-forms').' '.get_userdata( $item->state_user )->display_name.'<br>'.__('on', 'indoona-schools-forms').' '.date_i18n( get_option( 'date_format' ), strtotime( $item->state_time ) ).' '.date_i18n( get_option( 'time_format' ), strtotime( $item->state_time ) );
                }
                return $ret;
            default:
                return $item->$column_name; //print_r($item, true);
        }
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'user' => __('User', 'indoona-schools-forms'),
            'time' => __('Creation date', 'indoona-schools-forms'),
            'details' => __('Details', 'indoona-schools-forms'),
            'state' => __('State', 'indoona-schools-forms')
        );
        if ( !WP_DEBUG ) {
            unset($columns['id']);
        }
        return $columns;
    }

    function prepare_items() {

        $this->process_bulk_action();

        global $wpdb;

        $table_name = $wpdb->prefix . 'indoona_school_forms';
        $table_name_users = $wpdb->prefix . 'indoona_school_users';

        $query = "SELECT $table_name.*, $table_name_users.name, $table_name_users.surname, $table_name_users.role, $table_name_users.venue, $table_name_users.born_place, $table_name_users.born_date FROM $table_name LEFT JOIN $table_name_users ON $table_name.owner=$table_name_users.id";

        if ( isset( $_POST['s'] ) && ( !empty( $_POST['s'] ) ) ) {
            $search = trim( $_POST['s'] );
            $query.= " WHERE $table_name_users.name LIKE \"%$search%\" OR $table_name_users.surname LIKE \"%$search%\"";
        }

        if ( isset( $_GET['orderby']) && !empty($_GET["orderby"]) && isset( $_GET['order']) && !empty($_GET["order"]) ) {
            $query.=' ORDER BY ' . $_GET["orderby"] . ' ' . $_GET["order"];
        } else {
            $query.=' ORDER BY '.$table_name.'.time desc';
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
        $sortable = array( 'time'  => array( 'time', false)  );

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
    }

}
 ?>
