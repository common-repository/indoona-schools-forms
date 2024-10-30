<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    require 'moduli-class.php';

    $moduliListTable = new Indoona_Moduli_List_Table();

    $moduliListTable->prepare_items();
    ?>
    <div class="wrap">
 
        <h2><?php _e('Forms', 'indoona-schools-forms'); ?></h2>
        
        <style>
            .wp-list-table .column-id { width: 4%; }
            .wp-list-table .column-user { width: 20%; }
            .wp-list-table .column-time { width: 15%; }
            .wp-list-table .column-state { width: 15%; }
        </style>
        
        <form method="post">
            <input type="hidden" name="page" value="subscribers_list" />
            <?php $moduliListTable->search_box( __('Search', 'indoona-schools-forms'), 'search_id'); ?>
        </form>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="moduli-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $moduliListTable->display() ?>
        </form>