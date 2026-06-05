<?php

use Google\Web_Stories\Story_Renderer\HTML;
use Google\Web_Stories\Model\Story;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$checknoof = ( 'all' === $atts['show-no-of-story'] )
    ? -1
    : max( 1, absint( $atts['show-no-of-story'] ) );

$query_defaults = array(
    'numberposts' => $checknoof,
    'post_type'   => 'web-story',
    'order'       => $atts['order'],
);
$the_query = get_posts( $query_defaults );
$post_names = [];
$post_idss = [];

// CSS-safe values (esc_attr() is for HTML attributes, not CSS).
$wsae_columns      = max( 1, min( 12, absint( $atts['column'] ) ) );
$wsae_btn_color    = sanitize_hex_color( $atts['btn-color'] ) ?: '#046bd2';
$wsae_btn_text_color = sanitize_hex_color( $atts['btn-text-color'] ) ?: '#ffffff';
$wsae_border_color = sanitize_hex_color( $atts['border-color'] ) ?: 'transparent';
$showbtn           = ( 'yes' === $atts['show-button'] ) ? 'block' : 'none';

$html .= '<style>
.wsae-grid-container {
  display: grid;
  grid-template-columns: repeat(' . $wsae_columns . ', auto [col-start]);
  grid-gap:5px;
overflow-x: auto;
  overflow-y: clip;
  padding: 5px;
  
}
.wase_gridb_button{
  color:' . $wsae_btn_text_color . ';
  display:' . $showbtn . ';
  background-color: ' . $wsae_btn_color . ';
 
}
</style><div class="wsae-grid-container">';

$player_defaults = array(
    'align'  => 'center',
    'height' => '400px',
    'width'  => '250px',
);
$player_args = wp_parse_args( array(), $player_defaults );
$align       = sprintf( 'align%s', $player_args['align'] );
$margin      = ( 'center' === $player_args['align'] ) ? 'auto' : '0';

foreach ( $the_query as $value ) {
    $story = new Story();
    $story->load_from_post( $value );
    $post_names[ $value->post_title ] = $value->post_title;
    $post_idss[]                      = array(
        'id'     => $value->ID,
        'title'  => $value->post_title,
        'url'    => $story->get_url(),
        'poster' => $story->get_poster_portrait(),
    );
$url = $story->get_url();
$title = $story->get_title();
$poster = !empty($story->get_poster_portrait()) ? esc_url($story->get_poster_portrait()) : '';
$player_style = sprintf('width: %s;height: %s;margin: %s', esc_attr($player_args['width']), esc_attr($player_args['height']), esc_attr($margin));
$poster_style = !empty($poster) ? sprintf('--story-player-poster: url(%s)', $poster) : '';
$borderWidth = $atts['border-width'];
$borderWidthValue = (int) $borderWidth; 
// $wsae_circle = $atts['style'] == "circle" ? 'wsae_circle' : '';
$imageSrc = esc_url($poster);
if ( '' === $poster ) {
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
    <img src="' . esc_url($imageSrc) . '" alt="' . esc_attr($title) . '" style="width:100px; height:100px; border-radius:50%; border:' . absint( $borderWidthValue ) . 'px solid ' . $wsae_border_color . ';">
    </a>';
   } 
   else{
    $html.='<amp-story-player width="'.esc_attr( $player_args['width'] ).'" height="'.esc_attr( $player_args['height'] ).'" style="'.esc_attr( $player_style ).';" >
                <a href="'. esc_url( $url ).'" style="'.esc_attr( $poster_style ).'">'.esc_html( $title ).'</a>
            </amp-story-player>
           <a href="' . esc_url($url) . '">
                <button class="wae_btn_setting" style="display:' . $showbtn . '; color:' . $wsae_btn_text_color . '; background-color:' . $wsae_btn_color . ';">
                    ' . esc_html($atts['button-text']) . '
                </button>
            </a>';
        } 
$html.=' </div>';
}

$html.='</div>';  

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound