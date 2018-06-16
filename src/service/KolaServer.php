<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 16:19
 */

namespace sinri\KolaDB\service;


use sinri\ark\core\ArkHelper;
use sinri\KolaDB\storage\KolaAction;

class KolaServer
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

    public function listen()
    {
        $this->socketAgent->configSocketAsTcpIp("0.0.0.0", 3333);
        try {
            $this->socketAgent->runServer(
                function ($client) {
                    $pairName = stream_socket_get_name($client, true);
                    KolaServiceHelper::getLogger()->info('Accepted from ' . $pairName);
                    stream_set_timeout($client, 0, 100000);
                    $content = '';
                    while (!feof($client)) {
                        $got = fread($client, 1024);
                        $content .= $got;
                        $json = json_decode($content, true);
                        if (is_array($json)) {
                            // over
                            break;
                        }
                    }
                    KolaServiceHelper::getLogger()->debug("Yomi received data: " . $content);

                    try {
                        $action = KolaAction::loadEditString($content);
                        $done = $action->execute();
                        ArkHelper::assertItem($done, "KolaAction Error: " . $action->error);
                        $result = [
                            "code" => "OK",
                            "data" => $action->result,
                        ];
                    } catch (\Exception $exception) {
                        $result = [
                            "code" => "FAIL",
                            "data" => $exception->getMessage(),
                        ];
                    }

                    $responseString = json_encode($result);
                    fwrite($client, $responseString);

                    KolaServiceHelper::getLogger()->debug("Response: " . $responseString);

                    return KolaSocket::SERVER_CALLBACK_COMMAND_CLOSE_CLIENT;
                },
                function ($bindOK) {
                    KolaServiceHelper::getLogger()->debug("bind ok?", [$bindOK]);
                }
            );
        } catch (\Exception $exception) {
            KolaServiceHelper::getLogger()->error("Exception when listening: " . $exception->getMessage());
        }
    }
}