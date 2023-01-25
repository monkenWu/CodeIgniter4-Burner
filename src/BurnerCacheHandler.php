<?php

namespace Monken\CIBurner;

use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\Exceptions\CriticalError;
use Config\Cache;
use Exception;

/**
 * Burner OpenSwoole cache handler
 */
class BurnerCacheHandler extends BaseHandler
{
    /**
     * burner handler
     *
     * @var BaseHandler|null
     */
    protected $burnerDriverHandler;

    /**
     * cache
     *
     * @var \Config\Cache
     */
    protected $cacheConfig;

    public function __construct(Cache $config)
    {
        $this->cacheConfig = $config;
        if (is_cli() && ENVIRONMENT !== 'testing') {
            return;
        }

        $burnerDriver = BURNER_DRIVER ?? null;
        if ($burnerDriver === null) {
            throw new CriticalError('Cache: BurnerException occurred with message (You must be in one of the Server Driver environments provided by Burner to use this cache Handler.).');
        }

        $handlerClassName = sprintf('\Monken\CIBurner\%s\Cache\%sHandler', $burnerDriver, $burnerDriver);
        if (class_exists($handlerClassName) === false) {
            throw new CriticalError(sprintf(
                'The "%s" Driver is not yet installed, you can install it with the following command: composer require monken/codeigniter4-burner-%s',
                $burnerDriver,
                ucwords($burnerDriver)
            ));
        }

        $this->burnerDriverHandler = new $handlerClassName($this->cacheConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        if (is_cli() && ENVIRONMENT !== 'testing') {
            return;
        }

        try {
            $this->burnerDriverHandler->initialize();
        } catch (Exception $e) {
            throw new CriticalError($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $key, $value, int $ttl = 60)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function increment(string $key, int $offset = 1)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->increment($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement(string $key, int $offset = 1)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->decrement($key, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->clean();
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheInfo()
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->getCacheInfo();
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData(string $key)
    {
        if (null === $this->burnerDriverHandler) {
            return null;
        }

        return $this->burnerDriverHandler->getMetaData($key);
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        if (null === $this->burnerDriverHandler) {
            return false;
        }

        return $this->burnerDriverHandler->isSupported();
    }
}
