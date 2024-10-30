<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    $defaults   = array(
        array('wmuser', __('Welcome to our forms section!', 'indoona-schools-forms').PHP_EOL.__('Start by typing your pin (provided by the school)', 'indoona-schools-forms')),
        array('footdesc', __('For help, please contact the school secretary', 'indoona-schools-forms')),
        array('forms', __('Paid leave for exam', 'indoona-schools-forms').PHP_EOL.__('Leave for health reasons', 'indoona-schools-forms').PHP_EOL.__('Holidays', 'indoona-schools-forms')),
        array('display_name', __('Forms BOT', 'indoona-schools-forms').' - '.get_bloginfo( 'name' ))
    );

    $my_options = get_option('wp_indoona_msc');
    $conta = count($defaults);
    for ($i = 0; $i < $conta; $i++) {
        if ( $my_options[$defaults[$i][0]] == '' ) {
            $my_options[$defaults[$i][0]] = $defaults[$i][1];
            update_option('wp_indoona_msc', $my_options);
        }
    }
?>