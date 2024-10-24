<?php

use Google\Web_Stories\Story_Renderer\HTML;
use Google\Web_Stories\Model\Story;
$checknoof = ($atts['show-no-of-story'] !== 'all') ? $atts['show-no-of-story'] : -1;

$defaults = array(
    'numberposts'      => $checknoof,
    'post_type' => 'web-story',
    'order' => $atts['order'],
);
$the_query = get_posts($defaults);
$post_names = [];
$post_idss = [];
$showbtn=($atts['show-button']=="yes")?'block':'none';
$html .= '<style>
.wsae-grid-container {
  display: grid;
  grid-template-columns: repeat('.esc_attr($atts['column']).', auto [col-start]);
  grid-gap:5px;
overflow-x: auto;
  overflow-y: clip;
  padding: 5px;
  
}
.wase_gridb_button{
  color:'.esc_attr($atts['btn-text-color']).';
  display:'.esc_attr($showbtn).';
  background-color: '.esc_attr($atts['btn-color']).';
 
}
</style><div class="wsae-grid-container">';

foreach ($the_query as $key => $value) {
    $current_post = get_post(intval($value->ID));

    $story = new Story();

    $story->load_from_post($current_post);
    $post_names[$value->post_title] = $value->post_title;
    $post_idss[] = array('id' => $value->ID, 'title' => $value->post_title, 'url' => $story->get_url(), 'poster' => $story->get_poster_portrait());
    $args = '';

$defaults = [
    'align' => 'center',
    'height' => '400px',
    'width' =>'250px',
];
$args = wp_parse_args($args, $defaults);
$align = sprintf('align%s', $args['align']);
$url = $story->get_url();
$title = $story->get_title();
$poster = !empty($story->get_poster_portrait()) ? esc_url($story->get_poster_portrait()) : '';
$margin = ('center' === $args['align']) ? 'auto' : '0';
$player_style = sprintf('width: %s;height: %s;margin: %s', esc_attr($args['width']), esc_attr($args['height']), esc_attr($margin));
$poster_style = !empty($poster) ? sprintf('--story-player-poster: url(%s)', $poster) : '';
$borderWidth = $atts['border-width'];
$borderWidthValue = (int) $borderWidth; 
// $wsae_circle = $atts['style'] == "circle" ? 'wsae_circle' : '';
$imageSrc = esc_url($poster);
if($poster == ""){
$imageSrc = esc_url(WSAE_URL . 'assets/images/default_poster.png');
}

if (
    (function_exists('amp_is_request') && amp_is_request()) ||
    (function_exists('is_amp_endpoint') && is_amp_endpoint())
) {
    $player_style = sprintf('margin: %s', esc_attr($margin));

}

$html.='   <div class="wp-block-web-stories-embed '.esc_attr( $align ).'">';
if(  $atts['style'] === 'circle'){
    $html.='   <a href="' . esc_url($url) . '" style="text-decoration:none;"> 
    <img src="' . esc_url($imageSrc) . '" alt="' . esc_attr($title) . '" style="width:100px; height:100px; border-radius:50%; border:'.esc_attr($borderWidthValue).'px solid '. esc_attr($atts['border-color']).';">
    </a>';
   } 
   else{
    $html.='<amp-story-player width="'.esc_attr( $args['width'] ).'" height="'.esc_attr( $args['height'] ).'" style="'.esc_attr( $player_style ).';" >
                <a href="'. esc_url( $url ).'" style="'.esc_attr( $poster_style ).'">'.esc_html( $title ).'</a>
            </amp-story-player>
           <a href="' . esc_url($url) . '">
                <button class="wae_btn_setting" style="display:' . esc_attr($showbtn) . '; color:' . esc_attr($atts['btn-text-color']) . '; background-color:' . esc_attr($atts['btn-color']) . ';">
                    ' . esc_html($atts['button-text']) . '
                </button>
            </a>';
        } 
$html.=' </div>';
}

$html.='</div>';