<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-28 10:28:36
 * @modify date 2022-03-28 10:31:11
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

class CreateMemberOnline extends \SLiMS\Migration\Migration
{
    public function up()
    {
        DB::getInstance()->query("CREATE TABLE IF NOT EXISTS `member_online` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                    `member_name` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `birth_date` date DEFAULT NULL,
                                    `inst_name` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `gender` int(1) NOT NULL,
                                    `member_address` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `member_phone` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `member_email` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `mpasswd` varchar(64) COLLATE utf8mb4_bin DEFAULT NULL,
                                    `input_date` date DEFAULT NULL,
                                    `last_update` date DEFAULT NULL
                                ) ENGINE='MyISAM';");
    }

    public function down()
    {
        
    }
}