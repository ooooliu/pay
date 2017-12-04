<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class WapGateway extends Wechat
{

    /**
     * get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'JSAPI';
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

        $payRequest = [
            'appId'     => $this->pay_config['appid'],
            'timeStamp' => time(),
            'nonceStr'  => $this->createNonceStr(),
            'package'   => 'prepay_id='.$this->preOrder($config_biz)['prepay_id'],
            'signType'  => 'MD5',
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
