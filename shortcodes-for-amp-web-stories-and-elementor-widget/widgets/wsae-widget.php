<?php
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;
use Google\Web_Stories\Story_Renderer\HTML;
use Google\Web_Stories\Model\Story;

class WSAE_Widget extends \Elementor\Widget_Base
{

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
       
      wp_register_script('standalone-custom-script', WSAE_URL . 'assets/js/wsae-custom-script.js', ['elementor-frontend','jquery'], null, true);
        wp_register_style('standalone-custom-style', WSAE_URL . 'assets/css/wsae-custom-styl.css');

   
       


    }

    public function get_script_depends()
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            return ['standalone-custom-script','wsae-standalone-amp-story-player-script'];
        }
            return [ 'wsae-standalone-amp-story-player-script'];

            

        
    }

    public function get_style_depends()
    {
        return ['wsae-standalone-amp-story-player-style','standalone-custom-style'];

    }

    public function get_name()
    {
        return 'webstory-widget-addon';
    }

    public function get_title()
    {
        return __('Web Stories Widget', 'WSAE');
    }

    public function get_icon()
    {
        return 'eicon-time-line';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        if ( ! class_exists( '\Google\Web_Stories\Plugin' ) ) {
            return;
        }

			$defaults = array(   
				'numberposts'      => -1,
    			'post_type' => 'web-story',   
				);
				$the_query = get_posts( $defaults);
				$post_names=[];
                $post_idss=[];
                
                
				foreach ($the_query  as $key => $value) {
                    $current_post = get_post($value->ID);

                $story = new Story();
                $story->load_from_post($current_post);
				
                

				$post_names[$value->post_title]=$value->post_title;
				$post_idss[]=array('id'=>$value->ID,'title'=>$value->post_title,'url'=>$story->get_url(),'poster'=>$story->get_poster_portrait());
                }
                
                if(empty($post_names)){
                    $post_names['select'] = 'You have no story to show';
                }
               
                $defal_select = isset($the_query[0]->post_title) ? $the_query[0]->post_title : $post_names['select'];

        $this->start_controls_section(
            'WSAE_layout_section',
            [
                'label' => __('Layout Settings', 'WSAE'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'wsae_layout',
            [
                'label' => __('Select story', 'WSAE'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => $defal_select ,
                'options' => $post_names,
            ]
        );

        $this->add_control(
			'wsae_btn_text',
			[
				'label' => __( 'Button text', 'WSAE' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Default view', 'WSAE' ),
				'placeholder' => __( 'Enter text for button', 'WSAE' ),
			]
		);


   /*      $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'label' => __( 'Button Typography', 'WSAE' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .wae_btn_setting',
			]
		); */
        
       $this->add_control(
			'wsae_ids',
			[
				'label' => __( 'post ids', 'WSAE'),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => $post_idss,
			]
		);

        $this->add_control(
			'wsae_button',
			[
				'label' => __( 'Show Button', 'WSAE'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'WSAE'),
				'label_off' => __( 'Hide', 'WSAE'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
        $this->end_controls_section();

       

    }

    // for frontend
    protected function render()
    {
       
        if ( ! class_exists( '\Google\Web_Stories\Plugin' ) ) {
            return;
        }

       $settings = $this->get_settings_for_display();
	   $singlid='';
	   if($settings['wsae_layout']=='select'){
           return;
       }
       else{
	   foreach ($settings['wsae_ids'] as $key => $value) {
		 if($value['title']==$settings['wsae_layout']){
             $singlid=$value['id'];
             
		 }
	   }
	   require WSAE_PATH . 'widgets/wsae-rendor.php';

		}
	 

    }

    // for live editor
    protected function content_template()
    {
        if ( ! class_exists( '\Google\Web_Stories\Plugin' ) ) {
            return;
        }


        $args = '';
        
        $defaults = [
            'align' => 'none',
            'height' => '600px',
            'width' => '360px',
            
        ];
        $args = wp_parse_args($args, $defaults);
        $align = sprintf('align%s', $args['align']);
      
       
        $margin = ('center' === $args['align']) ? 'auto' : '0';
        $player_style = sprintf('width: %s;height: %s;margin: %s', esc_attr($args['width']), esc_attr($args['height']), esc_attr($margin));
        

        ?>
	    <#
			let url=''
            poster='' 
            title=''
            ;
            var showbtn = (settings.wsae_button == "yes") ? 'block' : 'none';

	    _.each( settings.wsae_ids, function( item, index ) {
			
			if(item.title==settings.wsae_layout){
				url=item.url;
                poster=item.poster;
                title=item.title;

			}
        
        })
        if(settings.wsae_layout=='select'){
           
       }
       else{
          
		#>
            <div class="wsae-wrapper wp-block-web-stories-embed  <?php echo esc_attr($align); ?>">
                <amp-story-player class="wsae-amp" width="360px" height="600px" style="<?php echo esc_attr($player_style) ; ?>">
                    <a href="{{{url}}}" style="--story-player-poster: url({{{poster}}})">{{{title}}}</a>
                </amp-story-player>
                <a href="{{{url}}}" ><button class="wae_btn_setting" style="display:{{{showbtn}}};">{{{settings.wsae_btn_text}}}</button></a>
            </div>
			<# }#>
				<?php
                




    }

}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new WSAE_Widget());
