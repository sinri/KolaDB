<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 22:41
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

$config = [];

$config["listen.address"] = "127.0.0.1";
$config['listen.port'] = 3897;

$configPaths = [
    __DIR__ . '/KolaDB.config.php',
    '/etc/KolaDB/KolaDB.config.php',
    '~/KolaDB/KolaDB.config.php',
];

foreach ($configPaths as $configPath) {
    if (file_exists($configPath)) {
        /** @noinspection PhpIncludeInspection */
        require $configPath;
        break;
    }
}

\sinri\KolaDB\storage\KolaCluster::setRuntimeDirectoryPath(\sinri\ark\core\ArkHelper::readTarget($config, 'runtime.dir', null));

$server = new \sinri\KolaDB\service\KolaServer($config["listen.address"], $config['listen.port']);

echo date('Y-m-d H:i:s') . ' KolaDB Startup' . PHP_EOL;
$server->listen();
echo date('Y-m-d H:i:s') . ' KolaDB Exited' . PHP_EOL;