<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class WebGateway extends Wechat
{

    /**
     * get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'MWEB';
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return string
     * @throws InvalidArgumentException
     */
    public function pay(array $config_biz = [])
    {
        if (is_null($this->pay_config['appid'])) {
            throw new InvalidArgumentException('Missing Config -- [appid]');
        }

        $data = $this->preOrder($config_biz);

        return is_null($this->pay_config['return_url']) ? $data['mweb_url'] : $data['mweb_url'].
                        '&redirect_url='.urlencode($this->pay_config['return_url']);
    }
}
