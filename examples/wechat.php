<?php

$config = [
  'mch_id'         => '',
  'appid'          => '',
  'key'            => '',
  'apiclient_cert' => '/apiclient_cert.pem',
  'apiclient_key'  => '/apiclient_key.pem',
  'rootca'         => '/rootca.pem'
];

//统一下单（JSAPI）
function pay()
{
    Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::WECHAT)
      ->setPayMethod(Charge::WX_JSAPI)
      ->setPayData([
        'title'      => '',
        'order_no'   => '',
        'pay_amount' => '',
        'openid'     => '',
        'notify_url' => ''
      ])->pay();
}

//申请退款
function refund()
{
    Charge::instance()
      ->setPayChannel(Charge::WECHAT)
      ->setConfig($config)
      ->setPayData([
        'order_no'      => '',//支付订单号
        'refund_no'     => '',//退款单号
        'pay_amount'    => '',//支付金额
        'refund_amount' => '',//退款金额
        'refund_desc'   => '',//退款说明
        'notify_url'    => ''//退款结果异步通知
      ])->refund();
}

//查询订单
function query()
{
    Charge::instance()
      ->setPayChannel(Charge::WECHAT)
      ->setConfig($config)
      ->setPayData(
        ['order_no' => '']
      )->query();
}

//支付异步通知
function notify()
{
    return Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::WECHAT)
      ->notify(function ($data) {
          //$data['out_trade_no'] 商户订单号
          //$data['total_fee'] 支付金额
          //处理商家自有业务逻辑
          //...
          return true;//处理失败返回false
      });
}

//退款异步通知
function refundNotify()
{
    return Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::WECHAT)
      ->refundNotify(function ($data) {
          //$data['out_trade_no'] 商户支付单号
          //$data['out_refund_no'] 商户退款单号
          //处理商家自有业务逻辑
          //...
          return true;//处理失败返回false
      });
}