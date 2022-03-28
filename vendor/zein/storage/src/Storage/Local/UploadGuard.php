<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-10 10:13:07
 * @modify date 2022-02-10 10:13:07
 * @license GPLv3
 * @desc [description]
 */

 namespace Zein\Storage\Local;

trait UploadGuard
{
    /**
     * Limit size of file
     *
     * @param string $Maxsize
     * @return Upload
     */
    public function limitSize(string $Maxsize)
    {
        if ($this->nextIfError()) return $this;

        $Filesize = $this->Attribute['Files']['size'];

        if ($Filesize > $this->toByteSize($Maxsize)) 
            $this->Error .= ($Filesize) . ' greater than ' . $this->toByteSize($Maxsize);

        return $this;
    }

    /**
     * Check if filesize is zero o not
     *
     * @return bool
     */
    public function isZeroSize()
    {
        return ($this->Attribute['Files']['size'] < 1);
    }

    /**
     * Check allow mime
     *
     * @param array/string $Mime
     * @return upload
     */
    public function allowMime($Mime)
    {
        if ($this->nextIfError()) return $this;

        $MimeFile = $this->Attribute['Files']['type'];

        if (is_array($Mime))
        {
            $Check = array_values(array_filter($Mime, function($mime) use($MimeFile) {
                if ($MimeFile == $mime) return true;
            }));

            return empty($Check[0]??null) 
                    ? 
                    $this->setError('Mime ' . $MimeFile . ' is not allowed!') : 
                    $this;
        }
        else
        {
            return $MimeFile === $Mime ? $this : $this->setError('Extension  ' . $MimeFile . ' is not allowed!');
        }
    }

    /**
     * Check allow extention
     *
     * @param array/string $Ext
     * @return Upload
     */
    public function allowExt($Ext)
    {
        if ($this->nextIfError()) return $this;

        $Pathinfo = pathinfo($this->Attribute['Files']['name']);

        if (is_array($Ext))
        {
            $Check = array_values(array_filter($Ext, function($ext) use($Pathinfo) {
                if (trim($ext, '.') === strtolower($Pathinfo['extension'])) return true;
            }));

            return empty(trim($Check[0]??null, '.')) 
                    ? 
                    $this->setError('Extension ' . $Pathinfo['extension'] . ' is not allowed!') : 
                    $this;
        }
        else
        {
            return $Pathinfo['extension'] === trim($Ext, '.') ? $this : $this->setError('Extension ' . $Pathinfo['extension'] . ' is not allowed!');
        }
    }

    /**
     * Check stream is exists of not
     *
     * @param string $Key
     * @return boolean
     */
    public function isStreamExists(string $Key)
    {
        return isset($_FILES[$Key]);
    }
}