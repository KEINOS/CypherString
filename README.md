# Cypher String

Simple PHP class to encrypt/decrypt a string with RSA (SHA-512, 4096 bit).

## Simple Usage

By default it encrypts with "public" key and decrypts with "private" key.

```php
<?php

require_once('/path/to/class/CypherString.php');

try {
    $path_file_json = '/path/to/key_pair.json'; // File path to save/load the key pair

    // Creates or loads the key pair info file and instantiates the class object
    $cypher = new KEINOS\lib\CypherString($path_file_json);

    $data_raw = 'Sample data';               // Sample data to encrypt
    $data_enc = $cypher->encrypt($data_raw); // Encrypt data
    $data_dec = $cypher->decrypt($data_enc); // Decrypt data

    echo 'Result enc/dec : ', ($data_raw === $data_dec) ? 'SUCCESS' : 'FAIL', PHP_EOL;
    echo 'Public Key     : ', $cypher->getKeyPublic(), PHP_EOL;
    echo 'Private Key    : ', $cypher->getKeyPrivate(), PHP_EOL;
    echo 'Encoded Data   : ', PHP_EOL, $data_enc, PHP_EOL;
} catch (\Exception $e) {
    echo 'Failed to encrypt/decrypt data.', PHP_EOL, $e->getMessage(), PHP_EOL;
}
```

## Advanced usage

- Create key pair with a passphrase

  ```php
  $path_file_json = '/path/to/key_pair.json';
  $passphrase = 'my passpharase to use the key pair';

  $cypher = new KEINOS\lib\CypherString($path_file_json, $passphrase);
  ```
