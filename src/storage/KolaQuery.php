<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/15
 * Time: 14:57
 */

namespace sinri\KolaDB\storage;


use sinri\ark\core\ArkHelper;

class KolaQuery
{
    const METHOD_EQ = "==";
    const METHOD_EQX = "===";
    const METHOD_NEQ = "!=";
    const METHOD_NEQX = "!==";
    const METHOD_GT = ">";
    const METHOD_EGT = ">=";
    const METHOD_LT = "<";
    const METHOD_ELT = "<=";

    const METHOD_PART = "part";
    const METHOD_IPART = "ipart";

    const METHOD_IN = "in";
    const METHOD_NIN = "not in";

    //const METHOD_INCLUDE = "include";
    //const METHOD_EXCLUDE = "exclude";
    //const METHOD_INTERSECTION = "intersection";
    //const METHOD_NO_INTERSECTION = "no_intersection";

    const METHOD_AND = "&&";
    const METHOD_OR = "||";

    /**
     * @var string
     */
    public $method;
    /**
     * @var string
     */
    public $field;
    /**
     * @var mixed
     */
    public $reference;
    /**
     * @var KolaQuery[]
     */
    public $queries;

    protected function __construct()
    {
        $this->method = null;
        $this->field = null;
        $this->reference = null;
        $this->queries = null;
    }

    /**
     * @param string $method
     * @param string $field
     * @param string $reference
     * @return KolaQuery
     * @throws \Exception
     */
    public static function createSingleQuery($method, $field, $reference)
    {
        if (!self::isValidMethod($method, $type) || $type !== 'SCALAR') {
            throw new \Exception("not correct method");
        }
        $instance = new KolaQuery();
        $instance->method = $method;
        $instance->field = $field;
        $instance->reference = $reference;
        return $instance;
    }

    /**
     * @param string $method
     * @param string $field
     * @param string[] $group
     * @return KolaQuery
     * @throws \Exception
     */
    public static function createMultipleQuery($method, $field, $group)
    {
        if (!self::isValidMethod($method, $type) || $type !== 'ARRAY') {
            throw new \Exception("not correct method");
        }
        $instance = new KolaQuery();
        $instance->method = $method;
        $instance->field = $field;
        $instance->reference = $group;
        return $instance;
    }

    /**
     * @param string $method
     * @param KolaQuery[] $queries
     * @return KolaQuery
     * @throws \Exception
     */
    public static function createGroupQuery($method, $queries)
    {
        if (!self::isValidMethod($method, $type) || $type !== 'QUERIES') {
            throw new \Exception("not correct method");
        }
        $instance = new KolaQuery();
        $instance->method = $method;
        $instance->queries = $queries;
        return $instance;
    }

    /**
     * @deprecated
     * @param string $queryString
     * @return KolaQuery
     * @throws \Exception
     */
    public static function loadQueryString($queryString)
    {
        $queryJson = json_decode($queryString, true);
        if (!is_array($queryJson)) {
            throw new \Exception("Query JSON invalid");
        }
        return self::loadQueryDictionary($queryJson);
    }

    /**
     * @param array $queryArray
     * @return KolaQuery
     * @throws \Exception
     */
    public static function loadQueryDictionary($queryArray)
    {
        $method = ArkHelper::readTarget($queryArray, 'method');
        if (!self::isValidMethod($method, $type)) {
            throw new \Exception("not a valid method");
        }

        $query = new KolaQuery();
        $query->method = $method;
        switch ($type) {
            case "SCALAR":
                $query->field = ArkHelper::readTarget($queryArray, 'field');
                $query->reference = ArkHelper::readTarget($queryArray, 'reference');
                ArkHelper::assertItem(is_string($query->field), "field invalid");
                ArkHelper::assertItem(is_scalar($query->reference), "reference is not scalar");
                break;
            case "ARRAY":
                $query->field = ArkHelper::readTarget($queryArray, 'field');
                $query->reference = ArkHelper::readTarget($queryArray, 'reference');
                ArkHelper::assertItem(is_string($query->field), "field invalid");
                ArkHelper::assertItem(is_array($query->reference), "reference is not array");
                break;
            case "QUERIES":
                $subs = ArkHelper::readTarget($query, 'queries');
                ArkHelper::assertItem(is_array($subs), "reference is not array");
                $query->queries = [];
                foreach ($subs as $sub) {
                    $query->queries[] = self::loadQueryDictionary($sub);
                }
                break;
        }

        return $query;
    }

    protected static function isValidMethod($method, &$type)
    {
        $type = "UNKNOWN";

        if (in_array($method, [
            self::METHOD_AND,
            self::METHOD_EQ,
            self::METHOD_EQX,
            self::METHOD_NEQ,
            self::METHOD_NEQX,
            self::METHOD_GT,
            self::METHOD_EGT,
            self::METHOD_LT,
            self::METHOD_ELT,
            self::METHOD_PART,
            self::METHOD_IPART,
        ])) {
            $type = "SCALAR";
            return true;
        }

        if (in_array($method, [
            self::METHOD_IN,
            self::METHOD_NIN,
        ])) {
            $type = "ARRAY";
            return true;
        }

        if (in_array($method, [

            self::METHOD_AND,
            self::METHOD_OR,
        ])) {
            $type = "QUERIES";
            return true;
        }

        return false;
    }

    /**
     * @param KolaCollection $collection
     * @return KolaObject[]
     * @throws \Exception
     */
    public function resultForCollection($collection)
    {
        $objectNameList = $collection->getObjectNameList();
        $result = [];
        foreach ($objectNameList as $objectName) {
            $object = $collection->getObject($objectName);
            if ($this->computeWithMethodAgainstObject($object)) {
                $result[] = $object;
            }
        }
        return $result;
    }

    /**
     * @param KolaObject $object
     * @return bool
     * @throws \Exception
     */
    protected function computeWithMethodAgainstObject($object)
    {
        if (!self::isValidMethod($this->method, $type)) {
            throw new \Exception("not a valid method");
        }
        switch ($type) {
            case "SCALAR":
            case "ARRAY":
                $op1 = $object->getData($this->field);
                $op2 = $this->reference;
                return self::computeWithMethod($this->method, $op1, $op2);
            case "QUERIES":
                if ($this->method === self::METHOD_OR) {
                    foreach ($this->queries as $query) {
                        $x = $query->computeWithMethodAgainstObject($object);
                        if ($x) {
                            return true;
                        }
                    }
                    return false;
                } elseif ($this->method === self::METHOD_AND) {
                    foreach ($this->queries as $query) {
                        $x = $query->computeWithMethodAgainstObject($object);
                        if (!$x) {
                            return false;
                        }
                    }
                    return true;
                }
        }
        throw new \Exception("cannot get a result");
    }

    /**
     * @param $method
     * @param $op1
     * @param $op2
     * @return bool
     */
    protected static function computeWithMethod($method, $op1, $op2 = null)
    {
        switch ($method) {
            case self::METHOD_EQ:
                return $op1 == $op2;
            case self::METHOD_EQX:
                return $op1 === $op2;
            case self::METHOD_NEQ:
                return $op1 != $op2;
            case self::METHOD_NEQX:
                return $op1 !== $op2;
            case self::METHOD_GT:
                return $op1 > $op2;
            case self::METHOD_EGT:
                return $op1 >= $op2;
            case self::METHOD_LT:
                return $op1 < $op2;
            case self::METHOD_ELT:
                return $op1 <= $op2;
            case self::METHOD_PART:
                return (strstr($op1, $op2) !== false);
            case self::METHOD_IPART:
                return (stristr($op1, $op2) !== false);
            case self::METHOD_IN:
                return in_array($op1, $op2);
            case self::METHOD_NIN:
                return !in_array($op1, $op2);
//            case self::METHOD_AND:
//                foreach ($op1 as $item){
//                    if(!$item)return false;
//                }
//                return true;
//            case self::METHOD_OR:
//                foreach ($op1 as $item){
//                    if($item)return true;
//                }
//                return false;
            default:
                return false;
        }
    }

    /**
     * @return array
     */
    public function encode()
    {
        $json = [
            "method" => $this->method,
        ];
        self::isValidMethod($this->method, $type);
        switch ($type) {
            case "SCALAR":
            case "ARRAY":
                $json['field'] = $this->field;
                $json['reference'] = $this->reference;
                break;
            case "QUERIES":
                $json['queries'] = [];
                foreach ($this->queries as $query) {
                    $json['queries'][] = $query->encode();
                }
                break;
        }
        return $json;
    }
}