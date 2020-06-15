<?php

/**
 * Tests loading an existing conf file.
 */

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class decodeJsonDataTest extends TestCase
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
        $this->decodeJsonData = $reflection->getMethod('decodeJsonData');
        $this->decodeJsonData->setAccessible(true);
    }

    public function testEmptyArgument(): void
    {
        $data = '';
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonData->invoke($this->object, $data);
    }

    public function testIntegerArgument(): void
    {
        $data = 1;
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonData->invoke($this->object, intval($data));
    }
    public function testArrayArgument(): void
    {
        $data = ['sample'=>'sample'];
        $this->expectException(\TypeError::class);
        $result = $this->decodeJsonData->invoke($this->object, $data);
    }

    public function testMustKeyDataEncryptedMissingInArgument(): void
    {
        $data = json_encode([
            //'data_encrypted' => 'something',
            'data_sealed' => 'something',
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonData->invoke($this->object, $data);
    }

    public function testMustKeyDataSealedMissingInArgument(): void
    {
        $data = json_encode([
            'data_encrypted' => 'something',
            //'data_sealed' => 'something',
        ]);
        $this->expectException(\Exception::class);
        $result = $this->decodeJsonData->invoke($this->object, $data);
    }

    public function testProvideAllDataInArgument(): void
    {
        $data = json_encode([
            'data_encrypted' => 'something1',
            'data_sealed' => 'something2',
            'key_private_pem' => 'something3',
            'passphrase' => 'something4',
        ]);
        $result = $this->decodeJsonData->invoke($this->object, $data);
        $this->assertTrue(isset($result['data_encrypted']), 'Return data did not include "data_encrypted" key.');
        $this->assertTrue(isset($result['data_sealed']), 'Return data did not include "data_sealed" key.');
        $this->assertTrue(isset($result['key_private_pem']), 'Return data did not include "key_private_pem" key.');
        $this->assertTrue(isset($result['passphrase']), 'Return data did not include "passphrase" key.');
    }

    public function testReturnSameValueInArgument(): void
    {
        $data = [
            'data_encrypted'  => base64_encode('something1'),
            'data_sealed'     => base64_encode('something2'),
            'key_private_pem' => 'something3',
            'passphrase'      => 'something4',
        ];
        $result = $this->decodeJsonData->invoke($this->object, json_encode($data));
        $this->assertSame(base64_decode($data['data_encrypted']), $result['data_encrypted']);
        $this->assertSame(base64_decode($data['data_sealed']), $result['data_sealed']);
        $this->assertSame($data['key_private_pem'], $result['key_private_pem']);
        $this->assertSame($data['passphrase'], $result['passphrase']);
    }
}
