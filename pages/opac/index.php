<?php
use SLiMS\DB;
use SLiMS\Url;
use SLiMS\Filesystems\Storage;
$schema = getActiveSchemaData();
if ($schema === null) throw new Exception("Tidak ada Skema yang aktif.");

if (isset($_POST['form'])) {
    try {
        $structure = json_decode($schema->structure, true);
        $option = json_decode($schema->option);

        $passwordFieldId = @array_pop(array_keys(array_filter($structure, function($data){
            return $data['field'] === 'mpasswd';
        })));

        $memberImageFieldId = @array_pop(array_keys(array_filter($structure, function($data){
            return $data['field'] === 'member_image';
        })));

        if ($_POST['form'][$passwordFieldId] !== $_POST['confirm_password']) throw new Exception("Password tidak cocok");

        $sqlSet = [];
        $sqlParams = [];
        $sqlRaw = 'insert ignore into self_registration_' . trim(strtolower(str_replace(' ', '_', $schema->name))) . ' set ';
        foreach ($_POST['form'] as $order => $value) {
            $detail = $structure[$order];
            if ($detail['field'] === 'advance') {
                $detail['field'] = $detail['advfield'];
            }
            $sqlSet[] = '`' .$detail['field'] . '` = ?';
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

        $insert = DB::query($sqlRaw . implode(',', $sqlSet), $sqlParams);
        $insert->run();

        if (!empty($error = $insert->getError())) throw new Exception($error);
        
        toastr($option->message_after_save)->jsAlert();

    } catch (Exception $e) {
        redirect()->withMessage('self_regis_error', $e->getMessage())->back();
    }
}

if (!isset($opac)) $opac = $this;

$path = str_replace(['\'', '"'], '', strip_tags($_GET['p']));
echo formGenerator($schema, actionUrl: Url::getSelf(fn($self) => $self . '?p=' . $path), opac: $opac);