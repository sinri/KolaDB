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
     * @param string $string
     * @return KolaAction
     * @throws \Exception
     */
    public static function loadEditString($string)
    {
        $json = json_decode($string, true);
        if (!is_array($json)) throw new \Exception("invalid edit string");
        return self::loadQueryDictionary($json);
    }

    /**
     * @param array $dictionary
     * @return KolaAction
     * @throws \Exception
     */
    public static function loadQueryDictionary($dictionary)
    {
        $instance = new KolaAction();
        $instance->action = ArkHelper::readTarget($dictionary, 'action');
        ArkHelper::assertItem(in_array($instance->action, [
            self::ACTION_DROP, self::ACTION_EDIT, self::ACTION_QUERY,
        ]), 'action invalid');

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
                    KolaFileSystemMapping::isValidEntityName($instance->clusterName)
                    || KolaFileSystemMapping::isValidEntityName($instance->collectionName)
                    || KolaFileSystemMapping::isValidEntityName($instance->objectName)
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
            case self::ACTION_QUERY:
                $instance->clusterName = ArkHelper::readTarget($dictionary, 'cluster');
                if (!KolaFileSystemMapping::isValidEntityName($instance->clusterName)) {
                    throw new \Exception("cluster name invalid");
                }
                $instance->collectionName = ArkHelper::readTarget($dictionary, 'collection');
                if (!KolaFileSystemMapping::isValidEntityName($instance->collectionName)) {
                    throw new \Exception("collection name invalid");
                }
                $instance->query = KolaQuery::loadQueryDictionary(ArkHelper::readTarget($dictionary, 'query'));
                break;
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
                    $this->result = $agent->selectObjectsInCollection($this->collectionName, $this->query);
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
                default:
                    throw new \Exception("action is not defined");
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }
}