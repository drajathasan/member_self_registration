<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:16:13
 * @modify date 2021-05-08 09:16:13
 * @desc [description]
 */

// create datagrid
$datagrid = new simbio_datagrid();

// Attribute
$attribute = (isset($meta['separateTable']) && (int)$meta['separateTable'] == 1) 
                ? 
                ['member_online', 'id', 'member_name IS NOT NULL '] 
                : 
                ['member', 'member_id', 'member_id IS NOT NULL AND is_pending = 1 '];

// table spec
$table_spec = $attribute[0];

// set column
$datagrid->setSQLColumn($attribute[1], 
                        'member_name AS \''.__('Member Name').'\'', 
                        'member_email AS \''.__('E-mail').'\'', 
                        'member_phone AS \''.__('Phone Number').'\'',
                        'input_date AS \'Tanggal Daftar\'',
                        'last_update AS \'' . __('Last Update') . '\'');

// ordering
$datagrid->setSQLorder('last_update DESC');

// is there any search
$criteria = $attribute[2];
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = $dbs->escape_string($_GET['keywords']);
    $criteria .= " AND (m.member_name LIKE '%$keywords%' OR m.member_id LIKE '%$keywords%') ";
}

$datagrid->setSQLCriteria($criteria);

// set table and table header attributes
$datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
$datagrid->table_name = 'memberList';
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->chbox_form_URL = null;

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, true);
if ((isset($_GET['keywords']) AND $_GET['keywords'])) {
    echo '<div class="infoBox">';
    echo __('Found').' '.$datagrid->num_rows.' '.__('from your search with keyword').' : "'.htmlentities($_GET['keywords']).'"'; //mfc
    echo '</div>';
}

echo $datagrid_result;
