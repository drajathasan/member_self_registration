<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-22 11:51:22
 * @modify date 2022-02-22 11:51:22
 * @license GPLv3
 * @desc [description]
 */

namespace Zein\Storage\Local;

class File
{

    use Error,Utils;

    private $Error = '';

    /**
     * Create http file stream
     * 
     * @param string $newName
     * @param string $Filepath
     * @param array $Attribute
     * @return void|bool
     */
    public static function httpStream(string $newName, string $Filepath, array $Attribute)
    {
        $Static = new Static;

        if (!File::exists($Filepath)) return false;

        // set header
        header("Content-Description: File Transfer");
        header('Content-Disposition: inline; filename="'.$newName.'"');
        header('Content-Transfer-Encoding: binary'); 
        header('Accept-Ranges: bytes'); 
        header('Content-Type: '.$Attribute['type']);
        readfile($Filepath);
        exit;
    }

    /**
     * Check if file is exists or not
     * 
     * @param string $Filepath
     * @param closure $callBack
     * @return string
     */
    public static function exists(string $Filepath, $callBack = '')
    {
        if (!file_exists($Filepath)) $Error = 'File ' . $Filepath . ' not found!';

        if (is_callable($callBack))
        {
            return $callBack($Error);
        }

        return empty($Error);
    }
}