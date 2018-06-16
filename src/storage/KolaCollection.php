<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/14
 * Time: 23:50
 */

namespace sinri\KolaDB\storage;


class KolaCollection extends KolaFileSystemMapping
{
    /**
     * @var string
     */
    protected $clusterName;

    /**
     * @return string
     */
    public function getClusterName()
    {
        return $this->clusterName;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @var string
     */
    protected $collectionName;

    public function __construct($clusterName, $collectionName)
    {
        $this->clusterName = $clusterName;
        $this->collectionName = $collectionName;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @return string
     */
    public static function getCollectionDirectoryPath($clusterName, $collectionName)
    {
        return KolaCluster::getClusterDirectoryPath($clusterName) . '/' . base64_encode($collectionName);
    }

    public function getObjectNameList()
    {
        if ($this->collectionName === null || $this->clusterName === null) {
            $this->error = "Not synced collection";
            return false;
        }

        $collectionDir = self::getCollectionDirectoryPath($this->clusterName, $this->collectionName);
        if (!file_exists($collectionDir)) {
            $this->error = ("No such collection!");
            return false;
        }
        if (!is_dir($collectionDir)) {
            $this->error = ("Collection is taken place by a file!");
            return false;
        }
        $list = [];
        if ($handle = opendir($collectionDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $realObjectName = base64_decode($entry);
                    $list[] = $realObjectName;
                }
            }
            closedir($handle);
        }
        return $list;
    }

    /**
     * @param $objectName
     * @return KolaObject
     */
    public function getObject($objectName)
    {
        return new KolaObject($this->clusterName, $this->collectionName, $objectName);
    }

    /**
     * @param $objectName
     * @return bool
     */
    public function deleteObject($objectName)
    {
        return KolaObject::deleteObject($this->clusterName, $this->collectionName, $objectName);
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @return bool
     */
    public static function deleteCollection($clusterName, $collectionName)
    {
        $collectionPath = KolaCollection::getCollectionDirectoryPath($clusterName, $collectionName);
        return self::removeDirectoryRecursively($collectionPath);
    }

    /**
     * Write the data in memory into disk
     * @return bool
     */
    public function synchronize()
    {
        if ($this->isSynchronized()) {
            return true;
        }
        $clusterDir = self::getCollectionDirectoryPath($this->clusterName, $this->collectionName);
        return mkdir($clusterDir, 0777, true);
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename($newName)
    {
        if (!self::isValidEntityName($newName)) {
            $this->error = "Invalid new name";
            return false;
        }
        $oldPath = self::getCollectionDirectoryPath($this->clusterName, $this->collectionName);
        $newPath = self::getCollectionDirectoryPath($this->clusterName, $newName);
        if (!rename($oldPath, $newPath)) {
            $this->error = "Cannot rename the collection directory!";
            return false;
        }

        $this->collectionName = $newName;
        return true;
    }

    /**
     * @return bool
     */
    public function isSynchronized()
    {
        $clusterDir = self::getCollectionDirectoryPath($this->clusterName, $this->collectionName);
        if (file_exists($clusterDir)) {
            if (is_dir($clusterDir)) {
                return true;
            }
            $this->error = ("Collection existed but not a directory!");
            unlink($clusterDir);
        }
        return false;
    }
}