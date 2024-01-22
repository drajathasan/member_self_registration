<?php
use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Table\Schema;

defined('INDEX_AUTH') or die('Direct access is not allowed');

// Run hook before droping schema
Plugins::getInstance()->execute('member_self_before_drop_schema');

// Fetch active schema
$schemaById->execute([$_POST['schema_id']]);
$detail = $schemaById->fetchObject();

// Delete schema data
DB::getInstance()->prepare('delete from `self_registration_schemas` where `id` = ?')->execute([$_POST['schema_id']]);
$delete = Schema::drop('self_registration_' . trim(str_replace(' ', '_', strtolower($detail->name))));

// filtering only for advance field only
$advanceOnly = array_filter(json_decode($detail->structure, TRUE), function($column){
    return $column['field'] === 'advance';
});

// Set only column name
$fieldsToDrop = array_map(function($data) {
    if (preg_match('/\|/', $data['advfield'])) {
        $data['advfield'] = explode(',', $data['advfield'])[0];
    }
    return $data['advfield'];
}, $advanceOnly);

// Drop column from member custom
foreach($fieldsToDrop as $column) Schema::dropColumn('member_custom', $column);
exit;