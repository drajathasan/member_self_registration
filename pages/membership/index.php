<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2021-05-07 05:25:56
 * @File name           : index.php
 */
use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Table\Schema;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SB . 'admin/default/session.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
// end dependency

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// Define section
$section = $_GET['section']??null;

// setup queries
$schemas = DB::getInstance()->query('select * from self_registration_schemas');
$schemaById = DB::getInstance()->prepare('select * from self_registration_schemas where id = ?');
$activeSchema = DB::getInstance()->query('select * from self_registration_schemas where status = 1');

/*---- Http Request Process ----*/
// an action for handle request by routes
$action = $_POST['action']??$_GET['action']??null;

// route list
$routes = [
    'export' => ['schemaById' => $schemaById],
    'create_schema' => [],
    'active_schema' => [],
    'form_config' => [],
    'drop_schema' => ['schemaById' => $schemaById],
    'active_schema' => [],
    'acc' => ['activeSchema' => $activeSchema],
    'delete_reg' => ['activeSchema' => $activeSchema]
];

$params = $routes[$action]??null;

if ($params !== null) action($action, $params);
/*---- End of Http Request Process ----*/

$page_title = 'Daftar Online';

if (!isset($_GET['headless'])) {
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <div class="sub_section <?= $schemas->rowCount() > 0 ? 'd-block' : 'd-none' ?>">
            <form name="search" action="<?= pluginUrl(reset: true) ?>" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
                <input type="text" name="keywords" class="form-control col-md-3" /><?php if (isset($_GET['expire'])) { echo '<input type="hidden" name="expire" value="true" />'; } ?>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
            </form>
            <div class="btn-group">
                <?php if ($activeSchema->rowCount() < 1): ?>
                    <a href="<?= pluginUrl(['section' => 'add_schema']) ?>" class="btn btn-outline-secondary" ><i class="fa fa-plus"></i> Tambah Skema Baru</a>
                <?php else: ?>
                    <?php
                    $activeSchemaData = getActiveSchemaData();
                    $path = trim(strtolower(str_replace(' ', '_', $activeSchemaData->name)));
                    ?>
                    <a href="<?= pluginUrl(reset: true) ?>" class="btn btn-primary"><i class="fa fa-list"></i> Daftar Anggota</a>
                    <a target="_blank" href="<?= SWB . '?p=' . $path ?>" class="notAJAX btn btn-success"><i class="fa fa-link"></i> Buka Form di OPAC</a>
                    <a href="<?= pluginUrl(['section' => 'form_config']) ?>" class="btn btn-outline-secondary"><i class="fa fa-cog"></i> Pengaturan Form</a>
                    <?php if ($section !== 'list'): ?>
                    <a href="<?= pluginUrl(['section' => 'list']) ?>" class="btn btn-outline-secondary"><i class="fa fa-list"></i> Daftar Skema</a>
                    <?php elseif ($section === 'list'): ?>
                    <a href="<?= pluginUrl(['section' => 'add_schema']) ?>" class="btn btn-outline-secondary" ><i class="fa fa-plus"></i> Tambah Skema Baru</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="alert alert-info <?= !isset($_GET['section']) && $schemas->rowCount() > 0 && $activeSchema->rowCount() < 1 ? '' : 'd-none' ?>">
    <strong>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill mr-2" viewBox="0 0 16 16">
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
        </svg>
        Anda belum menentukan skema mana yang akan digunakan
    </strong>
</div>
<?php
}

// Routing page
if (!$section) {
    if ($activeSchema->rowCount() < 1) include __DIR__ . DS . 'list.inc.php';
    else include __DIR__ . DS . 'active_list.inc.php';
} else if (file_exists($filepath = __DIR__ . DS . basename($section) . '.inc.php')) {
    include $filepath;
}