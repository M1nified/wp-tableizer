<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $wpdb;
global $tableizer_db_element;
$tableizer_db_element = "{$wpdb->prefix}tableizer_element";