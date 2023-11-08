<?php
/**
 * Plugin Name: member_self_registration
 * Plugin URI: https://github.com/drajathasan/member_self_registration
 * Description: Plugin untuk daftar online
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: Drajat Hasan
 */

use SLiMS\Plugins;
use SLiMS\Url;

define('MSLR', __DIR__);
define('MSWB', (string)Url::getSlimsBaseUri('plugins/' . basename(MSLR) . '/'));

// load helper
include_once __DIR__ . DS . 'helper.php';

// get plugin instance
$plugin = Plugins::getInstance();

// registering menus
$plugin->registerMenu('membership', 'Daftar Online', __DIR__ . '/pages/membership/index.php');
