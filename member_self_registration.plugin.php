<?php
/**
 * Plugin Name: member_self_registration
 * Plugin URI: https://github.com/drajathasan/member_self_registration
 * Description: Plugin untuk daftar online
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: Drajat Hasan
 */

use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Url;
use SLiMS\Table\Schema;

define('MSLR', __DIR__);

// exit(var_dump(class_exists(Url::class)));

define('MSWB', (string)Url::getSlimsBaseUri('plugins/' . basename(MSLR) . '/'));

// load helper
include_once __DIR__ . DS . 'helper.php';

// get plugin instance
$plugin = Plugins::getInstance();

// registering menus
$plugin->registerMenu('membership', 'Daftar Online', __DIR__ . '/pages/membership/index.php');

// Get active schema from database
if (Schema::hasTable($table = 'self_registration_schemas')) {
    $activeSchema = DB::getInstance()->query('select id,name from ' . $table . ' where status =  1');

    if ($activeSchema->rowCount()) {
        $data = $activeSchema->fetchObject();
        $plugin->registerMenu('opac', $data->name, __DIR__ . DS . 'pages' . DS . 'opac' . DS . 'index.php');
    }
}

// Overriding membership page
$plugin->register(Plugins::MEMBERSHIP_INIT, function()  use($table) {
    global $member_custom_fields, $can_read, $can_write, $sysconf, $dbs;

    include __DIR__ . '/pages/customs/membership/index.php';
    exit;
});