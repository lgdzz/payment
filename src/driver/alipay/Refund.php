<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/25
 */

namespace lgdz\driver\alipay;

use lgdz\Charge;

class Refund extends Config
{
    protected $method = 'alipay.trade.refund';

    public function run(Charge $charge)
    {
        $result = $this
          ->config($charge->config())
          ->body([
            'content' => function (&$data) use ($charge) {
                $data['out_trade_no']   = $charge->getPayDataByName('order_no');
                $data['out_request_no'] = $charge->getPayDataByName('refund_no');
                $data['refund_amount']  = $charge->getPayDataByName('refund_amount');
                $data['refund_reason']  = $charge->getPayDataByName('refund_desc');
            }
          ])
          ->send();

        $data = isset($result['alipay_trade_refund_response']) ? $result['alipay_trade_refund_response'] : null;
        $sign = isset($result['sign']) ? $result['sign'] : null;

        //验证签名
        if ($data && $sign && $this->succSyncVerifySign($data, $sign)) {
            if ($this->succRefund($data)) {
                //退款成功
                return $charge->success($data);
            } else {
                return $charge->failed('退款失败', $data);
            }
        } else {
            return $charge->failed('接口调用失败', $result);
        }
    }
}