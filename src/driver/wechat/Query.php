<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\wechat;

use lgdz\Charge;

/**
 * @title 查询订单
 * @document https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_2
 */
class Query extends Config
{
    /**
     * @param Charge $charge
     * @return array
     */
    public function run(Charge $charge)
    {
        //接口地址
        $this->api = 'https://api.mch.weixin.qq.com/pay/orderquery';

        $result = $this
          ->config($charge->config())
          ->body([
            'other' => function (&$data) use ($charge) {
                $data['out_trade_no'] = $charge->getPayDataByName('order_no');
            }
          ])
          ->send();

        if ($this->succSend($result)) {
            if ($this->succVerifySign($result)) {
                return $charge->success($result);
            } else {
                return $charge->failed('验签失败');
            }
        } else {
            return $charge->failed('接口请求失败');
        }
    }
}