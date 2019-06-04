<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\alipay;

use lgdz\Charge;

class Notify extends Config
{
    public function run(Charge $charge, $callback)
    {
        $result = $this
          ->config($charge->config())
          ->getMsgFromAli();

        if ($this->succVerifySign($result) && $this->succTrade($result)) {
            if (true === $callback($result)) {
                //商家业务处理完成返回
                return 'success';
            } else {
                return '商家业务处理失败';
            }
        } else {
            return '数据验证失败';
        }
    }
}