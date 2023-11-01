<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-28 10:28:36
 * @modify date 2022-03-28 10:31:11
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;
use SLiMS\Migration\Migration;

class CreateInfo extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn($table = 'self_registartion_schemas', $column = 'info')) {
            Schema::table($table, function(Blueprint $table) use($column) {
                $table->text($column)->notNull()->after('name')->add();
            });
        }
    }

    public function down()
    {
        
    }
}