<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WSAEFeedbackNotice' ) ) {
    class WSAEFeedbackNotice
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            // Do not wrap in is_admin(): at plugins_loaded, is_admin() is often still false.
            add_action( 'admin_notices', array( $this, 'wsae_admin_notice_for_reviews' ) );
            add_action( 'wp_ajax_wsae_dismiss_notice', array( $this, 'wsae_dismiss_review_notice' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'wsae_register_feedback_assets' ) );
        }

        /**
         * Register feedback assets on admin screens (enqueue only when the notice displays).
         */
        public function wsae_register_feedback_assets()
        {
            wp_register_style(
                'wsae-feedback-style',
                WSAE_URL . 'admin/feedback/assests/css/wsae-feedback-style.css',
                array(),
                WSAE_VERSION
            );

            wp_register_script(
                'wsae-feedback-script',
                WSAE_URL . 'admin/feedback/assests/js/wsae-feedback-script.js',
                array( 'jquery' ),
                WSAE_VERSION,
                true
            );
        }

        /**
         * Enqueue dismiss script with a fresh nonce when the notice is shown.
         */
        private function wsae_enqueue_feedback_assets()
        {
            wp_enqueue_style( 'wsae-feedback-style' );
            wp_enqueue_script( 'wsae-feedback-script' );
            wp_localize_script(
                'wsae-feedback-script',
                'wsaeFeedback',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'action'  => 'wsae_dismiss_notice',
                    'nonce'   => wp_create_nonce( 'wsae_dismiss_notice' ),
                )
            );
        }

        /**
         * AJAX callback for review notice dismiss.
         */
        public function wsae_dismiss_review_notice()
        {
            check_ajax_referer( 'wsae_dismiss_notice', 'nonce' );

            if ( ! current_user_can( 'update_plugins' ) ) {
                wp_send_json_error(
                    array(
                        'message' => __( 'Forbidden.', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                    ),
                    403
                );
            }

            update_option( 'wsae-alreadyRated', 'yes' );

            wp_send_json_success(
                array(
                    'dismissed' => true,
                )
            );
        }

        /**
         * Admin review notice (wp-admin only — not shown on the public site).
         */
        public function wsae_admin_notice_for_reviews()
        {
            if ( ! current_user_can( 'update_plugins' ) ) {
                return;
            }

            if ( 'yes' === get_option( 'wsae-alreadyRated' ) ) {
                return;
            }

            $installation_date = get_option( 'wsae-installDate' );
            if ( empty( $installation_date ) ) {
                $installation_date = gmdate( 'Y-m-d H:i:s' );
                update_option( 'wsae-installDate', $installation_date );
            }

            $install_timestamp = strtotime( $installation_date );
            if ( false === $install_timestamp ) {
                $install_timestamp = time();
                update_option( 'wsae-installDate', gmdate( 'Y-m-d H:i:s', $install_timestamp ) );
            }

            $diff_days = (int) floor( ( time() - $install_timestamp ) / DAY_IN_SECONDS );

            /**
             * Minimum days after install before showing the review notice.
             *
             * @param int $min_days Default 3.
             */
            $min_days = (int) apply_filters( 'wsae_feedback_notice_min_days', 3 );

            if ( $diff_days < $min_days ) {
                return;
            }

            $this->wsae_enqueue_feedback_assets();

            echo wp_kses_post( $this->wsae_create_notice_content() );
        }

        /**
         * Generated review notice HTML (dismiss AJAX uses wsaeFeedback from wp_localize_script).
         *
         * @return string
         */
        private function wsae_create_notice_content()
        {
            $wrap_cls = 'notice notice-info is-dismissible';
            $p_name   = __( 'Web Stories Widgets For Elementor', 'shortcodes-for-amp-web-stories-and-elementor-widget' );
            $like_it_text = __( 'Rate Now! ★★★★★', 'shortcodes-for-amp-web-stories-and-elementor-widget' );
            $already_rated_text = esc_html__( 'I already rated it', 'shortcodes-for-amp-web-stories-and-elementor-widget' );
            $not_interested = esc_html__( 'Not Interested', 'shortcodes-for-amp-web-stories-and-elementor-widget' );
            $p_link = esc_url(
                'https://wordpress.org/plugins/shortcodes-for-amp-web-stories-and-elementor-widget/#reviews'
            );

            $message = sprintf(
                /* translators: %s: plugin name */
                __( 'Thanks for using <b>%1$s</b> - WordPress plugin. We hope you liked it!<br/>Please give us a quick rating, it works as a boost for us to keep working on more <a href="https://coolplugins.net" target="_blank" rel="noopener noreferrer"><strong>Cool Plugins</strong></a>!<br/>', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                esc_html( $p_name )
            );

            $html = '<div class="cool-feedback-notice-wrapper %1$s">
        <div class="message_container">%2$s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%3$s" class="like_it_btn button button-primary" target="_blank" rel="noopener noreferrer" title="%4$s">%4$s</a></li>
            <li class="already_rated"><a href="#" class="already_rated_btn button wsae_dismiss_notice" role="button" title="%5$s">%5$s</a></li>
            <li class="already_rated"><a href="#" class="already_rated_btn button wsae_dismiss_notice" role="button" title="%6$s">%6$s</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';

            return sprintf(
                $html,
                $wrap_cls,
                $message,
                $p_link,
                esc_html( $like_it_text ),
                $not_interested,
                $already_rated_text
            );
        }
    }
}