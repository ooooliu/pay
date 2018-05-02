<?php

namespace Ooooliu\Pay\Gateways\Alipay;

class ScanGateway extends Alipay
{
    /**
     * get method config.
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.precreate';
    }

    /**
     * get productCode config.
     *
     * @return string
     */
    protected function getProductCode()
    {
        return '';
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return bool
     */
    public function pay(array $config_biz = [])
    {
        return $this->getResult($config_biz, $this->getMethod());
    }
}
