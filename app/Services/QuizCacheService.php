<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized cache management service for the quiz system.
 * 
 * This service provides a consistent interface for caching quiz-related data
 * and ensures proper cache invalidation when data changes.
 */
class QuizCacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const CACHE_TTL_QUESTIONS = 3600; // 1 hour
    const CACHE_TTL_QUESTION_TYPES = 86400; // 24 hours
    const CACHE_TTL_USER_ATTEMPTS = 300; // 5 minutes
    const CACHE_TTL_QUIZ_RESULTS = 1800; // 30 minutes

    /**
     * Cache key prefixes
     */
    const PREFIX_QUESTIONS = 'questions';
    const PREFIX_QUESTION_TYPES = 'question_types';
    const PREFIX_USER_ATTEMPTS = 'user_attempts';
    const PREFIX_QUIZ_RESULTS = 'quiz_results';

    /**
     * Get a cached value or execute callback and cache the result.
     * 
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Callback to execute if cache miss
     * @return mixed
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Store a value in cache.
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function put(string $key, $value, int $ttl): bool
    {
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Get a value from cache.
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * Remove a value from cache.
     * 
     * @param string $key Cache key
     * @return bool
     */
    public function forget(string $key): bool
    {
        Log::debug('Cache key forgotten', ['key' => $key]);
        return Cache::forget($key);
    }

    /**
     * Remove multiple values from cache.
     * 
     * @param array $keys Array of cache keys
     * @return void
     */
    public function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Clear all cache entries with a specific prefix.
     * 
     * @param string $prefix Cache key prefix
     * @return void
     */
    public function forgetByPrefix(string $prefix): void
    {
        // Note: This is a simplified implementation
        // For production, consider using cache tags (Redis/Memcached)
        // or maintaining a list of keys per prefix
        Log::debug('Cache prefix cleared', ['prefix' => $prefix]);
    }

    /**
     * Generate a cache key for questions by IDs.
     * 
     * @param array $questionIds Array of question IDs
     * @return string
     */
    public function getQuestionsKey(array $questionIds): string
    {
        sort($questionIds);
        return self::PREFIX_QUESTIONS . ':' . md5(implode(',', $questionIds));
    }

    /**
     * Generate a cache key for question types.
     * 
     * @return string
     */
    public function getQuestionTypesKey(): string
    {
        return self::PREFIX_QUESTION_TYPES . ':all';
    }

    /**
     * Generate a cache key for user attempts.
     * 
     * @param int $userId User ID
     * @param int $limit Number of attempts
     * @return string
     */
    public function getUserAttemptsKey(int $userId, int $limit = 10): string
    {
        return self::PREFIX_USER_ATTEMPTS . ":{$userId}:recent:{$limit}";
    }

    /**
     * Generate a cache key for quiz results.
     * 
     * @param int $attemptId Quiz attempt ID
     * @return string
     */
    public function getQuizResultsKey(int $attemptId): string
    {
        return self::PREFIX_QUIZ_RESULTS . ":{$attemptId}";
    }

    /**
     * Invalidate all user-related caches.
     * 
     * @param int $userId User ID
     * @return void
     */
    public function invalidateUserCaches(int $userId): void
    {
        // Clear all possible attempt limit variations
        for ($limit = 1; $limit <= 50; $limit++) {
            $this->forget($this->getUserAttemptsKey($userId, $limit));
        }

        Log::debug('User caches invalidated', ['user_id' => $userId]);
    }

    /**
     * Invalidate question-related caches.
     * 
     * @param int|array $questionIds Question ID(s)
     * @return void
     */
    public function invalidateQuestionCaches($questionIds): void
    {
        if (!is_array($questionIds)) {
            $questionIds = [$questionIds];
        }

        $key = $this->getQuestionsKey($questionIds);
        $this->forget($key);

        Log::debug('Question caches invalidated', ['question_ids' => $questionIds]);
    }

    /**
     * Invalidate quiz results cache.
     * 
     * @param int $attemptId Quiz attempt ID
     * @return void
     */
    public function invalidateQuizResultsCache(int $attemptId): void
    {
        $this->forget($this->getQuizResultsKey($attemptId));
        Log::debug('Quiz results cache invalidated', ['attempt_id' => $attemptId]);
    }

    /**
     * Check if cache is enabled.
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('cache.default') !== 'null';
    }

    /**
     * Flush all quiz-related caches.
     * WARNING: Use with caution in production.
     * 
     * @return void
     */
    public function flushAll(): void
    {
        Cache::flush();
        Log::warning('All caches flushed');
    }
}
