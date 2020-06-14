<?php

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class BasicTest extends TestCase
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

    public function testSimpleUsage()
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);
        $actual   = $sample->decrypt($data_enc);

        $this->assertSame($expect, $actual);
    }

    public function testUsePassphrase()
    {
        $path_file_conf = $this->getPathFileConf();
        $passphrase = 'this is my pass phrase to use the key pair';

        $sample = new \KEINOS\lib\CypherString($path_file_conf, $passphrase);

        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);
        $actual   = $sample->decrypt($data_enc);

        $this->assertSame($expect, $actual);
    }
}
