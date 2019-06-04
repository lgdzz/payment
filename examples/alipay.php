<?php
//配置
$config = [
  'debug'           => true,
  'app_id'          => '',
  'ali_public_key'  => '',
  'rsa_private_key' => ''
];

//手机网页支付
function wapPay()
{
    Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::ALI)
      ->setPayMethod(Charge::ALIPAY_WAP)
      ->setPayData([
        'title'      => '',
        'order_no'   => '',
        'pay_amount' => '',
        'notify_url' => ''
      ])
      ->pay();
}

//申请退款
function refund()
{
    Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::ALI)
      ->setPayData([
        'order_no'      => '',//支付订单号
        'refund_no'     => '',//退款单号
        'refund_amount' => '',//退款金额（元）
        'refund_desc'   => ''//退款说明
      ])
      ->refund();
}

//支付结果异步通知
function notify()
{
    return Charge::instance()
      ->setConfig($config)
      ->setPayChannel(Charge::ALI)
      ->notify(function ($data) {
          //商家业务逻辑
          //...
          return true;
      });
}