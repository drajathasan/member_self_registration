<?php
use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Table\Schema;
use SLiMS\Table\Grammar\Mysql;

if (isset($_POST['saveData'])) {
    // Get MySQL Grammer reflection class
    $mysqlGrammar = new ReflectionClass(new Mysql);

    if (empty($_POST['name']??'')) {
        exit(toastr('Nama skema tidak boleh kosong!')->warning('Peringatan'));
    }

    // had custom table
    $hadCustomTable = (bool)count(array_filter($_POST['column'], fn($column) => $column['field'] === 'advance'));

    if ($hadCustomTable) {
        foreach ($_POST['column'] as $key => $advColumn) {
            if ($advColumn['field'] === 'advance') {
                $_POST['column'][$key]['advfield'] = 'adv_' . $advColumn['advfield'];
            }
        }
    }

    // requirement field
    $isRequirementFieldsExists = (bool)count(array_filter($_POST['column'], fn($column) => in_array($column['field'], ['member_id','member_name','gender'])));

    if (!$isRequirementFieldsExists) exit(toastr('Ruas member_id, member_name dan gender tidak ditemukan')->error('Galat'));

    // Get private property without change it
    $property = @array_pop($mysqlGrammar->getProperties(ReflectionProperty::IS_PRIVATE));

    // Define some map to converting data
    $mysqlColumnType = array_values($property->getValue());
    $slimsSchemaColumnType = array_keys($property->getValue());

    // Retrive all column detail in member table
    $memberSchema = Schema::table('member')->columns($detail = true);
    
    // Statement
    $insert = DB::getInstance()->prepare('insert ignore into `self_registration_schemas` set name = ?, info = ?, structure = ?, created_at = now()');

    $_POST['name'] = preg_replace('/[^A-Za-z\s]/', '', $_POST['name']);
    $newTable = 'self_registration_' . strtolower(str_replace(' ', '_', $_POST['name']));

    $insert->execute([$_POST['name'], json_encode($_POST['info']), json_encode(array_map(function($data) {
        $data['is_required'] = (bool)$data['is_required'];
        return $data;
    }, $_POST['column']))]);

    $indexes = [];

    Plugins::getInstance()->execute('member_self_before_create_schema', [
        'memberSchema' => $memberSchema,
        'mysqlColumnType' => $mysqlColumnType,
        'slimsSchemaColumnType' => $slimsSchemaColumnType,
        'newTable' => $newTable,
        'structure' => $_POST['column'],
        'hadCustomTable' => $hadCustomTable
    ]);

    $createBase = Schema::create($newTable, function($table) use($memberSchema,$mysqlColumnType,$slimsSchemaColumnType) {

        foreach ($_POST['column'] as $key => $column) {

            // Search kolom in member schema
            $detail = @array_pop(array_filter($memberSchema, function($detail) use($column) {
                if ($column['field'] === $detail['COLUMN_NAME']) return true;
            }));

            // Determine data type based on member table or advance form
            $dataType = $detail['DATA_TYPE']??$column['advfieldtype'];
            $typeId = @array_pop(array_keys(array_filter($mysqlColumnType, fn($type) => $type === $dataType)));
    

            if ($column['field'] !== 'advance' && (empty($detail) || !isset($slimsSchemaColumnType[$typeId]))) {
                unset($detail);
                continue;
            }

            $blueprintMethod = $slimsSchemaColumnType[$typeId]??$dataType;

            $field = trim(empty($column['advfield']) ? $column['field'] : $column['advfield']);

            // ,'text_multiple'
            if ($blueprintMethod === 'enum') {
                $blueprintMethod = 'enum';
                list($field, $data) = explode(',', $field);
                $detail['CHARACTER_MAXIMUM_LENGTH'] = explode('|', trim($data));
            }

            if ($blueprintMethod === 'enum_radio') {
                $blueprintMethod = 'string';
                list($field, $data) = explode(',', $field);
            }

            if ($blueprintMethod === 'text_multiple') {
                $blueprintMethod = 'text';
                list($field, $data) = explode(',', $field);
            }

            if (in_array($field, ['member_id', 'member_name'])) {
                $table->index($field);
                if ($field == 'member_id') $table->unique('member_id');
            }

            $params = (!in_array($blueprintMethod, ['text','date','datetime']) ? [
                $field, ($detail['CHARACTER_MAXIMUM_LENGTH']??64)
            ] : [
                $field
            ]);

            $table->{$blueprintMethod}(...$params)->notNull();

            unset($detail);
            unset($typeId);
        }

        $table->timestamps();
        $table->engine = 'MyISAM';
        $table->charset = 'utf8';
        $table->collation = 'utf8_unicode_ci';
    });

    if ($hadCustomTable) {
        $createCustomBase = Schema::table('member_custom', function($table) use($memberSchema,$mysqlColumnType,$slimsSchemaColumnType) {
            foreach ($_POST['column'] as $column) {
                if ($column['field'] !== 'advance') continue;

                // Search kolom in member schema
                $detail = @array_pop(array_filter($memberSchema, function($detail) use($column) {
                    if ($column['field'] === $detail['COLUMN_NAME']) return true;
                }));

                // Determine data type based on member table or advance form
                $dataType = $detail['DATA_TYPE']??$column['advfieldtype'];
                $typeId = @array_pop(array_keys(array_filter($mysqlColumnType, fn($type) => $type === $dataType)));

                $blueprintMethod = $slimsSchemaColumnType[$typeId]??$dataType;

                $field = $column['advfield'];

                if ($blueprintMethod === 'enum') {
                    $blueprintMethod = 'enum';
                    list($field, $data) = explode(',', $field);
                    $detail['CHARACTER_MAXIMUM_LENGTH'] = explode('|', trim($data));
                }
    
                if ($blueprintMethod === 'enum_radio') {
                    $blueprintMethod = 'string';
                    list($field, $data) = explode(',', $field);
                }
    
                if ($blueprintMethod === 'text_multiple') {
                    $blueprintMethod = 'text';
                    list($field, $data) = explode(',', $field);
                }

                $params = (!in_array($blueprintMethod, ['text','date','datetime']) ? [
                    $field, ($detail['CHARACTER_MAXIMUM_LENGTH']??64)
                ] : [
                    $field
                ]);

                $table->{$blueprintMethod}(...$params)->nullable()->add();

                unset($detail);
                unset($typeId);
            }
        });
    }


    // dd($createCustomBase);
    redirect()->simbioAJAX(pluginUrl(reset: true));
    exit;
}