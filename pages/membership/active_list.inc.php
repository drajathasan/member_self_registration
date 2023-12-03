<?php
// create datagrid
$datagrid = new simbio_datagrid();

$data = $activeSchema->fetchObject();
$structure = json_decode($data->structure, true);
$structure = array_merge(array_values(array_filter($structure, function($column) {
    return in_array($column['field'], ['member_id','member_name']);
})), [['name' => _('Input Date'), 'field' => 'created_at']]);

$columns = [];
$columns[] = '`member_id` AS `Aksi`';

foreach ($structure as $no => $detail) {
    if ($detail['field'] === 'advance') continue;

    $columns[] = '`' . $detail['field'] . '` AS `' . $detail['name'] . '`';
}

// table spec
$table_spec = 'self_registration_' . trim(str_replace(' ', '_', strtolower($data->name)));

$datagrid->setSQLColumn(...$columns);

// modify column value
$datagrid->setSQLorder('created_at DESC');

function setButton($dbs, $data)
{
    return '<a height="500" title="Detail ' . $data[2] . '" href="' . pluginUrl(['section' => 'view_detail', 'member_id' => $data[0], 'headless' => 'yes']) . '" class="notAJAX openPopUp btn btn-primary"><i class="fa fa-pencil"></i></a>';
}

$datagrid->modifyColumnContent(0, 'callback{setButton}');

// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader thead-dark" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->chbox_form_URL = pluginUrl(reset: true);
$datagrid->column_width = ['10%', '10%'];

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 10, false);
echo $datagrid_result;