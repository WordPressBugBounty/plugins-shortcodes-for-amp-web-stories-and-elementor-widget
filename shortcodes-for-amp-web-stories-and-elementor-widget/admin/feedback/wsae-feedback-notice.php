<?php

if (!class_exists("WSAEFeedbackNotice")) {
    class WSAEFeedbackNotice
    {
        /**
         * The Constructor
         */
        public function __construct()
        {
            if (is_admin()) {
                add_action("admin_notices", [
                    $this,
                    "wsae_admin_notice_for_reviews",
                ]);
                add_action("wp_ajax_wsae_dismiss_notice", [
                    $this,
                    "wsae_dismiss_review_notice",
                ]);
                add_action("admin_enqueue_scripts", [
                    $this,
                    "wsae_load_assets",
                ]);
            }
        }

        public function wsae_load_assets()
        {

          

          wp_register_style( 'wsae-feedback-style', WSAE_URL. 'admin/feedback/assests/css/wsae-feedback-style.css' );


            wp_register_script(
                "wsae-feedback-script",
                WSAE_URL . "admin/feedback/assests/js/wsae-feedback-script.js",
                ["jquery"],
                WSAE_VERSION,
                true
            );

            wp_localize_script("wsae-feedback-script", "wsaeFeedback", [
                "ajaxUrl" => admin_url("admin-ajax.php"),
                "action" => "wsae_dismiss_notice",
            ]);


           
        }

        /**
         * Load script to dismiss notices.
         *
         * @return void
         */

        // ajax callback for review notice
        public function wsae_dismiss_review_notice()
        {
            $rs = update_option("wsae-alreadyRated", "yes");
            echo json_encode(["success" => "true"]);
            exit();
        }
        // admin notice
        public function wsae_admin_notice_for_reviews()
        {
            if (!current_user_can("update_plugins")) {
                return;
            }
            // get installation dates and rated settings
            $installation_date = get_option("wsae-installDate");
            $alreadyRated =
                get_option("wsae-alreadyRated") != false
                    ? get_option("wsae-alreadyRated")
                    : "no";

            // check user already rated
            if ($alreadyRated == "yes") {
                return;
            }

            // grab plugin installation date and compare it with current date
            $display_date = date("Y-m-d h:i:s");
            $install_date = new DateTime($installation_date);
            $current_date = new DateTime($display_date);
            $difference = $install_date->diff($current_date);
            $diff_days = $difference->days;

            // check if installation days is greator then week
            if (isset($diff_days) && $diff_days >= 3) {
                  wp_enqueue_script("wsae-feedback-script");
                 wp_enqueue_style('wsae-feedback-style');
                echo $this->wsae_create_notice_content();
              
            }
        }

        // generated review notice HTML
        function wsae_create_notice_content()
        {
            $ajax_url = admin_url("admin-ajax.php");
            $ajax_callback = "wsae_dismiss_notice";
            $wrap_cls = "notice notice-info is-dismissible";
            $p_name = "Web Stories Widgets For Elementor";
            $like_it_text = "Rate Now! ★★★★★";
            $already_rated_text = esc_html__(
                "I already rated it",
                "cool-timeline"
            );
            $not_interested = esc_html__("Not Interested", "ect");
            $not_like_it_text = esc_html__(
                "No, not good enough, i do not like to rate it!",
                "cool-timeline"
            );
            $p_link = esc_url(
                "https://wordpress.org/plugins/shortcodes-for-amp-web-stories-and-elementor-widget/#reviews"
            );
            $pro_url = esc_url(
                "https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fthe-events-calendar-templates-and-shortcode-wordpress-plugin%2F20143286"
            );

            $message = "Thanks for using <b>$p_name</b> - WordPress plugin. We hope you liked it !  
    <br/>Please give us a quick rating, it works as a boost for us to keep working on more 
    <a href='https://coolplugins.net' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";

            $html = '<div data-ajax-url="%6$s" data-ajax-callback="%7$s" class="cool-feedback-notice-wrapper %1$s">
        <div class="message_container">%3$s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%4$s" class="like_it_btn button button-primary" target="_new" title="%5$s">%5$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button wsae_dismiss_notice" title="%8$s">%8$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button wsae_dismiss_notice" title="%9$s">%9$s</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';

            return sprintf(
                $html,
                $wrap_cls, // %1$s
                $p_name, // %2$s (removed usage)
                $message, // %3$s
                $p_link, // %4$s
                $like_it_text, // %5$s
                $ajax_url, // %6$s
                $ajax_callback, // %7$s
                $already_rated_text, // %8$s
                $not_interested // %9$s
            );
        }
    }
}
