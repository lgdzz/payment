<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/27
 */

namespace lgdz\driver\alipay;

use lgdz\Charge;

class Query extends Config
{
    protected $method = 'alipay.trade.query';

    public function run(Charge $charge)
    {
        $result = $this
          ->config($charge->config())
          ->body([
            'content' => function (&$data) use ($charge) {
                $data['out_trade_no'] = $charge->getPayDataByName('order_no');
            }
          ])
          ->send();

        $data = isset($result['alipay_trade_query_response']) ? $result['alipay_trade_query_response'] : null;
        $sign = isset($result['sign']) ? $result['sign'] : null;

        //验证签名
        if ($data && $sign && $this->succSyncVerifySign($data, $sign)) {
            return $charge->success($data);
        } else {
            return $charge->failed('接口调用失败', $data);
        }
    }
}