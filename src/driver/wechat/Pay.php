<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\wechat;

use lgdz\Charge;

/**
 * 微信统一下单
 * @title Pay
 */
class Pay extends Config
{

    public function run(Charge $charge)
    {
        $this->api = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        //支付方式
        $pay_method = strtolower($charge->getPayMethod());//jsapi、native、app
        return $this->config($charge->config())->$pay_method($charge);
    }

    private function jsapi(Charge $charge)
    {
        $result = $this->body([
          'other' => function (&$data) use ($charge) {
              $data['device_info'] = $charge->getPayDataByName('device_info', 'WEB');
              $data['body'] = $charge->getPayDataByName('title', '');
              $data['detail'] = $charge->getPayDataByName('detail', '');
              $data['attach'] = $charge->getPayDataByName('attach', '');
              $data['out_trade_no'] = $charge->getPayDataByName('order_no');
              $data['total_fee'] = $charge->getPayDataByName('pay_amount') * 100;
              $data['spbill_create_ip'] = $charge->getPayDataByName('spbill_create_ip', $charge->getClientIp());
              $data['notify_url'] = $charge->getPayDataByName('notify_url');
              $data['trade_type'] = $charge->getPayDataByName('trade_type', 'JSAPI');
              $data['limit_pay'] = $charge->getPayDataByName('limit_pay', '');
              $data['openid'] = $charge->getPayDataByName('openid', '');
          }
        ])->send();

        if ($this->succSend($result) && $this->succVerifySign($result) && $this->succBusiness($result)) {
            return $charge->success($this->jsapiReturn($result['prepay_id']));
        } else {
            return $charge->failed('接口调用失败', $result);
        }
    }

    private function native(Charge $charge)
    {
        $result = $this->body([
          'other' => function (&$data) use ($charge) {
              $data['device_info'] = $charge->getPayDataByName('device_info', 'WEB');
              $data['body'] = $charge->getPayDataByName('title', '');
              $data['detail'] = $charge->getPayDataByName('detail', '');
              $data['attach'] = $charge->getPayDataByName('attach', '');
              $data['out_trade_no'] = $charge->getPayDataByName('order_no');
              $data['total_fee'] = $charge->getPayDataByName('pay_amount') * 100;
              $data['spbill_create_ip'] = $charge->getPayDataByName('spbill_create_ip', $charge->getClientIp());
              $data['notify_url'] = $charge->getPayDataByName('notify_url');
              $data['trade_type'] = $charge->getPayDataByName('trade_type', 'NATIVE');
              $data['limit_pay'] = $charge->getPayDataByName('limit_pay', '');
          }
        ])->send();

        if ($this->succSend($result) && $this->succVerifySign($result) && $this->succBusiness($result)) {
            return $charge->success(['code_url' => $result['code_url']]);
        } else {
            return $charge->failed('接口调用失败', $result);
        }
    }

    private function app(Charge $charge)
    {
        return '未接入';
    }

}