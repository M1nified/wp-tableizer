<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function shortcode_tableizer($atts , $content){
    if(is_page()){
        global $wpdb, $tableizer_tab, $tableizer_tab_row_option;

        $category = esc_sql($atts['category']);

        $table_class = array_key_exists ('class',$atts) ? " class=\"{$atts['class']}\"" : '';
        $table_class = array_key_exists ('table_class',$atts) ? " class=\"{$atts['table_class']}\"" : $table_class;

        $cells = $wpdb->get_results(
            "SELECT DISTINCTROW
                t.*
            FROM {$tableizer_tab} as t
            LEFT JOIN {$tableizer_tab_row_option} as tro ON t.row_id = tro.row_id
            WHERE option_value = '{$category}'
            ORDER BY row_id, `column`;
        ");

        $content = "<table{$table_class}><thead><tr>";
        $content .= "</tr></thead><tbody><tr>";
        $row_id = null;
        foreach($cells as $cell){
          if($row_id != $cell->row_id){
            $content .= "</tr><tr>";
            $row_id = $cell->row_id;
          }
          $cell_content = make_cell_content($cell, $atts);
          $content .= "<td>{$cell_content}</td>";
        }
        $content .= "</tr></tbody></table>";
        // $content = "<pre>".print_r($atts,true)."</pre>"."<pre>".print_r($cells,true)."</pre>".$content;
        return $content;
    }else{
        return "";
    }
}