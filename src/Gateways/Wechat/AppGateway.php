<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class AppGateway extends Wechat
{

    /**
     * get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'APP';
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return array
     * @throws InvalidArgumentException
     */
    public function pay(array $config_biz = [])
    {
        if (is_null($this->pay_config['appid'])) {
            throw new InvalidArgumentException('Missing Config -- [appid]');
        }

        $this->config['appid'] = $this->pay_config['appid'];

        $payRequest = [
            'appid'     => $this->pay_config['appid'],
            'partnerid' => $this->pay_config['mch_id'],
            'prepayid'  => $this->preOrder($config_biz)['prepay_id'],
            'timestamp' => time(),
            'noncestr'  => $this->createNonceStr(),
            'package'   => 'Sign=WXPay',
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
