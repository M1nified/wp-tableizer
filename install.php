<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook(__DIR__.'/tableizer.php',__NAMESPACE__.'\install');

function install(){
    global $wpdb;
    global $tableizer_tab;
    global $tableizer_tab_row_option;
    $wpdb->query(
        "CREATE TABLE `{$tableizer_tab}` (
            `row_id` int(11) NOT NULL,
            `value` text COLLATE utf8_bin,
            `type` varchar(255) COLLATE utf8_bin DEFAULT NULL,
            `column` int(11) DEFAULT NULL,
            PRIMARY KEY (`row_id`),
            UNIQUE KEY `id_UNIQUE` (`row_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
    ");
    $wpdb->query(
        "CREATE TABLE `{$tableizer_tab_row_option}` (
            `option_id` int(11) NOT NULL AUTO_INCREMENT,
            `row_id` int(11) DEFAULT NULL,
            `option_name` varchar(191) COLLATE utf8_bin DEFAULT NULL,
            `option_value` longtext COLLATE utf8_bin,
            PRIMARY KEY (`option_id`),
            UNIQUE KEY `option_id_UNIQUE` (`option_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
    ");
}
