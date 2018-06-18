<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/17
 * Time: 22:45
 */

namespace sinri\KolaDB\sdk;


use sinri\ark\core\ArkHelper;
use sinri\KolaDB\service\KolaClient;
use sinri\KolaDB\storage\KolaAction;
use sinri\KolaDB\storage\KolaFileSystemMapping;
use sinri\KolaDB\storage\KolaQuery;

class KolaDBSDK
{
    protected $address;
    protected $port;

    protected $client;

    public function __construct($address, $port)
    {
        $this->address = $address;
        $this->port = $port;

        $this->client = new KolaClient($this->address, $this->port);
    }

    // list

    /**
     * @return bool|string[]
     */
    public function listClusters()
    {
        $action = KolaAction::createListAction(null, null);
        $done = $this->client->call($action);
        if ($done) {
            return $this->client->getData();
        } else {
            return false;
        }
    }

    /**
     * @param string $clusterName
     * @return bool|string[]
     */
    public function listCollectionsInCluster($clusterName)
    {
        $action = KolaAction::createListAction($clusterName, null);
        $done = $this->client->call($action);
        if ($done) {
            return $this->client->getData();
        } else {
            return false;
        }
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @return bool|string[]
     */
    public function listObjectsInCollection($clusterName, $collectionName)
    {
        $action = KolaAction::createListAction($clusterName, $collectionName);
        $done = $this->client->call($action);
        if ($done) {
            return $this->client->getData();
        } else {
            return false;
        }
    }

    // query

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param KolaQuery $query
     * @return bool|array
     */
    public function queryObject($clusterName, $collectionName, $query)
    {
        $action = KolaAction::createQueryAction($clusterName, $collectionName, $query);
        $done = $this->client->call($action);
        if ($done) {
            return $this->client->getData();
        } else {
            return false;
        }
    }

    // edit

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $objectName
     * @param array $data
     * @return bool
     */
    public function editObject($clusterName, $collectionName, $objectName, $data)
    {
        $action = KolaAction::createEditAction($clusterName, $collectionName, $objectName, $data);
        $done = $this->client->call($action);
        return $done;
    }

    // drop

    /**
     * @param string $clusterName
     * @return bool
     * @throws \Exception
     */
    public function dropCluster($clusterName)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        $collectionName = null;
        $objectName = null;
        $action = KolaAction::createDropAction($clusterName, $collectionName, $objectName);
        $done = $this->client->call($action);
        return $done;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @return bool
     * @throws \Exception
     */
    public function dropCollection($clusterName, $collectionName)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($collectionName), 'collection name invalid');
        $objectName = null;
        $action = KolaAction::createDropAction($clusterName, $collectionName, $objectName);
        $done = $this->client->call($action);
        return $done;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $objectName
     * @return bool
     * @throws \Exception
     */
    public function dropObject($clusterName, $collectionName, $objectName)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($collectionName), 'collection name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($objectName), 'object name invalid');
        $action = KolaAction::createDropAction($clusterName, $collectionName, $objectName);
        $done = $this->client->call($action);
        return $done;
    }

    // rename

    /**
     * @param string $clusterName
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function renameCluster($clusterName, $name)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($name), 'new name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        $collectionName = null;
        $objectName = null;
        $action = KolaAction::createRenameAction($clusterName, $collectionName, $objectName, $name);
        $done = $this->client->call($action);
        return $done;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function renameCollection($clusterName, $collectionName, $name)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($name), 'new name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($collectionName), 'collection name invalid');
        $objectName = null;
        $action = KolaAction::createRenameAction($clusterName, $collectionName, $objectName, $name);
        $done = $this->client->call($action);
        return $done;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $objectName
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function renameObject($clusterName, $collectionName, $objectName, $name)
    {
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($name), 'new name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($clusterName), 'cluster name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($collectionName), 'collection name invalid');
        ArkHelper::assertItem(KolaFileSystemMapping::isValidEntityName($objectName), 'object name invalid');
        $action = KolaAction::createRenameAction($clusterName, $collectionName, $objectName, $name);
        $done = $this->client->call($action);
        return $done;
    }
}