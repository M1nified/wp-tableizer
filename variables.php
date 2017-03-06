<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $tableizer_tab;
global $tableizer_tab_row_option;
$tableizer_tab = "{$wpdb->prefix}tableizer";
$tableizer_tab_row_option = "{$wpdb->prefix}tableizer_row_option";