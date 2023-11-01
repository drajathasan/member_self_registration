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

if (!function_exists('pluginUrl'))
{
    /**
     * Generate URL with plugin_container.php?id=<id>&mod=<mod> + custom query
     *
     * @param array $data
     * @param boolean $reset
     * @return string
     */
    function pluginUrl(array $data = [], bool $reset = false): string
    {
        // back to base uri
        if ($reset) return Url::getSelf(fn($self) => $self . '?mod=' . $_GET['mod'] . '&id=' . $_GET['id']);
        
        // override current value
        foreach($data as $key => $val) {
            if (isset($_GET[$key])) {
                $_GET[$key] = $val;
                unset($data[$key]);
            }
        }

        return Url::getSelf(function($self) use($data) {
            return $self . '?' . http_build_query(array_merge($_GET,$data));
        });
    }
}

// get plugin instance
$plugin = Plugins::getInstance();

// registering menus
$plugin->registerMenu('membership', 'Daftar Online', __DIR__ . '/pages/membership/index.php');
