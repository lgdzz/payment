<?php
/**
 * Created by payment.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz;

class Channel
{
    //支付渠道
    const WECHAT = 'wechat';
    const ALI    = 'alipay';

    //支付方式
    const ALIPAY_WAP       = 'wap';//支付宝手机网站支付
    const ALIPAY_APP       = 'app';//支付宝APP支付
    const ALIPAY_PC        = 'pc';//支付宝电脑网站支付
    const ALIPAY_PRECREATE = 'precreate';//二维码支付

    const WX_JSAPI  = 'JSAPI';//JSAPI支付（或小程序支付）
    const WX_NATIVE = 'NATIVE';//Native支付
    const WX_APP    = 'APP';//app支付
    const WX_MWEB   = 'MWEB';//H5支付
}