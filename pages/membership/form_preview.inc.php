<?php
use SLiMS\DB;
use SLiMS\Table\Schema;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schemaById->execute([$_GET['schema_id']??0]);

$data = $schemaById->fetchObject();

// Retrive all column detail in member table
// $memberSchema = Schema::table('member')->columns($detail = true);

$content = formGenerator($data);

// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
exit;