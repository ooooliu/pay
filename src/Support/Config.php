<?php

namespace Ooooliu\Pay\Support;

use Ooooliu\Pay\Exceptions\InvalidArgumentException;

class Config
{
    /**
     * pay config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * get a config.
     *
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key = null, $default = null)
    {
        $config = $this->config;

        if (is_null($key)) {
            return $config;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * set a config.
     *
     * @param $key
     * @param $value
     * @return array
     * @throws InvalidArgumentException
     */
    public function set($key, $value)
    {
        if ($key == '') {
            throw new InvalidArgumentException('Invalid config key.');
        }

        // 只支持三维数组，多余无意义
        $keys = explode('.', $key);
        switch (count($keys)) {
            case '1':
                $this->config[$key] = $value;
                break;
            case '2':
                $this->config[$keys[0]][$keys[1]] = $value;
                break;
            case '3':
                $this->config[$keys[0]][$keys[1]][$keys[2]] = $value;
                break;

            default:
                throw new InvalidArgumentException('Invalid config key.');
        }

        return $this->config;
    }

    /**
     * offsetExists description
     *
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * offsetGet description
     *
     * @param $offset
     * @return array|mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * offsetSet description
     *
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * offsetUnset description
     *
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}
