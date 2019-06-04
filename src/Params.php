<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz;

trait Params
{
    private $config = [];
    private $pay_method = '';
    private $pay_channel = '';
    private $pay_data = [];

    public function setPayMethod($value)
    {
        $this->pay_method = $value;
        return $this;
    }

    public function setPayChannel($value)
    {
        $this->pay_channel = $value;
        return $this;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    public function setPayData(array $data)
    {
        $this->pay_data = $data;
        return $this;
    }

    public function getPayMethod()
    {
        return $this->pay_method;
    }

    public function getPayChannel()
    {
        return $this->pay_channel;
    }

    public function config()
    {
        return $this->config;
    }

    public function getPayData()
    {
        return $this->pay_data;
    }

    public function getPayDataByName($name, $default = null)
    {
        return isset($this->pay_data[$name]) ? $this->pay_data[$name] : $default;
    }

    public function getNonceStr($length = 32)
    {
        $array = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        shuffle($array);
        $value = '';
        for ($i = 0; $i < $length; $i++) {
            $value .= $array[array_rand($array, 1)];
        }
        return $value;
    }

    public function getClientIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function failed($msg, array $data = [])
    {
        return ['error' => 1, 'msg' => $msg, 'data' => $data];
    }

    public function success(array $data = [])
    {
        return ['error' => 0, 'msg' => 'success', 'data' => $data];
    }
}