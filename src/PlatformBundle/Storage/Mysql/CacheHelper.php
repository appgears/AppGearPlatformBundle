<?php

namespace AppGear\PlatformBundle\Storage\Mysql;

/**
 * Class CacheHelper contains helpers methods for storage communications with cache
 */
class CacheHelper
{
    /**
     * Use this separator in the cache key
     */
    const ID_SEPARATOR = ':';

    /**
     * Build cache key by passed variables
     *
     * @param mixed ...$vars One or more variables
     *
     * @return string Cache key
     */
    public static function buildCacheKey()
    {
        $args = func_get_args();

        $key = '';

        foreach ($args as $arg) {

            if (is_int($arg)) {
                $key .= self::ID_SEPARATOR . $arg;
            }
            elseif (is_string($arg)) {
                if (ctype_alnum($arg)) {
                    $key .= self::ID_SEPARATOR . $arg;
                }
                else {
                    $key .= self::ID_SEPARATOR . str_replace('=', '', md5($arg));
                }
            }
            elseif (is_array($arg)) {
                asort($arg);
                foreach ($arg as $item=>$value) {
                    $key .= self::buildCacheKey($item, $value);
                }
            }
            elseif (is_bool($arg)) {
                $key .= self::ID_SEPARATOR . (int)$arg;
            }
            elseif ($arg === null) {
                $key .= self::ID_SEPARATOR . '0';
            }
            else {
                throw new \RuntimeException('Can`t create cache id by passed var');
            }
        }

        return ltrim($key, ':');
    }
}