<?php

namespace Ooooliu\Pay\Gateways\Alipay;

class WebGateway extends Alipay
{

    /**
     * get method config.
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.page.pay';
    }

    /**
     * get productCode config.
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'FAST_INSTANT_TRADE_PAY';
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

        return $this->buildPayHtml();
    }
}
