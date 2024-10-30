<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo '
    <div class="wrap">
        <div id="welcome-panel" class="welcome-panel" style="padding-bottom: 20px;background: #00867A;">
            <div class="welcome-panel-content" style="color:white;">
                <center>
                    <a href="//indoona.com" target="_blank"><img src="'.plugins_url().'/indoona-connect/img/logo-white.png" width="30%" /></a>
                    <br>
                        <strong>'.__('Welcome to the indoona plugin for school forms!', 'indoona-schools-forms').'</strong>
                    </big>
                	<br>
                    * * *
                    <br>'.
                    __('The indoona plugin for school forms allows school personnel to fill and submit forms (e.g. work permits, holiday requests, etc..) directly from their indoona chat.','indoona-schools-forms').
					'<br>'.__('This plugin requires the indoona plugin (https://wordpress.org/plugins/indoona-connect) to be installed and active on your WordPress.', 'indoona-schools-forms').
                '</center>
            </div>
        </div>';

    echo '<div class="clear"></div>';

    echo '
        <div id="welcome-panel" class="welcome-panel" style="padding-bottom: 20px;">
            <div class="welcome-panel-content">
                <p class="about-description">Join us, build cool things</p>
            </div>
                
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <ul style="padding: 10px;">
                        <li><a target="_blank" href="http://developer.indoona.com/" class="welcome-icon dashicons-admin-customizer">developer.indoona.com</a></li>
                        <li><a target="_blank" href="http://developer.indoona.com/overview" class="welcome-icon dashicons-cloud">Open Platform overview</a></li>
                        <li><a target="_blank" href="http://developer.indoona.com/documentation/guidelines" class="welcome-icon dashicons-image-filter">Guidelines</a></li>
                    </ul>  
                </div>
                <div class="welcome-panel-column">
                    <h4>'.__('Documentation', 'indoona-schools-forms').'</h4>
                    <ul>
                        <li><a target="_blank" href="https://wordpress.org/plugins/indoona-schools-forms/installation/" class="welcome-icon dashicons-admin-generic">'.__('Installation', 'indoona-schools-forms').'</a></li>
                        <li><a target="_blank" href="https://wordpress.org/plugins/indoona-schools-forms/faq/" class="welcome-icon dashicons-format-aside">'.__('FAQ', 'indoona-schools-forms').'</a></li>
                        <li><a target="_blank" href="https://wordpress.org/plugins/indoona-schools-forms/changelog/" class="welcome-icon dashicons-images-alt">'.__('Changelog', 'indoona-schools-forms').'</a></li>
                        <li><a target="_blank" href="mailto:developersupport@indoona.com" class="welcome-icon dashicons-format-status">developersupport@indoona.com</a></li>
                    </ul>
                </div>
                <div class="welcome-panel-column welcome-panel-last">
                    <div id="fb-root"></div>
                    <script>(function(d, s, id) {
                      var js, fjs = d.getElementsByTagName(s)[0];
                      if (d.getElementById(id)) return;
                      js = d.createElement(s); js.id = id;
                      js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.4&appId=251544361535439";
                      fjs.parentNode.insertBefore(js, fjs);
                    }(document, \'script\', \'facebook-jssdk\'));</script>
                    <div class="fb-page" data-href="https://www.facebook.com/IndoonaForIndooners" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="false"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/IndoonaForIndooners"><a href="https://www.facebook.com/IndoonaForIndooners">indoona</a></blockquote></div></div>
                    </div>
                </div>
            </div>
        </div>';
?>