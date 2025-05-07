<?php

namespace Intimation\LaravelBloomFilter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LaravelBloomFilterCommand extends Command
{
    protected $signature = 'bloom-filter:make {name : The name of the Bloom filter class}';

    protected $description = 'Create a new Bloom filter class';

    public function handle()
    {
        $name = $this->argument('name');
        $stub = $this->getStub();
        $stub = str_replace('{{name}}', $name, $stub);

        $path = app_path("BloomFilters/{$name}.php");

        if (! File::exists(app_path('BloomFilters'))) {
            File::makeDirectory(app_path('BloomFilters'), 0755, true);
        }

        if (File::exists($path)) {
            $this->error("Bloom filter class {$name} already exists!");

            return;
        }

        File::put($path, $stub);
        $this->info("Bloom filter class {$name} created successfully!");
        $this->info("Path: {$path}");
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace App\BloomFilters;

use Intimation\LaravelBloomFilter\BloomFilter;

class {{name}} extends BloomFilter
{
    /**
     * Get the Redis key for this Bloom filter.
     * This key will be used to store the Bloom filter in Redis.
     * 
     * @return string
     */
    protected function getKey(): string
    {
        return 'your_custom_key'; // Example: 'users_email_filter'
    }

    /**
     * Get the size of the Bloom filter (number of bits).
     * 
     * Formula for optimal size:
     * m = -n * ln(p) / (ln(2)^2)
     * where:
     * - n = expected number of items
     * - p = desired false positive probability
     * 
     * Example: For 1 million items with 0.01 false positive rate:
     * m = -1,000,000 * ln(0.01) / (ln(2)^2) ≈ 9,585,059 bits
     * 
     * @return int
     */
    protected function getSize(): int
    {
        return 1000000; // Adjust based on your needs
    }

    /**
     * Get the number of hash functions to use.
     * 
     * Formula for optimal number of hash functions:
     * k = (m/n) * ln(2)
     * where:
     * - m = size of the filter
     * - n = expected number of items
     * 
     * Example: For 1 million items in a 9.5M bit filter:
     * k = (9,585,059/1,000,000) * ln(2) ≈ 7 hash functions
     * 
     * @return int
     */
    protected function getHashCount(): int
    {
        return 7; // Adjust based on your needs
    }

    /**
     * Get the hash algorithm to use.
     * 
     * Available algorithms:
     * - 'crc32': Fastest but less collision-resistant
     * - 'md5': Good balance of speed and collision resistance
     * - 'sha1': Most collision-resistant but slower
     * 
     * @return string
     */
    protected function getHashAlgorithm(): string
    {
        return 'md5'; // Choose from: 'crc32', 'md5', 'sha1'
    }
}
PHP;
    }
}
