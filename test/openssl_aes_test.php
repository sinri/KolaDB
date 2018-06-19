<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 16:45
 */

require_once __DIR__ . '/../src/autoload.php';

date_default_timezone_set("Asia/Shanghai");

$key = \sinri\KolaDB\security\CryptAES::generateKey();

$aes = new \sinri\KolaDB\security\CryptAES();

$data = "I think it right.";
var_dump($data);

$encrypted = $aes->encrypt($data, $key);
var_dump($encrypted);

$decrypt = $aes->decrypt($encrypted, $key);
var_dump($decrypt);
