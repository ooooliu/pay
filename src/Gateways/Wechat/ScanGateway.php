<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class ScanGateway extends Wechat
{
    /**
     * get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'NATIVE';
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function pay(array $config_biz = [])
    {
        if (is_null($this->pay_config['appid'])) {
            throw new InvalidArgumentException('Missing Config -- [appid]');
        }

        return $this->preOrder($config_biz)['code_url'];
    }
}
