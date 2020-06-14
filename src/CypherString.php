<?php

/**
 * This class creates a RSA key pair, encrypts and decrypts a string.
 *
 * Usage:
 *    $cypher   = new KEINOS\lib\CypherString('/path/to/key/pair.json');
 *    $data_enc = $cypher->encrypt('Sample data');
 *    $data_dec = $cypher->decrypt($data_enc);
 * Note:
 *    Be aware to be compatible with PHP 7.1 or higher.
 */

declare(strict_types=1);

namespace KEINOS\lib;

final class CypherString
{
    private const AS_ASSOCIATIVE_ARRAY = true;

    /** @var array<string,mixed> $config_ssl */
    private $config_ssl;
    /** @var string $key_private */
    private $key_private;
    /** @var string $key_public */
    private $key_public;
    /** @var string $passphrase */
    private $passphrase;
    /** @var string $path_file_config */
    private $path_file_config;
    /** @var bool $flag_keys_available */
    private $flag_keys_available = false;

    /**
     * Instantiate an object from the provided JSON file.
     *
     * @param  string $path_file_config  File path of the key pair and configuration in JSON format.
     *                                   It will generate a new one if the file doesn't exist.
     * @param  string $passphrase        The passphrase to use the key pair. (Optional)
     * @return void
     */
    public function __construct(string $path_file_config, string $passphrase = '')
    {
        $this->path_file_config = $path_file_config;

        // Load existing config file
        if (file_exists($path_file_config)) {
            $this->load($path_file_config);
            $this->setFlagAsKeysAvailable(true);
            return;
        }

        // Creates a key pair and saves to the provided file path
        $this->init($passphrase);
    }

    /**
     * Decodes JSON string from the configuration file to an array.
     *
     * @param  string $conf_json    JSON string from conf file.
     * @return array<string,mixed>
     * @throws \Exception           On any error occurred while decoding or missing requirements.
     */
    private function decodeJsonConf(string $conf_json): array
    {
        $data = json_decode($conf_json, self::AS_ASSOCIATIVE_ARRAY);

        if (is_null($data)) {
            throw new \Exception('Failed to decode JSON.' . PHP_EOL . 'JSON: ' . $conf_json);
        }

        // Check if $data contains must keys required
        $keys_required = [
            'key_private',
            'key_public',
            'passphrase',
            'config_ssl',
        ];
        if (array_diff_key(array_flip($keys_required), $data)) {
            throw new \Exception('Missing information in JSON config file.');
        }

        return $data;
    }

    /**
     * Decodes JSON string from the "encrypt()" method to an array.
     *
     * @param  string $data_json
     * @return array<string,mixed>
     * @throws \Exception
     *     On any error occurred while decoding or missing requirements.
     */
    private function decodeJsonData(string $data_json): array
    {
        $data = json_decode($data_json, self::AS_ASSOCIATIVE_ARRAY);

        if (is_null($data)) {
            throw new \Exception('Malformed JSON string given. Failed to decode JSON string to array.');
        }

        // Verify basic requirements
        if (! isset($data['data_encrypted'])) {
            throw new \Exception('"data_encrypted" key missing. The JSON string does not contain the encoded data.');
        }
        if (! isset($data['data_sealed'])) {
            throw new \Exception('"data_sealed" key missing. The JSON string does not contain the sealed data.');
        }

        // Decode base64 encoded basic requirements
        $data['data_encrypted'] = base64_decode($data['data_encrypted']);
        $data['data_sealed']    = base64_decode($data['data_sealed']);

        // Sets optional requirements
        $data['key_private_pem'] = isset($data['key_private_pem']) ? $data['key_private_pem'] : $this->getKeyPrivate();
        $data['passphrase']      = isset($data['passphrase'])      ? $data['passphrase']      : $this->getPassphrase();

        return $data;
    }

    /**
     * @param  string $data_json  JSON data from encrypt() method with additional data.
     * @return string
     * @throws \Exception         On any error occurred while decryption.
     */
    public function decrypt(string $data_json): string
    {
        if ($this->isKeysAvailable() === false) {
            throw new \Exception('No SSL configuration is loaded. Use init() method to initiate configuration.');
        }

        $data = $this->decodeJsonData($data_json);

        // Get resource id of the key
        $key_private = $data['key_private_pem'];
        $passphrase  = $data['passphrase'];
        $id_resource = openssl_pkey_get_private($key_private, $passphrase);
        if ($id_resource === false) {
            $msg  = PHP_EOL . 'Data:' . PHP_EOL . print_r($data, true) . PHP_EOL;
            throw new \Exception('Failed to decrypt data. Could NOT get resource ID of private key.' . $msg);
        }

        // Requirements to decrypt
        $data_sealed    = $data['data_sealed'];
        $data_decrypted = '';
        $data_encrypted = $data['data_encrypted'];

        // Decrypt data
        $result = openssl_open($data_sealed, $data_decrypted, $data_encrypted, $id_resource);
        if ($result === false) {
            $msg = 'Failed to decrypt data. Could NOT open the data with keys provided.' . PHP_EOL
                 . 'Keys provided:' . PHP_EOL . print_r($data, true);
            throw new \Exception($msg);
        }

        return $data_decrypted;
    }

    /**
     * @param  string $string      Data should be Base64 encoded.
     * @param  string $key_public  Public key in PEM to encrypt. (Optional)
     * @return string              JSON object string of the encrypted data and it's envelope key.
     * @throws \Exception          On any error occurred while encryption.
     */
    public function encrypt(string $string, string $key_public = ''): string
    {
        $key_public = empty($key_public) ? $this->getKeyPublic() : $key_public;
        $list_key_envelope = [];
        $data_sealed = '';

        // Encrypt/seal data
        $result = openssl_seal($string, $data_sealed, $list_key_envelope, [$key_public]);
        if ($result === false) {
            throw new \Exception('Failed to encrypt data.');
        }

        // Get envelope key
        if (! isset($list_key_envelope[0])) {
            throw new \Exception('Bad envelope keys returned.');
        }

        // Create a data pair of sealed and encrypted data to return
        $data = [
            'data_encrypted' => base64_encode($list_key_envelope[0]),
            'data_sealed'    => base64_encode($data_sealed),
        ];

        // Encode data to return as JSON string
        $result = json_encode($data, JSON_PRETTY_PRINT);

        if ($result === false) {
            $msg = print_r($data, true) . PHP_EOL . PHP_EOL;
            throw new \Exception('Failed to encode as JSON. Data to encode: ' . $msg);
        }

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public function getConfigSSL(): array
    {
        return $this->config_ssl;
    }

    /**
     * Gets PEM format private key.
     *
     * @return string
     */
    public function getKeyPrivate(): string
    {
        return $this->key_private;
    }

    /**
     * Gets PEM format public key.
     *
     * @return string
     */
    public function getKeyPublic(): string
    {
        return $this->key_public;
    }

    /**
     * @return string
     */
    public function getPassphrase(): string
    {
        return $this->passphrase;
    }

    /**
     * @return string
     */
    public function getPathFileConfig(): string
    {
        return $this->path_file_config;
    }

    /**
     * Creates a brand-new SSL (SHA-512, 4096 bit RSA) key pair.
     *
     * @param  string $passphrase (Optional)
     * @return void
     * @throws \Exception         On any error occurred while creating the keys.
     */
    private function init(string $passphrase = ''): void
    {
        // Set/generate passphrase (Fix Bug #73833 on PHP 7.1.23)
        $this->setPassphrase($passphrase);

        // Configuration/settings of the key pair
        $this->config_ssl = [
            "digest_alg"       => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the key pair
        $resource = openssl_pkey_new($this->config_ssl);
        if ($resource === false) {
            throw new \Exception('Failed to create key pair. Probably old OpenSSL module used.');
        }

        // Get and set private key
        $result = openssl_pkey_export($resource, $key_private, $this->getPassphrase());
        if ($result === false) {
            throw new \Exception('Failed to create key pair.');
        }
        if (empty($key_private)) {
            throw new \Exception('Failed to create key pair.');
        }
        $this->key_private = $key_private;

        // Get and set public key
        $array = openssl_pkey_get_details($resource);
        if ($array === false) {
            throw new \Exception('Failed to get public key.');
        }
        if (! isset($array['key'])) {
            throw new \Exception('Failed to get public key.');
        }
        $this->key_public = $array['key'];

        // Save the above data and flag up ready to use
        $this->save();
        $this->setFlagAsKeysAvailable(true);
    }

    /**
     * @return bool
     */
    private function isKeysAvailable(): bool
    {
        return $this->flag_keys_available;
    }

    /**
     * Loads the JSON file of SSL data such as key pair and the passphrase.
     *
     * @param  string $path_file_config
     * @return void
     * @throws \Exception
     *     On any error occurred while creating the keys.
     */
    public function load(string $path_file_config): void
    {
        if (! file_exists($path_file_config)) {
            throw new \Exception('File not found at: ' . $path_file_config);
        }

        $json = file_get_contents($path_file_config);
        if ($json === false) {
            throw new \Exception('Failed to read file from: ' . $path_file_config);
        }
        $data = $this->decodeJsonConf($json);

        // Re-dump properties.
        $this->key_private = $data['key_private'];
        $this->key_public  = $data['key_public'];
        $this->passphrase  = $data['passphrase'];
        $this->config_ssl  = $data['config_ssl'];
    }

    /**
     * Saves/overwrites the SSL data such as key pair and passphrase as JSON file.
     *
     * - NOTE:
     *   BE CAREFUL where to save the data! If the saved file gets public,
     *   then there's no meaning to encrypt the data.
     *
     * @param  string $path_file_config
     * @return bool
     */
    public function save(string $path_file_config = ''): bool
    {
        $path_file_config = (empty($path_file_config)) ? $this->getPathFileConfig() : $path_file_config;

        $data = [
            'key_private' => $this->getKeyPrivate(),
            'key_public'  => $this->getKeyPublic(),
            'passphrase'  => $this->getPassphrase(),
            'config_ssl'  => $this->getConfigSSL(),
        ];

        $data   = json_encode($data, JSON_PRETTY_PRINT);
        $result = file_put_contents($path_file_config, $data, LOCK_EX);

        return ($result !== false);
    }

    /**
     * @param  bool $flag
     * @return void
     */
    private function setFlagAsKeysAvailable(bool $flag): void
    {
        $this->flag_keys_available = $flag;
    }

    /**
     * @param  string $passphrase
     * @return void
     */
    public function setPassphrase(string $passphrase): void
    {
        // In PHP 7.1.23 there's a bug that with an empty pass phrase "openssl_pkey_get_private()"
        // fails to create the resource ID. So set a default pass phrase if empty.
        //   - Ref: Bug #73833 https://bugs.php.net/bug.php?id=73833
        $passphrase = (empty(trim($passphrase))) ? strval(hash_file('md5', __FILE__)) : trim($passphrase);
        $this->passphrase = $passphrase;
    }
}
