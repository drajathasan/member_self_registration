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

class CreateSchema extends Migration
{
    public function up()
    {
        Schema::create('self_registartion_schemas', function(Blueprint $table){
            $table->autoIncrement('id');
            $table->string('name', 32)->notNull();
            $table->text('structure')->notNull();
            $table->timestamps();
            $table->index('name');
            $table->engine = 'MyISAM';
        });
    }

    public function down()
    {
        
    }
}