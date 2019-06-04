<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\wechat;

use lgdz\Charge;

class Refund extends Config
{
    public function run(Charge $charge)
    {
        $this->api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

        $result = $this
          ->config($charge->config())
          ->body([
            'other' => function (&$data) use ($charge) {
                $data['out_trade_no']  = $charge->getPayDataByName('order_no');
                $data['out_refund_no'] = $charge->getPayDataByName('refund_no');
                $data['total_fee']     = $charge->getPayDataByName('pay_amount') * 100;
                $data['refund_fee']    = $charge->getPayDataByName('refund_amount') * 100;
                $data['refund_desc']   = $charge->getPayDataByName('refund_desc', '正常退款');
                $data['notify_url']    = $charge->getPayDataByName('notify_url');
            }
          ])
          ->send(true);

        if ($this->succSend($result)) {
            if ($this->succVerifySign($result)) {
                if ($this->succBusiness($result)) {
                    //申请退款成功
                    return $charge->success($result);
                } else {
                    return $charge->failed('申请退款失败', $result);
                }
            } else {
                return $charge->failed('验签失败', $result);
            }
        } else {
            return $charge->failed('接口请求失败', $result);
        }
    }
}