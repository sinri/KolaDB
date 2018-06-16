<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/15
 * Time: 11:23
 */

namespace sinri\KolaDB\storage;


abstract class KolaFileSystemMapping
{
    /**
     * @var string
     */
    protected $error;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $name
     * @return bool
     */
    public final static function isValidEntityName($name)
    {
        return (is_string($name) && strlen($name) > 0);
    }

    /**
     * @param string $newName
     * @return bool
     */
    abstract public function rename($newName);

    /**
     * Write the data in memory into disk
     * @return bool
     */
    abstract public function synchronize();

    /**
     * @return bool
     */
    abstract public function isSynchronized();

    /**
     * @param string $dir
     * @return bool
     */
    public final static function removeDirectoryRecursively($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($dir . '/' . $file)) ? self::removeDirectoryRecursively($dir . '/' . $file) : unlink($dir . '/' . $file);
        }
        return rmdir($dir);
    }
}