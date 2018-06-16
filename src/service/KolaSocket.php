<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 16:10
 */

namespace sinri\KolaDB\service;


class KolaSocket
{
    const SERVER_CALLBACK_COMMAND_NONE = "NONE";
    const SERVER_CALLBACK_COMMAND_CLOSE_CLIENT = "CLOSE_CLIENT";
    const SERVER_CALLBACK_COMMAND_CLOSE_SERVER = "CLOSE_SERVER";
    const SOCKET_TYPE_UNIX_DOMAIN = "UNIX_DOMAIN";
    const SOCKET_TYPE_TCP_IP = "TCP_IP";
    protected $socketType;
    protected $unixDomainFile;
    protected $address;
    protected $port;
    protected $listenTimeout;
    protected $peerName;
    protected $serverSocket;

    /**
     * SocketAgent constructor.
     */
    public function __construct()
    {
        $this->socketType = self::SOCKET_TYPE_UNIX_DOMAIN;
        $this->unixDomainFile = "/tmp/KolaServerSocket";
        $this->address = null;
        $this->port = null;
        $this->listenTimeout = -1;
        $this->peerName = __CLASS__;
        $this->serverSocket = null;
    }

    /**
     * @param string $socketFile
     */
    public function configSocketAsUnixDomain($socketFile = "/tmp/KolaServerSocket")
    {
        $this->socketType = self::SOCKET_TYPE_UNIX_DOMAIN;
        $this->unixDomainFile = $socketFile;
    }

    /**
     * Note: When specifying a numerical IPv6 address (e.g. fe80::1), you must enclose the IP in square brackets—for example, tcp://[fe80::1]:80.
     * @param string $address IPv4 or IPv6
     * @param int $port
     */
    public function configSocketAsTcpIp($address, $port)
    {
        $this->socketType = self::SOCKET_TYPE_TCP_IP;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function socketAddress()
    {
        if ($this->socketType == self::SOCKET_TYPE_UNIX_DOMAIN) {
            return "unix://" . $this->unixDomainFile;
        } elseif ($this->socketType == self::SOCKET_TYPE_TCP_IP) {
            return "tcp://{$this->address}:{$this->port}";
        }
        throw new \Exception("socket address error");
    }

    /**
     * @param callback|null $specialHandler
     */
    protected function registerDeathSignalHandler($specialHandler = null)
    {
        KolaServiceHelper::defineSignalHandler([SIGINT, SIGTERM, SIGHUP], function ($signal_number) use ($specialHandler) {
            KolaServiceHelper::getLogger()->error("SIGNAL: " . $signal_number);
            if ($specialHandler) {
                call_user_func_array($specialHandler, [$this->serverSocket, $signal_number]);
            }
            $this->terminateServerWhenSignalComes();
            exit();
        });
    }

    /**
     * @param callable|null $requestHandler (resource $client)
     * @param callable|null $bindStatusHandler (bool $bind_ok)
     * @param callable|null $specialHandler (resource $serverSocket, int $signal)
     * @throws \Exception
     */
    public function runServer($requestHandler = null, $bindStatusHandler = null, $specialHandler = null)
    {
        $this->serverSocket = stream_socket_server($this->socketAddress(), $errorNumber, $errorMessage);
        if ($bindStatusHandler) {
            $bind_ok = ($this->serverSocket === false ? false : true);
            call_user_func_array($bindStatusHandler, [$bind_ok]);
        }
        if ($this->serverSocket === false) {
            throw new \UnexpectedValueException("Could not bind to socket: $errorMessage");
        }
        $this->registerDeathSignalHandler($specialHandler);
        KolaServiceHelper::getLogger()->info("BEGIN LISTEN...");
        while (true) {
            if (function_exists("pcntl_signal_dispatch")) {
                KolaServiceHelper::getLogger()->debug("Now server runs `pcntl_signal_dispatch`");
                pcntl_signal_dispatch();
            }
            $client = stream_socket_accept($this->serverSocket, $this->listenTimeout, $this->peerName);
            if ($client) {
                $callback_command = self::SERVER_CALLBACK_COMMAND_NONE;
                $pairName = stream_socket_get_name($client, true);
                if ($requestHandler) {
                    $callback_command = call_user_func_array($requestHandler, [$client]);
                } else {
                    $content = stream_get_contents($client);
                    KolaServiceHelper::getLogger()->info("Received from [{$pairName}]: " . $content);
                }
                if (
                    $callback_command == self::SERVER_CALLBACK_COMMAND_CLOSE_CLIENT
                    || $callback_command == self::SERVER_CALLBACK_COMMAND_CLOSE_SERVER
                ) {
                    fclose($client);
                    KolaServiceHelper::getLogger()->info("CLOSE CLIENT [{$pairName}]");
                }
                if ($callback_command == self::SERVER_CALLBACK_COMMAND_CLOSE_SERVER) {
                    KolaServiceHelper::getLogger()->info("CLOSE SERVER as required");
                    break;
                }
            }
        }
        $this->terminateServerWhenSignalComes();
    }

    protected function terminateServerWhenSignalComes()
    {
        if ($this->serverSocket) {
            KolaServiceHelper::getLogger()->info("CLOSE SERVER by " . __METHOD__);
            fclose($this->serverSocket);
            if ($this->socketType == self::SOCKET_TYPE_UNIX_DOMAIN) {
                $deleted = unlink($this->unixDomainFile);
                KolaServiceHelper::getLogger()->info("Deleting unix domain socket file [{$this->unixDomainFile}]..." . json_encode($deleted));
            }
        }
    }

    /**
     * @param callable|null $callback (resource $server)
     * @return mixed|string
     * @throws \Exception
     */
    public function runClient($callback = null)
    {
        $server = stream_socket_client($this->socketAddress(), $errNumber, $errorMessage, $this->listenTimeout);
        if ($server === false) {
            throw new \UnexpectedValueException("Failed to connect, check Daemon logs for details: {$errorMessage}");
        }
        if ($callback) {
            $response = call_user_func_array($callback, [$server]);
        } else {
            fwrite($server, 'PING');
            $response = '';
            while (!feof($server)) {
                $response .= fgets($server, 1024);
            }
            //DaemonHelper::log("DEBUG", " sent PING, response: " . $response);
        }
        fclose($server);
        return $response;
    }
}