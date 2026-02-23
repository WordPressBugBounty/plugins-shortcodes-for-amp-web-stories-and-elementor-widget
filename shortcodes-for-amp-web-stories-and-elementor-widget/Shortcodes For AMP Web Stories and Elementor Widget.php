<?php
/**
 * Plugin Name: Web Stories Widgets For Elementor
 * Description: Web Stories Shortcodes for recent Story [Recent-stories column="3" show-button="yes"     show-no-of-story="all" button-text="View" order="DESC" btn-color="#0063a6" btn-text-color="#f6f3ef" style="default" border-color="#BA0109" border-width="1px"].
 * Plugin URI:  https://coolplugins.net
 * Version:     1.2.6
 * Author:      Cool Plugins
 * Author URI:  https://coolplugins.net/
 * Text Domain: shortcodes-for-amp-web-stories-and-elementor-widget   
 * License:GPLv2 or later
 * License URI:http://www.gnu.org/licenses/gpl-2.0.html
 * Elementor tested up to: 3.35.5
*/

use Google\Web_Stories\Story_Renderer\HTML;
use Google\Web_Stories\Model\Story;
if (!defined('ABSPATH')) {
    exit;
}

if (defined('WSAE_VERSION')) {
    return;
}

define('WSAE_VERSION', '1.2.6');
define('WSAE_FILE', __FILE__);
define('WSAE_PATH', plugin_dir_path(WSAE_FILE));
define('WSAE_URL', plugin_dir_url(WSAE_FILE));

register_activation_hook(WSAE_FILE, array('Webstory_Widget_Addon', 'WSAE_activate'));
register_deactivation_hook(WSAE_FILE, array('Webstory_Widget_Addon', 'WSAE_deactivate'));

/**
 * Class Webstory_Widget_Addon
 */
final class Webstory_Widget_Addon
{

    /**
     * Plugin instance.
     *
     * @var Webstory_Widget_Addon
     * @access private
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return Webstory_Widget_Addon
     * @static
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @access private
     */
    private function __construct()
    {
        //Load the plugin after Elementor (and other plugins) are loaded. 
        add_action('wp_enqueue_scripts', array($this, 'wsae_reg_script'));

        
        add_action( 'plugins_loaded', array($this, 'WSAE_plugins_loaded') );
        add_shortcode('webstory', array($this, 'wsae_webstory_call'));
        add_shortcode('Recent-stories', array($this, 'wsae_recent_webstory_call'));
        add_filter('manage_web-story_posts_columns', array($this, 'set_custom_edit_wsae_columns'));
        add_action('manage_web-story_posts_custom_column', array($this, 'custom_wsae_column'), 10, 2);

    }
    function set_custom_edit_wsae_columns($columns) {
            
            $columns['shortcode'] = __( 'Shortcode', 'shortcodes-for-amp-web-stories-and-elementor-widget' );
            
            return $columns;
        }
        function custom_wsae_column( $columns, $post_id ) {
            if($columns=='shortcode'){
                echo '<code>[webstory id="'.esc_attr($post_id).'"]</code>';
            }
        }    

        function wsae_reg_script(){
            wp_register_script('wsae-standalone-amp-story-player-script', WSAE_URL . 'assets/js/amp-story-player-v0.js', [], 'v0', false);
            wp_register_style('wsae-standalone-amp-story-player-style', WSAE_URL . 'assets/css/amp-story-player-v0.css', [], 'v0');

        }
    public function wsae_recent_webstory_call($atts){
         // Load styles and scripts only if shortcodes are used in the content
        if (!class_exists('\Google\Web_Stories\Plugin')) {
            return '<p>' . __('Error: Web Stories plugin is not activated.', 'shortcodes-for-amp-web-stories-and-elementor-widget') . '</p>';
        }
        wp_enqueue_style( 'wsae-standalone-amp-story-player-style' );
        wp_enqueue_script( 'wsae-standalone-amp-story-player-script' );
        wp_enqueue_style('standalone-custom-style', WSAE_URL . 'assets/css/wsae-custom-styl.css', [], WSAE_VERSION);

         $atts = shortcode_atts( array( 
             'column'=>'3',
             'show-no-of-story'=>'all',
             'show-button'=>'yes',
             'button-text'=>'Default view',
             'order'=>'DESC',
             'btn-color'=>'#046bd2',
             'btn-text-color'=>' #ffffff',
             'style'=> 'default',
             'border-color'=>'none',
             'border-width'=>'0px',

         ), $atts, 'wsae' );

            // Sanitize the values
            $atts['column'] = intval($atts['column']); // Ensure column is an integer
            if (is_numeric($atts['show-no-of-story'])) {
                $atts['show-no-of-story'] = intval($atts['show-no-of-story']); // Sanitize as integer
            } else {
                $atts['show-no-of-story'] = sanitize_text_field($atts['show-no-of-story']); // Sanitize as string (e.g., 'all')
            }
            $atts['show-button'] = sanitize_text_field($atts['show-button']);
            $atts['button-text'] = sanitize_text_field($atts['button-text']);
            $atts['order'] = sanitize_text_field($atts['order']);
            $atts['style'] = sanitize_text_field($atts['style']);
            $atts['border-width'] = sanitize_text_field($atts['border-width']);

            // Sanitize colors using a similar approach
            if (sanitize_hex_color($atts['btn-color'])) {
                $atts['btn-color'] = sanitize_hex_color($atts['btn-color']); // Valid hex color
            } else {
                $atts['btn-color'] = sanitize_text_field($atts['btn-color']); // Sanitize as string
            }
            
            if (sanitize_hex_color($atts['btn-text-color'])) {
                $atts['btn-text-color'] = sanitize_hex_color($atts['btn-text-color']); // Valid hex color
            } else {
                $atts['btn-text-color'] = sanitize_text_field($atts['btn-text-color']); // Sanitize as string
            }

            if (sanitize_hex_color($atts['border-color'])) {
                $atts['border-color'] = sanitize_hex_color($atts['border-color']); // Valid hex color
            } else {
                $atts['border-color'] = sanitize_text_field($atts['border-color']); // Sanitize as string
            }

         $html = '';

         
       

            require WSAE_PATH . 'includes/wsae-recent-story.php';
            return $html;
        
    }
   public function wsae_webstory_call($atts){
        wp_enqueue_style( 'wsae-standalone-amp-story-player-style' );
        wp_enqueue_script( 'wsae-standalone-amp-story-player-script' );
        wp_enqueue_style('standalone-custom-style', WSAE_URL . 'assets/css/wsae-custom-styl.css', [], WSAE_VERSION);

         $atts = shortcode_atts( array(
            'id'=>'', 
            'show-button'=>'yes',            
            'button-text'=>'Default view',             
            'style'=> 'default',
            'border-color'=>'none',
            'border-width'=>'0px',
        
         ), $atts, 'wsae' );

          // Sanitize attributes
          $atts['id'] = intval($atts['id']); // Expecting 'id' to be numeric
          $showbtn = ($atts['show-button'] === "yes") ? 'block' : 'none';
          $atts['button-text'] = sanitize_text_field($atts['button-text']);
          $atts['style'] = sanitize_text_field($atts['style']);
          $atts['border-width'] = sanitize_text_field($atts['border-width']);
          
          // Add sanitization for border color
          if (sanitize_hex_color($atts['border-color'])) {
              $atts['border-color'] = sanitize_hex_color($atts['border-color']); // Valid hex color
          } else {
              $atts['border-color'] = sanitize_text_field($atts['border-color']); // Sanitize as string
          }

         $html = '';
        
          
        if(empty($atts['id'])){
            return;

        }
          
         $current_post = get_post($atts['id']);     
         $story = new Story();
         $story->load_from_post( $current_post );  
         $args ='';
         $html='';
         $defaults     = [
                 'align'  => 'none',
                 'height' => 600,
                 'width'  => 360,
             ];
        $args         = wp_parse_args( $args, $defaults );
        $align        = sprintf( 'align%s', $args['align'] );
        $url          = $story->get_url();
        $title        = $story->get_title();
        $poster       = ! empty( $story->get_poster_portrait() ) ? esc_url( $story->get_poster_portrait() ) : '';
        $margin       = ( 'center' === $args['align'] ) ? 'auto' : '0';
        $player_style = sprintf( 'width: %dpx;height: %dpx;margin: %s', absint( $args['width'] ), absint( $args['height'] ), esc_attr( $margin ) );
        $poster_style = ! empty( $poster ) ? sprintf( '--story-player-poster: url(%s)', $poster ) : '';
        $borderWidth = $atts['border-width'];
        $borderWidthValue = (int) $borderWidth;
        // $wsae_circle = $atts['style'] == "circle" ? 'wsae_circle' : '';
        $imageSrc = esc_url($poster);
        if($poster == ""){
        $imageSrc = esc_url(WSAE_URL . 'assets/images/default_poster.png');
        }

        if (
            ( function_exists( 'amp_is_request' ) && amp_is_request() ) ||
            ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() )
            ) {
                $player_style = sprintf( 'margin: %s', esc_attr( $margin ) );            
            }
            
            if($atts['style'] === 'circle'){
            $html.='   <div class="wp-block-web-stories-embed '.esc_attr( $align ).'">';
            $html.='   <a href="' . esc_url($url) . '" style="text-decoration:none;"> <img src="' . esc_url($imageSrc) . '" alt="' . esc_attr($title) . '" style="width:100px; height:100px; border-radius:50%; border:'.esc_attr($borderWidthValue).'px solid '. esc_attr($atts['border-color']).';">
            </a>';
            $html.=' </div>';
       }else{
            $html.='   <div class="wp-block-web-stories-embed '.esc_attr( $align ).'">';
            $html.='<amp-story-player width="'.esc_attr( $args['width'] ).'" height="'.esc_attr( $args['height'] ).'" style="'.esc_attr( $player_style ).'">
                        <a href="'. esc_url( $url ).'" style="'.esc_attr( $poster_style ).'">'.esc_html( $title ).'</a>
                    </amp-story-player>
                    <a href="' . esc_url($url) . '" ><button class="wae_btn_setting" style="display:'. esc_attr($showbtn) .';">'. esc_html($atts['button-text']) .'</button></a>';
            $html.=' </div>';
          }
        
        return $html;

   	
   }

    /**
     * Code you want to run when all other plugins loaded.
    */
	function WSAE_plugins_loaded() {
		
		// Notice if the Elementor is not active
	/*	if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array($this, 'WSAE_fail_to_load') );
			return;
		}*/
         if ( ! class_exists( '\Google\Web_Stories\Plugin' ) ) {
            add_action('admin_notices', array($this, 'wsae_webstory_fail_to_load'));
            return;

        }
       
		
		// Require the main plugin file
      require( __DIR__ . '/includes/class-WSAE.php' );
      
          if( is_admin() ){
			
			require_once(__DIR__ . '/admin/feedback/wsae-feedback-notice.php');
			new WSAEFeedbackNotice();
			
			}
	
	
    }	// end of ctla_loaded()
    
    
	function WSAE_fail_to_load() { 
        
        if (!is_plugin_active( 'elementor/elementor.php' ) ) : ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php 
				// translators: %s: URL to Elementor plugin page
				echo wp_kses_post( sprintf( __( '<a href="%s"  target="_blank" >Elementor Page Builder</a>  must be installed and activated for "<strong>Shortcodes For AMP Web Stories and Elementor Widget</strong>" to work', 'shortcodes-for-amp-web-stories-and-elementor-widget' ), esc_url( 'https://wordpress.org/plugins/elementor/' ) ) ); ?></p>
			</div>
        <?php endif;
        
    }
    function wsae_webstory_fail_to_load()
    {

    if (current_user_can('activate_plugins')): ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php 
				// translators: %s: URL to Web Stories plugin page
				echo wp_kses_post( sprintf( __( '<a href="%s"  target="_blank" >Webstory</a>  must be installed and activated for "<strong>Shortcodes For AMP Web Stories and Elementor Widget</strong>" to work', 'shortcodes-for-amp-web-stories-and-elementor-widget' ), esc_url( 'https://wordpress.org/plugins/web-stories/' ) ) ); ?></p>
			</div>
        <?php endif;

    }
    /**
     * Run when activate plugin.
     */
    public static function WSAE_activate()
    {
        update_option("WSAE-v",WSAE_VERSION);
		update_option("WSAE-type","FREE");
		update_option("wsae-installDate",gmdate('Y-m-d h:i:s') );
    }

    /**
     * Run when deactivate plugin.
     */
    public static function WSAE_deactivate()
    {

    }
}

function Webstory_Widget_Addon()
{
    return Webstory_Widget_Addon::get_instance();
}

$GLOBALS['Webstory_Widget_Addon'] = Webstory_Widget_Addon();