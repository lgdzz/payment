<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/24
 */

namespace lgdz\driver\alipay;

use lgdz\utils\Http;
use lgdz\utils\Rsa2Encrypt;

class Config
{
    private $config = [
        'app_id' => '',
        'format' => 'JSON',
        'charset' => 'UTF-8',
        'sign_type' => 'RSA2',
        'version' => '1.0',
        'ali_public_key' => '',
        'rsa_private_key' => ''
    ];

    private $gateway = 'https://openapi.alipay.com/gateway.do';
    private $body = '';

    //============================== 业务流程 ==============================

    //配置开发者信息
    protected function config(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    protected function body(array $callback)
    {
        $body = [];

        //公共请求参数
        $body['method'] = $this->method;
        $body['app_id'] = $this->_getConfig('app_id');
        $body['format'] = $this->_getConfig('format');
        $body['charset'] = $this->_getConfig('charset');
        $body['sign_type'] = $this->_getConfig('sign_type');
        $body['version'] = $this->_getConfig('version');
        $body['timestamp'] = date('Y-m-d H:i:s');

        //其它请求参数
        if (isset($callback['other'])) {
            $other = [];
            $callback['other']($other);
            $body = array_merge($body, $other);
        }

        //请求参数
        if (isset($callback['content'])) {
            $content = [];
            $callback['content']($content);
            $body['biz_content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        //签名
        $body['sign'] = $this->sign($body);

        $this->body = $body;

        return $this;
    }

    protected function sign(array $data)
    {
        $data = array_filter($data);
        ksort($data);
        $rsa2 = new Rsa2Encrypt;
        return $rsa2->setKey($rsa2->getRsaKeyValue($this->_getConfig('rsa_private_key'), 'private'))
            ->encrypt(urldecode(http_build_query($data)));
    }

    protected function send()
    {
        return json_decode(Http::get($this->getUrl()), true);
    }

    protected function getUrl()
    {
        return $this->gateway . '?' . $this->_splicingString($this->body);
    }

    protected function getString()
    {
        return $this->_splicingString($this->body);
    }

    //获取支付宝推送数据
    protected function getMsgFromAli()
    {
        return $_POST;
    }

    //============================== 返回信息验证 ==============================

    //成功验证签名（同步） @return bool
    protected function succSyncVerifySign(array $data, $sign)
    {
        $rsa2 = new Rsa2Encrypt;
        return $rsa2->setKey($rsa2->getRsaKeyValue($this->_getConfig('ali_public_key'), 'public'))
            ->rsaVerify(json_encode($data, JSON_UNESCAPED_UNICODE), $sign);
    }

    //成功验证签名（异步） @return bool
    protected function succVerifySign(array $data)
    {
        if (isset($data['sign'])) {
            $sign = $data['sign'];
            unset($data['sign'], $data['sign_type']);
            ksort($data);
            $rsa2 = new Rsa2Encrypt;
            return $rsa2->setKey($rsa2->getRsaKeyValue($this->_getConfig('ali_public_key'), 'public'))
                ->rsaVerify(urldecode(http_build_query($data)), $sign);
        } else {
            return false;
        }
    }

    //成功交易 @return bool
    protected function succTrade(array $data)
    {
        return ($data['trade_status'] === 'TRADE_SUCCESS' || $data['trade_status'] === 'TRADE_FINISHED') ? true : false;
    }

    //成功退款 @return bool
    protected function succRefund(array $data)
    {
        if ($data['code'] === '10000' && $data['fund_change'] === 'Y') {
            return true;
        } else {
            return false;
        }
    }

    //接口调用成功
    protected function succRequest(array $data)
    {
        if ($data['code'] === '10000') {
            return true;
        } else {
            return false;
        }
    }

    //============================== 私有方法 ==============================

    private function _getConfig($name)
    {
        return $this->config[$name];
    }

    private function _splicingString(array $data)
    {
        $str = '';
        foreach ($data as $key => $value) {
            $str .= $key . '=' . urlencode($value) . '&';
        }
        return rtrim($str, '&');
    }
}