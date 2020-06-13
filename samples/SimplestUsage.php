<?php

/**
 * Most sample script to use.
 *
 * This is the simplest script, though it is recommended to declare "strict_types" as
 * true and catch the exeptions.
 * See other samples.
 */

require_once __DIR__ . '/../src/CypherString.php';

$path_file_conf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sample_key_pair.json';
$cypher = new \KEINOS\lib\CypherString($path_file_conf);

$data_raw = 'Sample data';
$data_enc = $cypher->encrypt($data_raw);
$data_dec = $cypher->decrypt($data_enc);

echo 'Result enc/dec : ', ($data_raw === $data_dec) ? 'SUCCESS' : 'FAIL', PHP_EOL;
