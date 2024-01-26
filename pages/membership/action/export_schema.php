<?php
use SLiMS\Json;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schemaById->execute([$_GET['schema_id']]);

if ($schemaById->rowCount() < 1) {
    toastr('Data skema tidak tersedia')->error();
    exit;
}

$data = $schemaById->fetchObject();
unset($data->id);

$data->status = 0;

$filename = 'self_registration_' . trim(strtolower(str_replace(' ', '_', $data->name)));

header('Content-disposition: attachment; filename=' . $filename . '.json' );
exit(Json::stringify($data)->withHeader());