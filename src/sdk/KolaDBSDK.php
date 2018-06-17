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
}