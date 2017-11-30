<?php

namespace Ooooliu\Pay\Contracts;

interface GatewayInterface
{

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return mixed
     */
    public function pay(array $config_biz);


    /**
     * refund a order.
     *
     * @param $config_biz
     * @return mixed
     */
    public function refund($config_biz);


    /**
     * close a order.
     *
     * @param $config_biz
     * @return mixed
     */
    public function close($config_biz);


    /**
     * find a order.
     *
     * @param $out_trade_no
     * @return mixed
     */
    public function find($out_trade_no);


    /**
     * verify notify.
     *
     * @param $data
     * @param null $sign
     * @param bool $sync
     * @return mixed
     */
    public function verify($data, $sign = null, $sync = false);
}
