<?php

namespace Monken\CIBurner\Test;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cache;
use Config\OpenSwoole;
use Monken\CIBurner\BurnerCacheHandler;
use Monken\CIBurner\OpenSwoole\Cache\SwooleTable;
use OpenSwoole\Coroutine as Co;

final class OpenSwooleCacheTest extends CIUnitTestCase
{
    /**
     * @var \Monken\CIBurner\BurnerCacheHandler
     */
    protected $handler;
    protected static $key1  = 'key1';
    protected static $key2  = 'key2';
    protected static $key3  = 'key3';
    protected static $dummy = 'dymmy';

    private Cache $config;

    private static function getKeyArray()
    {
        return [
            self::$key1,
            self::$key2,
            self::$key3,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! (extension_loaded('openswoole') || extension_loaded('swoole')) ) {
            $this->markTestSkipped('openswoole or swoole extension not loaded.');
        }
        defined('BURNER_DRIVER') or define('BURNER_DRIVER', 'OpenSwoole');

        //init table
        $openSwooleConfig = new OpenSwoole();
        $swooleTable = new SwooleTable($openSwooleConfig);

        $this->config = new Cache();
        $this->handler = new BurnerCacheHandler($this->config);

        $this->handler->initialize();
    }

    protected function tearDown(): void
    {
        foreach (self::getKeyArray() as $key) {
            $this->handler->delete($key);
        }
    }

    public function testGetMetaDataMiss()
    {
        $this->assertNull($this->handler->getMetaData(self::$dummy));
    }

    public function testGetMetaData()
    {
        $time = Time::now()->getTimestamp();
        $this->handler->save(self::$key1, 'value');
        $actual = $this->handler->getMetaData(self::$key1);

        // This test is time-dependent, and depending on the timing,
        // seconds in `$time` (e.g. 12:00:00.9999) and seconds of
        // `$this->memcachedHandler->save()` (e.g. 12:00:01.0000)
        // may be off by one second. In that case, the following calculation
        // will result in maximum of (60 + 1).
        $this->assertLessThanOrEqual(60 + 1, $actual['expire'] - $time);

        $this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
        $this->assertSame('value', $actual['data']);
    }

    public function testNew()
    {
        $this->assertInstanceOf(BurnerCacheHandler::class, $this->handler);
    }

    public function testDestruct()
    {
        $this->handler = new BurnerCacheHandler($this->config);
        $this->handler->initialize();

        $this->assertInstanceOf(BurnerCacheHandler::class, $this->handler);
    }

    /**
     * This test waits for 3 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 3.5
     */
    public function testGet()
    {
        $this->handler->save(self::$key1, 'value', 2);

        $this->assertSame('value', $this->handler->get(self::$key1));
        $this->assertNull($this->handler->get(self::$dummy));
    
        //sleep and open TTL-Recycler
        co::run(function() {
            SwooleTable::instance()->initTtlRecycler();
            go(function (){
                co::sleep(3);
                SwooleTable::instance()->deleteTtlRecycler();
            });
        });

        $this->assertNull($this->handler->get(self::$key1));
    }

    public function testSave()
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value'));
    }

    public function testSavePermanent()
    {
        $this->assertTrue($this->handler->save(self::$key1, 'value', 0));
        $metaData = $this->handler->getMetaData(self::$key1);

        $this->assertNull($metaData['expire']);
        $this->assertLessThanOrEqual(1, $metaData['mtime'] - Time::now()->getTimestamp());
        $this->assertSame('value', $metaData['data']);

        $this->assertTrue($this->handler->delete(self::$key1));
    }

    public function testDelete()
    {
        $this->handler->save(self::$key1, 'value');

        $this->assertTrue($this->handler->delete(self::$key1));
        $this->assertFalse($this->handler->delete(self::$dummy));
    }

    // public function testDeleteMatchingPrefix()
    // {
    //     // Save 101 items to match on
    //     for ($i = 1; $i <= 101; $i++) {
    //         $this->handler->save('key_' . $i, 'value' . $i);
    //     }

    //     // check that there are 101 items is cache store
    //     $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
    //     $this->assertSame('keys=101', $dbInfo[0]);

    //     // Checking that given the prefix "key_1", deleteMatching deletes 13 keys:
    //     // (key_1, key_10, key_11, key_12, key_13, key_14, key_15, key_16, key_17, key_18, key_19, key_100, key_101)
    //     $this->assertSame(13, $this->handler->deleteMatching('key_1*'));

    //     // check that there remains (101 - 13) = 88 items is cache store
    //     $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
    //     $this->assertSame('keys=88', $dbInfo[0]);
    // }

    // public function testDeleteMatchingSuffix()
    // {
    //     // Save 101 items to match on
    //     for ($i = 1; $i <= 101; $i++) {
    //         $this->handler->save('key_' . $i, 'value' . $i);
    //     }

    //     // check that there are 101 items is cache store
    //     $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
    //     $this->assertSame('keys=101', $dbInfo[0]);

    //     // Checking that given the suffix "1", deleteMatching deletes 11 keys:
    //     // (key_1, key_11, key_21, key_31, key_41, key_51, key_61, key_71, key_81, key_91, key_101)
    //     $this->assertSame(11, $this->handler->deleteMatching('*1'));

    //     // check that there remains (101 - 13) = 88 items is cache store
    //     $dbInfo = explode(',', $this->handler->getCacheInfo()['db0']);
    //     $this->assertSame('keys=90', $dbInfo[0]);
    // }

    // public function testIncrementAndDecrement()
    // {
    //     $this->handler->save('counter', 100);

    //     foreach (range(1, 10) as $step) {
    //         $this->handler->increment('counter', $step);
    //     }

    //     $this->assertSame(155, $this->handler->get('counter'));

    //     $this->handler->decrement('counter', 20);
    //     $this->assertSame(135, $this->handler->get('counter'));

    //     $this->handler->increment('counter', 5);
    //     $this->assertSame(140, $this->handler->get('counter'));
    // }

    // public function testClean()
    // {
    //     $this->handler->save(self::$key1, 1);

    //     $this->assertTrue($this->handler->clean());
    // }

    public function testGetCacheInfo()
    {
        $this->handler->save(self::$key1, 'value');

        $this->assertIsString($this->handler->getCacheInfo());
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->handler->isSupported());
    }

}
