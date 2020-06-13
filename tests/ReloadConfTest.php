<?php

/**
 * Tests loading an existing conf file.
 */

declare(strict_types=1);

namespace KEINOS\Tests;

use KEINOS\Tests\TestCase;

final class ReloadConfTest extends TestCase
{
    private const NAME_FILE_CONF = 'sample_conf.json';
    private $data_restore = '';

    public static function getPathFileConf()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::NAME_FILE_CONF;
    }

    public function setUp(): void
    {
        // Cache conf data to restore
        $path_file_conf = self::getPathFileConf();
        $this->data_restore = file_get_contents($path_file_conf);
    }

    public function tearDown(): void
    {
        // Restore data
        $path_file_conf = self::getPathFileConf();
        file_put_contents($path_file_conf, $this->data_restore);
    }

    public static function setUpBeforeClass(): void
    {
        // Create conf file
        $path_file_conf = self::getPathFileConf();
        $sample = new \KEINOS\lib\CypherString($path_file_conf);
        unset($sample);
    }

    public static function tearDownAfterClass(): void
    {
        // Delete conf file
        $path_file_conf = self::getPathFileConf();
        if (file_exists($path_file_conf)) {
            if (! unlink($path_file_conf)) {
                throw new \Exception('Failed to ');
            }
        }
    }

    public function testReloadExistingConfigurationFile()
    {
        $path_file_conf = $this->getPathFileConf();

        if (! file_exists($path_file_conf)) {
            $this->assertFalse(true, 'File not found. Conf file does not exist at: ' . $path_file_conf);
        }

        $sample = new \KEINOS\lib\CypherString($path_file_conf);

        $expect   = 'Hello, World!' . hash('md5', strval(time()));
        $data_enc = $sample->encrypt($expect);
        $actual   = $sample->decrypt($data_enc);

        $this->assertSame($expect, $actual);
        $this->assertNotSame($expect, $data_enc);
    }

    public function testReloadExistingMalformedConfigurationFile()
    {
        $path_file_conf = $this->getPathFileConf();

        if (! file_exists($path_file_conf)) {
            $this->assertFalse(true, 'File not found. Conf file does not exist at: ' . $path_file_conf);
        }

        $data = json_encode([
            'hoge' => 'fuga',
            'piyo' => 'mogera',
        ]);

        if (! file_put_contents($path_file_conf, $data)) {
            $this->assertFalse(true, 'Failed to write dummy data into: ' . $path_file_conf);
        }
        $this->expectException(\Exception::class);
        $sample = new \KEINOS\lib\CypherString($path_file_conf);
    }
}
