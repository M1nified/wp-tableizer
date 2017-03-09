<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function add_menu_settings(){
    add_submenu_page(
        'themes.php',
        'Tableizer',
        'Tableizer',
        'edit_pages',
        'tableizer_settings',
        function(){
            include realpath(__DIR__.'/settings_page.php');
        }
    );
}
add_action('admin_menu',__NAMESPACE__.'\add_menu_settings');

add_shortcode( 'tableizer', __NAMESPACE__.'\shortcode_tableizer' );