<?php

namespace Ooooliu\Pay\Gateways\Alipay;

class AppGateway extends Alipay
{

    /**
     * get method config.
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.app.pay';
    }


    /**
     * get productCode method.
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'QUICK_MSECURITY_PAY';
    }


    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return string
     */
    public function pay(array $config_biz = [])
    {
        parent::pay($config_biz);

        return htmlspecialchars(http_build_query($this->config));
    }
}
