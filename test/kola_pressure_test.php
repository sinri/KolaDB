<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 15:13
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

for ($i = 0; $i < 100; $i++) {
    $pid = pcntl_fork();
    if ($pid > 0) {
        continue;
    } elseif ($pid < 0) {
        echo "Fork error!" . PHP_EOL;
    } else {
        echo "CHILD " . getmypid() . ' START' . PHP_EOL;
        require __DIR__ . '/kola_sdk_test.php';
        echo "CHILD " . getmypid() . ' FINISH' . PHP_EOL;
        exit();
    }
}

for ($i = 0; $i < 100; $i++) {
    $done = pcntl_wait($status);
    echo "WAIT: " . $done . " ... " . $status . PHP_EOL;
}
echo "OVER" . PHP_EOL;