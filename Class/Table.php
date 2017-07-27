<?php namespace wp_tableizer;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Table
{
    const OFFSET_FIELD_NAME = "toff";
    const DEFAULT_OPTIONS = [
        'class' => null,
        'category' => null,
        'category-exclude' => null,
        'row-limit' => 10,
    ];

    protected static $table_count = 0;
    
    protected $only_rows = null;
    protected $options = [];
    protected $row_offset = 0;
    protected $nav_next = null;
    protected $nav_prev = null;

    protected $html_table_class = null;

    protected $category = null;
    protected $sql_category_exclude = null;

    protected $coll_max_index;
    protected $row_count_total;

    private $table_number = 0;
    private $offset_field_name = Table::OFFSET_FIELD_NAME;

    function __construct($shortcode_attributes)
    {
        Table::$table_count++;
        $this->options = shortcode_atts( Table::DEFAULT_OPTIONS, $shortcode_attributes);
        $this->table_number = Table::$table_count;
        $this->offset_field_name = Table::OFFSET_FIELD_NAME . "_{$this->table_number}";

        $this->row_offset = array_key_exists($this->offset_field_name, $_GET) && is_numeric($_GET[$this->offset_field_name]) && $_GET[$this->offset_field_name] > 0
            ? intval($_GET[$this->offset_field_name])
            : 0;

        $this->update_only_rows();
        $this->update_navs();
        $this->update_category();
        $this->update_sql_category_exclude();
        $this->update_html_table_class();
        $this->update_coll_max_index();
        $this->update_row_count_total();
        // echo "<pre>"; print_r($this); echo "</pre>";
    }

    // Public
    public function make_table()
    {
        global $wpdb;
        $query = $this->sql_build_query_table();
        // echo "<pre>$query</pre>";
        $rows = $wpdb->get_results($query);
        $query = $this->sql_build_query_header();
        // echo "<pre>$query</pre>";
        $header = $wpdb->get_results($query);
        $table = $this->html_build_table($rows, $header);
        return $table;
    }
    public function print_table()
    {
        echo $this->make_table();
    }

    // Protected
    protected function update_only_rows()
    {
        $this->only_rows = array_key_exists('only-rows', $this->options) && $this->options['only-rows'] != 'false' && $this->options['only-rows'] != 'off' ? true : false;
    }
    protected function update_navs()
    {
        $this->nav_next = add_query_arg([
            $this->offset_field_name => $this->row_offset + $this->options['row-limit']
        ]);
        $this->nav_prev = add_query_arg([
            $this->offset_field_name => $this->row_offset - $this->options['row-limit']
        ]);
    }
    protected function update_category()
    {
        $this->category = esc_sql( $this->options['category'] );
    }
    protected function update_sql_category_exclude()
    {
        $this->sql_category_exclude = array_key_exists('category-exclude', $this->options)
            ? implode( // returns "'cat1','cat2'"
            ',',
            array_map( // returns ["'cat1'","'cat2'"]
                function ($cat) {
                    return '\''.esc_sql(trim($cat)).'\'';
                },
                explode(',', $this->options['category-exclude'])
            )
            ) : '\'\'';
    }
    protected function update_html_table_class()
    {
        $this->html_table_class = array_key_exists ('class', $this->options) ? " class=\"{$this->options['class']}\"" : '';
        $this->html_table_class = array_key_exists ('table_class', $this->options) ? " class=\"{$this->options['table_class']}\"" : $this->html_table_class;
    }
    protected function update_coll_max_index()
    {
        global $wpdb, $tableizer_tab, $tableizer_tab_row_option;
        $query = "SELECT MAX(tt.`column`) FROM {$tableizer_tab} as tt NATURAL JOIN {$tableizer_tab_row_option} as ttro WHERE ttro.option_value = '{$this->category}' AND ttro.option_name = 'category';";
        // echo "<pre>$query</pre>";
        $this->coll_max_index = $wpdb->get_var($query);
    }
    protected function update_row_count_total()
    {
        global $wpdb;
        $query = $this->sql_build_query_row_count_total();
        // echo "<pre>$query</pre>";
        $this->row_count_total = $wpdb->get_var($query);
    }

    protected function sql_build_query_table()
    {
        global $tableizer_tab, $tableizer_tab_row_option, $tableizer_tab_order;
        $query = "SELECT t3.row_id, t3.order_value as `order_value`";
        for ($i=0; $i<=$this->coll_max_index; $i++) {
                $query .= ", GROUP_CONCAT(col_{$i}) as col_{$i}";
        }
        $query .= " FROM ( SELECT t2.row_id, t2.order_value as `order_value`";
        for ($i=0; $i<=$this->coll_max_index; $i++) {
                $query .= ", CASE WHEN t2.`column` = {$i} THEN t2.cell_json END AS col_{$i}";
        }

        $query .= " FROM
                (SELECT 
                t1.`row_id` AS row_id, CONCAT('{\"value\":\"', t1.value, '\", \"type\":\"', t1.type, '\"}') AS cell_json, t1.`column` AS `column`,
                t1.order_value as `order_value`
            FROM
                (SELECT DISTINCT
                t.*,
                t_order.order_value as `order_value`
            FROM
                {$tableizer_tab} AS t
            LEFT JOIN {$tableizer_tab_order} AS t_order ON t.row_id = t_order.row_id
                AND t_order.category_name = '{$this->category}'
            LEFT JOIN {$tableizer_tab_row_option} AS tro_cat ON t.row_id = tro_cat.row_id
                AND tro_cat.option_name = 'category'
            LEFT JOIN {$tableizer_tab_row_option} AS tro_ish ON t.row_id = tro_ish.row_id
                AND tro_ish.option_name = 'header'
            WHERE
                tro_cat.option_value = '{$this->category}'
                    AND (tro_ish.option_value = 0
                    OR tro_ish.option_value IS NULL)
                    AND t.row_id NOT IN (SELECT DISTINCT
                        t_2.row_id
                    FROM
                        {$tableizer_tab} AS t_2
                    LEFT JOIN {$tableizer_tab_row_option} AS tro_cat_2 ON t_2.row_id = tro_cat_2.row_id
                        AND tro_cat_2.option_name = 'category'
                    LEFT JOIN {$tableizer_tab_row_option} AS tro_ish_2 ON t_2.row_id = tro_ish_2.row_id
                        AND tro_ish_2.option_name = 'header'
                    WHERE
                        (tro_ish_2.option_value = 0
                            OR tro_ish_2.option_value IS NULL)
                            AND tro_cat_2.option_value IN ({$this->sql_category_exclude}))) AS t1) AS t2) AS t3
        GROUP BY t3.row_id
        ORDER BY t3.order_value , t3.row_id
        LIMIT {$this->options['row-limit']} OFFSET {$this->row_offset};
        ;";
        return $query;
    }
    protected function sql_build_query_header()
    {
        global $tableizer_tab, $tableizer_tab_row_option;
        $query = "SELECT DISTINCT
                t.*
            FROM {$tableizer_tab} as t
            LEFT JOIN {$tableizer_tab_row_option} as tro_cat ON t.row_id = tro_cat.row_id AND tro_cat.option_name = 'category'
            LEFT JOIN {$tableizer_tab_row_option} as tro_ish ON t.row_id = tro_ish.row_id AND tro_ish.option_name = 'header'
            WHERE
                tro_cat.option_value = '{$this->category}'
                AND
                tro_ish.option_value = 1
            ORDER BY row_id, `column`;";
        return $query;
    }
    protected function sql_build_query_row_count_total()
    {
        global $tableizer_tab, $tableizer_tab_row_option;
        $query = "SELECT DISTINCT
                COUNT(DISTINCT tab.row_id) AS row_count
            FROM
                {$tableizer_tab} AS tab
            NATURAL JOIN
                {$tableizer_tab_row_option} AS opt 
            WHERE 
                opt.option_name = 'category'
                AND
                opt.option_value = '{$this->category}'
                AND
                opt.option_value NOT IN ({$this->sql_category_exclude});";
        return $query;
    }

    protected function html_build_table($rows, $header)
    {
        // print_r($rows);
        // print_r($header);
        $content = "";
        if (!$this->only_rows) {
            $content .= "<table{$this->html_table_class}>";
            $content .= "<thead><tr>";
            foreach ($header as $cell) {
                $cell_content = $cell->value;
                $content .= "<th>{$cell_content}</th>";
            }
            $content .= "</tr></thead>";
            $content .= "<tbody>";
        }
        $content .= "<tr>";
        $row_id = null;
        foreach ($rows as $index => $row) {
            for ($i=0; $i<=$this->coll_max_index; $i++) {
                // print_r($row->{"col_".$i});
                try {
                    $cell = (object) json_decode( $row->{"col_".$i}, true );
                    // print_r($cell);
                    $cell_content = make_cell_content($cell);
                    $content .= "<td>{$cell_content}</td>";
                } catch (\Exception $ex) {
                    $content .= "<td></td>";
                }
            }
            $content .= "</tr><tr>";
        }
        $content .= "</tr>";
        if (!$this->only_rows) {
                $content .= "</tbody></table>";
        }
        // $content = "<pre>".print_r($options,true)."</pre>"."<pre>".print_r($cells,true)."</pre>".$content;

        $content .= "<div style=\"clear: both;\"></div>";
        $content .= "<p class=\"tabelizer-nav\" style=\"text-align: center;\">";
        if ($this->row_offset > 0) {
                $content .= "<a href=\"{$this->nav_prev}\">&lt;&lt;Poprzednia strona</a>&nbsp;";
        }
        if ($this->row_count_total >= $this->row_offset + $this->options['row-limit']) {
            $content .= "&nbsp;<a href=\"{$this->nav_next}\">Następna strona &gt;&gt;</a>";
        }
        $content .= "</p>";
        return $content;
    }
}
