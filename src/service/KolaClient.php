<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 17:12
 */

namespace sinri\KolaDB\service;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
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

    public function __construct($address = '127.0.0.1', $port = 3333)
    {
        $this->socketAgent = new KolaSocket();
        $this->socketAgent->configSocketAsTcpIp($address, $port);
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return ArkLogger
     */
    public function getLogger()
    {
        return ArkLogger::makeSilentLogger();
    }

    /**
     * @param KolaAction $action
     * @return bool
     */
    public function call($action)
    {
        try {
            $response = $this->socketAgent->runClient(
                function ($server) use ($action) {
                    fwrite($server, json_encode($action->encode()));

                    $this->getLogger()->info('Accepted from server');
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
                    $this->getLogger()->debug("Yomi received data: " . $content);
                    return $content;
                }
            );
            $response = json_decode($response, true);
            $this->getLogger()->debug("response parsed as", [$response]);
            if (ArkHelper::readTarget($response, 'code') !== 'OK') {
                $error = ArkHelper::readTarget($response, 'error');
                throw new \Exception($error);
            }
            $this->data = ArkHelper::readTarget($response, 'data');
            return true;
        } catch (\Exception $exception) {
            $this->error = "Exception when listening: " . $exception->getMessage();
            $this->getLogger()->error($this->error);
            return false;
        }
    }
}