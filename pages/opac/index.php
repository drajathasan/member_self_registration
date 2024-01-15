<?php
use SLiMS\DB;
use SLiMS\Url;
use SLiMS\Filesystems\Storage;
use SLiMS\Captcha\Factory as Captcha;
use Volnix\CSRF\CSRF;

$schema = getActiveSchemaData();
if ($schema === null) throw new Exception("Tidak ada Skema yang aktif.");

if (isset($_POST['form'])) {
    try {
        $structure = json_decode($schema->structure, true);
        $option = json_decode($schema->option??'');

        $passwordFieldId = @array_pop(array_keys(array_filter($structure, function($data){
            return $data['field'] === 'mpasswd';
        })));

        $memberImageFieldId = @array_pop(array_keys(array_filter($structure, function($data){
            return $data['field'] === 'member_image';
        })));

        if (!CSRF::validate($_POST)) {
            // session_unset();
            // throw new Exception(__('Invalid login form!'));
        }

        # <!-- Captcha form processing - start -->
        $captcha = Captcha::section('memberarea');
        if (($option?->captcha??false) && $captcha->isSectionActive() && $captcha->isValid() === false) {
            // set error message
            $message = isDev() ? $captcha->getError() : __('Wrong Captcha Code entered, Please write the right code!'); 
            // What happens when the CAPTCHA was entered incorrectly
            session_unset();
            throw new Exception($message);
        }
        # <!-- Captcha form processing - end -->

        if (isset($_POST['form'][$passwordFieldId]) && $_POST['form'][$passwordFieldId] !== $_POST['confirm_password']) {
            throw new Exception("Password tidak cocok");
        }

        $sqlSet = [];
        $sqlParams = [];
        $sqlRaw = 'insert ignore into self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name))) . ' set ';
        
        foreach ($_POST['form'] as $order => $value) {

            $detail = $structure[$order];
            
            if ($detail['field'] === 'advance') {
                if (in_array($detail['advfieldtype'], ['enum','enum_radio','text_multiple'])) {
                    $field = explode(',', $detail['advfield']);
                    $detail['field'] = $field[0];
                } else {
                    $detail['field'] = $detail['advfield'];
                }
            }

            $sqlSet[] = '`' .$detail['field'] . '` = ?';

            if ($detail['field'] === 'mpasswd') {
                $value = password_hash($value, PASSWORD_BCRYPT);
            }

            if (is_array($value)) $value = json_encode($value);

            $sqlParams[] = $value;
        }

        if (($option?->image??false)) {
            
            if ($_FILES['member_image']['error'] == 1) {
                $max = ini_get('upload_max_filesize');
                throw new Exception("Gagal membaca file foto profil, karena file terindikasi lebih besar dari nilai di server ({$max}B)");
            }

            // image uploading
            $images_disk = Storage::images();
            if (!empty($_FILES['member_image']) AND $_FILES['member_image']['size']) {

                // Title
                $newFilename = md5(rand(1,1000) . date('this'));

                // create upload object
                $image_upload = $images_disk->upload('member_image', function($images) use($sysconf) {
                    // Extension check
                    $images->isExtensionAllowed($sysconf['allowed_images']);

                    // File size check
                    $images->isLimitExceeded(500*1024);

                    // destroy it if failed
                    if (!empty($images->getError())) $images->destroyIfFailed();

                    // remove exif data
                    if (empty($images->getError())) $images->cleanExifInfo();

                })->as('persons' . DS . $newFilename);

                if ($image_upload->getUploadStatus()) {
                    $sqlSet[] = '`member_image` = ?';
                    $sqlParams[] = $image_upload->getUploadedFileName();
                } else {
                    throw new Exception('Gagal upload foto profil karena : ' . $image_upload->getError());
                }
            }
        }
        $sqlSet[] = '`created_at` = now()';

        $insert = DB::getInstance()->prepare($sqlRaw . implode(',', $sqlSet));
        $insert->execute($sqlParams);

        if ($insert->rowCount() == 0) throw new Exception('Data tidak berhasil disimpan, mungkin karena data sudah ada.');
        
        if ($option?->message_after_save??false) toastr($option->message_after_save)->jsAlert();

    } catch (Exception $e) {
        redirect()->withMessage('self_regis_error', $e->getMessage())->back();
    }
}

if (!isset($opac)) $opac = $this;

$path = str_replace(['\'', '"'], '', strip_tags($_GET['p']));
echo formGenerator($schema, actionUrl: Url::getSelf(fn($self) => $self . '?p=' . $path), opac: $opac);