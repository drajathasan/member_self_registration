<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2021-05-07 05:25:56
 * @File name           : index.php
 */
use SLiMS\DB;

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

if (isset($_POST['schema_id'])) {
    $db = DB::getInstance();
    $db->query('update self_registartion_schemas set status = 0');
    $db->prepare('update self_registartion_schemas set status = 1 where id = ?')->execute([$_POST['schema_id']]);
    exit;
}


$schemas = DB::getInstance()->query('select * from self_registartion_schemas');
$schemaById = DB::getInstance()->prepare('select * from self_registartion_schemas where id = ?');
$activeSchema = DB::getInstance()->query('select * from self_registartion_schemas where status = 1');

$page_title = 'Daftar Online';

if (!isset($_GET['headless'])) {
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <div class="sub_section <?= $schemas->rowCount() > 0 ? 'd-block' : 'd-none' ?>">
            <div class="btn-group">
                <?php if ($activeSchema->rowCount() < 1): ?>
                    <a href="<?= pluginUrl(['section' => 'add_schema']) ?>" class="btn btn-outline-secondary" ><i class="fa fa-pencil"></i> Tambah Skema Baru</a>
                <?php else: ?>
                    <a href="<?= pluginUrl(reset: true) ?>" class="btn btn-primary"><i class="fa fa-list"></i> Daftar Anggota</a>
                    <a href="<?= pluginUrl(['section' => 'form_config']) ?>" class="btn btn-outline-secondary"><i class="fa fa-cog"></i> Pengaturan Form</a>
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

if (!isset($_GET['section'])) {
    if ($activeSchema->rowCount() < 1) include __DIR__ . DS . 'list.inc.php';
    else include __DIR__ . DS . 'active_list.inc.php';
} else if (file_exists($filepath = __DIR__ . DS . basename($_GET['section']) . '.inc.php')) {
    include $filepath;
}