<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook(__DIR__.'/tableizer.php',__NAMESPACE__.'\install');

function install(){
    global $wpdb;
    global $tableizer_db_element;
    $wpdb->query(
        "CREATE TABLE `{$tableizer_db_element}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `element_id` int(11) NOT NULL,
            `option_name` varchar(255) CHARACTER SET utf8 NOT NULL,
            `value` text COLLATE utf8_bin,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id_UNIQUE` (`id`);
        ) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
    ");
}
