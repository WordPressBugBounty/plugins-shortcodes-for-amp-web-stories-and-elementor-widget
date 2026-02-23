<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Google\Web_Stories\Story_Renderer\HTML;
use Google\Web_Stories\Model\Story;

class WSAE_Widget extends Widget_Base
{

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
       
      wp_register_script('standalone-custom-script', WSAE_URL . 'assets/js/wsae-custom-script.js', ['elementor-frontend','jquery'], WSAE_VERSION, true);
      wp_register_style('standalone-custom-style', WSAE_URL . 'assets/css/wsae-custom-styl.css', [], WSAE_VERSION);
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
        return __('Web Stories Widget', 'shortcodes-for-amp-web-stories-and-elementor-widget');
    }

    public function get_icon()
    {
        return 'eicon-slider-push';
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
				
                // Escape the post title before adding it to the array
                $escaped_title = esc_html( $value->post_title );
                $post_names[$escaped_title] = $escaped_title;
                $post_idss[] = array(
                    'id'     => $value->ID,
                    'title'  => $escaped_title, // Use escaped title
                    'url'    => esc_url( $story->get_url() ), // Escape the URL
                    'poster' => esc_url( $story->get_poster_portrait() ), // Escape the poster URL
                );
                }
                
                if(empty($post_names)){
                    $post_names['select'] = esc_html__( 'You have no story to show', 'shortcodes-for-amp-web-stories-and-elementor-widget' ); // Escape static text
                }              

                $defal_select = isset( $the_query[0]->post_title ) ? esc_html( $the_query[0]->post_title ) : esc_html__( 'select', 'shortcodes-for-amp-web-stories-and-elementor-widget' ); // Escape default select text

            $this->start_controls_section(
            'WSAE_layout_section',
            [
                'label' => __('Layout Settings', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'wsae_layout',
            [
                'label' => __('Select story', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => Controls_Manager::SELECT,
                'default' => $defal_select ,
                'options' => $post_names,
            ]
        );

        $this->add_control(
            'wsae_style',
            [
                'label' => __('Style', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'=>  esc_html__('Default','shortcodes-for-amp-web-stories-and-elementor-widget'),
                    'circle'=>  esc_html__('Circle','shortcodes-for-amp-web-stories-and-elementor-widget'),
                ],
            ]
        );

        $this->add_control(
            'wsae_story_height',
            [
                'label' => esc_html__('Story Height', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                        'step' => 1,
                    ],
                
                ],  
            
                'selectors' => [
                    '{{WRAPPER}} .wsae-wrapper' => '--story-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
			'wsae_button',
			[
				'label' => __( 'Show Button', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
				'label_off' => __( 'Hide', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
				'return_value' => 'yes',
				'default' => 'no',
                'condition' => [
                    'wsae_style' => 'default',
                ],
			]
		);
        

        $this->add_control(
			'wsae_btn_text',
			[
				'label' => __( 'Button text', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'View', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
				'placeholder' => __( 'Enter text for button', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                'sanitize_callback' => 'sanitize_text_field',
                'condition' => [
                    'wsae_button' => 'yes',
                    'wsae_style' => 'default',
                ],
			]
		);


        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'label' => __( 'Button Typography', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
				'type' => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .wae_btn_setting',
                'condition' => [
                    'wsae_button' => 'yes',
                    'wsae_style' => 'default',
                ],
			]
		); 
        $this->add_control(
            'wsae_button_text_color',
            array(
                'label' => __('Button Text Color', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => Controls_Manager::COLOR,
                'selectors' => array('{{WRAPPER}} .wae_btn_setting' => 'color: {{VALUE}} !important',
                ),
                'condition' => [
                    'wsae_button' => 'yes',
                    'wsae_style' => 'default',
                ],
            )
        );

        $this->add_control(
            'wsae_button_color',
            array(
                'label' => __('Button Background', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => Controls_Manager::COLOR,
                'selectors' => array('{{WRAPPER}} .wae_btn_setting' => 'background-color: {{VALUE}} !important',
                ),
                'condition' => [
                    'wsae_button' => 'yes',
                    'wsae_style' => 'default',
                ],
            )
        );

        $this->add_control(
            'wsae_border_width',
            array(
                'label' => __('Border Width', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'size' => 2,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],  
                'selectors' => array(
                    '{{WRAPPER}} .wsae-wrapper' => '--wsae-border-width: {{SIZE}}{{UNIT}};',
                ),
                'condition' => [
                    'wsae_style' => 'circle',
                ],
            )
        );

        $this->add_control(
            'wsae_border_padding',
            array(
                'label' => __('Padding', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'size' => 5,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],  
                'selectors' => array(
                    '{{WRAPPER}} .wsae-wrapper' => '--wsae-border-padding: {{SIZE}}{{UNIT}};',
                ),
                'condition' => [
                    'wsae_style' => 'circle',
                ],
            )
        );

        
        $this->add_control(
            'wsae_border_color_type',
            array(
                'label' => __('Border Color Type', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'gradient', // or 'gradient'
                'options' => array(
                    'simple'     => array(
                        'title' => esc_html__( 'Simple', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                        'icon'  => 'eicon-paint-brush',
                    ),
                    'gradient'   => array(
                        'title' => esc_html__( 'Gradient', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                        'icon'  => 'eicon-barcode',
                    )),
                'condition' => [
                    'wsae_style' => 'circle',
                ],
            )
        );

        // Border Normal
        $this->add_control(
            'wsae_primary_border_color',
            array(
                'label'     => esc_html__( 'Primary Border Color', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default' => '#F01111', 
                'selectors' => array(
                    '{{WRAPPER}} .wsae-wrapper' => '--wsae-border-color: {{VALUE}}',
                        ),
                'condition' => array(
                    'wsae_border_color_type' => array( 'simple', 'gradient'),
                    'wsae_style' => 'circle',
                ),
            )
        );

       // Border Gradient 
        $this->add_control(
            'wsae_secondary_border_color',
            array(
                'label'     => esc_html__( 'Secondary Border Color', 'shortcodes-for-amp-web-stories-and-elementor-widget' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default' => '#800080', 
                'selectors' => array(
                    '{{WRAPPER}} .wsae-wrapper' => '--wsae-gradient-color: {{VALUE}}',
                    ),
                'condition' => array(
                    'wsae_border_color_type' => 'gradient',
                    'wsae_style' => 'circle',
                ),
            )
        );

       $this->add_control(
			'wsae_ids',
			[
				'label' => __( 'post ids', 'shortcodes-for-amp-web-stories-and-elementor-widget'),
				'type' => Controls_Manager::HIDDEN,
				'default' => $post_idss,
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
        echo esc_html__('You have no story to show', 'shortcodes-for-amp-web-stories-and-elementor-widget');
        return;
       }
       else{
	   foreach ($settings['wsae_ids'] as $key => $value) {
		 if($value['title']==$settings['wsae_layout']){
            $singlid = esc_attr($value['id']);
             
		 }
	   }
	   require WSAE_PATH . 'widgets/wsae-rendor.php';

	}
	  // Escape the button text to prevent XSS
      $button_text = isset($settings['wsae_btn_text']) ? esc_html($settings['wsae_btn_text']) : '';

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
                console.log(poster);
			}
        })
        if (settings.wsae_layout == 'select') {
                #>
                <span><?php echo esc_html__('You have no story to show', 'shortcodes-for-amp-web-stories-and-elementor-widget'); ?></span>
                <#
            } else {
                function esc_html(str) {
                       return String(str).replace(/&/g, '&amp;')
                      .replace(/</g, '&lt;')
                      .replace(/>/g, '&gt;')
                      .replace(/"/g, '&quot;')
                      .replace(/'/g, '&#039;');
                }
                let wsae_circle = settings.wsae_style == 'circle'? 'wsae_circle':"";
                #>
                
                <div class="wsae-wrapper wp-block-web-stories-embed {{{wsae_circle}}} <?php echo esc_attr($align); ?>">
                <# 
                    // Determine the image source based on whether the poster is set
                    var imageSrc = (poster) ? poster : '<?php echo esc_url(WSAE_URL . 'assets/images/default_poster.png'); ?>';
                    #>
                      <#  if (settings.wsae_style == 'circle') { #>
                        <a href="{{{url}}}"> 
                        <div class="borderDiv">
                            <!-- <# console.log(poster) #> -->
                    
                        <img src="{{{imageSrc}}}" alt="{{title}}">
                        </div>
                        </a>
                <# } else { #>
                        <amp-story-player class="wsae-amp">
                            <a href="{{{url}}}" style="--story-player-poster: url({{{poster}}})">{{{esc_html(title)}}}</a>
                        </amp-story-player>
                        <a href="{{{url}}}"><button class="wae_btn_setting" style="display:{{{showbtn}}};">{{{ esc_html(settings.wsae_btn_text) }}}</button></a>
                        <# }
                    }
                    #>
            </div>
		<?php
    }    
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new WSAE_Widget());
