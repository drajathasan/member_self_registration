<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2021-05-07 05:25:56
 * @File name           : index.php
 */

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
require __DIR__ . '/helper.php';
// end dependency

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

$page_title = 'Daftar Online';

// set meta
$meta = [];
if (isset($sysconf['selfRegistration']))
{
    $meta = $sysconf['selfRegistration'];
}

/* Action Area */
// save setting
saveSetting(getCurrentUrl(['memberList' => 1]));

updateRegister(getCurrentUrl(['memberList' => 1]));

// delete item
deleteItem(getCurrentUrl(['memberList' => 1]));
/* End Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?= getCurrentUrl(['memberList' => 1]) ?>" class="btn btn-primary">Daftar Anggota Online</a>
                <a href="<?= getCurrentUrl(['formSetting' => 1]) ?>" class="btn btn-success">Pengaturan Form</a>
            </div>
        </div>
    </div>
</div>

<?php

if (!empty(dirCheckPermission()))
{
    die('<div class="errorBox">' . dirCheckPermission() . '</div>');
}

// set view
switch (true) {
    case (count($meta) === 0):
        include __DIR__ . '/form-element.inc.php';
        break;

    case (isset($_GET['formSetting']) && count($meta) > 0):
        include __DIR__ . '/form-element.inc.php';
        break;

    case (isset($_GET['memberList']) && count($meta) > 0):
        include __DIR__ . '/member-grid.inc.php';
        break;

    case ((isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) && count($meta) > 0):
        include __DIR__ . '/member-edit.inc.php';
        break;

    default:
        include __DIR__ . '/member-grid.inc.php';
        break;
}
?>
<script>
    // set edit link
    let a = document.querySelectorAll('.editLink');

    a.forEach((el,index) => {
        var href = el.getAttribute('href').replace(/(\&memberList=1)/g, '');
        a[index].setAttribute('href', href);
    })

    if (document.querySelector('.simbio_form_maker') !== null)
    {
        // set form to delete
        let form = document.querySelector('.simbio_form_maker');

        form.setAttribute('action', '<?= getCurrentUrl() ?>');
    }
</script>