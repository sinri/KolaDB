<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 14:34
 */

namespace sinri\KolaDB\storage;


use sinri\ark\core\ArkHelper;

class KolaAction
{
    const ACTION_EDIT = "edit";
    const ACTION_DROP = "drop";
    const ACTION_QUERY = "query";
    const ACTION_LIST = "list";
    const ACTION_RENAME = "rename";

    protected $username;
    protected $password;

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
     * @var string
     */
    public $action;
    /**
     * @var null|array
     */
    public $data;
    /**
     * @var null|KolaQuery
     */
    public $query;
    /**
     * @var null|string
     */
    public $change;

    public $error;
    public $result;

    protected function __construct()
    {
        $this->action = null;
        $this->data = null;
        $this->query = null;
        $this->clusterName = null;
        $this->collectionName = null;
        $this->objectName = null;
    }

    /**
     * @param string $clusterName
     * @param null|string $collectionName
     * @param null|string $objectName
     * @return KolaAction
     */
    public static function createDropAction($clusterName, $collectionName = null, $objectName = null)
    {
        $instance = new KolaAction();
        $instance->action = self::ACTION_DROP;
        $instance->clusterName = $clusterName;
        $instance->collectionName = $collectionName;
        $instance->objectName = $objectName;
        return $instance;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string $objectName
     * @param array $data
     * @return KolaAction
     */
    public static function createEditAction($clusterName, $collectionName, $objectName, $data)
    {
        $instance = new KolaAction();
        $instance->action = self::ACTION_EDIT;
        $instance->clusterName = $clusterName;
        $instance->collectionName = $collectionName;
        $instance->objectName = $objectName;
        $instance->data = $data;
        return $instance;
    }

    /**
     * @param string $clusterName
     * @param string $collectionName
     * @param string|KolaQuery $query
     * @return KolaAction
     */
    public static function createQueryAction($clusterName, $collectionName, $query)
    {
        $instance = new KolaAction();
        $instance->action = self::ACTION_QUERY;
        $instance->clusterName = $clusterName;
        $instance->collectionName = $collectionName;
        if (is_string($query)) {
            $instance->objectName = $query;
        } else {
            $instance->query = $query;
        }
        return $instance;
    }

    /**
     * @param string|null $clusterName
     * @param string|null $collectionName
     * @return KolaAction
     */
    public static function createListAction($clusterName = null, $collectionName = null)
    {
        $instance = new KolaAction();
        $instance->action = self::ACTION_LIST;
        $instance->clusterName = $clusterName;
        $instance->collectionName = $collectionName;
        return $instance;
    }

    /**
     * @param string $clusterName
     * @param null|string $collectionName
     * @param null|string $objectName
     * @param string $change
     * @return KolaAction
     */
    public static function createRenameAction($clusterName, $collectionName, $objectName, $change)
    {
        $instance = new KolaAction();
        $instance->action = self::ACTION_RENAME;
        $instance->clusterName = $clusterName;
        $instance->collectionName = $collectionName;
        $instance->objectName = $objectName;
        $instance->change = $change;
        return $instance;
    }

    /**
     * @param string $string
     * @return KolaAction
     * @throws \Exception
     */
    public static function loadEditString($string)
    {
        $json = json_decode($string, true);
        if (!is_array($json)) throw new \Exception("invalid edit string");
        return self::loadActionDictionary($json);
    }

    /**
     * @param array $dictionary
     * @return KolaAction
     * @throws \Exception
     */
    public static function loadActionDictionary($dictionary)
    {
        $instance = new KolaAction();
        $instance->action = ArkHelper::readTarget($dictionary, 'action');

        $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
        $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
        $instance->objectName = ArkHelper::readTarget($dictionary, 'object');

        switch ($instance->action) {
            case self::ACTION_DROP:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                if (!KolaFileSystemMapping::isValidEntityName($instance->clusterName)) {
                    throw new \Exception("cluster name invalid");
                }
                $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                if (KolaFileSystemMapping::isValidEntityName($instance->collectionName)) {
                    $instance->objectName = ArkHelper::readTarget($dictionary, 'object');
                }
                break;
            case self::ACTION_EDIT:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                $instance->objectName = ArkHelper::readTarget($dictionary, 'object');
                if (
                    !KolaFileSystemMapping::isValidEntityName($instance->clusterName)
                    || !KolaFileSystemMapping::isValidEntityName($instance->collectionName)
                    || !KolaFileSystemMapping::isValidEntityName($instance->objectName)
                ) {
                    throw new \Exception("belonging is not valid");
                }
                $instance->data = ArkHelper::readTarget($dictionary, 'data');
                if (!is_array($instance->data)) {
                    throw new \Exception("data is not array");
                }
                foreach ($instance->data as $key => $value) {
                    if (!is_string($key) || !is_string($value)) throw new \Exception("data should be all string");
                }
                break;
            case self::ACTION_RENAME:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                if (!KolaFileSystemMapping::isValidEntityName($instance->clusterName)) {
                    throw new \Exception("cluster name invalid");
                }
                $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                if (KolaFileSystemMapping::isValidEntityName($instance->collectionName)) {
                    $instance->objectName = ArkHelper::readTarget($dictionary, 'object');
                }
                $instance->change = ArkHelper::readTarget($dictionary, 'change');
                if (!KolaFileSystemMapping::isValidEntityName($instance->change)) {
                    throw new \Exception("Not a valid change!");
                }
                break;
            case self::ACTION_LIST:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                if (KolaFileSystemMapping::isValidEntityName($instance->clusterName)) {
                    $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                }
                break;
            case self::ACTION_QUERY:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                if (!KolaFileSystemMapping::isValidEntityName($instance->clusterName)) {
                    throw new \Exception("cluster name invalid");
                }
                $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                if (!KolaFileSystemMapping::isValidEntityName($instance->collectionName)) {
                    throw new \Exception("collection name invalid");
                }

                $query = ArkHelper::readTarget($dictionary, 'query');
                if (!is_string($query))
                    $instance->query = KolaQuery::loadQueryDictionary($query);
                else
                    $instance->objectName = $query;
                break;
            default:
                throw new \Exception("action is not defined");
        }

        return $instance;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            switch ($this->action) {
                case self::ACTION_QUERY:
                    $agent = new KolaAgent($this->clusterName);
                    if (!$this->query) {
                        $this->result = $agent->getObjectInCollection($this->collectionName, $this->objectName);
                    } else {
                        $this->result = $agent->selectObjectsInCollection($this->collectionName, $this->query);
                    }
                    break;
                case self::ACTION_LIST:
                    $agent = new KolaAgent($this->clusterName);
                    if (KolaFileSystemMapping::isValidEntityName($this->collectionName)) {
                        $this->result = $agent->getCollection($this->collectionName)->getObjectNameList();
                    } elseif (KolaFileSystemMapping::isValidEntityName($this->clusterName)) {
                        $this->result = $agent->getCluster()->getCollectionNameList();
                    } else {
                        $this->result = KolaAgent::listClusters();
                    }
                    break;
                case self::ACTION_EDIT:
                    $agent = new KolaAgent($this->clusterName);
                    $done = $agent->writeObjectInCollection($this->collectionName, $this->objectName, $this->data);
                    ArkHelper::assertItem($done, 'written failed');
                    break;
                case self::ACTION_DROP:
                    if (KolaFileSystemMapping::isValidEntityName($this->objectName)) {
                        $agent = new KolaAgent($this->clusterName);
                        $done = $agent->deleteObjectInCollection($this->collectionName, $this->objectName);
                    } elseif (KolaFileSystemMapping::isValidEntityName($this->collectionName)) {
                        $agent = new KolaAgent($this->clusterName);
                        $done = $agent->dropCollection($this->collectionName);
                    } else {
                        $done = KolaAgent::dropCluster($this->clusterName);
                    }
                    ArkHelper::assertItem($done, 'drop failed');
                    break;
                case self::ACTION_RENAME:
                    $agent = new KolaAgent($this->clusterName);
                    if (KolaFileSystemMapping::isValidEntityName($this->objectName)) {
                        $done = $agent->getCollection($this->collectionName)->getObject($this->objectName)->rename($this->change);
                    } elseif (KolaFileSystemMapping::isValidEntityName($this->collectionName)) {
                        $done = $agent->getCollection($this->collectionName)->rename($this->change);
                    } else {
                        $done = $agent->getCluster()->rename($this->change);
                    }
                    ArkHelper::assertItem($done, 'rename failed');
                    break;
                default:
                    throw new \Exception("action is not defined");
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * @return array
     */
    public function encode()
    {
        $json = [
            'action' => $this->action,
        ];
        switch ($this->action) {
            case self::ACTION_QUERY:
                $json['cluster'] = $this->clusterName;
                $json['collection'] = $this->collectionName;
                //$json['object']=$this->objectName;
                if (is_a($this->query, KolaQuery::class)) {
                    $json['query'] = $this->query->encode();
                } else {
                    $json['query'] = $this->objectName;
                }
                break;
            case self::ACTION_EDIT:
                $json['cluster'] = $this->clusterName;
                $json['collection'] = $this->collectionName;
                $json['object'] = $this->objectName;
                $json['data'] = $this->data;
                break;
            case self::ACTION_LIST:
            case self::ACTION_DROP:
                $json['cluster'] = $this->clusterName;
                if (KolaFileSystemMapping::isValidEntityName($this->collectionName)) {
                    $json['collection'] = $this->collectionName;
                    if (KolaFileSystemMapping::isValidEntityName($this->objectName)) {
                        $json['object'] = $this->objectName;
                    }
                }
                break;
        }
        return $json;
    }
}