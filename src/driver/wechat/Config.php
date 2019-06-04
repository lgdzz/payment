<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\wechat;

class Config
{
    private $config = [
      'appid'       => '',
      'mch_id'      => '',
      'key'         => '',
      'client_cert' => '',
      'client_key'  => '',
      'rootca'      => '',
      'sign_type'   => 'MD5'
    ];

    protected $api = '';//请求接口地址
    protected $body = '';//请求接口内容

    //============================== 业务流程 ==============================

    //配置开发者信息
    protected function config(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    //设置请求数据
    protected function body(array $callback)
    {
        //请求参数
        $body = [];
        $body['appid'] = $this->_getConfig('appid');
        $body['mch_id'] = $this->_getConfig('mch_id');
        $body['sign_type'] = $this->_getConfig('sign_type');
        $body['nonce_str'] = $this->_getNonceStr();

        //其它请求参数
        if (isset($callback['other'])) {
            $other = [];
            $callback['other']($other);
            $body = array_merge($body, $other);
        }

        //签名
        $body['sign'] = $this->sign($body);
        $this->body = $this->_arrayToXml($body);
        return $this;
    }

    //请求数据签名
    protected function sign(array $data)
    {
        $sign_data = [];
        foreach ($data as $key => $item) {
            if ($item !== '' && $key !== 'sign') {
                $sign_data[$key] = $item;
            }
        }
        ksort($sign_data);
        return strtoupper(md5(urldecode(http_build_query($sign_data)) . '&key=' . $this->_getConfig('key')));
    }

    //发送请求
    protected function send($cert = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (true === $cert) {
            curl_setopt($ch, CURLOPT_SSLCERT, $this->_getConfig('client_cert'));
            curl_setopt($ch, CURLOPT_SSLKEY, $this->_getConfig('client_key'));
            curl_setopt($ch, CURLOPT_CAINFO, $this->_getConfig('rootca'));
        }
        $output = curl_exec($ch);
        if (false === $output) {
            $result = ['curl_error' => curl_error($ch)];
        } else {
            $result = $this->_xmlToArray($output);
        }
        curl_close($ch);
        return $result;
    }

    //返回结果给微信
    protected function sendMsgToWechat($code, $msg)
    {
        $data = [
          'return_code' => $code,
          'return_msg'  => $msg
        ];
        return $this->_arrayToXml($data);
    }

    //获取微信推送信息
    protected function getMsgFromWechat()
    {
        $content = file_get_contents('php://input');
        return $this->_xmlToArray($content);
    }

    //微信业务信息解密
    protected function infoDecode($req_info)
    {
        $content = base64_decode($req_info, true);
        $key = md5($this->_getConfig('key'));
        $xml = openssl_decrypt($content, 'AES-256-ECB', $key, OPENSSL_RAW_DATA, '');
        return $this->_xmlToArray($xml);
    }

    //jsapi数据返回
    protected function jsapiReturn($prepay_id)
    {
        $data = [];
        $data['timeStamp'] = (string)time();
        $data['package'] = 'prepay_id=' . $prepay_id;
        $data['nonceStr'] = $this->_getNonceStr();
        $data['appId'] = $this->_getConfig('appid');
        $data['signType'] = $this->_getConfig('sign_type');
        $data['paySign'] = $this->sign($data);
        return $data;
    }

    //============================== 返回信息验证 ==============================

    //成功发送请求  @return bool
    protected function succSend(array $data)
    {
        return (isset($data['return_code']) && $data['return_code'] === 'SUCCESS') ? true : false;
    }

    //成功处理业务  @return bool
    protected function succBusiness(array $data)
    {
        return (isset($data['result_code']) && $data['result_code'] === 'SUCCESS') ? true : false;
    }

    //成功退款 @return bool
    protected function succRefund(array $data)
    {
        return (isset($data['refund_status']) && $data['refund_status'] === 'SUCCESS') ? true : false;
    }

    //成功验签  @return bool
    protected function succVerifySign(array $data)
    {
        if (isset($data['sign'])) {
            return ($data['sign'] === $this->sign($data)) ? true : false;
        } else {
            return false;
        }
    }

    //============================== 私有方法 ==============================

    private function _arrayToXml(array $array)
    {
        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    private function _xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    private function _getNonceStr($length = 32)
    {
        $array = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        shuffle($array);
        $value = '';
        for ($i = 0; $i < $length; $i++) {
            $value .= $array[array_rand($array, 1)];
        }
        return $value;
    }

    private function _getConfig($name)
    {
        return $this->config[$name];
    }
}