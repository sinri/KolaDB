<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/14
 * Time: 23:53
 */

namespace sinri\KolaDB\storage;


class KolaCluster extends KolaFileSystemMapping
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

    public function __construct($clusterName)
    {
        $this->clusterName = $clusterName;
    }

    /**
     * @param string $clusterName
     * @return string
     */
    public static function getClusterDirectoryPath($clusterName)
    {
        return __DIR__ . '/../../runtime/' . base64_encode($clusterName);
    }

    /**
     * @return string[]|bool
     */
    public function getCollectionNameList()
    {
        if ($this->clusterName === null) {
            $this->error = "Not a synced cluster";
            return false;
        }

        $clusterDir = self::getClusterDirectoryPath($this->clusterName);
        if (!file_exists($clusterDir)) {
            $this->error = ("No such cluster!");
            return false;
        }
        if (!is_dir($clusterDir)) {
            $this->error = ("Cluster is taken place by a file!");
            return false;
        }

        $list = [];
        if ($handle = opendir($clusterDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $realCollectionName = base64_decode($entry);
                    $list[] = $realCollectionName;
                }
            }
            closedir($handle);
        }
        return $list;
    }

    /**
     * @param $collectionName
     * @return KolaCollection
     */
    public function getCollection($collectionName)
    {
        return new KolaCollection($this->clusterName, $collectionName);
    }

    /**
     * @param string $collectionName
     * @return bool
     */
    public function deleteCollection($collectionName)
    {
        return KolaCollection::deleteCollection($this->clusterName, $collectionName);
    }

    /**
     * @param string $clusterName
     * @return bool
     */
    public static function deleteCluster($clusterName)
    {
        $clusterPath = self::getClusterDirectoryPath($clusterName);
        if (file_exists($clusterPath)) return self::removeDirectoryRecursively($clusterPath);
        return true;
    }

    public function rename($newName)
    {
        if (!self::isValidEntityName($newName)) {
            $this->error = "It is not a valid entity name!";
            return false;
        }
        if ($this->clusterName === $newName) {
            return true;
        }

        $oldPath = self::getClusterDirectoryPath($this->clusterName);
        $newPath = self::getClusterDirectoryPath($newName);

        if (!rename($oldPath, $newPath)) {
            $this->error = "Cannot rename the cluster directory!";
            return false;
        }

        $this->clusterName = $newName;
        return true;
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
        $clusterDir = self::getClusterDirectoryPath($this->clusterName);
        return mkdir($clusterDir, 0777, true);
    }

    /**
     * @return bool
     */
    public function isSynchronized()
    {
        $clusterDir = self::getClusterDirectoryPath($this->clusterName);
        if (file_exists($clusterDir)) {
            if (is_dir($clusterDir)) {
                return true;
            }
            $this->error = ("Cluster existed but not a directory!");
            unlink($clusterDir);
        }
        return false;
    }
}