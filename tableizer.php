<?php namespace wp_tableizer;

/**
 * Plugin Name: Tableizer
 * Description: Table data collector and formatter
 * Version: 1.0.1
 * Author: M1nified
 */
 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

 include_once(realpath(__DIR__.'/variables.php'));

 include_once(realpath(__DIR__.'/functions.php'));

 include_once(realpath(__DIR__.'/Table.php'));

 include_once(realpath(__DIR__.'/shortcode.php'));

 include_once(realpath(__DIR__.'/setup.php'));

 include_once(realpath(__DIR__.'/install.php'));
