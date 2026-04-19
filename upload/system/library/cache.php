<?php
class Cache {
    /**
     * @var int
     */
    private int $expire;

    /**
     * Constructor
     *
     * @param int $expire
     */
    public function __construct(int $expire = 3600) {
        $this->expire = $expire;

        if (!is_dir(DIR_CACHE)) {
            return;
        }

        $iterator = new FilesystemIterator(DIR_CACHE, FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $entry) {
            if (!$entry->isFile()) {
                continue;
            }

            $filename = $entry->getFilename();

            if (!str_starts_with($filename, 'cache.')) {
                continue;
            }

            // Expiry timestamp is the last segment after the final dot
            $time = substr(strrchr($filename, '.'), 1);

            if (is_numeric($time) && (int)$time < time()) {
                unlink($entry->getPathname());
            }
        }
    }

    /**
     * Sanitise a cache key to a safe filename segment
     *
     * @param string $key
     *
     * @return string
     */
    private function sanitiseKey(string $key): string {
        return preg_replace('/[^A-Z0-9\._-]/i', '', $key);
    }

    /**
     * Get
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
        $file = $this->findCacheFile($key);

        if ($file === null) {
            return false;
        }

        $handle = fopen($file, 'r');

        if ($handle === false) {
            return false;
        }

        flock($handle, LOCK_SH);
        $data = fread($handle, filesize($file));
        flock($handle, LOCK_UN);
        fclose($handle);

        return json_decode($data, true);
    }

    /**
     * Set
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void {
        $this->delete($key);

        $file = DIR_CACHE . 'cache.' . $this->sanitiseKey($key) . '.' . (time() + $this->expire);

        $handle = fopen($file, 'w');

        if ($handle === false) {
            return;
        }

        flock($handle, LOCK_EX);
        fwrite($handle, json_encode($value));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Delete
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void {
        $file = $this->findCacheFile($key);

        if ($file !== null) {
            unlink($file);
        }
    }

    /**
     * Find an existing cache file for a given key, regardless of timestamp suffix
     *
     * @param string $key
     *
     * @return string|null Full path, or null if not found
     */
    private function findCacheFile(string $key): ?string {
        if (!is_dir(DIR_CACHE)) {
            return null;
        }

        $prefix = 'cache.' . $this->sanitiseKey($key) . '.';

        $iterator = new FilesystemIterator(DIR_CACHE, FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $entry) {
            if ($entry->isFile() && str_starts_with($entry->getFilename(), $prefix)) {
                return $entry->getPathname();
            }
        }

        return null;
    }
}
