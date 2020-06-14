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

    public function testInteger(): void
    {
        $path_file_conf = $this->getPathFileConf();

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect = intval(1);
        $this->expectException(\TypeError::class);
        $data_enc = $sample->encrypt($expect);
    }
}
