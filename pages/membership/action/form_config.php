<?php
use SLiMS\DB;

defined('INDEX_AUTH') or die('Direct access is not allowed');

// Fetch active schema
$update = DB::getInstance()->prepare('update `self_registration_schemas` set `option` = ? where `id` = ?');
$update->execute([json_encode($_POST['form_config']), $_POST['schema_id']]);

toastr('Data berhasil disimpan')->success();
redirect()->simbioAJAX(pluginUrl(reset: true));
exit;