<?php

namespace Ooooliu\Pay\Gateways\Alipay;

class TransferGateway extends Alipay
{
    /**
     * get method config.
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.fund.trans.toaccount.transfer';
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
     * transfer amount to account.
     *
     * @param array $config_biz
     * @return array|bool
     */
    public function pay(array $config_biz = [])
    {
        return $this->getResult($config_biz, $this->getMethod());
    }
}
