<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\alipay;

use lgdz\Charge;

class Pay extends Config
{
    protected $method;

    public function run(Charge $charge)
    {
        //支付方式
        $pay_method = strtolower($charge->getPayMethod());//wap、APP
        return $this->config($charge->config())->$pay_method($charge);
    }

    /**
     * 手机网页支付
     * @param Charge $charge
     * @return string
     */
    private function wap(Charge $charge)
    {
        $this->method = 'alipay.trade.wap.pay';
        return $this->body([
          'other'   => function (&$data) use ($charge) {
              $data['return_url'] = $charge->getPayDataByName('return_url', '');
              $data['notify_url'] = $charge->getPayDataByName('notify_url', '');
          },
          'content' => function (&$data) use ($charge) {
              $data['subject'] = $charge->getPayDataByName('title');
              $data['body'] = $charge->getPayDataByName('detail', '');
              $data['out_trade_no'] = $charge->getPayDataByName('order_no');
              $data['total_amount'] = $charge->getPayDataByName('pay_amount');
              $data['passback_params'] = urlencode($charge->getPayDataByName('passback_params', ''));
              $data['product_code'] = 'QUICK_WAP_WAY';
          }
        ])->getUrl();
    }

    //pc网站支付
    private function pc(Charge $charge)
    {
        $this->method = 'alipay.trade.page.pay';
        return $this->body([
          'other'   => function (&$data) use ($charge) {
              $data['return_url'] = $charge->getPayDataByName('return_url', '');
              $data['notify_url'] = $charge->getPayDataByName('notify_url', '');
          },
          'content' => function (&$data) use ($charge) {
              $data['subject'] = $charge->getPayDataByName('title');
              $data['body'] = $charge->getPayDataByName('detail', '');
              $data['out_trade_no'] = $charge->getPayDataByName('order_no');
              $data['total_amount'] = $charge->getPayDataByName('pay_amount');
              $data['goods_type'] = $charge->getPayDataByName('goods_type', '1');
              $data['qr_pay_mode'] = $charge->getPayDataByName('qr_pay_mode', '2');
              $data['qrcode_width'] = $charge->getPayDataByName('qrcode_width', '100');
              $data['passback_params'] = urlencode($charge->getPayDataByName('passback_params', ''));
              $data['product_code'] = 'FAST_INSTANT_TRADE_PAY';
          }
        ])->getUrl();
    }

    private function precreate(Charge $charge)
    {
        $this->method = 'alipay.trade.precreate';
        $result = $this->body([
          'other'   => function (&$data) use ($charge) {
              $data['return_url'] = $charge->getPayDataByName('return_url', '');
              $data['notify_url'] = $charge->getPayDataByName('notify_url', '');
          },
          'content' => function (&$data) use ($charge) {
              $data['subject'] = $charge->getPayDataByName('title');
              $data['body'] = $charge->getPayDataByName('detail', '');
              $data['out_trade_no'] = $charge->getPayDataByName('order_no');
              $data['total_amount'] = $charge->getPayDataByName('pay_amount');
              $data['passback_params'] = urlencode($charge->getPayDataByName('passback_params', ''));
              $data['product_code'] = $charge->getPayDataByName('goods_type', 'FACE_TO_FACE_PAYMENT');
          }
        ])->send();

        $data = $result['alipay_trade_precreate_response'] ?? null;
        $sign = $result['sign'] ?? null;

        if ($data && $sign && $this->succSyncVerifySign($data, $sign) && $this->succRequest($data)) {
            return $charge->success(['code_url' => $data['qr_code']]);
        } else {
            return $charge->failed('接口调用失败', $data);
        }
    }

    //APP支付
    private function app(Charge $charge)
    {

    }

}