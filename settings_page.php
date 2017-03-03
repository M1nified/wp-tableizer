<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once(realpath(__DIR__.'/variables.php'));

global $wpdb;
global $tableizer_db_element;

// Save

echo '<pre>';
print_r($_POST);
echo '</pre>';

if(isset($_POST['action']) && $_POST['action'] === 'add_row'){
    foreach ($_POST['table'] as $row_number => $row) {
        $init = $wpdb->get_row("SELECT
                max(element_id) + 1 as next_element_id,
                (SELECT max(value) FROM {$tableizer_db_element} WHERE option_name = 'row') as next_row_number
            FROM 
                {$tableizer_db_element};
        ");
        $new_row_number = $init->next_row_number + $row_number;
        foreach ($_POST['table'][$row_number] as $col_number => $column) {
            $elem_id = $init->next_element_id + $col_number;
            $column = esc_sql( $column );
            $wpdb->query("INSERT INTO {$tableizer_db_element}
                    (
                        element_id,
                        option_name,
                        value
                    )
                VALUES
                    (   {$elem_id},'value','$column'  ),
                    (   {$elem_id},'type', '{$_POST['types'][$row_number]}'),
                    (   {$elem_id},'row_number',$new_row_number  )
            ");
        }
    }
}



// View

?>
<form method="get" action="#">
<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
<p>
<label>Number of new columns: 
<input type="number" name="cols_count" value="<?php echo isset($_GET['cols_count']) && is_numeric($_GET['cols_count']) ? $_GET['cols_count'] : 1; ?>" min="1" step="1">
</label>
<input type="submit" class="button">
</p>
</form>

<p><button id="btn-add-row" class="button">Add row</button></p>

<form action="<?php echo add_query_arg([]); ?>" method="post">
<input type="hidden" name="action" value="add_row">
<table id="table-input"><thead></thead><tbody>
    <tr>
        <?php for($i=0;$i<(isset($_GET['cols_count']) && is_numeric($_GET['cols_count']) ? $_GET['cols_count'] : 1);$i++){ ?>
        <td><select name="types[<?php echo $i; ?>]" style="width:100%;">
            <option value="text" selected>text</option>
            <option value="image">image</option>
        </select></td>
        <?php } ?>
    </tr>
</tbody></table>
<input type="submit" class="button">
</form>

<?php
// Elements

?>
<div style="display: none;">
<table id="new-rows">
<thead>
    <tr>
        <?php for($i=0;$i<(isset($_GET['cols_count']) && is_numeric($_GET['cols_count']) ? $_GET['cols_count'] : 1);$i++){ ?>
        <th>Col <?php echo $i; ?></th>
        <?php } ?>
    </tr>
</thead>
<tbody>
    <tr>
        <?php for($i=0;$i<(isset($_GET['cols_count']) && is_numeric($_GET['cols_count']) ? $_GET['cols_count'] : 1);$i++){ ?>
        <td><input type="text" name="table[$row_number][<?php echo $i; ?>]"></td>
        <?php } ?>
    </tr>
</tbody>
</table>
</div>
<script><?php include(__DIR__.'/js/settings_page.js'); ?></script>