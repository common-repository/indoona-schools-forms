<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!DOCTYPE html>
<html>
<head>
    <title>indoona connect</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="indoona management page">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
<?php
    
    if ( isset($_COOKIE["indoona"]) ) {
        $user_info = explode('-', $_COOKIE["indoona"]);
        $user_id = sanitize_text_field($user_info[0]);
        $user_secure = sanitize_text_field($user_info[1]);
        $user_name = sanitize_text_field($user_info[2]);
        $user = indoona_get_msc_user( $user_id );
    } else {
        die('</head><body>Error while validating user</body></html>');
    }

    $logoUrl = plugins_url().'/indoona-connect/img/logo-violet.png';
    $backgroundUrl = plugins_url().'/indoona-schools-forms/img/bkg_schools.png';
    $backButton = plugins_url().'/indoona-schools-forms/img/back_button.png';
?>
    <style>

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Open Sans', sans-serif; padding: 0; margin: 0;
        }

        th {
            background-color: #C1D6D7;
            color: #1A1A1B;
            font-weight: normal;
        }
        
        table {
            margin: 20px 0;
        }
        
        td {
            border-bottom: 1px solid #EFEFEF;
            padding: 5px;
        }
        
        .iwp-container {
            margin: 0 auto;
            text-align: center;
            height: 100%;
        }

        .iwp-content--top__container,
        .iwp-content--bottom__container {
            display: inline-block;
            width: 100%;
            max-width: 960px;
            color: #1A1A1B;
        }

        .iwp-content--top {
            max-width: 100%;
            min-height: 280px;
            padding: 1.5em 2em;
            background: #C1D6D7 url("<?php echo $backgroundUrl; ?>") no-repeat top center;
        }

        .iwp-label--powered {
            position: absolute;
            right: 5%;
            font-size: small;
            font-weight: 100;
            color: #5B5D5F;
        }

        .iwp-label--logo {
            position: relative;
            display: inline-block;
            width: 76px;
            height: 12px;
            background-size: cover;
            background-repeat: no-repeat;
            background-image: url("<?php echo $logoUrl;?>");
        }

        .iwp-content--top__container h2 {
            margin-top: 0;
            font-weight: 100;
        }

        .iwp-content--top__container span {
            font-weight: 100;
            font-size: 1.2em;
        }

        .iwp-avatar--box {
            display: inline-block;
            padding: 2em;
            width: 128px;
        }

        .iwp-avatar--box img {
            width: 100%;
            border-radius: 50%;
            height: 128px;
            object-fit: fill;
        }
        
        .iwp-content--featured {
            padding: 20px;
            margin: 10px;
            color: #5B5D5F;
            text-align: left;
            border-bottom: 1px solid #EFEFEF;
        }

        .iwp-content--bottom {
            max-width: 100%;
            padding: 0 2em 1.5em 2em;
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
        }

        .iwp-category--list {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        .iwp-category--item {
            display: -webkit-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            align-items: center;
            padding: 1em 0;
            border-bottom: 1px solid #EFEFEF;
        }

        .iwp-category--item__name {
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            -ms-flex: 1;
            flex: 1;
            color: #5B5D5F;
            font-weight: 100;
            font-size: 1.2em;
        }

        .iwp-button {
            background-color: #3C97DE;
            color: #fff;
            display: inline-block;
            padding: 8px 16px;
            width: 80px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 100;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid transparent;
            border-radius: 4px;
            text-decoration: none;
        }

        .iwp-button--follow {
            background-color: #3C97DE;
        }

        .iwp-button--unfollow {
            background-color: #88B732;
        }
        
        .iwp-button--featured {
            background-color: #5B5D5F;
            margin: 5px 20px;
            float: right;
            color: #fff;
            padding: 8px 16px;
            width: 80px;
            font-size: 14px;
            font-weight: 100;
            line-height: 1.42857143;
            text-align: center;
            user-select: none;
            border: 1px solid transparent;
            border-radius: 4px;
            text-decoration: none;
        }

        .iwp-actions--link {
            display: -webkit-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            text-align: left;
        }

        .iwp-actions--link a:first-child {
            -webkit-box-flex: 1;
            -webkit-flex: 1;
            -ms-flex: 1;
            flex: 1;
        }

        .iwp-actions--link a{
            color: #3C97DE;
            font-weight: 100;
            text-decoration: none;
        }

        .iwp-disconnect--message {
            color: #5B5D5F;
            font-weight: 100;
            font-size: 1.2em;
        }

        @media (max-width: 479px) {
            .iwp-actions--link {
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -webkit-flex-direction: column;
                -ms-flex-direction: column;
                flex-direction: column;
                text-align: center;
            }

            .iwp-actions--link a{
                padding: 0.5em;
            }
        }

        input {
            margin: 0 5px;
            font-size: 0.8em;
            border: 1px solid #C1D6D7;
            font-family: "Open Sans", sans-serif;
            padding: 5px;
            border-radius: 4px;
        }
    
    </style>
    
    <script>

        function indoona_msc(val, name) {
            var id = $(this).attr('id');
            var data = {
                action: 'indoona_msc_mng',
                token: '<?php echo md5( $user_id.indoona_option('client_id') ); ?>',
                user: '<?php echo $user_id; ?>',
                val: val,
                name: name
            };
            
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                    if ( response == 'err' ) {
                        alert('Error. Please try again');
                        location.reload();
                    } else if ( response == 'ok') {
                    
                    } else {
                        console.log('Unknown error. Response: '+response);
                    }
                });
        }
    </script>
    <script src="<?php echo plugins_url().'/indoona-connect/js/jquery.min.js'; ?>"></script>
</head>
<body>
    <div class="iwp-container">
        <div class="iwp-content--top">
            <span class="iwp-label--powered">powered by <i class="iwp-label--logo"></i></span>
            <div class="iwp-content--top__container">
                <?php
                    $user_info = explode('-', $_COOKIE["indoona"]);
                    $user_id = $user_info[0];
                    $user_secure = $user_info[1];
                    $user_name = $user_info[2];

                    $management_link = get_home_url().'/indoona/management/?user='.$user_id.'&indoona_name='.$user_name.'&indoona_token='.$user_secure;
                ?>
                <a style="margin: 80px 0 0 -20px; position: absolute;" href="<?php echo $management_link; ?>" title="back">
                    <img src="<?php echo esc_url( $backButton ); ?>" alt="back" />
                </a>
                <div class="iwp-avatar--box">
                    <a href="<?php echo $management_link; ?>" title="back">
                        <img src="<?php echo esc_url( indoona_option('avatar_url') ); ?>" alt="indoona avatar" />
                    </a>
                </div>

                <h2><?php echo indoona_msc_option('display_name'); ?></h2>
            <span><?php _e( 'In this page you can manage your forms and edit your profile', 'indoona-schools-forms'); ?></span>
            </div>
        </div>
        
        <div class="iwp-content--bottom">
            <div class="iwp-content--bottom__container">
                <span style="float:left;padding:20px;">
                    <?php esc_html_e('Welcome', 'indoona-schools-forms'); echo ', <b>'.$user->name.' '.$user->surname.'</b>'; ?>
                </span>
                
                <?php
                    global $wpdb;
        
                    $moduli = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix . 'indoona_school_forms WHERE owner="'.$user_id.'" ORDER BY time_conf DESC' );
                
                ?>
                
                <table style="width:100%">
                    <tr>
                        <th><?php _e('Subject', 'indoona-schools-forms'); ?></th>
                        <th style="width:15%;"><?php _e('Submitted', 'indoona-schools-forms'); ?></th>
                        <th><?php _e('Status', 'indoona-schools-forms'); ?></th>
                    </tr>
                
                    <?php
                        if ( $moduli ) {
                            foreach ( $moduli as $modulo ) {
                                echo '<tr>';
                                echo '<td><b>'.$modulo->subject.'</b><br>'.$modulo->days.' '. __('days from', 'indoona-schools-forms').' '.$modulo->date.'<br><small>note: '.$modulo->note.'</small></td>';
                                if ( $modulo->time_conf == 0 ) {
                                    echo '<td>N.A.</td>';
                                } else {
                                    echo '<td>'.date_i18n( get_option( 'date_format' ), strtotime( $modulo->time_conf ) ).'<br><small>'.date_i18n( get_option( 'time_format' ), strtotime( $modulo->time_conf ) ).'</small></td>';   
                                }
                                echo '<td>'.indoona_msc_get_readable_state( $modulo->state ).'</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td>'.__('No form found', 'indoona-schools-forms').'</td><td></td><td></td></tr>';
                        }
                        ?>
                </table>
                
                <details>
                    <summary style="background-color: #C1D6D7; color: #1A1A1B;"><?php _e( 'Edit profile', 'indoona-schools-forms'); ?></summary>
                    
                    <ul class="iwp-category--list">

                        <li class="iwp-category--item"><?php _e( 'Name', 'indoona-schools-forms'); ?>: <input type="text" name="msc_nome"  onchange="indoona_msc(this.value, 'name')" value="<?php echo $user->name; ?>"></li>

                        <li class="iwp-category--item"><?php _e( 'Surname', 'indoona-schools-forms'); ?>: <input type="text" name="msc_cognome"  onchange="indoona_msc(this.value, 'surname')" value="<?php echo $user->surname; ?>"></li>

                        <li class="iwp-category--item"><?php _e( 'Birthplace', 'indoona-schools-forms'); ?>: <input type="text" name="msc_luogo_nascita" onchange="indoona_msc(this.value, 'born_place')" value="<?php echo $user->born_place; ?>"></li>

                        <li class="iwp-category--item"><?php _e( 'Date of birth', 'indoona-schools-forms'); ?>: <input type="text" name="msc_data_nascita" onchange="indoona_msc(this.value, 'born_date')" value="<?php echo $user->born_date; ?>"><small><?php _e( '(format dd/mm/yyyy)', 'indoona-schools-forms'); ?></small></li>

                       <li class="iwp-category--item"><?php _e( 'Service venue', 'indoona-schools-forms'); ?>:<input type="text" name="msc_sede" onchange="indoona_msc(this.value, 'venue')" value="<?php echo $user->venue; ?>"></li>

                        <li class="iwp-category--item"><?php _e( 'School role', 'indoona-schools-forms'); ?>: <input type="text" name="msc_ruolo" onchange="indoona_msc(this.value, 'role')" value="<?php echo $user->role; ?>"></li>

                        <li class="iwp-category--item">email:<input type="text" name="msc_email" onchange="indoona_msc(this.value, 'email')" value="<?php echo $user->email; ?>"></li>

                    </ul>
                </details>
            </div>
        </div>
        <br>
        <small>
            <?php echo indoona_msc_option('footdesc'); ?>
        </small>
        <br><br>
    </div>
    <!--  iwp-container closing -->
    </body>
</html>
