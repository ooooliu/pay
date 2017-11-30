<?php

namespace Ooooliu\Pay;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;
use Ooooliu\Pay\Support\Config;

class Pay
{
    /**
     * pay config
     *
     * @var $config
     */
    private $config;

    /**
     * pay style
     *
     * @var $drivers
     */
    private $drivers;

    /**
     * GatewayInterface
     * @var $gateways
     */
    private $gateways;

    /**
     * Pay constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * set pay's driver.
     *
     * @param $driver
     * @return $this
     * @throws InvalidArgumentException
     */
    public function driver($driver)
    {
        if (is_null($this->config->get($driver))) {
            throw new InvalidArgumentException("Driver [$driver]'s Config is not defined.");
        }

        $this->drivers = $driver;

        return $this;
    }

    /**
     * set pay's gateway.
     *
     * @param string $gateway
     * @return Contracts\GatewayInterface|mixed
     * @throws InvalidArgumentException
     */
    public function gateway($gateway = 'web')
    {
        if (!isset($this->drivers)) {
            throw new InvalidArgumentException('Driver is not defined.');
        }

        $this->gateways = $this->createGateway($gateway);

        return $this->gateways;
    }

    /**
     * create pay's gateway.
     *
     * @param $gateway
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function createGateway($gateway)
    {
        if (!file_exists(__DIR__.'/Gateways/'.ucfirst($this->drivers).'/'.ucfirst($gateway).'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }

        $gateway = __NAMESPACE__.'\\Gateways\\'.ucfirst($this->drivers).'\\'.ucfirst($gateway).'Gateway';

        return $this->build($gateway);
    }

    /**
     * build pay's gateway.
     *
     * @param $gateway
     * @return mixed
     */
    protected function build($gateway)
    {
        return new $gateway($this->config->get($this->drivers));
    }
}