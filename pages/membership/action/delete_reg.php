<?php
use SLiMS\DB;
use SLiMS\Plugins;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

$schema = $activeSchema->fetchObject();
$baseTable = 'self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name)));

Plugins::getInstance()->execute('member_self_before_reject', ['baseTable' => $baseTable]);

$delete = DB::getInstance()->prepare('delete from ' . $baseTable . ' where `member_id` = ?');
$delete->execute([$_GET['member_id']]);

echo '<script>top.jQuery.colorbox.close();</script>';
redirect()->simbioAJAX(pluginUrl(reset: true));
exit;