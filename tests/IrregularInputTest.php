<?php

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class IrregularInputTest extends TestCase
{
    private const NAME_FILE_CONF = 'sample_conf.json';

    private function getPathFileConf(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::NAME_FILE_CONF;
    }

    public function tearDown(): void
    {
        $path_file_conf = $this->getPathFileConf();

        if (file_exists($path_file_conf)) {
            unlink($path_file_conf);
        }
    }

    public function testDecryptingWithoutInstantiation(): void
    {
        $path_file_conf = $this->getPathFileConf();
        $passphrase = 'this is my pass phrase to use the key pair';

        // Create encrypted data
        $sample   = new \KEINOS\lib\CypherString($path_file_conf, $passphrase);
        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);
        // unset
        unset($sample);

        $this->expectException(\RuntimeException::class);
        $result = \KEINOS\lib\CypherString::decrypt($data_enc);
    }

    public function testDecryptWithTheFlagOfIsKeyAvailableDown(): void
    {
        $path_file_conf = $this->getPathFileConf();
        $passphrase = 'this is my pass phrase to use the key pair';

        // Create encrypted data
        $sample   = new \KEINOS\lib\CypherString($path_file_conf, $passphrase);
        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);

        // Re-write Flag to false
        $reflection = new \ReflectionClass($sample);
        $property = $reflection->getProperty('flag_keys_available');
        $property->setAccessible(true);
        $property->setValue($sample, false);

        $this->expectException(\Exception::class);
        $result = $sample->decrypt($data_enc);
    }

    public function testIntegerToDecrypt(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect = intval(1);
        $this->expectException(\TypeError::class);
        $data_enc = $sample->decrypt($expect);
    }

    public function testIntegerToEncrypt(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect = intval(1);
        $this->expectException(\TypeError::class);
        $data_enc = $sample->encrypt($expect);
    }

    public function testUseMalformedPassphrase()
    {
        $path_file_conf = $this->getPathFileConf();
        $passphrase = 'this is my pass phrase to use the key pair';

        $sample   = new \KEINOS\lib\CypherString($path_file_conf, $passphrase);
        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);

        // Set bad pass phrase
        $sample->setPassphrase('This is a bad pass phrase');

        $this->expectException(\Exception::class);
        $actual = $sample->decrypt($data_enc);
    }
}
