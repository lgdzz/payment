<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/24
 */

namespace lgdz\utils;

class Rsa2Encrypt
{
    protected $key;

    /**
     * 设置key
     * @param $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * RSA2签名, 此处秘钥是私有秘钥
     * @param string $data 签名的数组
     * @throws \Exception
     * @return string
     */
    public function encrypt($data)
    {
        if ($this->key === false) {
            return '';
        }

        $res = openssl_get_privatekey($this->key);
        if (empty($res)) {
            throw new \Exception('您使用的私钥格式错误，请检查RSA私钥配置');
        }

        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA2解密 此处秘钥是用户私有秘钥
     * @param string $content 需要解密的内容，密文
     * @throws \Exception
     * @return string
     */
    public function decrypt($content)
    {
        if ($this->key === false) {
            return '';
        }

        $res = openssl_get_privatekey($this->key);
        if (empty($res)) {
            throw new \Exception('您使用的私钥格式错误，请检查RSA私钥配置');
        }

        //用base64将内容还原成二进制
        $decodes = base64_decode($content);

        $str     = '';
        $dcyCont = '';
        foreach ($decodes as $n => $decode) {
            if (!openssl_private_decrypt($decode, $dcyCont, $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $str .= $dcyCont;
        }

        openssl_free_key($res);
        return $str;
    }

    /**
     * RSA2验签 ，此处的秘钥，是第三方公钥
     * @param string $data 待签名数据
     * @param string $sign 要校对的的签名结果
     * @throws \Exception
     * @return bool
     */
    public function rsaVerify($data, $sign)
    {
        // 初始时，使用公钥key
        $res = openssl_get_publickey($this->key);
        if (empty($res)) {
            throw new \Exception('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }

        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        return $result;
    }

    public function getRsaKeyValue($key, $type = 'private')
    {
        if (is_file($key)) {// 是文件
            $keyStr = @file_get_contents($key);
        } else {
            $keyStr = $key;
        }
        $keyStr = str_replace(PHP_EOL, '', $keyStr);
        // 为了解决用户传入的密钥格式，这里进行统一处理
        if ($type === 'private') {
            $beginStr = ['-----BEGIN RSA PRIVATE KEY-----', '-----BEGIN PRIVATE KEY-----'];
            $endStr   = ['-----END RSA PRIVATE KEY-----', '-----END PRIVATE KEY-----'];
        } else {
            $beginStr = ['-----BEGIN PUBLIC KEY-----', ''];
            $endStr   = ['-----END PUBLIC KEY-----', ''];
        }
        $keyStr = str_replace($beginStr, ['', ''], $keyStr);
        $keyStr = str_replace($endStr, ['', ''], $keyStr);

        $rsaKey = $beginStr[0] . PHP_EOL . wordwrap($keyStr, 64, PHP_EOL, true) . PHP_EOL . $endStr[0];

        return $rsaKey;
    }

}