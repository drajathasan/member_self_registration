<?php
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
    function formGenerator($data)
    {
        $structure = json_decode($data->structure, true);
        ob_start();
        echo '<form';
        echo '<h3>Pratinjau</h3>';
        echo '<h5>Skema ' . $data->name . '</h5>';
        foreach ($structure as $key => $column) {
            echo <<<HTML
            <div class="my-3">
                <label class="form-label"><strong>{$column['name']}</strong></label>
            HTML;

            switch ($column['field']) {
                case 'mpasswd':
                    echo <<<HTML
                    <br>
                    <small>tulis dibawah berikut</small>
                    <input type="password" placeholder="masukan {$column['name']} anda" name="form[]" id="pass1" class="form-control">
                    <small>konfirmasi ulang password anda</small>
                    <input type="password" placeholder="masukan ulang {$column['name']} anda" id="pass2" class="form-control">
                    HTML;
                    break;

                case 'gender':
                    echo <<<HTML
                    <select name="form[]" class="form-control">
                        <option>Pilih</option>
                        <option value="1">Laki-Laki</option>
                        <option value="0">Perempuan</option>
                    </select>
                    HTML;
                    break;

                case 'member_address':
                    echo <<<HTML
                    <textarea name="form[]" placeholder="masukan {$column['name']} anda" class="form-control"></textarea>
                    HTML;
                    break;

                case 'member_type_id':
                    $memberType = \SLiMS\DB::getInstance()->query('select member_type_id, member_type_name from mst_member_type');
                    echo '<select class="form-control" name="form[]">';
                    echo '<option value="0">Pilih</option>';
                    while ($result = $memberType->fetch(PDO::FETCH_NUM)) {
                        echo '<option value="' . $result[0] . '">' . $result[1] . '</option>';
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
                            <input type="{$type}" name="form[]" placeholder="masukan {$column['name']} anda" class="form-control"/>
                            HTML;
                            break;

                        case 'text':
                            echo <<<HTML
                            <textarea name="form[]" placeholder="masukan {$column['name']} anda" class="form-control"></textarea>
                            HTML;
                            break;
                        
                        case 'enum':
                            list($field,$list) = explode(',', $column['advfield']);
                            echo '<select name="form[]" class="form-control">';
                            echo '<option value="">Pilih</option>';
                            foreach (explode('|', $list) as $item) {
                                echo '<option value="'.$item.'">' . $item . '</option>';
                            }
                            echo '</select>';
                            break;
                    }
                    break;

                case 'member_image':
                    if ($data->option === null) {
                        echo '<div class="alert alert-info font-weight-bold">Anda belum mengantur ruas ini pada "Pengaturan Form"</div>';
                    } else {
                        echo <<<HTML
                        <input type="file" name="image" placeholder="masukan {$column['name']} anda" class="form-control d-block"/>
                        <small>Maksimal ukuran file foto adalah 2MB</small>
                        HTML;
                    }
                    break;

                default:
                    $types = ['birth_date' => 'date', 'member_email' => 'email'];
                    $type = isset($types[$column['field']]) ? $types[$column['field']] : 'text';
                    echo <<<HTML
                    <input type="{$type}" name="form[]" placeholder="masukan {$column['name']} anda" class="form-control"/>
                    HTML;
                    break;
            }

            echo <<<HTML
            </div>
            HTML;
        }
        echo '</form>';
        return ob_get_clean();
    }
}