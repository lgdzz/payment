<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz\driver\wechat;

use lgdz\Charge;

class Notify extends Config
{

    /**
     * appid 公众账号ID
     * bank_type 付款银行
     * cash_fee 现金支付金额
     * device_info 设备号
     * fee_type 货币种类
     * is_subscribe 是否关注公众账号
     * mch_id 商户号
     * nonce_str 随机字符串
     * openid 用户标识
     * out_trade_no 商户订单号
     * result_code 业务结果
     * return_code 返回状态码
     * sign 签名
     * time_end 支付完成时间
     * total_fee 订单金额
     * trade_type 交易类型
     * transaction_id 微信支付订单号
     * attach 商家数据包 String(128)
     */
    public function run(Charge $charge, $callback)
    {
        $result = $this
          ->config($charge->config())
          ->getMsgFromWechat();

        if ($this->succSend($result) && $this->succVerifySign($result) && $this->succBusiness($result)) {
            if (true === $callback($result)) {
                //商家业务处理完成返回
                return $this->sendMsgToWechat('SUCCESS', 'OK');
            } else {
                return $this->sendMsgToWechat('FAIL', '商家业务处理失败');
            }
        } else {
            return $this->sendMsgToWechat('FAIL', '数据验证失败');
        }

        if ($this->verifySignSucc($data) && $this->requestSucc($data) && $this->businessSucc($data)) {
            return $this->_merchantBusiness($callback, $data);
        } else {
            return $this->returnResult('FAIL', '数据验证失败');
        }
    }
}