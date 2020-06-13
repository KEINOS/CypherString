<?php

/**
 * Sample script to use.
 */

declare(strict_types=1);

namespace KEINOS\SampleApp;

require_once __DIR__ . '/../bin/CypherString.phar';

try {
    // Path of the conf/key pair file
    $path_file_conf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sample_key_pair.json';

    // User data
    $data_raw   = 'Sample data';
    $passphrase = 'this is a pass phrase';

    // Creates or loads the key pair info file and instantiates the class object
    $cypher = new \KEINOS\lib\CypherString($path_file_conf, $passphrase);

    // Encrypt data
    $data_enc = $cypher->encrypt($data_raw);
    $data_dec = $cypher->decrypt($data_enc);

    // Show results
    echo 'Result enc/dec : ', ($data_raw === $data_dec) ? 'SUCCESS' : 'FAIL', PHP_EOL;
    echo 'Public Key     : ', $cypher->getKeyPublic(), PHP_EOL;
    echo 'Private Key    : ', $cypher->getKeyPrivate(), PHP_EOL;
    echo 'Encoded Data   : ', PHP_EOL, $data_enc, PHP_EOL;
} catch (\Exception $e) {
    echo 'Failed to encrypt/decrypt data.', PHP_EOL, $e->getMessage(), PHP_EOL;
}
