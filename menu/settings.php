<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap">
    <h1><?php _e('Settings'); ?></h1>
    <?php
        if( isset($_GET['settings-updated']) ) {
            echo '
            <div id="message" class="updated notice notice-success is-dismissible">
                <p>'.__('Settings saved!', 'indoona-schools-forms').'</p>
            </div>';
        }
    ?>
    <hr>
    <form method="post" action="options.php">
        <?php
            settings_fields( 'wp_indoona_msc_options');
            $options=get_option( 'wp_indoona_msc');
            //indoona_contact_add();
        ?>
        <table class="form-table">
            
            <tr valign="top">
                <th scope="row">
                    <label for="wmuser"><?php _e('Welcome message', 'indoona-schools-forms'); ?></label>
                </th>
                <td>
                    <textarea id="wmuser" rows="4" columns="55" class="widefat" name="wp_indoona_msc[wmuser]"><?php echo indoona_msc_option('wmuser'); ?></textarea>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="email"><?php _e( 'School email address', 'indoona-schools-forms'); ?>
                        <br><small><?php _e( 'for forms notifications', 'indoona-schools-forms'); ?></small> </label>
                </th>
                <td>
                    <?php if ( !indoona_msc_option('email') ) { $style='style="border: 5px solid red;" ';} ?>
                    <input id="email" type="text" <?php echo $style; ?>name="wp_indoona_msc[email]"
                           value="<?php echo indoona_msc_option('email'); ?>" size="55" />
                    <br><small><?php _e( 'Enter the list of school email addresses (space separated) where the forms notifications are to be sent
', 'indoona-schools-forms'); ?></small>
                </td>
                </td>
            </tr>
    
            <tr valign="top">
                <th scope="row">
                    <label for="footdesc"><?php _e( 'Footer text', 'indoona-schools-forms'); ?>
                        <br><small><?php _e( 'shown in forms page', 'indoona-schools-forms'); ?></small> </label>
                </th>
                <td>
                    <textarea id="footdesc" rows="4" columns="55" class="widefat" name="wp_indoona_msc[footdesc]"><?php echo indoona_msc_option('footdesc'); ?></textarea>
                    <br><small><?php _e( 'You can use HTML tags', 'indoona-schools-forms'); ?></small>
                </td>
            </tr>
    
            <tr valign="top">
                <th scope="row">
                    <label for="forms"><?php _e( 'Forms list', 'indoona-schools-forms'); ?></label>
                </th>
                <td>
                    <?php indoona_msc_forms_array_set_default(); ?>
                    <textarea id="forms" rows="4" columns="55" class="widefat" name="wp_indoona_msc[forms]"><?php echo indoona_msc_option('forms'); ?></textarea>
                    <br><small><?php _e( 'List of form subjects (one per line) that your school allows to create via chat', 'indoona-schools-forms'); ?><br><?php _e( 'Every form follows the same chat pattern (subject + start day + number of days + details)', 'indoona-schools-forms'); ?></small>
                </td>
            </tr>
            
        </table>

        <h2><?php _e( 'BOT details', 'indoona-schools-forms'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="display_name"><?php _e( 'Display name', 'indoona-schools-forms'); ?></label>
                </th>
                <td>
                    <input id="display_name" type="text" name="wp_indoona_msc[display_name]"
                           value="<?php echo indoona_msc_option('display_name'); ?>" size="55" />
                    <br><small><?php _e( 'Enter the name of your bot (it will be shown in users address book)
', 'indoona-schools-forms'); ?></small> </td>
            </tr>
            
        </table>

        <h2><?php _e( 'Advanced settings', 'indoona-schools-forms'); ?></h2>

         <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="delete_uninstall"><?php _e( 'Uninstall', 'indoona-schools-forms'); ?></label>
                </th>
                <td>
                    <input id="delete_uninstall" name="wp_indoona_msc[delete_uninstall]" type="checkbox" value="1" <?php checked( '1', indoona_msc_option('delete_uninstall') ); ?> />
                    <br> <small><?php _e( 'Delete indoona tables and options on plugin uninstall', 'indoona-schools-forms'); ?></small> </td>
            </tr>
        </table>


        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /> </p>
    </form>
</div>
