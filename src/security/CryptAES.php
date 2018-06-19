<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 16:26
 */

namespace sinri\KolaDB\security;


class CryptAES
{
    public static function generateKey()
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }

    public static function generateInitializationVector()
    {
        return base64_encode(openssl_random_pseudo_bytes(16));
    }

    public function __construct()
    {
        //TODO load IV?
    }

    protected function getInitializationVector()
    {
        return 'w2wJCnctEG09danPPI7SxQ==';//TODO
    }

    public function encrypt($data, $key)
    {
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($this->getInitializationVector()));
        return base64_encode($encrypted);
    }

    public function decrypt($encrypted, $key)
    {
        $encrypted = base64_decode($encrypted);
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($this->getInitializationVector()));
        return $decrypted;
    }
}
/*
AES:
<?php
header('Content-Type: text/plain;charset=utf-8');
$data = 'phpbest';
$key = 'oScGU3fj8m/tDCyvsbEhwI91M1FcwvQqWuFpPoDHlFk='; //echo base64_encode(openssl_random_pseudo_bytes(32));
$iv = 'w2wJCnctEG09danPPI7SxQ=='; //echo base64_encode(openssl_random_pseudo_bytes(16));
echo '内容: '.$data."\n";

$encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
echo '加密: '.base64_encode($encrypted)."\n";

$encrypted = base64_decode('To3QFfvGJNm84KbKG1PLzA==');
$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
echo '解密: '.$decrypted."\n";
?>

    RSA:
    用openssl生成rsa密钥对(私钥/公钥):
    openssl genrsa -out rsa_private_key.pem 1024
    openssl rsa -pubout -in rsa_private_key.pem -out rsa_public_key.pem
    <?php
header('Content-Type: text/plain;charset=utf-8');
$data = 'phpbest';
echo '原始内容: '.$data."\n";

openssl_public_encrypt($data, $encrypted, file_get_contents(dirname(__FILE__).'/rsa_public_key.pem'));
echo '公钥加密: '.base64_encode($encrypted)."\n";

$encrypted = base64_decode('nMD7Yrx37U5AZRpXukingESUNYiSUHWThekrmRA0oD0=');
openssl_private_decrypt($encrypted, $decrypted, file_get_contents(dirname(__FILE__).'/rsa_private_key.pem'));
echo '私钥解密: '.$decrypted."\n";
?>
*/