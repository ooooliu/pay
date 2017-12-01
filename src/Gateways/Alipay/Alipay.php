<?php

namespace Ooooliu\Pay\Gateways\Alipay;

use Ooooliu\Pay\Contracts\GatewayInterface;
use Ooooliu\Pay\Exceptions\InvalidArgumentException;
use Ooooliu\Pay\Support\Config;
use Ooooliu\Pay\Traits\HasHttpRequest;

abstract class Alipay implements GatewayInterface
{

    use HasHttpRequest;

    /**
     * alipay url
     *
     * @var $gateway
     */
    protected $gateway = "https://openapi.alipay.com/gateway.do";

    /**
     * alipay config.
     *
     * @var $config
     */
    protected $config = [];

    /**
     * pay config.
     *
     * @var $pay_config
     */
    protected $pay_config = [];

    /**
     * Alipay constructor.
     *
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $config = new Config($config);

        if (is_null($config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $this->config = [
            'app_id'      => $config->get('app_id'),
            'biz_content' => '',
            'charset'     => 'utf-8',
            'format'      => 'JSON',
            'method'      => '',
            'notify_url'  => $config->get('notify_url', ''),
            'sign_type'   => $config->get('sign_type', 'RSA'),
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0'
        ];

        $this->pay_config = [
            'ali_public_key' => $config->get('ali_public_key'),
            'ali_private_key' => $config->get('ali_private_key'),
        ];
    }

    /**
     * pay a order.
     *
     * @param array $config_biz
     * @return mixed
     */
    public function pay(array $config_biz)
    {
        $config_biz['product_code'] = $this->getProductCode();
        $config_biz['timeout_express'] = '30m';

        $this->config['method'] = $this->getMethod();
        $this->config['biz_content'] = json_encode($config_biz);

        $this->config['sign'] = $this->getSign();
    }

    /**
     * refund a order.
     *
     * @param $config_biz
     * @param null $refund_amount
     * @return array|bool
     */
    public function refund($config_biz, $refund_amount = null)
    {
        if (!is_array($config_biz)) {
            $config_biz = [
                'out_trade_no'  => $config_biz,
                'refund_amount' => $refund_amount,
            ];
        }

        return $this->getResult($config_biz, 'alipay.trade.refund');
    }

    /**
     * close a order.
     *
     * @param $config_biz
     * @return array|bool
     */
    public function close($config_biz)
    {
        if (!is_array($config_biz)) {
            $config_biz = [
                'out_trade_no' => $config_biz,
            ];
        }

        return $this->getResult($config_biz, 'alipay.trade.close');
    }

    /**
     * find a order.
     *
     * @param string $out_trade_no
     * @return array|bool
     */
    public function find($out_trade_no = '')
    {
        $config_biz = [
            'out_trade_no' => $out_trade_no,
        ];

        return $this->getResult($config_biz, 'alipay.trade.query');
    }

    /**
     * verify the notify.
     *
     * @param $data
     * @param null $sign
     * @param bool $sync
     * @return bool
     * @throws InvalidArgumentException
     */
    public function verify($data, $sign = null, $sync = false)
    {
        if (is_null($this->pay_config['ali_public_key'])) {
            throw new InvalidArgumentException('Missing Config -- [ali_public_key]');
        }

        $sign = is_null($sign) ? $data['sign'] : $sign;

        $res = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($this->pay_config['ali_public_key'], 64, "\n", true).
                "\n-----END PUBLIC KEY-----";

        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);

        return openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }

    /**
     * get method config.
     *
     * @return mixed
     */
    abstract protected function getMethod();

    /**
     * get productCode config.
     *
     * @return mixed
     */
    abstract protected function getProductCode();

    /**
     * build pay html.
     *
     * @return string
     */
    protected function buildPayHtml()
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->gateway."' method='POST'>";
        while (list($key, $val) = each($this->config)) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * get alipay api result.
     *
     * @param $config_biz
     * @param $method
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function getResult($config_biz, $method)
    {
        $this->config['biz_content'] = json_encode($config_biz);
        $this->config['method'] = $method;
        $this->config['sign'] = $this->getSign();

        $method = str_replace('.', '_', $method).'_response';

        $data = json_decode($this->post($this->gateway, $this->config), true);

        if (!isset($data[$method]['code']) || $data[$method]['code'] !== '10000') {
            throw new InvalidArgumentException('get result error:'.$data[$method]['msg'].' - '.$data[$method]['sub_code']);
        }

        return $this->verify($data[$method], $data['sign'], true);
    }

    /**
     * get sign.
     *
     * @return string
     */
    protected function getSign()
    {
        return $this->sign($this->getSignContent($this->config), $this->config['sign_type']);
    }

    /**
     * get signContent that is to be signed.
     *
     * @param array $toBeSigned
     * @param bool $verify
     * @return bool|string
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);

        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);

        return $stringToBeSigned;
    }

    /**
     * alipay sign
     *
     * @param $data
     * @param string $signType
     * @return string
     * @throws InvalidArgumentException
     */
    protected function sign($data, $signType = "RSA")
    {
        if($this->checkEmpty($this->pay_config['ali_private_key'])){
            throw new InvalidArgumentException('Missing Config -- [ali_private_key]');
        }

        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->pay_config['ali_private_key'], 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        return base64_encode($sign);
    }

    /**
     * check $value is empty
     * @param $value
     * @return bool
     */
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }
}
