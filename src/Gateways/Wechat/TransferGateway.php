<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class TransferGateway extends Wechat
{
    /**
     * @var string
     */
    protected $gateway_transfer = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    /**
     * get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return '';
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
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $config_biz['mch_appid'] = $this->config['appid'];
        $config_biz['mchid'] = $this->config['mch_id'];

        unset($this->config['appid']);
        unset($this->config['mch_id']);
        unset($this->config['sign_type']);
        unset($this->config['trade_type']);
        unset($this->config['notify_url']);

        $this->config = array_merge($this->config, $config_biz);

        $this->config['sign'] = $this->getSign($this->config);

        $data = $this->fromXml($this->post(
            $this->gateway_transfer,
            $this->toXml($this->config),
            [
                'cert'    => $this->pay_config['cert'],
                'ssl_key' => $this->pay_config['ssl_key'],
            ]
        ));

        if (!isset($data['return_code']) || $data['return_code'] !== 'SUCCESS' || $data['result_code'] !== 'SUCCESS') {
            $error = 'getResult error:'.$data['return_msg'];
            $error .= isset($data['err_code_des']) ? ' - '.$data['err_code_des'] : '';
        }

        if (isset($error)) {
            throw new InvalidArgumentException($error);
        }

        return $data;
    }
}
