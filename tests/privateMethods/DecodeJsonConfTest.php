<?php

/**
 * Tests loading an existing conf file.
 */

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class DecodeJsonConfTest extends TestCase
{
    private const PASSPHRASE = 'this is my pass phrase to use the key pair';
    private const NAME_FILE_CONF = 'sample_conf.json';

    private function getPathFileConf(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::NAME_FILE_CONF;
    }

    public function setUp(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $this->object = new \KEINOS\lib\CypherString($path_file_conf, self::PASSPHRASE);
        $reflection = new \ReflectionClass($this->object);
        $this->decodeJsonConf = $reflection->getMethod('decodeJsonConf');
        $this->decodeJsonConf->setAccessible(true);
    }

    public function testArrayArgument(): void
    {
        $data = ['sample' => 'sample'];
        $this->expectException(\TypeError::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testEmptyArgument(): void
    {
        $data = '';
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testIntegerArgument(): void
    {
        $data = 1;
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, intval($data));
    }

    public function testMustKeyKeyPrivateMissingInArgument(): void
    {
        $data = json_encode([
            //'key_private' => 'something1',
            'key_public'  => 'something2',
            'passphrase'  => 'something3',
            'config_ssl'  => ['something4'],
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testMustKeyKeyPublicMissingInArgument(): void
    {
        $data = json_encode([
            'key_private' => 'something1',
            //'key_public'  => 'something2',
            'passphrase'  => 'something3',
            'config_ssl'  => ['something4'],
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testMustKeyPassphraseMissingInArgument(): void
    {
        $data = json_encode([
            'key_private' => 'something1',
            'key_public'  => 'something2',
            //'passphrase'  => 'something3',
            'config_ssl'  => ['something4'],
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testMustKeyConfigSslMissingInArgument(): void
    {
        $data = json_encode([
            'key_private' => 'something1',
            'key_public'  => 'something2',
            'passphrase'  => 'something3',
            //'config_ssl'  => ['something4'],
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testMustKeyConfigSslIsWrongInArgument(): void
    {
        $data = json_encode([
            'key_private' => 'something1',
            'key_public'  => 'something2',
            'passphrase'  => 'something3',
            'config_ssl'  => 'something4', // config_ssl must be an array
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
    }

    public function testProvideAllDataInArgument(): void
    {
        $data = json_encode([
            'key_private' => 'something1',
            'key_public'  => 'something2',
            'passphrase'  => 'something3',
            'config_ssl'  => ['something4'],
        ]);
        $result = $this->decodeJsonConf->invoke($this->object, $data);
        $this->assertTrue(isset($result['key_private']), 'Return data did not include "key_private" key.');
        $this->assertTrue(isset($result['key_public']), 'Return data did not include "key_public" key.');
        $this->assertTrue(isset($result['passphrase']), 'Return data did not include "passphrase" key.');
        $this->assertTrue(isset($result['config_ssl']), 'Return data did not include "config_ssl" key.');
    }

    public function testReturnSameValueInArgument(): void
    {
        $data = [
            'key_private' => 'something1',
            'key_public'  => 'something2',
            'passphrase'  => 'something3',
            'config_ssl'  => ['something4'],
        ];
        $result = $this->decodeJsonConf->invoke($this->object, json_encode($data));
        $this->assertSame($data['key_private'], $result['key_private']);
        $this->assertSame($data['key_public'], $result['key_public']);
        $this->assertSame($data['passphrase'], $result['passphrase']);
        $this->assertSame($data['config_ssl'], $result['config_ssl']);
    }
}
