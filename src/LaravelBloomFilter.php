<?php

namespace Intimation\LaravelBloomFilter;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Store;
use Exception;
use Intimation\LaravelBloomFilter\Exceptions\RedisCompatibilityException;

abstract class BloomFilter
{
    protected string $key;
    protected int $hashCount;
    protected int $size;
    protected string $hashAlgorithm;
    protected static array $allowedAlgorithms = ['crc32', 'md5', 'sha1'];

    /**
     * @throws RedisCompatibilityException
     */
    public function __construct()
    {
        $this->ensureRedisCompatibility();
        $this->key = $this->getKey();
        $this->size = $this->getSize();
        $this->hashCount = $this->getHashCount();
        $this->hashAlgorithm = $this->getHashAlgorithm();

        if (!in_array($this->hashAlgorithm, static::$allowedAlgorithms)) {
            throw new Exception("Unsupported hash algorithm: {$this->hashAlgorithm}");
        }
    }

    /**
     * Get the Redis key for this Bloom filter
     */
    protected function getKey(): string
    {
        return 'default_key';
    }

    /**
     * Get the size of the Bloom filter (number of bits)
     */
    protected function getSize(): int
    {
        return 1000;
    }

    /**
     * Get the number of hash functions to use
     */
    protected function getHashCount(): int
    {
        return 3;
    }

    /**
     * Get the hash algorithm to use
     */
    protected function getHashAlgorithm(): string
    {
        return 'crc32';
    }

    protected function ensureRedisCompatibility(): void
    {
        $store = Cache::getStore();
        if (!($store instanceof \Illuminate\Cache\RedisStore)) {
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
            if (!Cache::getBit($this->key, $hash)) {
                return false;
            }
        }
        return true;
    }

    protected function getHashes(string $value): array
    {
        $hashes = [];
        for ($i = 0; $i < $this->hashCount; $i++) {
            $data = $value . $i;
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