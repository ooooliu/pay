<?php

namespace Ooooliu\Pay\Gateways\Wechat;

use Ooooliu\Pay\Contracts\GatewayInterface;
use Ooooliu\Pay\Exceptions\InvalidArgumentException;
use Ooooliu\Pay\Support\Config;
use Ooooliu\Pay\Traits\HasHttpRequest;

abstract class Wechat implements GatewayInterface
{

    use HasHttpRequest;

    /**
     * Wechat pay url
     *
     * @var string
     */
    protected $gateway = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /**
     * Wechat find url
     *
     * @var string
     */
    protected $gateway_query = 'https://api.mch.weixin.qq.com/pay/orderquery';

    /**
     * Wechat close url
     *
     * @var string
     */
    protected $gateway_close = 'https://api.mch.weixin.qq.com/pay/closeorder';

    /**
     * Wechat refund url
     *
     * @var string
     */
    protected $gateway_refund = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    /**
     * config
     *
     * @var array
     */
    protected $config;

    /**
     * pay config.
     *
     * @var $pay_config
     */
    protected $pay_config = [];

    /**
     * Wechat constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $config = new Config($config);

        $this->config = [
            'appid'      => $config->get('app_id', ''),
            'mch_id'     => $config->get('mch_id', ''),
            'nonce_str'  => $this->createNonceStr(),
            'sign_type'  => 'MD5',
            'notify_url' => $config->get('notify_url', ''),
            'trade_type' => $this->getTradeType(),
        ];

        $this->pay_config = [
            'appid' => $config->get('app_id'),
            'mch_id' => $config->get('mch_id'),
            'key' => $config->get('key'),
            'cert'    => $config->get('cert_client'),
            'ssl_key' => $config->get('cert_key'),
        ];
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return mixed
     */
    abstract public function pay(array $config_biz = []);

    /**
     * refund.
     *
     * @param array $config_biz
     * @return mixed
     */
    public function refund($config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);
        $this->config['op_user_id'] = isset($this->config['op_user_id']) ?: $this->pay_config['mch_id'];

        $this->unsetTradeTypeAndNotifyUrl();

        return $this->getResult($this->gateway_refund, true);
    }

    /**
     * close a order.
     *
     * @param string $out_trade_no
     * @return mixed
     */
    public function close($out_trade_no = '')
    {
        $this->config['out_trade_no'] = $out_trade_no;

        $this->unsetTradeTypeAndNotifyUrl();

        return $this->getResult($this->gateway_close);
    }

    /**
     * find a order.
     *
     * @param string $out_trade_no
     * @return mixed
     */
    public function find($out_trade_no = '')
    {
        $this->config['out_trade_no'] = $out_trade_no;

        $this->unsetTradeTypeAndNotifyUrl();

        return $this->getResult($this->gateway_query);
    }

    /**
     * verify the notify.
     *
     * @param $data
     * @param null $sign
     * @param bool $sync
     * @return bool|mixed
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $data = $this->fromXml($data);

        $sign = is_null($sign) ? $data['sign'] : $sign;

        return $this->getSign($data) === $sign ? $data : false;
    }

    /**
     * get trade type config.
     *
     * @return mixed
     */
    abstract protected function getTradeType();

    /**
     * pre order.
     *
     * @param array $config_biz
     * @return mixed
     */
    protected function preOrder($config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);
        return $this->getResult($this->gateway);
    }

    /**
     * get api result.
     *
     * @param $end_point
     * @param bool $cert
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getResult($end_point, $cert = false)
    {
        $this->config['sign'] = $this->getSign($this->config);

        if ($cert) {
            $data = $this->fromXml($this->post(
                $end_point,
                $this->toXml($this->config),
                [
                    'cert'    => $this->pay_config['cert'],
                    'ssl_key' => $this->pay_config['ssl_key'],
                ]
            ));
        } else {
            $data = $this->fromXml($this->post($end_point, $this->toXml($this->config)));
        }

        if (!isset($data['return_code']) || $data['return_code'] !== 'SUCCESS' || $data['result_code'] !== 'SUCCESS') {
            $error = 'getResult error:'.$data['return_msg'];
            $error .= isset($data['err_code_des']) ? ' - '.$data['err_code_des'] : '';
        }

        if (!isset($error) && $this->getSign($data) !== $data['sign']) {
            $error = 'getResult error: return data sign error';
        }

        if (isset($error)) {
            throw new InvalidArgumentException($error);
        }

        return $data;
    }

    /**
     * sign.
     *
     * @param $data
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getSign($data)
    {
        if (is_null($this->pay_config['key'])) {
            throw new InvalidArgumentException('Missing Config -- [key]');
        }

        ksort($data);

        $string = md5($this->getSignContent($data).'&key='.$this->pay_config['key']);

        return strtoupper($string);
    }

    /**
     * get sign content.
     *
     * @param $data
     * @return string
     */
    protected function getSignContent($data)
    {
        $buff = '';

        foreach ($data as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k.'='.$v.'&' : '';
        }

        return trim($buff, '&');
    }

    /**
     * create random string.
     *
     * @param int $length
     * @return string
     */
    protected function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     * convert to xml.
     *
     * @param $data
     * @return string
     * @throws InvalidArgumentException
     */
    protected function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('convert to xml error!invalid array!');
        }

        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * convert to array.
     *
     * @param $xml
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function fromXml($xml)
    {
        if (!$xml) {
            throw new InvalidArgumentException('convert to array error !invalid xml');
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * delete trade_type and notify_url.
     *
     * @return bool
     */
    protected function unsetTradeTypeAndNotifyUrl()
    {
        unset($this->config['notify_url']);
        unset($this->config['trade_type']);

        return true;
    }
}
