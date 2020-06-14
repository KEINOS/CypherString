<?php

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class PublicMethodsTest extends TestCase
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

    public function testGetConfigSsl(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect = [
            "digest_alg"       => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $actual = $sample->getConfigSSL();

        $this->assertSame($expect, $actual);
    }

    public function testGetKeyPrivate(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $actual = $sample->getKeyPrivate();

        $this->assertSame(is_string($actual), ! empty($actual));
    }

    public function testGetKeyPublic(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $actual = $sample->getKeyPublic();

        $this->assertSame(is_string($actual), ! empty($actual));
    }

    public function testGetPassphrase(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $actual = $sample->getPassphrase();

        $this->assertSame(is_string($actual), ! empty($actual));
    }

    public function testGetPathFileConfig(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $actual = $sample->getPathFileConfig();

        $this->assertSame(is_string($actual), ! empty($actual));
    }

    public function testLoad(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect1   = 'Hello, World!' . hash('md5', strval(microtime()));
        $data_enc1 = $sample->encrypt($expect1);
        $actual1   = $sample->decrypt($data_enc1);
        $this->assertSame($expect1, $actual1);

        $sample->load($path_file_conf);
        $expect2   = 'Hello, World!' . hash('md5', strval(microtime()));
        $data_enc2 = $sample->encrypt($expect2);
        $actual2   = $sample->decrypt($data_enc2);
        $this->assertSame($expect2, $actual2);

        $this->assertNotSame($actual1, $actual2);
    }

    public function testSave(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);
        $expect   = 'Hello, World!' . hash('md5', strval(microtime()));
        $data_enc = $sample->encrypt($expect);
        $actual   = $sample->decrypt($data_enc);
        $this->assertSame($expect, $actual);

        // Re-create conf file
        if (unlink($path_file_conf) === false) {
            $this->assertFalse(true, 'Failed to unlink conf file at: ' . $path_file_conf);
        }
        $sample->save($path_file_conf);
        unset($sample);

        $sample = new \KEINOS\lib\CypherString($path_file_conf);
        $expect = 'Hello, World!' . hash('md5', strval(microtime()));
        $data_enc = $sample->encrypt($expect);
        $actual   = $sample->decrypt($data_enc);
        $this->assertSame($expect, $actual);
    }
}
