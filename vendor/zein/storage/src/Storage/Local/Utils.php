<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-09 17:35:14
 * @modify date 2022-02-09 20:25:40
 * @license GPLv3
 * @desc [description]
 */

namespace Zein\Storage\Local;

trait Utils
{
    /**
     * Check if directory is exists or not
     *
     * @param string $DirectoryName
     * @param string $childPath
     * @return boolean
     */
    public function isExists(string $DirectoryName, string $childPath = '/')
    {
        $getContents = $this->listOf($DirectoryName);

        if (!is_null($getContents) && $childPath === '/') return true;

        foreach ($getContents as $Contents) {
            if ($Contents->path() === $childPath) return true;
        }

        return false;
    }

    /**
     * Convert filesize to byte
     * 
     * @param string $Format
     */
    public function toByteSize(string $NumberLimit) 
    {
        $NumberLimit = str_replace(',', '.', $NumberLimit);
        $Unitmap = ['B'=> 0, 'KB'=> 3, 'MB'=> 6, 'GB'=> 9, 'TB'=> 12, 'PB'=> 15, 'EB'=> 18, 'ZB'=> 21, 'YB'=> 24];
        $InjectUnit = strtoupper(trim(substr($NumberLimit, -2)));
        $Number = trim(str_replace($InjectUnit, '', $NumberLimit));

        if (intval($InjectUnit) !== 0) {
            $InjectUnit = 'B';
        }

        if (!in_array($InjectUnit, array_keys($Unitmap))) {
            return false;
        }

        $inByte = $Number * ('1' . str_repeat(0,$Unitmap[$InjectUnit]));
        return $inByte;
    }

    /**
     * Get attribute
     * 
     * @return array
     */
    public function getAttribute()
    {
        unset($this->Attribute['Stream']);
        return $this->Attribute;
    }
}