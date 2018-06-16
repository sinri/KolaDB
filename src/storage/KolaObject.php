<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/14
 * Time: 23:44
 */

namespace sinri\KolaDB\storage;


use sinri\ark\core\ArkHelper;

class KolaObject extends KolaFileSystemMapping
{
    /**
     * @var string
     */
    protected $clusterName;
    /**
     * @var string
     */
    protected $collectionName;
    /**
     * @var string
     */
    protected $objectName;
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function __construct($clusterName, $collectionName, $objectName)
    {
        $this->clusterName = $clusterName;
        $this->collectionName = $collectionName;
        $this->objectName = $objectName;
        $this->data = [];
        $this->loadData();
    }

    /**
     * @return bool
     */
    public function loadData()
    {
        $this->data = [];

        if ($this->clusterName === null || $this->collectionName === null || $this->objectName === null) {
            return true;
        }

        $objectFilePath = self::getObjectFilePath($this->clusterName, $this->collectionName, $this->objectName);
        if (!file_exists($objectFilePath)) {
            $this->error = ("No such object!");
            return false;
        }
        if (!is_file($objectFilePath)) {
            $this->error = ("Object is taken place by a directory!");
            return false;
        }
        $content = file_get_contents($objectFilePath);
        $array = json_decode($content, true);
        if (!is_array($array)) {
            $this->error = "Object Content is not an array!";
            return false;
        }
        $this->data = $array;
        return true;
    }

    /**
     * @param $cluster
     * @param string $collectionName
     * @param string $objectName
     * @return string
     */
    public static function getObjectFilePath($cluster, $collectionName, $objectName)
    {
        return KolaCollection::getCollectionDirectoryPath($cluster, $collectionName) . '/' . base64_encode($objectName);
    }

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
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * @param null|string $field
     * @param null $default
     * @return array
     */
    public function getData($field = null, $default = null)
    {
        if ($field === null)
            return $this->data;
        else
            return ArkHelper::readTarget($this->data, $field, $default);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        return ArkHelper::readTarget($this->data, $name);
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->data, $name, $value);
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $objectName
     * @return bool
     */
    public static function deleteObject($clusterName, $collectionName, $objectName)
    {
        $objectPath = KolaObject::getObjectFilePath($clusterName, $collectionName, $objectName);
        return unlink($objectPath);
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
        $oldPath = self::getObjectFilePath($this->clusterName, $this->collectionName, $this->objectName);
        $newPath = self::getObjectFilePath($this->clusterName, $this->collectionName, $newName);
        if (!rename($oldPath, $newPath)) {
            $this->error = "Cannot rename the object directory!";
            return false;
        }

        $this->objectName = $newName;
        return true;
    }

    /**
     * Write the data in memory into disk
     * @return bool
     */
    public function synchronize()
    {
        $collection = new KolaCollection($this->clusterName, $this->collectionName);
        if (!$collection->isSynchronized()) {
            if (!$collection->synchronize()) {
                return false;
            }
        }
        $objectPath = self::getObjectFilePath($this->clusterName, $this->collectionName, $this->objectName);
        return !!file_put_contents($objectPath, json_encode($this->data));
    }

    /**
     * For Object it has sometimes no meaning
     * @return bool
     */
    public function isSynchronized()
    {
        $objectPath = self::getObjectFilePath($this->clusterName, $this->collectionName, $this->objectName);
        if (file_exists($objectPath)) {
            if (is_file($objectPath)) {
                return true;
            }
            $this->error = "Object is placed by a directory, delete it";
            self::removeDirectoryRecursively($objectPath);
        }
        return false;
    }
}