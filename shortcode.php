<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function shortcode_tableizer($atts , $content){
    if(is_page()){
        // return make_table($atts);
        $table = new Table($atts);
        $html = $table->make_table();
        return $html;
    }else{
        return "";
    }
}