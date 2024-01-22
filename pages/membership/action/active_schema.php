<?php
use SLiMS\DB;

defined('INDEX_AUTH') or die('Direct access is not allowed');

$db = DB::getInstance();
$db->query('update self_registration_schemas set status = 0');
$db->prepare('update self_registration_schemas set status = 1 where id = ?')->execute([$_POST['schema_id']]);
exit;