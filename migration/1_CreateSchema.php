<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-28 10:28:36
 * @modify date 2023-12-03 05:09:20
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;
use SLiMS\Migration\Migration;

class CreateSchema extends Migration
{
    public function up()
    {
        Schema::create('self_registration_schemas', function(Blueprint $table){
            $table->autoIncrement('id');
            $table->string('name', 32)->notNull();
            $table->text('info')->notNull();
            $table->text('structure')->notNull();
            $table->tinynumber('status', 1)->default(0);
            $table->text('option')->nullable();
            $table->timestamps();
            $table->index('name');
            $table->unique('name');
            $table->engine = 'MyISAM';
        });
    }

    public function down()
    {
        
    }
}