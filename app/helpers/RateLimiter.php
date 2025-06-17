<?php

namespace App\Helpers;

class RateLimiter {
    private $redis;
    private $maxAttempts;
    private $decayMinutes;

    public function __construct($maxAttempts = 5, $decayMinutes = 1) {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        
        // Initialize Redis connection
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function tooManyAttempts($key) {
        return $this->attempts($key) >= $this->maxAttempts;
    }

    public function hit($key) {
        $this->redis->incr($key);
        $this->redis->expire($key, $this->decayMinutes * 60);
    }

    public function attempts($key) {
        return (int) $this->redis->get($key) ?? 0;
    }

    public function resetAttempts($key) {
        $this->redis->del($key);
    }

    public function availableIn($key) {
        return $this->redis->ttl($key);
    }
} 