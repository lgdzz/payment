<?php
/**
 * Created by wxapp_service.
 * User: lgdz
 * Date: 2018/12/24
 */

namespace lgdz\driver\wechat;

use lgdz\Charge;

class RefundNotify extends Config
{
    public function run(Charge $charge, $callback)
    {
        $result = $this
          ->config($charge->config())
          ->getMsgFromWechat();

        if ($this->succSend($result)) {

            //消息解密
            $data = $this->infoDecode($result['req_info']);

            if ($this->succRefund($data)) {
                if (true === $callback($data)) {
                    //商家业务处理完成返回
                    return $this->sendMsgToWechat('SUCCESS', 'OK');
                } else {
                    return $this->sendMsgToWechat('FAIL', '商家业务处理失败');
                }
            } else {
                return $this->sendMsgToWechat('FAIL', '退款失败');
            }
        } else {
            return $this->sendMsgToWechat('FAIL', '数据验证失败');
        }
    }
}