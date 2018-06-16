<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/15
 * Time: 17:49
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

$agent = new \sinri\KolaDB\storage\KolaAgent("test");

DEBUG_CALL("drop cluster at first", function () {
    return \sinri\KolaDB\storage\KolaAgent::dropCluster("test");
});

DEBUG_CALL("write into object", function () use ($agent) {
    return $agent->writeObjectInCollection("collection1", "object1", [
        "key1" => "value1",
        "key2" => "value2",
    ]);
});

DEBUG_CALL("read object", function () use ($agent) {
    $collectionNames = $agent->getCluster()->getCollectionNameList();
    echo "collections:" . PHP_EOL;
    print_r($collectionNames);
    echo "see collection1." . PHP_EOL;
    $valueForKey0 = $agent->getCollection("collection1")->getObject("object1")->getData("key0");
    $valueForKey1 = $agent->getCollection("collection1")->getObject("object1")->getData("key1");
    $valueForKey2 = $agent->getCollection("collection1")->getObject("object0")->getData("key2");

    echo "value for key 0: " . json_encode($valueForKey0) . PHP_EOL;
    echo "value for key 1: " . json_encode($valueForKey1) . PHP_EOL;
    echo "value for key 2: " . json_encode($valueForKey2) . PHP_EOL;

    return true;
});