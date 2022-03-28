<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-28 10:28:36
 * @modify date 2022-03-28 12:40:44
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

class UpdateMemberOnline extends \SLiMS\Migration\Migration
{
    public function up()
    {
        if (!static::columnExists('member_type_id'))
            DB::getInstance()->query("ALTER TABLE `member_online` ADD `member_type_id` int(6) NULL AFTER `member_email`;");

        if (!static::columnExists('member_image'))
            DB::getInstance()->query("ALTER TABLE `member_online` ADD `member_image` varchar(200) NULL AFTER `member_type_id`;");
    }

    public function down()
    {
        
    }

    private static function columnExists(string $columnName)
    {
        return DB::getInstance()->query('describe `member_online` ' . $columnName)->fetch(\PDO::FETCH_ASSOC);
    }
}