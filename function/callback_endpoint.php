<?php

function indoona_msc_callback_endpoint( $data, $appid ) {
    if ( $appid != 101 ) { return; }

    $user_id = str_replace( '@indoona', '', $data['sender'] );
    if ( indoons_msc_pin_expired( $user_id ) ) {
        if ( strtolower($data['data']['body']) == indoona_msc_pin( $user_id ) || md5(strtolower($data['data']['body'])) == indoona_msc_pin( $user_id ) ) {
            indoona_refresh_pin_timestamp( $user_id );
            indoona_set_msc_user_state( $user_id, 0, 0 );
            $user = indoona_get_msc_user( $user_id  );

            $message = __( 'USER AUTHENTICATED!', 'indoona-schools-forms').PHP_EOL.__( 'You will be disconnected after 5 minutes of inactivity.', 'indoona-schools-forms').PHP_EOL;

            if ( !$user->name ) {
                indoona_set_msc_user_state( $user_id, 20, 0 );
                $action = __( 'Type your name', 'indoona-schools-forms');
                $message .= __( 'Before you begin, you need to enter some information', 'indoona-schools-forms');
            } else if ( !$user->born_place ) {
                indoona_set_msc_user_state( $user_id, 22, 0 );
                $action = __( 'Where were you born?', 'indoona-schools-forms');
                $message .= __( 'Before you begin, you need to enter some information', 'indoona-schools-forms');
            } else {
                $message .= __( 'Type "new" to start a new form or "/list" to view your forms', 'indoona-schools-forms');
            }
            indoona_tp_message_send( $user_id, $user_id, $message, true, false, 101 );
            indoona_tp_message_send( $user_id, $user_id, $action, true, false, 101 );
        } else if ( indoona_msc_user_enabled( $user_id ) ) {
            indoona_tp_message_send( $user_id, $user_id, __( 'You are not authenticated.', 'indoona-schools-forms').PHP_EOL.__( 'Type your pin to continue', 'indoona-schools-forms'), true, false, 101);
        } else {
            indoona_tp_message_send( $user_id, $user_id, __( 'You cannot use this service', 'indoona-schools-forms'), true, false, 101);
        }
    } else {
        indoona_refresh_pin_timestamp( $user_id );
        $parameters = explode( ' ', strtolower( $data['data']['body'] ) );

        if ( $parameters[0] == '/list' ) {
            global $wpdb;
            $moduli = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix . 'indoona_school_forms WHERE owner="'.$user_id.'" ORDER BY time_conf DESC' );

            if ( $moduli ) {
                foreach ( $moduli as $modulo ) {
                    $message .= $modulo->subject.PHP_EOL.$modulo->days.' '.__('days from', 'indoona-schools-forms').' '.$modulo->date.PHP_EOL.indoona_msc_get_readable_state( $modulo->state ).PHP_EOL.PHP_EOL;
                }
                indoona_tp_message_send( $user_id, $user_id, $message, true, false, 101 );
            } else {
                indoona_tp_message_send( $user_id, $user_id, __('No form found', 'indoona-schools-forms'), true, false, 101 );
            }

        } else if ( $parameters[0] == '/pin' ) {
            if ( !$parameters[1] || !$parameters[2] ) {
                indoona_tp_message_send( $user_id, $user_id, __( 'Pin error', 'indoona-schools-forms').PHP_EOL.__( 'Use the format: /pin newpin newpin', 'indoona-schools-forms'), true, false, 101 );
            } else if ( $parameters[1] == $parameters[2] ) {
              if ( strlen($parameters[1]) == 4 && is_numeric($parameters[1]) ) {
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'indoona_school_users',
                    array(
                        'pin' => md5(strtolower($parameters[1]))
                    ),
                    array( 'id' => $user_id )
                );
                indoona_refresh_pin_timestamp( $user_id );
                indoona_tp_message_send( $user_id, $user_id, __( 'New pin has been set.', 'indoona-schools-forms'), true, false, 101 );
              } else {
                indoona_tp_message_send( $user_id, $user_id, __( 'New pin must be a 4-digits number.', 'indoona-schools-forms'), true, false, 101 );
              }
            } else {
                indoona_tp_message_send( $user_id, $user_id, __( 'New pin doesn\'t match', 'indoona-schools-forms').PHP_EOL.__( 'Use the format: /pin newpin newpin', 'indoona-schools-forms'), true, false, 101 );
            }
        } else {

            $input = strtolower( $parameters[0] );
            $input_completo = $data['data']['body'];

            $curr = indoona_get_msc_user_state( $user_id );
            $stato = $curr->state; //id stato FMS corrente
            $curr_form = $curr->curr_form; //id modulo corrente

            $user = indoona_get_msc_user( $user_id  );

            if ( $input == __( 'new', 'indoona-schools-forms') && $user->name && $user->surname && $user->born_place && $user->born_date && $user->role && $user->venue && $user->email ) {

                $curr = indoona_get_msc_user_state( $user_id );
                if ( $curr->state > 0 && $curr->curr_form > 0 ) {
                    global $wpdb;
                    $wpdb->delete(
                        $table_name = $wpdb->prefix . 'indoona_school_forms',
                        array( 'id' => $curr->curr_form ),
                        array( '%s' )
                    );
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                }

                $tipi = indoona_msc_forms_array();
                $tipi_id = 1;
                foreach( $tipi as $tipo) {
                    $message .= $tipi_id++ . '. ' . $tipo . PHP_EOL;
                }

                indoona_tp_message_send( $user_id, $user_id, $message, true, false, 101 );
                indoona_tp_message_send( $user_id, $user_id, __('Type the form number you want to submit', 'indoona-schools-forms'), false, false, 101 );

                $form_id = indoona_msc_create_form( $user_id );
                $user = indoona_get_msc_user( $user_id  );
                $form_fields = array( 'owner_name' => $user->name.' '.$user->surname );
                indoona_msc_set_form( $form_id, $form_fields );

                indoona_set_msc_user_state( $user_id, 1, $form_id );

            }

            switch ( $stato ) {

                case 0:
                    //Moved to the bottom
                    break;

                case 1:

                    if ( is_numeric( $input ) && $input > 0 && $input <= count( indoona_msc_forms_array() ) ) {
                        $form_fields = array( 'subject' => indoona_msc_forms_array( $input ) );
                        indoona_msc_set_form( $curr_form, $form_fields );

                        indoona_tp_message_send( $user_id, $user_id,
                                                __('Insert the start date of the request (dd/mm/yyyy)', 'indoona-schools-forms'), false, false, 101 );

                        indoona_set_msc_user_state( $user_id, 2, $curr_form );
                    } else if ( $input != __('new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id,
                                                __( 'Typing error', 'indoona-schools-forms'), false, false, 101 );
                    }
                    break;

                case 2:
                    $checkdate = explode( '/', $input );
                    if ( checkdate ( $checkdate[1] , $checkdate[0] , $checkdate[2] ) && strlen($checkdate[2]) == 4 ) {
                        if ( mktime(0, 0, 0, $checkdate[1], $checkdate[0], $checkdate[2]) > time() && $checkdate[2] < 2100 ) {
                            $form_fields = array( 'date' => $input );
                            indoona_msc_set_form( $curr_form, $form_fields );

                            indoona_tp_message_send( $user_id, $user_id,
                                                    __('How many days are you requesting? (ex. 5)', 'indoona-schools-forms'), false, false, 101 );
                            indoona_set_msc_user_state( $user_id, 3, $curr_form );
                        } else {
                            indoona_tp_message_send( $user_id, $user_id,
                                                    __('The date must be in the future!', 'indoona-schools-forms'), false, false, 101 );
                        }
                    } else if ( $input != __('new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id,
                                                __('Invalid date, please retry (dd/mm/yyyy)', 'indoona-schools-forms'), false, false, 101 );
                    }
                    break;

                case 3:
                    if ( is_numeric( $input ) && $input > 0 ) {
                        $form_fields = array( 'days' => $input );
                        indoona_msc_set_form( $curr_form, $form_fields );

                        indoona_tp_message_send( $user_id, $user_id,
                                                __('Insert additional notes (if any, otherwise type "no")', 'indoona-schools-forms'), false, false, 101 );
                        indoona_set_msc_user_state( $user_id, 4, $curr_form );
                    } else if ( $input != __('new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id,
                                                __('Invalid number', 'indoona-schools-forms'), false, false, 101 );
                    }
                    break;

                 case 4:
                    if ( $input && strlen( $input_completo ) < 256 ) {
                        $form_fields = array( 'note' => $data['data']['body'] );
                        indoona_msc_set_form( $curr_form, $form_fields );

                        $form = indoona_msc_get_form( $curr_form );
                        $user = indoona_get_msc_user( $user_id  );

                        $message = __('SUBJECT: ', 'indoona-schools-forms').$form->subject.PHP_EOL;
                        $message .= __('I undersigned', 'indoona-schools-forms').' '.$user->name.' '.$user->surname.' '.__('born in', 'indoona-schools-forms').' '.$user->born_place.' '.__('on', 'indoona-schools-forms').' '.$user->born_date.' '.__( 'serving as', 'indoona-schools-forms').' '.$user->role.' '.__( 'at', 'indoona-schools-forms').' '.$user->venue.PHP_EOL;
                        $message .= __( 'request', 'indoona-schools-forms').PHP_EOL;
                        $message .= $form->days. ' '.__('days off starting from', 'indoona-schools-forms').' '.$form->date.PHP_EOL;
                        $message .= 'Note: '.$form->note.PHP_EOL;
                        $message .= __('Subscribed and electronically confirmed', 'indoona-schools-forms');
                        $message .= PHP_EOL.$user->name.' '.$user->surname;

                        indoona_tp_message_send( $user_id, $user_id, $message , true, false, 101 );
                        indoona_tp_message_send( $user_id, $user_id,
                                                __('Type "ok" to confirm your request and send it to the school', 'indoona-schools-forms').PHP_EOL.__('A receipt will be sent to', 'indoona-schools-forms').' '.$user->email, false, false, 101 );

                        indoona_set_msc_user_state( $user_id, 5, $curr_form );

                    } else if ( $input != __('new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id, __('details cannot exceed 256 characters', 'indoona-schools-forms'), false, false, 101 );
                    }
                    break;

                case 5:
                    if ( $input == 'ok' ) {

                        $form = indoona_msc_get_form( $curr_form );
                        $user = indoona_get_msc_user( $user_id  );

                        $message = __('SUBJECT: ', 'indoona-schools-forms').$form->subject.PHP_EOL;
                        $message .= __('I undersigned', 'indoona-schools-forms').' '.$user->name.' '.$user->surname.' '.__('born in', 'indoona-schools-forms').' '.$user->born_place.' '.__('on', 'indoona-schools-forms').' '.$user->born_date.' '.__( 'in service as', 'indoona-schools-forms').' '.$user->role.' '.__( 'at', 'indoona-schools-forms').' '.$user->venue.PHP_EOL;
                        $message .= __( 'requests', 'indoona-schools-forms').PHP_EOL;
                        $message .= $form->days. ' '.__('days starting from', 'indoona-schools-forms').' '.$form->date.PHP_EOL;
                        $message .= 'note: '.$form->note.PHP_EOL;
                        $message .= __('Subscribed and confirmed electronically', 'indoona-schools-forms');
                        $message .= PHP_EOL.$user->name.' '.$user->surname;
                        $message .= PHP_EOL.PHP_EOL.__( 'contact email', 'indoona-schools-forms').': '.$user->email;

                        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                            $sitename = substr( $sitename, 4 );
                        }
                        $from_email = 'wordpress@' . $sitename;
                        $headers = 'From: indoona plugin for schools <'.$from_email.'>' . "\r\n";

                        wp_mail(
                            $user->email,
                            __('[FORMS] Request receipt ', 'indoona-schools-forms').' '.$form->subject,
                            __('Welcome', 'indoona-schools-forms').', '.$user->name.PHP_EOL.__('here is the receipt of your request', 'indoona-schools-forms').PHP_EOL.PHP_EOL.$message,
                            $headers
                        );

                        $email_scuola = indoona_msc_option('email');
                        $email_scuola = explode( ' ', $email_scuola);
                        foreach( $email_scuola as $email ) {
                            wp_mail(
                                $email,
                                __('[FORMS] Request', 'indoona-schools-forms').' '.$form->subject.' ('.$user->name.' '.$user->surname.')',
                                $message,
                                $headers
                            );
                        }

                        $form_fields = array( 'time_conf' => current_time( 'mysql' ), 'state' => 1 );
                        indoona_msc_set_form( $curr_form, $form_fields );

                        indoona_tp_message_send( $user_id, $user_id, __('Request sent!', 'indoona-schools-forms').PHP_EOL.__('Type /list to view your forms or "new" to start a new one.', 'indoona-schools-forms'), false, false, 101 );

                        indoona_set_msc_user_state( $user_id, 0, 0 );

                    } else if ( $input != __('new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id, __('Type "ok" to confirm, "new" to cancel current form and start another one', 'indoona-schools-forms'), false, false, 101 );
                    }
                    break;

                case 20:
                    if ( $input == __( 'new', 'indoona-schools-forms') ) {
                        indoona_tp_message_send( $user_id, $user_id, 'err', false, false, 101 );
                    } else if ( strlen ( $input_completo ) > 3 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'name' => $input_completo
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('At least 4 characters are required', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 21:
                    if ( strlen ( $input_completo ) > 3 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'surname' => $input_completo
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('At least 4 characters are required', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 22:
                    if ( strlen ( $input_completo ) > 3 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'born_place' => $input_completo
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('At least 4 characters are required', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 23:
                    $checkdate = explode( '/', $input );
                    if ( checkdate ( $checkdate[1] , $checkdate[0] , $checkdate[2] ) && $checkdate[2] < 2015 && strlen($checkdate[2]) == 4 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'born_date' => $input
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('Invalid date. Use the format dd/mm/yyyy', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 24:
                    if ( strlen ( $input_completo ) > 3 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'role' => $input_completo
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('At least 4 characters are required', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 25:
                    if ( strlen ( $input_completo ) > 3 ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'venue' => $input_completo
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id, __('At least 4 characters are required', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;

                case 26:
                    if ( is_email( $input ) ) {
                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'indoona_school_users',
                            array(
                                'email' => $input
                            ),
                            array( 'id' => $user_id ),
                            array( '%s' ),
                            array( '%s' )
                        );
                        indoona_tp_message_send( $user_id, $user_id,  __( 'Thank you! To change your data visit the app management page', 'indoona-schools-forms') , false, false, 101 );
                    } else {
                        indoona_tp_message_send( $user_id, $user_id,  __( 'Invalid email', 'indoona-schools-forms'), false, false, 101 );
                    }
                    indoona_set_msc_user_state( $user_id, 0, 0 );
                    break;
                }
            $user = indoona_get_msc_user( $user_id  );

            $curr = indoona_get_msc_user_state( $user_id );
            $stato = $curr->state; //id stato FMS corrente

            $set = true;
            if ( !$user->name && $set ) {
                indoona_set_msc_user_state( $user_id, 20, 0 );
                indoona_tp_message_send( $user_id, $user_id, __( 'Type your name', 'indoona-schools-forms'), true, false, 101 );
            } else if ( !$user->surname && $set ) {
                indoona_set_msc_user_state( $user_id, 21, 0 );
                indoona_tp_message_send( $user_id, $user_id,  __( 'Type your surname', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( !$user->born_place && $set ) {
                indoona_set_msc_user_state( $user_id, 22, 0 );
                indoona_tp_message_send( $user_id, $user_id,  __( 'Where were you born?', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( !$user->born_date && $set ) {
                indoona_set_msc_user_state( $user_id, 23, 0 );
                indoona_tp_message_send( $user_id, $user_id,  __( 'When were you born? (dd/mm/yyyy format)', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( !$user->role && $set ) {
                indoona_set_msc_user_state( $user_id, 24, 0 );
                indoona_tp_message_send( $user_id, $user_id, __( 'Type your role in the school', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( !$user->venue && $set ) {
                indoona_set_msc_user_state( $user_id, 25, 0 );
                indoona_tp_message_send( $user_id, $user_id, __( 'Type your venue', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( !$user->email && $set ) {
                indoona_set_msc_user_state( $user_id, 26, 0 );
                indoona_tp_message_send( $user_id, $user_id, __( 'Type your email', 'indoona-schools-forms'), true, false, 101 ); return;
            } else if ( $stato == 0 && $input != __( 'new', 'indoona-schools-forms') && $input != 'ok' ) {
                indoona_tp_message_send( $user_id, $user_id,  __( 'Type "new" to start a new form', 'indoona-schools-forms'), false, false, 101 );
                indoona_set_msc_user_state( $user_id, 0, 0 );
            }
        }
    }

}
add_action( 'indoona_hook_endpoint', 'indoona_msc_callback_endpoint', 10, 2 );

?>
