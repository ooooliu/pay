<?php

namespace Ooooliu\Pay\Gateways\Alipay;

class WapGateway extends Alipay
{

    /**
     * get method config.
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.wap.pay';
    }

    /**
     * get productCode config.
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'QUICK_WAP_WAY';
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
