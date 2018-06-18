<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/14
 * Time: 23:57
 */

namespace sinri\KolaDB\storage;

class KolaAgent
{
    protected $handleClusterName;

    public function __construct($clusterName)
    {
        $this->handleClusterName = $clusterName;
    }

    /**
     * @return KolaCluster
     */
    public function getCluster()
    {
        return (new KolaCluster($this->handleClusterName));
    }

    /**
     * @param string $collectionName
     * @return KolaCollection
     */
    public function getCollection($collectionName)
    {
        return $this->getCluster()->getCollection($collectionName);
    }

    // show

    /**
     * @return KolaCollection[]
     */
    public function getCollectionList()
    {
        $cluster = $this->getCluster();
        $collectionNameList = $cluster->getCollectionNameList();
        if (!is_array($collectionNameList)) return [];
        $collections = [];
        foreach ($collectionNameList as $collectionName) {
            $collections[] = $cluster->getCollection($collectionName);
        }
        return $collections;
    }

    // select

    /**
     * @param string $collectionName
     * @param KolaQuery $query
     * @return array
     */
    public function selectObjectsInCollection($collectionName, $query)
    {
        try {
            $collection = $this->getCollection($collectionName);
            $list = $query->resultForCollection($collection);
            $result = [];
            foreach ($list as $item) {
                $result[] = [
                    "object_name" => $item->getObjectName(),
                    "data" => $item->getData(),
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param string $collectionName
     * @param string $objectName
     * @return array|bool
     */
    public function selectObjectInCollection($collectionName, $objectName)
    {
        try {
            $object = $this->getCollection($collectionName)->getObject($objectName);
            return [
                "object_name" => $object->getObjectName(),
                "data" => $object->getData(),
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    // insert/update

    /**
     * @param string $collectionName
     * @param string $objectName
     * @param array $data
     * @return bool
     */
    public function writeObjectInCollection($collectionName, $objectName, $data)
    {
        try {
            if (!is_array($data)) throw new \Exception("data is not an array");
            foreach ($data as $key => $item) {
                if (!is_string($key) || !is_string($item)) throw new \Exception("key or item is not string");
            }

            $collection = $this->getCollection($collectionName);
            $object = $collection->getObject($objectName);
            $object->setData($data);
            return $object->synchronize();
        } catch (\Exception $e) {
            return false;
        }
    }

    // delete

    /**
     * @param string $collectionName
     * @param string $objectName
     * @return bool
     */
    public function deleteObjectInCollection($collectionName, $objectName)
    {
        try {
            $collection = $this->getCollection($collectionName);
            return $collection->deleteObject($objectName);
        } catch (\Exception $e) {
            return false;
        }
    }

    // drop

    /**
     * @param string $collectionName
     * @return bool
     */
    public function dropCollection($collectionName)
    {
        try {
            return $this->getCluster()->deleteCollection($collectionName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $clusterName
     * @return bool
     */
    public static function dropCluster($clusterName)
    {
        try {
            return KolaCluster::deleteCluster($clusterName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string[]
     */
    public static function listClusters()
    {
        $list = [];
        if ($handle = opendir(__DIR__ . '/../../runtime')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $realClusterName = base64_decode($entry);
                    $list[] = $realClusterName;
                }
            }
            closedir($handle);
        }
        return $list;
    }

}