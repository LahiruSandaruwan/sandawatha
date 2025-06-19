<?php

namespace App\helpers;

class RateLimiter {
    private $storage;
    private $maxAttempts;
    private $decayMinutes;
    private $storageType;

    public function __construct($maxAttempts = 5, $decayMinutes = 1) {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        
        // Try Redis first, fall back to file-based storage
        if (extension_loaded('redis')) {
            try {
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                $this->storage = $redis;
                $this->storageType = 'redis';
            } catch (\Exception $e) {
                $this->initializeFileStorage();
            }
        } else {
            $this->initializeFileStorage();
        }
    }

    private function initializeFileStorage() {
        $this->storageType = 'file';
        $storagePath = sys_get_temp_dir() . '/rate_limits';
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }
        $this->storage = $storagePath;
    }

    private function getFilePath($key) {
        return $this->storage . '/' . md5($key) . '.txt';
    }

    public function tooManyAttempts($key) {
        return $this->attempts($key) >= $this->maxAttempts;
    }

    public function hit($key) {
        if ($this->storageType === 'redis') {
            $this->storage->incr($key);
            $this->storage->expire($key, $this->decayMinutes * 60);
        } else {
            $filePath = $this->getFilePath($key);
            $attempts = $this->attempts($key);
            $data = [
                'attempts' => $attempts + 1,
                'expires_at' => time() + ($this->decayMinutes * 60)
            ];
            file_put_contents($filePath, json_encode($data));
        }
    }

    public function attempts($key) {
        if ($this->storageType === 'redis') {
            return (int) $this->storage->get($key) ?? 0;
        } else {
            $filePath = $this->getFilePath($key);
            if (!file_exists($filePath)) {
                return 0;
            }
            
            $data = json_decode(file_get_contents($filePath), true);
            if ($data['expires_at'] < time()) {
                unlink($filePath);
                return 0;
            }
            
            return (int) $data['attempts'];
        }
    }

    public function resetAttempts($key) {
        if ($this->storageType === 'redis') {
            $this->storage->del($key);
        } else {
            $filePath = $this->getFilePath($key);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function availableIn($key) {
        if ($this->storageType === 'redis') {
            return $this->storage->ttl($key);
        } else {
            $filePath = $this->getFilePath($key);
            if (!file_exists($filePath)) {
                return 0;
            }
            
            $data = json_decode(file_get_contents($filePath), true);
            return max(0, $data['expires_at'] - time());
        }
    }
} 