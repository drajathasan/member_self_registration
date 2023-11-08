<?php
use SLiMS\Migration\Migration;
use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

class AddOption extends Migration
{
    function up()
    {
        if (!Schema::hasColumn($table = 'self_registartion_schemas', $column = 'option')) {
            Schema::table($table, function(Blueprint $table) use($column) {
                $table->text($column)->nullable()->after('structure')->add();
            });
        }
    }

    function down()
    {

    }
}