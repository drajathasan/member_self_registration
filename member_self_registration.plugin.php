<?php
/**
 * Plugin Name: member_self_registration
 * Plugin URI: https://github.com/drajathasan/member_self_registration
 * Description: Plugin untuk daftar online
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: Drajat Hasan
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus
$plugin->registerMenu('membership', 'Daftar Online', __DIR__ . '/index.php');
