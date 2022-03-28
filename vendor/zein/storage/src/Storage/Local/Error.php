<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-09 17:31:43
 * @modify date 2022-02-09 18:17:47
 * @license GPLv3
 * @desc [description]
 */

namespace Zein\Storage\Local;

trait Error
{
    /**
     * Getter for Error property
     * 
     * @return string
     */
    public function getError()
    {
        return $this->Error;
    }

    /**
     * Setter for Error property
     *
     * @param string $Error
     * @return Upload
     */
    private function setError($Error)
    {
        $this->Error = $Error;

        return $this;
    }

    /**
     * Bypass chaning method
     */
    public function nextIfError()
    {
        return !empty($this->Error);  
    }

}

