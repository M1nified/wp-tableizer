<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $tableizer_tab;
global $tableizer_tab_row_option;
global $tableizer_tab_order;
$tableizer_tab = "{$wpdb->prefix}tableizer";
$tableizer_tab_row_option = "{$wpdb->prefix}tableizer_row_option";
$tableizer_tab_order = "{$wpdb->prefix}tableizer_order";

global $view_row_limit;
$view_row_limit = 10;