<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 17:12
 */

namespace sinri\KolaDB\service;


use sinri\ark\core\ArkHelper;
use sinri\KolaDB\storage\KolaAction;

class KolaClient
{
    /**
     * @var KolaSocket
     */
    protected $socketAgent;
    /**
     * @var null|string
     */
    protected $error;
    /**
     * @var null|
     */
    protected $data;

    public function __construct()
    {
        $this->socketAgent = new KolaSocket();
        //$this->workers = [];
        //$this->max_workers = InfuraOfficeToolkit::readConfig(['daemon', 'max_workers'], 0);
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        return $this->error;
    }

    //protected $workers = [];
    //protected $max_workers = 0;

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param KolaAction $action
     * @return bool
     */
    public function call($action)
    {
        try {
            $this->socketAgent->configSocketAsTcpIp("127.0.0.1", 3333);
            $response = $this->socketAgent->runClient(
                function ($server) use ($action) {
                    fwrite($server, json_encode($action->encode()));

                    KolaServiceHelper::getLogger()->info('Accepted from server');
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
                    KolaServiceHelper::getLogger()->debug("Yomi received data: " . $content);
                    return $content;
                }
            );
            $response = json_decode($response, true);
            KolaServiceHelper::getLogger()->debug("response parsed as", [$response]);
            if (ArkHelper::readTarget($response, 'code') !== 'OK') {
                $error = ArkHelper::readTarget($response, 'error');
                throw new \Exception($error);
            }
            $this->data = ArkHelper::readTarget($response, 'data');
            return true;
        } catch (\Exception $exception) {
            $this->error = "Exception when listening: " . $exception->getMessage();
            KolaServiceHelper::getLogger()->error($this->error);
            return false;
        }
    }
}