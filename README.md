[![](https://travis-ci.org/KEINOS/CypherString.svg?branch=master)](https://travis-ci.org/KEINOS/CypherString "View build status in Travis CI")
[![](https://img.shields.io/coveralls/github/KEINOS/CypherString)](https://coveralls.io/github/KEINOS/CypherString "Code Coverage in COVERALLS")
[![](https://img.shields.io/scrutinizer/quality/g/KEINOS/CypherString/master)](https://scrutinizer-ci.com/g/KEINOS/CypherString/build-status/master "Code quality at Scrutinizer")
[![](https://img.shields.io/packagist/php-v/keinos/cypher-string)](https://github.com/KEINOS/CypherString/blob/master/.travis.yml "PHP Version Support")

# Cypher String

Simple PHP class to encrypt/decrypt a string with RSA (SHA-512, 4096 bit).

## Install

Copy the [source code](https://github.com/KEINOS/CypherString/blob/master/src/CypherString.php) or use "[Composer](https://getcomposer.org/)" to keep the source up-to-date.

```bash
composer require keinos/cypher-string
```

## Usage

```php
<?php

require_once __DIR__ . '/../src/CypherString.php';

$path_file_conf = '/path/to/my_key_pair.json';

$cypher = new \KEINOS\lib\CypherString($path_file_conf);

$data_raw = 'Sample data';
$data_enc = $cypher->encrypt($data_raw);
$data_dec = $cypher->decrypt($data_enc);

echo 'Result enc/dec : ', ($data_raw === $data_dec) ? 'SUCCESS' : 'FAIL', PHP_EOL;
```

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Use of "try" and "catch" is recommended. Since the error message might contain the
// raw data, the passphrase and/or key pair info when an exception was thrown.
try {
    // File path of the key pair. It will be created if the file doesn't exist.
    $path_file_json = '/path/to/key_pair.json';

    // Creates or loads the key pair info file and instantiates the class object
    $cypher = new KEINOS\lib\CypherString($path_file_json);

    $data_raw = 'Sample data';               // Sample data to encrypt
    $data_enc = $cypher->encrypt($data_raw); // Encrypt data
    $data_dec = $cypher->decrypt($data_enc); // Decrypt data

    // View results
    echo 'Result enc/dec : ', ($data_raw === $data_dec) ? 'SUCCESS' : 'FAIL', PHP_EOL;
    echo 'Public Key     : ', $cypher->getKeyPublic(), PHP_EOL;
    echo 'Private Key    : ', $cypher->getKeyPrivate(), PHP_EOL;
    echo 'Encoded Data   : ', PHP_EOL, $data_enc, PHP_EOL;
} catch (\Exception $e) {
    echo 'Failed to encrypt/decrypt data.', PHP_EOL, $e->getMessage(), PHP_EOL;
}
```

- [See other sample usages](https://github.com/KEINOS/CypherString/tree/master/samples) @ GitHub

### Advanced usage

- Create key pair with a pass-phrase.

  ```php
  $path_file_json = '/path/to/key_pair.json';
  $passphrase = 'my passpharase to use the key pair';

  $cypher = new KEINOS\lib\CypherString($path_file_json, $passphrase);
  ```
  **Important**: Note that if the pass-phrase is not set or providing an empty string, then it will use the default password. Due to the [bug #73833 of PHP](https://bugs.php.net/bug.php?id=73833) that [occur with PHP 7.1.23 test](https://travis-ci.org/github/KEINOS/CypherString/builds/697941898).

## Information

- [Licence/MIT](https://github.com/KEINOS/CypherString/blob/master/LICENSE)
- [Reporitory](https://github.com/KEINOS/CypherString) @ GitHub
- [Package](https://packagist.org/packages/keinos/cypher-string) @ Packagist
- [Issues](https://github.com/KEINOS/CypherString/issues) @ GitHub
