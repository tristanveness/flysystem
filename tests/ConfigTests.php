<?php

namespace League\Flysystem;

use PHPUnit\Framework\TestCase;

class ConfigTests extends TestCase
{
    public function test_hash_check()
    {
        $config = new Config();
        $this->assertFalse($config->has('setting'));
        $config->set('setting', 'value');
        $this->assertTrue($config->has('setting'));
    }

    public function test_get()
    {
        $config = new Config();
        $this->assertNull($config->get('setting'));
        $default = 'default';
        $this->assertEquals($default, $config->get('setting', $default));
        $value = 'value';
        $config->set('setting', $value);
        $this->assertEquals($value, $config->get('setting'));
    }

    public function test_setting_defaults()
    {
        $a = new Config(['a' => 1, 'b' => 2]);
        $b = new Config(['b' => 3, 'c' => 4]);
        $a->setDefaults($b);
        $this->assertEquals(1, $a->get('a'));
        $this->assertEquals(2, $a->get('b'));
        $this->assertEquals(4, $a->get('c'));
    }
}
