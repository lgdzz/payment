<?php
/**
 * Created by payment.
 * User: lgdz
 * Date: 2018/12/22
 */

namespace lgdz;

class Charge extends Channel
{
    use Params;

    protected static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function pay()
    {
        $class = '\lgdz\driver\\' . $this->getPayChannel() . '\Pay';
        return call_user_func([new $class, 'run'], self::instance());
    }

    public function notify($callback)
    {
        $class = '\lgdz\driver\\' . $this->getPayChannel() . '\Notify';
        return call_user_func([new $class, 'run'], self::instance(), $callback);
    }

    public function query()
    {
        $class = '\lgdz\driver\\' . $this->getPayChannel() . '\Query';
        return call_user_func([new $class, 'run'], self::instance());
    }

    public function refund()
    {
        $class = '\lgdz\driver\\' . $this->getPayChannel() . '\Refund';
        return call_user_func([new $class, 'run'], self::instance());
    }

    public function refundNotify($callback)
    {
        $class = '\lgdz\driver\\' . $this->getPayChannel() . '\RefundNotify';
        return call_user_func([new $class, 'run'], self::instance(), $callback);
    }

}