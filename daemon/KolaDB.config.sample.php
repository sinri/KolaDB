<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/6/16
 * Time: 22:41
 */

/**
 * The address to listen, in IPv4 or IPv6 format.
 * For security reason, local address is used as default.
 * To listen to the internet, use '0.0.0.0'.
 * Note: When specifying a numerical IPv6 address (e.g. fe80::1), you must enclose the IP in square brackets—for example, tcp://[fe80::1]:80.
 */
$config["listen.address"] = "127.0.0.1";

/**
 * The port to listen, an integer.
 */
$config['listen.port'] = 3897;

/**
 * The directory path of the runtime folder, which should be a writable folder.
 * Leave it to null would refer to runtime folder under KolaDB root folder.
 */
$config['runtime.dir'] = null;