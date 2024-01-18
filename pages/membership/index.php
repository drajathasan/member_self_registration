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

if (isset($_POST['form_config'])) {
    // Fetch active schema
    $update = DB::getInstance()->prepare('update `self_registration_schemas` set `option` = ? where `id` = ?');
    $update->execute([json_encode($_POST['form_config']), $_POST['schema_id']]);
    
    toastr('Data berhasil disimpan')->success();
    redirect()->simbioAJAX(pluginUrl(reset: true));
    exit;
}

// Schema modification process
if (isset($_POST['schema_id']) && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Fetch active schema
    $schemaById->execute([$_POST['schema_id']]);
    $detail = $schemaById->fetchObject();

    // Delete schema data
    DB::getInstance()->prepare('delete from `self_registration_schemas` where `id` = ?')->execute([$_POST['schema_id']]);
    Schema::drop('self_registration_' . trim(str_replace(' ', '_', strtolower($detail->name))));

    // filtering only for advance field only
    $advanceOnly = array_filter(json_decode($detail->structure, TRUE), function($column){
        return $column['field'] === 'advance';
    });

    // Set only column name
    $fieldsToDrop = array_map(function($data) {
        if (preg_match('/\|/', $data['advfield'])) {
            $data['advfield'] = explode(',', $data['advfield'])[0];
        }
        return $data['advfield'];
    }, $advanceOnly);

    // Drop column from member custom
    foreach($fieldsToDrop as $column) Schema::dropColumn('member_custom', 'adv_' . $column);
    exit;
}

// Activate schema data
if (isset($_POST['schema_id']) && isset($_POST['action']) && $_POST['action'] == 'activate') {
    $db = DB::getInstance();
    $db->query('update self_registration_schemas set status = 0');
    $db->prepare('update self_registration_schemas set status = 1 where id = ?')->execute([$_POST['schema_id']]);
    exit;
}

if (isset($_POST['acc']) && $activeSchema->rowCount() > 0) {

    $member_id = $_POST['form']['member_id']??0;
    Plugins::getInstance()->execute('member_self_before_acc', ['member_id' => $member_id]);

    $schema = $activeSchema->fetchObject();
    $baseTable = 'self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name)));

    $data = DB::getInstance()->prepare('select * from ' . $baseTable . ' where member_id = ?');
    $data->execute([$member_id]);

    if ($data->rowCount() < 1) {
        redirect()->back();
        exit;
    }

    $result = $data->fetch(PDO::FETCH_ASSOC);
    $result_customs = [];

    $columnNames = array_keys($result);
    foreach ($columnNames as $columnName) {
        $newValue = $_POST['form'][$columnName]??'';

        if (is_array($newValue)) $newValue = json_encode($newValue);

        if (substr($columnName, 0,4) === 'adv_') {
            if (!isset($result_customs['member_id'])) {
                $result_customs['member_id'] = $member_id;
            }
            $result_customs[$columnName] = $newValue;
            unset($result[$columnName]);
            continue;
        }

        if ($columnName === 'mpasswd' && !empty($newValue)) {
            $_POST['form'][$columnName] = password_hash($newValue, PASSWORD_BCRYPT);
        }

        if (empty($newValue)) continue;
        if (is_array($newValue)) $newValue = json_encode($newValue);

        $result[$columnName] = $newValue;
    }

    $result['input_date'] = $result['created_at'];
    unset($result['created_at']);
    unset($result['updated_at']);

    $result['register_date'] = date('Y-m-d');
    $result['member_since_date'] = date('Y-m-d');
    $result['last_update'] = date('Y-m-d');
    $result['expire_date'] = date('Y-m-d', strtotime('+1 year'));
    $result['is_new'] = 1;

    if (isset($result['member_type_id'])) {
        $memberType = DB::getInstance()->prepare('select member_periode from mst_member_type where member_type_id = ?');
        $memberType->execute([$result['member_type_id']]);

        if ($memberType->rowCount() == 1) {
            $memberTypeData = $memberType->fetchObject();
            $periode = $memberTypeData->member_periode;
            $result['expire_date'] = date('Y-m-d', strtotime('+' . $periode . ' days'));
        }
    }

    $columns = implode(',', array_map(function($column) {
        return '`' . $column . '` = ?';
    }, array_keys($result)));

    $insert = DB::getInstance()->prepare(<<<SQL
    insert ignore 
            into `member`
                set {$columns}
    SQL);

    $process = $insert->execute(array_values($result));

    if (count($result_customs) && $process) {
        $column_customs = implode(',', array_map(function($column) {
            return '`' . $column . '` = ?';
        }, array_keys($result_customs)));

        $insert_custom = DB::getInstance()->prepare(<<<SQL
        insert ignore 
                into `member_custom`
                    set {$column_customs}
        SQL);

        $process_custom = $insert_custom->execute(array_values($result_customs));
    }

    if ($process) {
        
        if (isset($process_custom) && $process_custom == false) {
            toastr('Gagal menyimpan data custom')->success();
        }
        
        toastr('Data berhasil disimpan')->success();
        echo '<script>top.jQuery.colorbox.close();</script>';

        // delete data
        $delete = DB::getInstance()->prepare('delete from ' . $baseTable . ' where member_id = ?');
        $delete->execute([$member_id]);
        
        echo '<script>top.jQuery.colorbox.close();</script>';
        redirect()->simbioAJAX(pluginUrl(reset: true));
    }

    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_reg') {
    $schema = $activeSchema->fetchObject();
    $baseTable = 'self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name)));

    $delete = DB::getInstance()->prepare('delete from ' . $baseTable . ' where `member_id` = ?');
    $delete->execute([$_GET['member_id']]);

    echo '<script>top.jQuery.colorbox.close();</script>';
    redirect()->simbioAJAX(pluginUrl(reset: true));
    exit;
}

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