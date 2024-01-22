<?php
use SLiMS\DB;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schema = $activeSchema->fetchObject();
$baseTable = 'self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name)));

$delete = DB::getInstance()->prepare('delete from ' . $baseTable . ' where `member_id` = ?');
$delete->execute([$_GET['member_id']]);

echo '<script>top.jQuery.colorbox.close();</script>';
redirect()->simbioAJAX(pluginUrl(reset: true));
exit;