<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 17:18
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

$client = new \sinri\KolaDB\service\KolaClient();

// drop cluster

DEBUG_CALL("drop cluster test1", function () use ($client) {
    $action_1 = \sinri\KolaDB\storage\KolaAction::createDropAction("test1");
    $done = $client->call($action_1);
    if ($done) {
        var_dump($client->getData());
    } else {
        var_dump($client->getError());
    }
    return $done;
});

// edit

DEBUG_CALL("write cluster test1.collection1.object1", function () use ($client) {
    $data = ['key1' => 'value1'];
    $action_1 = \sinri\KolaDB\storage\KolaAction::createEditAction("test1", "collection1", "object1", $data);
    $done = $client->call($action_1);
    if ($done) {
        var_dump($client->getData());
    } else {
        var_dump($client->getError());
    }
    return $done;
});

// read

DEBUG_CALL("read cluster test1.collection1.object1[key1=value1]", function () use ($client) {
    $query = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQX, "key1", "value1");
    $action_1 = \sinri\KolaDB\storage\KolaAction::createQueryAction("test1", "collection1", $query);
    $done = $client->call($action_1);
    if ($done) {
        var_dump($client->getData());
    } else {
        var_dump($client->getError());
    }
    return $done;
});

DEBUG_CALL("read cluster test1.collection1.object1[key1=value2]", function () use ($client) {
    $query = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQX, "key1", "value2");
    $action_1 = \sinri\KolaDB\storage\KolaAction::createQueryAction("test1", "collection1", $query);
    $done = $client->call($action_1);
    if ($done) {
        var_dump($client->getData());
    } else {
        var_dump($client->getError());
    }
    return $done;
});