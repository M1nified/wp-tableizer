<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function make_cell_content(\stdClass $cell, &$options=null){
  if($cell->type === 'image' ) return make_cell_content_image($cell, $options);
  if($cell->type === 'text'  ) return make_cell_content_text($cell, $options);
  if($cell->type === 'link'  ) return make_cell_content_link($cell, $options);
  return "";
}

function make_cell_content_image(\stdClass $cell, &$options=null){
  $img_src = preg_replace('/\[.*\]/i','',$cell->value);
  $img_alt = preg_replace('/.*\[|\].*/i','',$cell->value);
  $img_alt = $img_alt == $img_src ? basename($img_src) : $img_alt;
  $content = "<img src=\"{$img_src}\" alt=\"{$img_alt}\">";
  return $content;
}

function make_cell_content_text(\stdClass $cell, &$options=null){
  $content = $cell->value;
  return $content;
}

function make_cell_content_link(\stdClass $cell, &$options=null){
  $url = preg_replace('/\[.*\]/i','',$cell->value);
  $anchor = preg_replace('/.*\[|\].*/i','',$cell->value);
  $target = is_array($options) && array_key_exists('link_target', $options) ? " target=\"{$options['link_target']}\"" : "";
  $content = "<a href=\"{$url}\"{$target}>{$anchor}</a>";
  return $content;
}