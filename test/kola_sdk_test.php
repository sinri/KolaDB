<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 10:49
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

$sdk = new \sinri\KolaDB\sdk\KolaDBSDK('127.0.0.1', 3897);

$test_cluster_name = 'kola_sdk_test_cluster';

try {
    DEBUG_CALL("clean cluster", function () use ($sdk, $test_cluster_name) {
        return $sdk->dropCluster($test_cluster_name);
    });
    DEBUG_CALL("read from empty cluster 1 --> false", function () use ($sdk, $test_cluster_name) {
        return $sdk->queryObject($test_cluster_name, 'c1', 'o1');
    });
    DEBUG_CALL("read from empty cluster 2 --> []", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "kkk", "eee");
        return $sdk->queryObject($test_cluster_name, 'c1', $q1);
    });

    DEBUG_CALL("write into c1.o1", function () use ($sdk, $test_cluster_name) {
        return $sdk->editObject($test_cluster_name, 'c1', 'o1', [
            'k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3',
        ]);
    });

    DEBUG_CALL("write into c1.o2", function () use ($sdk, $test_cluster_name) {
        return $sdk->editObject($test_cluster_name, 'c1', 'o2', [
            'k1' => 'v1', 'k2' => 'v1', 'k3' => 'v3',
        ]);
    });

    DEBUG_CALL("list items --> 2", function () use ($sdk, $test_cluster_name) {
        $count = 0;
        $clusters = $sdk->listClusters();
        foreach ($clusters as $cluster) {
            $collections = $sdk->listCollectionsInCluster($cluster);
            foreach ($collections as $collection) {
                $objects = $sdk->listObjectsInCollection($cluster, $collection);
                foreach ($objects as $object) {
                    echo $cluster . "." . $collection . "." . $object . PHP_EOL;
                    $count++;
                }
            }
        }
        return $count;
    });

    DEBUG_CALL("read from empty cluster 3 --> exist 1", function () use ($sdk, $test_cluster_name) {
        return $sdk->queryObject($test_cluster_name, 'c1', 'o1');
    });
    DEBUG_CALL("read from empty cluster 4 --> exist 2", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k1", "v1");
        return $sdk->queryObject($test_cluster_name, 'c1', $q1);
    });
    DEBUG_CALL("read from empty cluster 5 --> exist 1", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k1", "v1");
        $q2 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_NEQ, "k2", "v1");
        $q3 = \sinri\KolaDB\storage\KolaQuery::createGroupQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_AND, [$q1, $q2]);
        return $sdk->queryObject($test_cluster_name, 'c1', $q3);
    });

    DEBUG_CALL("read from empty cluster 6 --> false", function () use ($sdk, $test_cluster_name) {
        return $sdk->queryObject($test_cluster_name, 'c1', 'o3');
    });
    DEBUG_CALL("read from empty cluster 7 --> []", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k1", "v2");
        return $sdk->queryObject($test_cluster_name, 'c1', $q1);
    });
    DEBUG_CALL("read from empty cluster 8 --> []", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k2", "v1");
        $q2 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_NEQ, "k5", "v1");
        $q3 = \sinri\KolaDB\storage\KolaQuery::createGroupQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_AND, [$q1, $q2]);
        return $sdk->queryObject($test_cluster_name, 'c1', $q3);
    });

    DEBUG_CALL("drop c1.o1", function () use ($sdk, $test_cluster_name) {
        return $sdk->dropObject($test_cluster_name, 'c1', 'o1');
    });

    DEBUG_CALL("read from empty cluster 3 --> false", function () use ($sdk, $test_cluster_name) {
        return $sdk->queryObject($test_cluster_name, 'c1', 'o1');
    });
    DEBUG_CALL("read from empty cluster 4 --> []", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k1", "v1");
        return $sdk->queryObject($test_cluster_name, 'c1', $q1);
    });
    DEBUG_CALL("read from empty cluster 5 --> []", function () use ($sdk, $test_cluster_name) {
        $q1 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_EQ, "k1", "v1");
        $q2 = \sinri\KolaDB\storage\KolaQuery::createSingleQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_NEQ, "k2", "v1");
        $q3 = \sinri\KolaDB\storage\KolaQuery::createGroupQuery(\sinri\KolaDB\storage\KolaQuery::METHOD_AND, [$q1, $q2]);
        return $sdk->queryObject($test_cluster_name, 'c1', $q3);
    });

} catch (Exception $exception) {
    echo "EXCEPTION: " . $exception->getMessage() . PHP_EOL;
}