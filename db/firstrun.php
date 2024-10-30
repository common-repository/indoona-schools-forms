<?php
    function indoona_msc_create_db() {
        global $wpdb;

        $table_name_utenti = $wpdb->prefix . 'indoona_school_users';

        if($wpdb->get_var("show tables like '$table_name_utenti'") != $table_name_utenti) {
            $sql = "CREATE TABLE {$table_name_utenti} (
                id varchar(191) NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                pin_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                pin varchar(32) NOT NULL,
                name varchar(32),
                surname varchar(32),
                born_date varchar(32),
                born_place varchar(64),
                venue varchar(64),
                role varchar(32),
                email varchar(191),
                state smallint(6),
                curr_form int(11),
                PRIMARY KEY (id)
            )
            COLLATE utf8mb4_unicode_ci;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }

        $table_name_moduli = $wpdb->prefix . 'indoona_school_forms';

        if($wpdb->get_var("show tables like '$table_name_moduli'") != $table_name_moduli) {
            $sql = "CREATE TABLE {$table_name_moduli} (
                id int NOT NULL AUTO_INCREMENT,
                owner varchar(191),
                owner_name varchar(256),
                subject text,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                time_conf datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                date varchar(10),
                days smallint,
                note varchar(256),
                state smallint,
                state_user int,
                state_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY (id)
            )
            COLLATE utf8mb4_unicode_ci;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }

?>
