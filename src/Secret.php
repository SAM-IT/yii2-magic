<?php


namespace SamIT\Yii2;

/**
 * Class Secret
 * NOTICE
 * The goal of this class is to primarily prevent accidental leakage via things like `var_dump` and memory dumps of the
 * PHP process. Note that at some point the secret must be unencrypted in memory, all we do is reduce the time it spends
 * there, thereby reducing the chances of secrets occurring in random memory dumps.
 *
 * If an attack has permanent access to memory (ie can read it between instructions) then no data is safe.
 *
 * Some protection is also given for accidentally passing around or exposing the secrets object. This uses PHPs stack
 * frames to authorize decryption requests.
 * From a perspective of protection / prevention this has limited effect, since using reflection one can reset the lock.
 * Another interesting use could be auditing where the stack frames are logged to an append-only file. This means no one
 * will have un-audited access.
 *
 * Howto circumvent the protection / audit or manually decrypt the secret:
 * 1. Obtain the encryption key via reflection.
 * 2. Obtain the resource handle.
 * 3. Rewind the handle
 * 4. Read data from the handle
 * 5. XOR the read data with the key.
 * This can easily be done if you have access to execute any PHP code.
 * However there are things like template engines (twig) that support calling methods on objects which would be properly
 * prevented.
 *
 * At the very least this class will allow you as a developer to track usage of secrets in your code (searching for
 * usages of `string` vs usages of `Secret`.
 *
 * NOTICE
 *
 * @package SamIT\Yii2
 */
class Secret
{
    /**
     * @var resource
     */
    private $secret;

    /**
     * Random key
     * @var int[] Array of bytes
     */
    private $key;

    /**
     * @var int Length of the key and secret
     */
    private $length;

    /**
     * @var int
     */
    private $lock = 0;
    private $hash;

    /**
     * @var bool Whether to return the secret via `__toString()`
     */
    private $allowStringConversion;


    public function __construct($value, $allowStringConversion = false)
    {
        // The secret is stored in the file system and its not kept in memory.
        $this->secret = fopen('php://temp/maxmemory:0', 'w');
        $this->allowStringConversion = $allowStringConversion;
        $this->setValue($value);
    }


    private function setValue(string $value)
    {
        // Key has same length as secret, we use XOR for encryption.
        $value = $this->unpack($value);
        $this->length = count($value);
        $this->key = $this->unpack(random_bytes($this->length));

        // Truncate the file
        ftruncate($this->secret, 0);
        rewind($this->secret);
        fwrite($this->secret, $this->pack($this->apply_xor($this->key, $value)));
    }

    /**
     * Convert a string to a array of bytes (which are of type int)
     * @param string $value
     * @return int[]
     */
    private function unpack(string $value): array
    {
        return unpack('C*', $value);
    }

    /**
     * Convert an array of bytes to a string
     * @param int[] $value
     * @return string
     */
    private function pack(array $value): string
    {
        return pack('C*', ...$value);
    }

    private function authorize()
    {
        if ($this->lock === 0) {
            return;
        }

        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->lock + 1);
        $hash = hash('sha256', json_encode($frames));
        if (!isset($this->hash)) {
            $this->hash = $hash;
        }

        if ($this->hash === $hash) {
            return;
        }
        throw new \Exception("Secret access violation");
    }

    /**
     * @return string The secret
     * @throws \Exception if decryption fails
     */
    public function getValue(): string
    {
        $this->authorize();
        if (!isset($this->secret)) {
            return '';
        }
        rewind($this->secret);
        return $this->pack($this->apply_xor($this->key, $this->unpack(fread($this->secret, $this->length))));
    }

    public function lock($count = 1): bool
    {
        if (!isset($this->hash)) {
            $this->lock = $count;
            return true;
        }
        return false;
    }

    /**
     * Prevents accidental leakage
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }

    /**
     * Prevents accidental leakage
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->allowStringConversion ? $this->getValue() : '<< secret >>';
    }

    /**
     * @param array $key Must be an array of integers.
     * @param array $value Must be an array of integers.
     * @return array
     * @throws \Exception
     */
    private function apply_xor(array $key, array $value)
    {
        if (count($key) != count($value)) {
            throw new \Exception("Lengths not equal: " . count($key) . '-' . count($value));
        }

        $result = [];
        for ($i = 1; $i <= count($value); $i++) {
            $result[] = $key[$i] ^ $value[$i];
        }
        return $result;
    }
}