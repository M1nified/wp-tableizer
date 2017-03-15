<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function make_table($options){
  global $wpdb, $tableizer_tab, $tableizer_tab_row_option;

  $only_rows = array_key_exists('only_rows', $options) && $options['only_rows'] != 'false' && $options['only_rows'] != 'off' ? true : false;
  $category = esc_sql($options['category']);

  $table_class = array_key_exists ('class',$options) ? " class=\"{$options['class']}\"" : '';
  $table_class = array_key_exists ('table_class',$options) ? " class=\"{$options['table_class']}\"" : $table_class;

  $cells = $wpdb->get_results(
      "SELECT DISTINCT
          t.*
      FROM {$tableizer_tab} as t
      LEFT JOIN {$tableizer_tab_row_option} as tro_cat ON t.row_id = tro_cat.row_id AND tro_cat.option_name = 'category'
      LEFT JOIN {$tableizer_tab_row_option} as tro_ish ON t.row_id = tro_ish.row_id AND tro_ish.option_name = 'header'
      WHERE
        tro_cat.option_value = '{$category}'
        AND
        ( tro_ish.option_value = 0 OR tro_ish.option_value IS NULL )
      ORDER BY row_id, `column`;
  ");

  $header = $wpdb->get_results(
      "SELECT DISTINCT
          t.*
      FROM {$tableizer_tab} as t
      LEFT JOIN {$tableizer_tab_row_option} as tro_cat ON t.row_id = tro_cat.row_id AND tro_cat.option_name = 'category'
      LEFT JOIN {$tableizer_tab_row_option} as tro_ish ON t.row_id = tro_ish.row_id AND tro_ish.option_name = 'header'
      WHERE
        tro_cat.option_value = '{$category}'
        AND
        tro_ish.option_value = 1
      ORDER BY row_id, `column`;
  ");

  $content = null;
  if(!$only_rows){
    $content .= "<table{$table_class}><thead><tr>";
    foreach($header as $cell){
      $cell_content = $cell->value;
      $content .= "<th>{$cell_content}</th>";
    }
    $content .= "</tr></thead><tbody>";
  }
  $content .= "<tr>";
  $row_id = null;
  foreach($cells as $cell){
    if($row_id != $cell->row_id){
      $content .= "</tr><tr>";
      $row_id = $cell->row_id;
    }
    $cell_content = make_cell_content($cell, $options);
    $content .= "<td>{$cell_content}</td>";
  }
  $content .= "</tr>";
  if(!$only_rows){
    $content .= "</tbody></table>";
  }
  // $content = "<pre>".print_r($options,true)."</pre>"."<pre>".print_r($cells,true)."</pre>".$content;
  return $content;
}

function make_table_editor($filter_category = null){
  global $wpdb, $tableizer_tab, $tableizer_tab_row_option;

  if($filter_category === null ){
    $cells = $wpdb->get_results(
        "SELECT DISTINCTROW
            t.*
        FROM {$tableizer_tab} as t
        ORDER BY row_id, `column`;
    ");
  }else{
    $cells = $wpdb->get_results(
        "SELECT DISTINCTROW
            t.*
        FROM {$tableizer_tab} as t
        LEFT JOIN {$tableizer_tab_row_option} as tro ON t.row_id = tro.row_id
        WHERE 
          tro.option_value = '$filter_category'
          AND tro.option_name = 'category'
        ORDER BY row_id, `column`;
    ");
  }

  $row_cat = $wpdb->get_results(
    "SELECT
      row_id,
      GROUP_CONCAT(option_value SEPARATOR ',') as categories
    FROM $tableizer_tab_row_option
    WHERE option_name = 'category'
    GROUP BY row_id
    ORDER BY row_id
    ",
    OBJECT_K
  );

  $categories = $wpdb->get_col(
    "SELECT DISTINCTROW
      option_value
    FROM $tableizer_tab_row_option
    WHERE option_name = 'category'
  ");

  $content = "<table class=\"tableizer-table-editor\"><thead><tr>";
  $content .= "</tr></thead><tbody><tr>";
  $row_id = null;
  foreach($cells as $cell){
    if($row_id != $cell->row_id){
      $content .= "</tr><tr>";
      $category_selection = "<select name=\"categories[$cell->row_id][]\" style=\"width:100%\" multiple size=\"3\">";
      $active_categories = array_key_exists($cell->row_id, $row_cat) ? explode(',',$row_cat[$cell->row_id]->categories) : [];
      foreach ($categories as $category) {
        $selected = in_array($category, $active_categories) ? ' selected' : '';
        $category_selection .= "<option value=\"$category\"$selected>$category</option>";
      }
      $category_selection .= "</select>";

      $content .= "<td>{$cell->row_id}</td>";
      $content .= "<td><input type=\"checkbox\" name=\"remove[{$cell->row_id}]\" title=\"Remove\"></td>";
      $content .= "<td>{$category_selection}</td>";
      
      $row_id = $cell->row_id;
    }
    $value = esc_html(stripcslashes($cell->value));
    $cell_content = "
      <select name=\"types[{$cell->cel_id}]\" style=\"width:100%;\">";
    $cell_content .= "<option value=\"text\"".($cell->type==='text'?' selected':'').">text</option>";
    $cell_content .= "<option value=\"image\"".($cell->type==='image'?' selected':'').">image</option>";
    $cell_content .= "<option value=\"link\"".($cell->type==='link'?' selected':'').">link</option>";
    $cell_content .= "
      </select>
      <input name=\"values[{$cell->cel_id}]\" type=\"text\" value=\"$value\">
    ";
    $content .= "<td>{$cell_content}</td>";
  }
  $content .= "</tr></tbody></table>";
  // $content = "<pre>".print_r($options,true)."</pre>"."<pre>".print_r($cells,true)."</pre>".$content;
  return $content;
}

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