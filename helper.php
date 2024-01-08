<?php
use SLiMS\Captcha\Factory as Captcha;
use SLiMS\Filesystems\Storage;

if (!function_exists('getActiveSchemaData'))
{
    function getActiveSchemaData()
    {
        $state = \SLiMS\DB::getInstance()->query('select * from self_registration_schemas where status = 1');

        return $state->rowCount() ? $state->fetchObject() : null;
    }
}


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
        
        return Url::getSelf(function($self) use($data) {
            return $self . '?' . http_build_query(array_merge($_GET,$data));
        });
    }
}

if (!function_exists('textColor')) {
    // source : https://www.bitbook.io/php-function-to-calculate-the-best-font-color-for-a-background-color/
    function textColor($hexCode){
        $redHex = substr($hexCode,0,2);
        $greenHex = substr($hexCode,2,2);
        $blueHex = substr($hexCode,4,2);
    
        $r = (hexdec($redHex)) / 255;
        $g = (hexdec($greenHex)) / 255;
        $b = (hexdec($blueHex)) / 255;
    
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        // human eye sees 60% easier
        if($brightness > .6){
            return '000000';
        }else{
            return 'ffffff';
        }
    }
}

if (!function_exists('formGenerator'))
{
    function formGenerator($data, $record = [], $actionUrl = '', $opac = null)
    {
        $structure = json_decode($data->structure, true);
        $option = json_decode($data->option??'');
        $info = json_decode($data->info);

        ob_start();

        $withUpload = '';
        if (($option?->image??false)) $withUpload = 'enctype="multipart/form-data"';

        echo '<form method="POST" action="' . $actionUrl . '" ' . $withUpload . '>';

        if ($key = flash()->includes('self_regis_error'))
        {
            flash()->danger($key);
        }

        if ($actionUrl === '' || stripos($actionUrl, 'admin') !== false) {
            if ($actionUrl === '') {
                echo '<h3>Pratinjau</h3>';
                echo '<h5>Skema ' . $data->name . '</h5>';
            } else {
                echo '<h3>Pratinjau Data</h3>';
                echo '<h5>Calon anggota ' . $record['member_name'] . '</h5>';
            }
        } else {
            if ($opac !== null) $opac->page_title = $info->title;
            echo '<div class="alert alert-info"' . strip_tags($info->desc, '<p><a><i><em><h1><h2><h3><ul><ol><li>') . '</div>';
        }
        
        foreach ($structure as $key => $column) {
            echo <<<HTML
            <div class="my-3">
                <label class="form-label"><strong>{$column['name']}</strong></label>
            HTML;

            $defaultValue = $record[$column['field']]??$record[$column['advfield']]??'';

            if ($column['advfieldtype'] == 'enum') {
                list($name, $detail) = explode(',', $column['advfield']);
                $defaultValue = $record[$name]??'';
            }

            $is_required = $column['is_required'] === true ? ' required' : '';
    
            switch ($column['field']) {
                case 'mpasswd':
                    if ($actionUrl !== '') {
                        $is_required = '';
                    }
                    echo <<<HTML
                    <br>
                    <small>tulis dibawah berikut</small>
                    <input type="password" placeholder="masukan {$column['name']} anda" name="form[{$key}]" id="pass1" class="form-control" {$is_required}>
                    <small>konfirmasi ulang password anda</small>
                    <input type="password" name="confirm_password" placeholder="masukan ulang {$column['name']} anda" id="pass2" class="form-control" {$is_required}>
                    HTML;
                    break;

                case 'gender':
                    $man = $defaultValue != 1 ?:'selected';
                    $woman = $defaultValue != 0 ?:'selected';
                    echo <<<HTML
                    <select name="form[{$key}]" class="form-control" {$is_required}>
                        <option>Pilih</option>
                        <option value="1" {$man}>Laki-Laki</option>
                        <option value="0" {$woman}>Perempuan</option>
                    </select>
                    HTML;
                    break;

                case 'member_address':
                    echo <<<HTML
                    <textarea name="form[{$key}]" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}>{$defaultValue}</textarea>
                    HTML;
                    break;

                case 'member_type_id':
                    $memberType = \SLiMS\DB::getInstance()->query('select member_type_id, member_type_name from mst_member_type');
                    echo '<select class="form-control" name="form[' . $key . ']" ' . $is_required . '>';
                    echo '<option value="0">Pilih</option>';
                    while ($result = $memberType->fetch(PDO::FETCH_NUM)) {
                        echo '<option value="' . $result[0] . '" ' . ($defaultValue != $result[0] ?:'selected') . '>' . $result[1] . '</option>';
                    }
                    echo '</select>';
                    break;
                
                case 'advance':
                    switch ($column['advfieldtype']) {
                        case 'varchar':
                        case 'int':
                            $types = ['varchar' => 'text', 'int' => 'number'];
                            $type = $types[$column['advfieldtype']];
                            echo <<<HTML
                            <input type="{$type}" name="form[{$key}]" value="{$defaultValue}" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}/>
                            HTML;
                            break;

                        case 'text':
                            echo <<<HTML
                            <textarea name="form[{$key}]" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}>{$defaultValue}</textarea>
                            HTML;
                            break;
                        
                        case 'enum':
                            list($field,$list) = explode(',', $column['advfield']);
                            echo '<select name="form[' . $key . ']" class="form-control" '.$defaultValue.'>';
                            echo '<option value="">Pilih</option>';
                            $selected = '';
                            foreach (explode('|', $list) as $item) {
                                if ($defaultValue == $item) $selected = 'selected';
                                echo '<option value="'.$item.'" '.$selected.'>' . $item . '</option>';
                                $selected = '';
                            }
                            echo '</select>';
                            break;
                    }
                    break;

                case 'member_image':
                    if (($option?->image??null) === null) {
                        echo '<div class="alert alert-info font-weight-bold">Anda belum mengantur ruas ini pada "Pengaturan Form"</div>';
                    } else {
                        if (!isset($record['member_image'])) {
                            echo <<<HTML
                            <input type="file" name="member_image" placeholder="masukan {$column['name']} anda" class="form-control d-block" {$is_required}/>
                            <small>Maksimal ukuran file foto adalah 2MB</small>
                            HTML;
                        } else {
                            $image = Storage::images()->isExists('persons/' . $record['member_image']) ? $record['member_image'] : 'avatar.jpg';
                            echo '<img class="d-block"src="' . SWB . 'lib/minigalnano/createthumb.php?filename=images/persons/' . $image . '&width=120"/>';
                        }
                    }
                    break;

                default:
                    $types = ['birth_date' => 'date', 'member_email' => 'email'];
                    $type = isset($types[$column['field']]) ? $types[$column['field']] : 'text';
                    echo <<<HTML
                    <input type="{$type}" name="form[{$key}]" value="{$defaultValue}" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}/>
                    HTML;
                    break;
            }

            echo <<<HTML
            </div>
            HTML;
        }
        if ($actionUrl !== '') {
            // Captcha initialize
            $captcha = Captcha::section('memberarea');

            if (strpos($actionUrl, 'admin') === false) {
                if (($option?->captcha??false) && $captcha->isSectionActive()) 
                {
                    echo '<div class="captchaMember my-2">';
                    echo $captcha->getCaptcha();
                    echo '</div>';
                }
    
                echo \Volnix\CSRF\CSRF::getHiddenInputString();
                echo '<div class="form-group">
                    <button class="btn btn-primary" type="submit" name="save">Daftar</button>
                    <button class="btn btn-outline-secondary" type="reset" name="save">Batal</button>
                </div>';
            } else {
                echo '<div class="form-group">
                    <button class="btn btn-success" type="submit" name="acc">Setujui</button>
                </div>';
            }
        }
        echo '</form>';
        return ob_get_clean();
    }
}