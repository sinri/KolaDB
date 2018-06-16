<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 17:12
 */

namespace sinri\KolaDB\service;


class KolaClient
{
    /**
     * @var KolaSocket
     */
    protected $socketAgent;
    //protected $workers = [];
    //protected $max_workers = 0;
    public function __construct()
    {
        $this->socketAgent = new KolaSocket();
        //$this->workers = [];
        //$this->max_workers = InfuraOfficeToolkit::readConfig(['daemon', 'max_workers'], 0);
    }

    public function call()
    {
        try {
            $this->socketAgent->configSocketAsTcpIp("127.0.0.1", 3333);
            $this->socketAgent->runClient(
                function ($server) {
                    fwrite($server, json_encode(["test" => "test"]));

                    KolaServiceHelper::getLogger()->info('Accepted from erver');
                    stream_set_timeout($server, 0, 100000);
                    $content = '';
                    while (!feof($server)) {
                        $got = fread($server, 1024);
                        $content .= $got;
                        $json = json_decode($content, true);
                        if (is_array($json)) {
                            // over
                            break;
                        }
                    }
                    KolaServiceHelper::getLogger()->debug("Yomi received data: " . PHP_EOL . $content . PHP_EOL);

                }
            );
        } catch (\Exception $exception) {
            KolaServiceHelper::getLogger()->error("Exception when listening: " . $exception->getMessage());
        }
    }
}