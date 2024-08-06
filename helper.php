<?php
use SLiMS\Url;
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

if (!function_exists('action')) {
    function action(string $actionName, array $attribute = [])
    {
        extract($attribute);
        $trace = debug_backtrace(limit: 1);
        $info = pathinfo(array_pop($trace)['file']);
        
        if (file_exists($path = $info['dirname'] . DS . 'action' . DS . basename($actionName) . '.php')) {
            include $path;
        } else {
            throw new Exception('Action ' . $actionName . ' is not found!', 404);
        }
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
    /**
     * Form generator based on schema
     *
     * @param [type] $data
     * @param array $record
     * @param string $actionUrl
     * @param [type] $opac
     * @return void
     */
    function formGenerator($data, $record = [], $actionUrl = '', $opac = null)
    {
        $structure = json_decode($data->structure, true);
        $option = json_decode($data->option??'');
        $info = json_decode($data->info);

        ob_start();

        // Start form
        $js = '';
        $withUpload = '';
        if (($option?->image??false)) $withUpload = 'enctype="multipart/form-data"';

        echo '<form id="self_member" method="POST" action="' . $actionUrl . '" ' . $withUpload . '>';

        // set error
        if ($key = flash()->includes('self_regis_error'))
        {
            flash()->danger($key);
        }

        // set action url
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
            $descInfo = '<div class="alert alert-info p-3">' . strip_tags($info->desc, '<p><a><i><em><h1><h2><h3><ul><ol><li>') . '</div>';
        }

        if ($info->position == 'top' && isset($descInfo)) {
            echo $descInfo;
        }

        // Generate form structure
        foreach ($structure as $key => $column) {
            // Convert key to fieldname
            if (strpos($actionUrl, 'admin') == true) { 
                if (empty($column['advfield'])) {
                    $key = $column['field'];
                } else {
                    $advfield = explode(',', $column['advfield']);
                    $key = $advfield[0];
                }
            }

            // determine mandatory of the element
            $is_required = $column['is_required'] === true ? ' required' : '';

            // Set label element
            $required_mark = $is_required ? '<em class="text-danger">*</em>' : '';
            echo <<<HTML
            <div class="my-3">
                <label class="form-label"><strong>{$column['name']} {$required_mark}</strong></label>
            HTML;

            // Get default value
            $defaultValue = $record[$column['field']]??$record[$column['advfield']]??'';

            // special condition of some field type
            if (in_array($column['advfieldtype'], ['enum','enum_radio','text_multiple'])) {
                list($name, $detail) = explode(',', $column['advfield']);
                $defaultValue = $record[$name]??'';
            }
    
            // set html form element based on database field
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
                
                // Advance field element
                case 'advance':
                    switch ($column['advfieldtype']) {

                        // short text field
                        case 'varchar':
                        case 'int':
                            $types = ['varchar' => 'text', 'int' => 'number'];
                            $type = $types[$column['advfieldtype']];
                            echo <<<HTML
                            <input type="{$type}" name="form[{$key}]" value="{$defaultValue}" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}/>
                            HTML;
                            break;

                        // long text
                        case 'text':
                            echo <<<HTML
                            <textarea name="form[{$key}]" placeholder="masukan {$column['name']} anda" class="form-control" {$is_required}>{$defaultValue}</textarea>
                            HTML;
                            break;
                        
                        // select list
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

                        // Select list as radio button
                        case 'enum_radio':
                            $field = explode(',', $column['advfield']);
                            $uniqueId = md5($field[0]);
                            $checked = '';

                            if ($is_required) {
                                $js .= <<<HTML
                                if ($('.radio{$uniqueId}:checked').length < 1) {
                                    evt.preventDefault();
                                    alert('Pilih salah satu dari isian {$column['name']}');
                                    return;
                                }
                                HTML;
                            }

                            echo '<div class="d-flex flex-column">';
                            foreach (explode('|', trim($field[1])) as $optionKey => $value) {
                                if (empty($value)) continue;
                                if ($defaultValue == $value) $checked = 'checked';
                                echo '<div>
                                    <input class="radio'.$uniqueId.'" id="radio' . $uniqueId . '-' . $optionKey . '" data-title="' . $column['name'] . '" type="radio" name="form[' . $key . ']" value="' . $value . '" ' . $checked . '/>
                                    <label for="radio' . $uniqueId . '-' . $optionKey . '" style="cursor: pointer">' . $value . '</label>
                                </div>';
                            }
                            echo '</div>';
                            break;

                        // multiple choise data
                        case 'text_multiple':
                            $field = explode(',', $column['advfield']);
                            $uniqueId = md5($field[0]);
                            $defaultValue = json_decode(trim($defaultValue), true);
                            $checked = '';

                            if ($is_required) {
                                $js .= <<<HTML
                                if ($('.checkbox{$uniqueId}:checked').length < 1) {
                                    evt.preventDefault();
                                    alert('Pilih salah satu dari isian {$column['name']}');
                                    return;
                                }
                                HTML;
                            }

                            echo '<div class="d-flex flex-column">';
                            foreach (explode('|', trim($field[1])) as $optionKey => $value) {
                                if (empty($value)) continue;
                                if (in_array($value, $defaultValue??[])) $checked = 'checked';
                                echo '<div class="mx-3">
                                    <input class="checkbox'.$uniqueId.'" id="checkbox' . $uniqueId . '-' . $optionKey . '" type="checkbox" name="form[' . $key . '][]" value="' . $value . '" ' . $checked . '/>
                                    <label for="checkbox' . $uniqueId . '-' . $optionKey . '" style="cursor: pointer">' . $value . '</label>
                                </div>';
                                $checked = '';
                            }
                            echo '</div>';
                            break;
                    }
                    break;

                //  image cover
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

                // lets generate as inptu type text or date or email
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

        if ($info->position == 'bottom' && isset($descInfo)) {
            echo $descInfo;
        }

        if (($option?->with_agreement??false) && strpos($actionUrl, 'admin') === false) {
            echo <<<HTML
            <div>
                <input type="checkbox" id="iAgree"/>
                <label for="iAgree" style="cursor: pointer">Saya menyetujui prasyarat diatas</label>
            </div>
            HTML;    
        }

        // set form action url
        if ($actionUrl !== '') {
            // Captcha initialize
            $captcha = Captcha::section('memberarea');

            // public area
            if (strpos($actionUrl, 'admin') === false) {
                if (($option?->captcha??false) && $captcha->isSectionActive() && config('captcha', false)) 
                {
                    echo '<div class="captchaMember my-2">';
                    echo $captcha->getCaptcha();
                    echo '</div>';
                }
    
                echo \Volnix\CSRF\CSRF::getHiddenInputString();

                $disableBeforeAgree = '';
                if ($option?->with_agreement??false) $disableBeforeAgree = 'disabled';

                echo '<div class="form-group">
                    <input type="hidden" name="action" value="save"/>
                    <button class="btn btn-primary" type="submit" name="save" '.$disableBeforeAgree.' ' . (empty($disableBeforeAgree) ? '' : 'title="Klik \'Saya menyetujui prasyarat diatas\'"') . '>Daftar</button>
                    <button class="btn btn-outline-secondary" type="reset" name="save">Batal</button>
                </div>
                ';
            } else {
                echo '<div class="form-group">
                    <input type="hidden" name="action" value="acc"/>
                    <button class="btn btn-success" type="submit" name="acc">Setujui</button>
                    <a class="btn btn-danger" href="' .  pluginUrl(['section' => 'view_detail', 'member_id' => $_GET['member_id']??0, 'headless' => 'yes', 'action' => 'delete_reg']) . '">Hapus</a>
                </div>';
            }
            if (strpos($actionUrl, 'admin') === false) {
                echo '<strong><em class="text-danger">*</em> ) wajib diisi</strong>';
            }
        }
        echo '</form>';

        // Custom JS
        if (strpos($actionUrl, 'admin') === false) {
            $agreeJs = '';
            if ($option?->with_agreement??false) {
                $agreeJs = <<<HTML
                $('#iAgree').click(function() {
                    if ($('#iAgree:checked').length < 1) { 
                        $('button[name="save"]').prop('disabled', true)
                        $('button[name="save"]').prop('title', 'Klik \'Saya menyetujui prasyarat diatas\'')
                    } else {
                        $('button[name="save"]').prop('title', 'Klik untuk menyimpan data')
                        $('button[name="save"]').prop('disabled', false)
                    }
                });
                HTML;
            }
            echo <<<HTML
            <script>
                $(document).ready(function() {
                    {$agreeJs}
                    $('#self_member').submit(function(evt) {
                        {$js}
                    })
                })
            </script>
            HTML;
        }
        return ob_get_clean();
    }
}