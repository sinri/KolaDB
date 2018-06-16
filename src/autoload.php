<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/14
 * Time: 23:20
 */

require_once __DIR__ . '/../vendor/autoload.php';

\sinri\ark\core\ArkHelper::registerAutoload("sinri\KolaDB", __DIR__);

function DEBUG_CALL($call_title, $callback)
{
    $date = date('Y-m-d H:i:s');
    echo "[{$date}] BEGIN: [" . $call_title . ']' . PHP_EOL;
    $result = call_user_func_array($callback, []);
    $date = date('Y-m-d H:i:s');
    echo "[{$date}] E N D: [" . $call_title . "] returned " . json_encode($result) . PHP_EOL;
}