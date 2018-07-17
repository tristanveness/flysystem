<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\SafeStorage;
use PHPUnit\Framework\TestCase;

class SafeStorageTests extends TestCase
{
    /**
     * @test
     */
    public function storing_and_retrieving_something_from_a_safe_storage()
    {
        $storage = new SafeStorage();
        $storage->storeSafely($key = 'some.key', $value = 'some value');
        $this->assertEquals($value, $storage->retrieveSafely($key));
    }
    /**
     * @test
     */
    public function retrieving_an_unknown_key()
    {
        $storage = new SafeStorage();
        $this->assertNull($storage->retrieveSafely('unknown.key'));
    }
}