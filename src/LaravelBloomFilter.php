<?php

namespace Intimation\LaravelBloomFilter;

use Exception;
use Illuminate\Support\Facades\Cache;
use Intimation\LaravelBloomFilter\Exceptions\RedisCompatibilityException;

class BloomFilter
{
    protected string $key;

    protected int $hashCount;

    protected int $size;

    protected string $hashAlgorithm;

    protected static array $allowedAlgorithms = ['crc32', 'md5', 'sha1'];

    /**
     * @throws RedisCompatibilityException
     */
    public function __construct(
        string $key,
        int $size = 1000,
        int $hashCount = 3,
        string $hashAlgorithm = 'crc32'
    ) {
        $this->ensureRedisCompatibility();
        $this->key = $key;
        $this->size = $size;
        $this->hashCount = $hashCount;
        if (! in_array($hashAlgorithm, static::$allowedAlgorithms)) {
            throw new Exception("Unsupported hash algorithm: $hashAlgorithm");
        }
        $this->hashAlgorithm = $hashAlgorithm;
    }

    protected function ensureRedisCompatibility(): void
    {
        $store = Cache::getStore();
        if (! ($store instanceof \Illuminate\Cache\RedisStore)) {
            throw new RedisCompatibilityException('Cache store is not Redis compatible.');
        }
    }

    public function add(string $value): void
    {
        foreach ($this->getHashes($value) as $hash) {
            Cache::setBit($this->key, $hash, 1);
        }
    }

    public function exists(string $value): bool
    {
        foreach ($this->getHashes($value) as $hash) {
            if (! Cache::getBit($this->key, $hash)) {
                return false;
            }
        }

        return true;
    }

    protected function getHashes(string $value): array
    {
        $hashes = [];
        for ($i = 0; $i < $this->hashCount; $i++) {
            $data = $value.$i;
            switch ($this->hashAlgorithm) {
                case 'crc32':
                    $hash = abs(crc32($data));
                    break;
                case 'md5':
                    $hash = abs(hexdec(substr(md5($data), 0, 8)));
                    break;
                case 'sha1':
                    $hash = abs(hexdec(substr(sha1($data), 0, 8)));
                    break;
                default:
                    throw new Exception("Unsupported hash algorithm: {$this->hashAlgorithm}");
            }
            $hashes[] = $hash % $this->size;
        }

        return $hashes;
    }

    public function clear(): void
    {
        Cache::delete($this->key);
    }
}
