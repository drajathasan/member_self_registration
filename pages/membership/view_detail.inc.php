<?php
defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schema = $activeSchema->fetchObject();
$table_name = strtolower(trim(str_replace(' ', '_', $schema->name)));
$record = \SLiMS\DB::getInstance()->prepare('select * from self_registration_' . $table_name . ' where member_id = ?');
$record->execute([$_GET['member_id']]);

// Retrive all column detail in member table
// $memberSchema = Schema::table('member')->columns($detail = true);

$content = formGenerator($schema, $record->fetch(PDO::FETCH_ASSOC), pluginUrl(['acc_member' => 'yes']));

// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
exit;