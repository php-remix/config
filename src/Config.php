<?php

namespace PhpRemix\Config;

use PhpRemix\Foundation\Application;
use function JmesPath\search;

/**
 * 为避免解析错误，key只能用以下特殊符号
 * - _
 */
class Config
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * 传入app的话，就从app获取config路径；否则从dir获取
     * @param Application|null $app
     * @param string|null $dir
     */
    public function __construct(?Application $app, $dir = null)
    {
        if (!is_null($app)) {
            $dir = $app->getConfigPath();
        }

        foreach (glob($dir . DIRECTORY_SEPARATOR . "*.php") as $file) {
            $base = basename($file, '.php');
            $data = include $file;
            if (!is_array($data)) continue;
            $this->data[$base] = $data;
        }
    }

    /**
     * https://jmespath.org/specification.html#grammar
     * 仅key为string时才能应用default
     *
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (is_array($key)) {
            $data = [];
            foreach ($key as $k) {
                $data[] = $this->get($k);
            }
            return $data;
        } elseif (is_null($key)) {
            return $this->data;
        } else {
            return search($key, $this->data) ?? $default;
        }
    }

    /**
     * 支持用.分隔多层key
     * 同时也支持传入数组，仅在key传入不会覆盖无关设置
     * 需要覆盖掉整个数组，请用key+value的形式
     *
     * @param array|string $key
     * @param mixed|null $value
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $this->setInnerData($this->data, $key);
        } else {
            $parts = explode(".", $key);

            $data = &$this->data;

            foreach ($parts as $part) {
                $data = &$data[$part];
            }

            $data = $value;
        }
    }

    /**
     * 通过递归来设置数组
     *
     * @param array $arr
     * @param array $data
     */
    private function setInnerData(array &$arr, array $data)
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $this->setInnerData($arr[$key], $val);
            } else {
                $arr[$key] = $val;
            }
        }
    }
}