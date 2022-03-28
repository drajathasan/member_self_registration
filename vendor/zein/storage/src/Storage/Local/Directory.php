<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-09 09:03:52
 * @modify date 2022-02-09 22:21:12
 * @license GPLv3
 * @desc [description]
 */

namespace Zein\Storage\Local;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Directory
{

    use Error,Utils;

    /**
     * Filesystem instance based adapter
     */
    private $Filesystem = [];

    /**
     * Error Message
     */
    protected $Error = '';

    /**
     * Directory scope
     */
    private array $ListDirectory = [];

    /**
     * "Parent" and "Parent" property
     */
    private string $Parent = '';
    private string $From = '';

    /**
     * Callback method
     */
    private string $CallMethod = '';

    /**
     * Get filesystem instance
     * 
     * @param string $directoryName
     * @return Filesystem
     */
    protected function getFileSystem(string $directoryName)
    {
        // Check the local adapter
        if (is_null($this->{$directoryName})) die('Adapter ' . $directoryName . ' is not available!' . PHP_EOL);

        // Register file system instance based directory path name
        if (!array_key_exists($directoryName, $this->Filesystem))
            $this->Filesystem[$directoryName] = new Filesystem($this->{$directoryName});

        return $this->Filesystem[$directoryName];
    }

    /**
     * Create root directory based scope
     * 
     * @param string $parentDirectory
     * @param string $newDirectoryName
     * @return bool
     */
    public function create(string $rootDirectory)
    {
        return $this->createIn($rootDirectory);
    }

    /**
     * Create directory based scope
     * 
     * @param string $parentDirectory
     * @param string $newDirectoryName
     * @return bool
     */
    public function createIn(string $parentDirectory, string $newDirectoryName = '/')
    {
        $Filesystem = $this->getFileSystem($parentDirectory);

        try {
            return $Filesystem->createDirectory($newDirectoryName);
        } catch (UnableToCreateDirectory $e) {
            $this->Error = $e->getMessage();
            return false;
        }
    }

    /**
     * Delete function
     *
     * @param string $parentDirectory
     * @param string $childPath
     * @return bool
     */
    public function delete(string $parentDirectory, string $childPath = '/')
    {
        $Filesystem = $this->getFileSystem($parentDirectory);

        try {
            return $Filesystem->deleteDirectory($childPath);
        } catch (FilesystemException | UnableToDeleteFile $e) {
            $this->Error = $e->getMessage();
            return false;
        }
    }

    /**
     * Move method
     *
     * @param string $parentDirectory
     * @param string $sourceDirectory
     * @return Directory
     */
    public function move(string $parentDirectory, string $sourceDirectory):Directory
    {
        $this->Parent = $parentDirectory;
        $this->From = $sourceDirectory;
        $this->CallMethod = 'move';

        return $this;
    }

    /**
     * to setup destination
     *
     * @param string $destinationDirectory
     * @return boolean
     */
    public function to(string $destinationDirectory):bool
    {
        if (empty($this->Parent) || empty($this->From)) die('No adapter available!');

        $Filesystem = $this->getFileSystem($this->Parent);

        try {
            @$Filesystem->{$this->CallMethod}($this->From, $destinationDirectory);

            $this->From = '';
            $this->Parent = '';
            
            return true;
        } catch (FilesystemException | UnableToMoveFile $e) {
            $this->Error = $e->getMessage();
            return false;
        }
    }

    /**
     * Create directory based scope
     * 
     * @param string $parentDirectory
     * @param array $newDirectoryName
     * @return bool
     */
    public function createBatchIn(string $parentDirectory, array $newDirectoryName)
    {
        $Filesystem = $this->getFileSystem($parentDirectory);

        $this->Error = [];
        foreach ($newDirectoryName as $Directory) {
            try {
                $Filesystem->createDirectory($Directory);
            } catch (UnableToCreateDirectory $e) {
                $this->Error[] = $e->getMessage();
            }
        }
    }

    /**
     * Create director based scope
     * 
     * @param string $parentDirectory
     * @param string $newDirectoryName
     * @return bool
     */
    public function listOf(string $parentDirectory, string $currentDirectory = '/', bool $recursive = true)
    {
        $Filesystem = $this->getFileSystem($parentDirectory);

        return $Filesystem->listContents($currentDirectory);
    }

    /**
     * Magic method to organize scope function
     * 
     * @param string $methodName
     * @param array $arguments
     * @return void
     */
    public function __call($methodName, $arguments)
    {
        foreach (get_class_methods($this) as $methodInClass) {
            if (preg_match('/'.$methodInClass.'/i', $methodName))
            {
                $method = lcfirst($methodInClass);
                $parentDirectory = str_replace([ucfirst($method), $method], '', $methodName);
                
                if (!isset($this->ListDirectory[strtolower($parentDirectory)]))
                {
                    continue;
                }

                return call_user_func_array([$this, $method], array_merge([strtolower($parentDirectory)], $arguments));
                break;
            }
        }

        exit('Gak ada');
    }

    /**
     * Magic method to set ListDirectory scope
     * 
     * @param string $directory
     * @param string $directoryPath
     * @return void
     */
    public function __set($directory, $directoryPath)
    {
        $this->ListDirectory[$directory] = new LocalFilesystemAdapter($directoryPath);
    }

    /**
     * Magic method to get scope list
     * 
     * @param string $directory
     * @return void
     */
    public function __get($directory)
    {
        if (array_key_exists($directory, $this->ListDirectory)) return $this->ListDirectory[$directory];
    }
}