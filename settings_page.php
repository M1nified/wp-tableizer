<?php namespace wp_tableizer;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once(realpath(__DIR__.'/variables.php'));

global $wpdb;
global $tableizer_tab;
global $tableizer_tab_row_option;

// Save

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

if(isset($_POST['action']) && $_POST['action'] === 'add_row'){
    $categories= is_array($_POST['categories']) ? $_POST['categories'] : array($_POST['categories']);
    if(isset($_POST['new_category'])){
        array_push($categories,$_POST['new_category']);
    }
    foreach ($_POST['table'] as $row_number => $row) {
        $init = $wpdb->get_row("SELECT IFNULL(max(row_id),-1)+1 AS next_row_number FROM {$tableizer_tab}");
        $next_row_number = $init->next_row_number;
        foreach ($_POST['table'][$row_number] as $col_number => $column) {
            $column = esc_sql( $column );
            $wpdb->insert(
                $tableizer_tab,
                [
                    'row_id' => $next_row_number,
                    'value' =>  $column,
                    'type'  =>  $_POST['types'][$col_number],
                    'column' => $col_number,
                ]
            );
        }
        if(sizeof($categories)>0){
            $wpdb->query("INSERT INTO {$tableizer_tab_row_option} (row_id, option_name, option_value) VALUES ('{$next_row_number}','category','".implode("'),('{$next_row_number}','category','",$categories)."')");
        }
    }
}

// Collect data

$categories = $wpdb->get_col("SELECT DISTINCT `option_value` FROM {$tableizer_tab_row_option} WHERE `option_name` = 'category';");


// View

?>

<h1>Tableizer</h1>
<section>
<h2>Usage</h2>
<p><code>[tableizer category="category name"]</code></p>
<h3>Attributes</h3>
<table>
<thead><tr><th>Attribute</th><th>Description</th></thead>
<tbody>
    <tr><td><code>category</code></td><td>category to display</td></tr>
    <tr><td><code>link_target</code></td><td>target for all displayed link cells</td></tr>
    <tr><td><code>per_page</code></td><td>number of rows displayed per page</td></tr>
    <tr><td><code>top</code></td><td>number of the first N rows to display</td></tr>
</tbody>
</table>
<h3>Examples</h3>
<p><code>[tableizer category="category name"]</code></p>
<p><code>[tableizer category="category name" top="10"]</code></p>
<p><code>[tableizer category="category name" per_page="20"]</code></p>
<p><code>[tableizer category="category name" link_target="_blank"]</code></p>
</section>

<section>
<h2>Add content</h2>

<section>
<h3>Cell content examples</h3>
<table>
<thead><tr><th>Type</th><th>Input</th><th>Output HTML</th></thead>
<tbody>
<tr>
<td>text</td>
<td><code>just plain text</code></td>
<td><samp>just plain text</samp></td>
</tr>
<tr>
<td>text</td>
<td><code>&lt;button&gt;Click me&lt;/button&gt;</code></td>
<td><samp>&lt;button&gt;Click me&lt;/button&gt;</samp></td>
</tr>
<tr>
<td>image</td>
<td><code>[Example image]http://example.com/example.png</code></td>
<td><samp>&lt;img src="http://example.com/example.png" alt="Example image"&gt;</samp></td>
</tr>
<tr>
<td>link</td>
<td><code>[Read more]http://example.com/full_article</code></td>
<td><samp>&lt;a href="http://example.com/full_article"&gt;Read more&lt;/a&gt;</samp></td>
</tr>
</tbody>
</table>
</section>
<h3>Add cells</h3>
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
            <option value="link">link</option>
        </select></td>
        <?php } ?>
    </tr>
</tbody></table>
<p>Category: <input type="text" name="new_category" placeholder="New category name"> <select name="categories" multiple><option></option><?php foreach($categories as $category){print("<option value=\"{$category}\">{$category}</option>");}?></select></p>
<p><input type="submit" class="button"></p>
</form>
</section>

<section>
<h2>Stored data</h2>
<?php

?>
<table>
<thead>
    <th></th>
</thead>
<tbody>
</tbody>
</table>
</section>


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