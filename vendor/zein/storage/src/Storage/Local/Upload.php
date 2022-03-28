<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-10 08:02:29
 * @modify date 2022-02-10 08:02:29
 * @license GPLv3
 * @desc [description]
 */

namespace Zein\Storage\Local;

use Exception;

class Upload extends Directory
{
    use Utils,UploadGuard,Error;

    /**
     * Attribute
     */
    private $Attribute = [];

    /**
     * Success property
     */
    private $Success = false;

    /**
     * Stream from upload files
     * 
     * @param string $FileName
     * @return Upload
     */
    public function streamFrom(string $FileName, string $Mode = 'r+')
    {
        try {
            if (!$this->isStreamExists($FileName))
            {
                throw new Exception("$FileName is not set!");
            }
            else
            {
                $this->Attribute['Files'] = $_FILES[$FileName];
                $this->Attribute['Stream'] = fopen($_FILES[$FileName]['tmp_name'], $Mode);
                $this->Attribute['Name'] = $_FILES[$FileName]['name'];

                if ($this->isZeroSize()) throw new Exception("File size is less than 1 byte!");
            }
        } catch (Exception $e) {
            $this->Error .= $e->getMessage();
        }

        return $this;
    }

    /**
     * Store file into some folder
     * 
     * it based from scope or direct method.
     * 
     * @param string $ParentDirectory
     * @param string $ChildDirectory
     * @return Upload
     */
    public function storeTo(string $ParentDirectory, string $ChildDirectory = '/')
    {
        if ($this->nextIfError()) return $this;

        $this->Attribute['Parent'] = $ParentDirectory;
        $this->Attribute['Path'] = ($ChildDirectory !== '/' ? basename($ChildDirectory) . '/' : $ChildDirectory);

        try {
            
            $this
                ->getFileSystem($this->Attribute['Parent'])
                ->writeStream($this->Attribute['Path'] . $this->Attribute['Name'], $this->Attribute['Stream']);

        } catch (FilesystemException | UnableToWriteFile $e) {
            $this->Error .= $e->getMessage();
        }

        $this->close();

        return $this;
    }

    /**
     * Save file with new name
     * 
     * @param string $newname
     * @return void
     */
    public function as(string $newname)
    {
        if ($this->nextIfError()) return;

        $this
            ->move($this->Attribute['Parent'], $this->Attribute['Path'] . $this->Attribute['Name'])
            ->to($this->Attribute['Path'] . $newname);
    }

    /**
     * Get Success result
     * @return bool
     */
    public function isSuccess()
    {
        return empty($this->Error) ? true : false;
    }

    /**
     * Close streaming process
     * @return void
     */
    private function close()
    {
        if ($this->nextIfError()) return;

        fclose($this->Attribute['Stream']);
    }
}