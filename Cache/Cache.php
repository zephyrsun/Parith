<?php

/**
 * Cache
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2012 Zephyr Sun
 * @license http://www.parith.net/license
 * @version 0.3
 * @link http://www.parith.net/
 */

namespace Parith\Cache;

class Cache
{
    public $configs, $options = array();

    private $_cache = array();

    /**
     * @param $name
     * @param array $options
     * @return Cache
     */
    public function config($name, array $options = array())
    {
        $this->configs = \Parith\App::config($name, $options) + $this->options;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return \Parith\Arr::get($this->_cache, $key, null);
    }

    /**
     * @param $key
     * @param $var
     * @return Cache
     */
    public function set($key, $var)
    {
        $this->_cache[$key] = $var;
        return $this;
    }

    /**
     * @param $key
     * @return Cache
     */
    public function delete($key)
    {
        unset($this->_cache[$key]);
        return $this;
    }

    /**
     * @return Cache
     */
    public function flush()
    {
        $this->_cache = array();
        return $this;
    }
}