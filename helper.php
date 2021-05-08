<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-05-08 09:15:53
 * @modify date 2021-05-08 09:15:53
 * @desc [description]
 */

// Save Register
function saveRegister()
{
    global $dbs, $sysconf;

    // set meta
    $meta = $sysconf['selfRegistration'];

    // Set Table Attribute
    $table = (isset($meta['separateTable']) && (int)$meta['separateTable'] == 1) ? 'member_online': 'member';

    // load simbio dbop
    require_once SB.'simbio2/simbio_DB/simbio_dbop.inc.php';

    if (!\Volnix\CSRF\CSRF::validate($_POST)) {
        echo '<script type="text/javascript">';
        echo 'alert("Invalid login form!");';
        echo 'location.href = \'index.php?p=daftar_online\';';
        echo '</script>';
        exit();
    }

    # <!-- Captcha form processing - start -->
    if ($sysconf['captcha']['member']['enable']) {
        if ($sysconf['captcha']['member']['type'] == 'recaptcha') {
            require_once LIB . $sysconf['captcha']['member']['folder'] . '/' . $sysconf['captcha']['member']['incfile'];
            $privatekey = $sysconf['captcha']['member']['privatekey'];
            $resp = recaptcha_check_answer($privatekey,
                $_SERVER["REMOTE_ADDR"],
                $_POST["g-recaptcha-response"]);

            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly
                header("location:index.php?p=daftar_online&captchaInvalid=true");
                die();
            }
        } else if ($sysconf['captcha']['member']['type'] == 'others') {
            # other captchas here
        }
    }
    # <!-- Captcha form processing - end -->

    // set up data
    $map = [
            'memberName' => 'member_name', 'memberBirth' => 'birth_date', 
            'memberInst' => 'inst_name', 'memberSex' => 'gender',
            'memberAddress' => 'member_address', 'memberPhone' => 'member_phone',
            'memberEmail' => 'member_email'
           ];

    $data = [];
    foreach ($map as $key => $column_name) {
        if (isset($_POST[$key]))
        {
            $data[$column_name] = str_replace(['"'], '', strip_tags($_POST[$key]));
        }
    }

    if ((isset($_POST['memberPassword1']) && !empty($_POST['memberPassword1'])) && (isset($_POST['memberPassword2']) && !empty($_POST['memberPassword2'])))
    {
        if ($_POST['memberPassword2'] === $_POST['memberPassword1'])
        {
            $data['mpasswd'] = password_hash($_POST['memberPassword1'], PASSWORD_BCRYPT);
        }
        else
        {
            echo '<script type="text/javascript">';
            echo 'alert("Password tidak boleh kosong");';
            echo 'location.href = \'index.php?p=daftar_online\';';
            echo '</script>';
            exit();
        }
    }
    else
    {
        echo '<script type="text/javascript">';
        echo 'alert("Password tidak boleh kosong");';
        echo 'location.href = \'index.php?p=daftar_online\';';
        echo '</script>';
        exit();
    }

    // Date time
    $data['input_date'] = date('Y-m-d');
    $data['last_update'] = date('Y-m-d');


    if ($table === 'member' && (int)$meta['autoActive'] === 0)
    {
        $data['is_pending'] = 1;
    }

    if ($table === 'member')
    {
        $data['member_id'] = substr(md5($data['member_name']), 0,20);
        $data['expire_date'] = date('Y-m-d', strtotime("+1 year"));
    }

    // do insert
    // initialise db operation
    $sql = new simbio_dbop($dbs);

    // setup for insert
    $insert = $sql->insert($table, $data);

    if ($insert)
    {
        echo '<script type="text/javascript">';
        echo 'alert("Berhasil terdaftar. '.$meta['regisInfo'].'");';
        echo 'location.href = \'index.php?p=daftar_online\';';
        echo '</script>';
        exit();
    }
    else
    {
        echo '<script type="text/javascript">';
        echo 'alert("Gagal terdaftar segera hubungi petugas perpustakaan, untuk info selanjutnya. '.$sql->error.'");';
        echo 'location.href = \'index.php?p=daftar_online\';';
        echo '</script>';
        exit();
    }

    // header("location:index.php?p=daftar_online");
    exit();
}

// update register
function updateRegister()
{
    global $dbs, $sysconf;

    if (isset($_POST['updateRecordID']) && isset($_POST['saveDataMember']))
    {
        // set meta
        $meta = $sysconf['selfRegistration'];

        // Set Table Attribute
        $table = (isset($meta['separateTable']) && (int)$meta['separateTable'] == 1) ? 'member_online': 'member';

        // load simbio dbop
        require_once SB.'simbio2/simbio_DB/simbio_dbop.inc.php';

        // initialise db operation
        $sql = new simbio_dbop($dbs);
        $updateRecId = $dbs->escape_string($_POST['updateRecordID']);

        if ($table === 'member_online')
        {
            // select data
            $dataQuery = $dbs->query('select * from member_online where id = \''.$updateRecId.'\'');

            $memberId = $dbs->escape_string($_POST['memberID']);
            $dataResult = ($dataQuery->num_rows > 0) ? $dataQuery->fetch_assoc() : [];

            // check status
            if ((int)$meta['editableData'] === 0 && count($dataResult) > 0)
            {
                // unset id
                unset($dataResult['id']);
                // merge data
                $dataOnline = array_merge(['member_id' => $memberId, 'expire_date' => date('Y-m-d', strtotime("+1 year"))], $dataResult);
                // prepare to insert
                $insert = $sql->insert('member', $dataOnline);

                if ($insert)
                {
                    $sql->delete('member_online', "id='$updateRecId'");
                    utility::jsToastr('Self Register Form', 'Berhasil menyimpan data', 'success');
                    echo '<script>parent.$("#mainContent").simbioAJAX("'.MWB.'membership/index.php")</script>';
                    exit;
                }
                else
                {
                    utility::jsAlert($sql->error);
                    utility::jsToastr('Self Register Form', 'Gagal menyimpan data 1', 'error');
                    exit;
                }
            }
            else
            {
                // set up data
                $map = [
                        'memberName' => 'member_name', 'memberBirth' => 'birth_date', 
                        'memberInst' => 'inst_name', 'memberSex' => 'gender',
                        'memberAddress' => 'member_address', 'memberPhone' => 'member_phone',
                        'memberEmail' => 'member_email'
                    ];

                $data = [];
                foreach ($map as $key => $column_name) {
                    if (isset($_POST[$key]))
                    {
                        $data[$column_name] = str_replace(['"'], '', strip_tags($_POST[$key]));
                    }
                }
                
                $data['member_id'] = $memberId;
                $data['mpasswd'] = (isset($dataResult['mpasswd'])) ? $dataResult['mpasswd'] : 'Tidak Ada Password';
                $data['input_date'] = (isset($dataResult['input_date'])) ? $dataResult['input_date'] : date('Y-m-d');
                $data['last_update'] = date('Y-m-d');
                $data['expire_date'] = date('Y-m-d', strtotime("+1 year"));

                $insert = $sql->insert('member', $data);

                if ($insert)
                {
                    $sql->delete('member_online', "id='$updateRecId'");
                    utility::jsToastr('Self Register Form', 'Berhasil menyimpan data', 'success');
                    echo '<script>parent.$("#mainContent").simbioAJAX("'.MWB.'membership/index.php")</script>';
                    exit;
                }
                else
                {
                    utility::jsToastr('Self Register Form', 'Gagal menyimpan data 2', 'error');
                    exit;
                }
            }
        }
        else
        {
            $update = $sql->update('member', ['member_id' => $updateRecId, 'isPending' => (int)$_POST['isPending']], "member_id = '$updateRecId'");

            if ($update)
            {
                utility::jsToastr('Self Register Form', 'Berhasil menyimpan data', 'success');
                echo '<script>parent.$("#mainContent").simbioAJAX("'.MWB.'membership/index.php")</script>';
                exit;
            }
            else
            {
                utility::jsToastr('Self Register Form', 'Gagal menyimpan data 3', 'error');
                exit;
            }
        }
        exit;
    }
}

// save Setting
function saveSetting($self)
{
    global $dbs;

    // load simbio dbop
    require_once SB.'simbio2/simbio_DB/simbio_dbop.inc.php';

    // action
    if (isset($_POST['saveData']))
    {
        // save into serialize data
        $allowData = ['selfRegistrationActive','title','autoActive','separateTable','useRecaptcha','regisInfo','editableData'];

        // loop for filter
        foreach ($_POST as $key => $value) {
            if (in_array($key, $allowData))
            {
                $_POST[$key] = $dbs->escape_string($value);
            }
            else
            {
                unset($_POST[$key]);
            }
        }

        // copy template
        copyTemplate($_POST);
        
        // serialize data
        $data = serialize($_POST);

        // initialise db operation
        $sql = new simbio_dbop($dbs);

        // Delete data
        $sql->delete('setting', 'setting_name = "selfRegistration"');

        // setup for insert
        $insert = $sql->insert('setting', ['setting_name' => 'selfRegistration', 'setting_value' => $data]);

        if ($insert)
        {
            if ((int)$_POST['separateTable'] === 1 )
            {
                createTable();
            }

            // set alert
            utility::jsToastr('Self Register Form', 'Berhasil menyimpan data', 'success');
            echo '<script>parent.$("#mainContent").simbioAJAX("'.$self.'")</script>';
        }
        else
        {
            utility::jsToastr('Self Register Form', 'Gagal menyimpan data '.$sql->error, 'error');
        }
        exit;
    }
} 

// delete item
function deleteItem($self)
{
    global $dbs,$meta;

    if ((isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])))
    {
        // Set Table Attribute
        $table = (isset($meta['separateTable']) && (int)$meta['separateTable'] == 1) ? ['member_online', "id = '{id}'"] : ['member', "member_id = '{id}'"];

        // load simbio dbop
        require_once SB.'simbio2/simbio_DB/simbio_dbop.inc.php';

        // process delete
        // initialise db operation
        $sql = new simbio_dbop($dbs);
        
        $fail = 0;
        foreach ($_POST['itemID'] as $itemID) {
            $delete = $sql->delete($table[0], str_replace('{id}', $dbs->escape_string($itemID), $table[1]));

            if (!$delete)
            {
                $fail++;
            }
        }
        

        if (!$fail)
        {
            utility::jsToastr('Register Member Online', 'Berhail menghapus data.', 'success');
            echo '<script>parent.$("#mainContent").simbioAJAX("'.$self.'")</script>';
        }
        else
        {
            utility::jsToastr('Register Member Online', 'Gagal menghapus data', 'error');
        }
        exit;
    }
}

// copy template
function copyTemplate($data)
{
    if ((int)$data['selfRegistrationActive'] === 1 && !file_exists(SB.'lib'.DS.'contents'.DS.'daftar_online.inc.php'))
    {
        copy(__DIR__.DS.'daftar_online.inc.php', SB.'lib'.DS.'contents'.DS.'daftar_online.inc.php');
    }
    else if ((int)$data['selfRegistrationActive'] === 0 && file_exists(SB.'lib'.DS.'contents'.DS.'daftar_online.inc.php'))
    {
        unlink(SB.'lib'.DS.'contents'.DS.'daftar_online.inc.php');
    }
}

// Creating Table
function createTable()
{
    global $dbs;

    // setup query
    @$dbs->query("CREATE TABLE IF NOT EXISTS `member_online` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `member_name` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
        `birth_date` date DEFAULT NULL,
        `inst_name` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
        `gender` int(1) NOT NULL,
        `member_address` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
        `member_phone` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
        `member_email` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
        `mpasswd` varchar(64) COLLATE utf8mb4_bin DEFAULT NULL,
        `input_date` date DEFAULT NULL,
        `last_update` date DEFAULT NULL
      ) ENGINE='MyISAM';");
    
}

// compose Url
function getCurrentUrl($query = [])
{
    
    return $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge(['mod' => $_GET['mod'], 'id' => $_GET['id']], $query));
}

// premission check
function dirCheckPermission()
{
    $msg = '';
    if (!is_writable(SB.'lib'.DS.'contents'.DS))
    {
        $msg = 'Direktori : <b>'.SB.'lib'.DS.'contents'.DS.'</b> tidak dapat ditulis!. Harap merubah permission pada folder tersebut.';
    }

    return $msg;
}