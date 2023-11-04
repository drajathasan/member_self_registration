<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-28 10:28:36
 * @modify date 2023-11-04 15:40:10
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;
use SLiMS\Migration\Migration;

class CreateStatus extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn($table = 'self_registartion_schemas', $column = 'status')) {
            Schema::table($table, function(Blueprint $table) use($column) {
                $table->tinynumber($column, 1)->default(0)->after('structure')->add();
                $table->index($column)->add();
            });
        }
    }

    public function down()
    {
        
    }
}