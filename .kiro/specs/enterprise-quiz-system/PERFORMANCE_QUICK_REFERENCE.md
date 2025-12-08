# Performance Optimization Quick Reference

Quick reference for using the performance optimizations in the Enterprise Quiz System.

## Database Indexes

### Migration
```bash
php artisan migrate
```

### Verify Indexes
```sql
SHOW INDEX FROM questions;
SHOW INDEX FROM question_options;
SHOW INDEX FROM quiz_attempts;
SHOW INDEX FROM quiz_attempt_answers;
```

## Caching

### Using QuizService Cache Methods

```php
use App\Services\QuizService;

$quizService = new QuizService();

// Get cached question types
$types = $quizService->getQuestionTypes();

// Get cached questions by IDs
$questions = $quizService->getQuestionsByIds([1, 2, 3]);

// Get cached user attempts
$attempts = $quizService->getUserRecentAttempts($user, 10);

// Invalidate caches
$quizService->invalidateQuestionCache([1, 2, 3]);
$quizService->invalidateUserAttemptsCache($userId);
```

### Using QuizCacheService

```php
use App\Services\QuizCacheService;

$cache = new QuizCacheService();

// Cache with TTL
$cache->remember('my-key', 3600, function() {
    return expensiveOperation();
});

// Get cache key
$key = $cache->getQuestionsKey([1, 2, 3]);

// Invalidate
$cache->invalidateQuestionCaches([1, 2, 3]);
$cache->invalidateUserCaches($userId);
```

### Cache Configuration

```env
# .env
CACHE_DRIVER=redis  # Recommended
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Clear Cache
```bash
php artisan cache:clear
```

## Lazy Loading Components

### Import Composable

```typescript
import { useLazyQuizComponents, preloadQuizComponents } from '@/composables/useLazyQuizComponents'
```

### Use Lazy Components

```typescript
// In your component
const { QuizEngine, ProgressIndicator } = useLazyQuizComponents()

// Use in template
<QuizEngine :quiz="questions" :config="config" />
```

### Preload Components

```typescript
import { onMounted } from 'vue'
import { preloadQuizComponents } from '@/composables/useLazyQuizComponents'

onMounted(() => {
  // Preload when user likely to need them
  preloadQuizComponents()
})
```

## Performance Monitoring

### Import Monitor

```typescript
import { performanceMonitor } from '@/utils/performanceMonitor'
```

### Basic Usage

```typescript
// Start/end timer
performanceMonitor.startTimer('operation-name')
// ... do work
performanceMonitor.endTimer('operation-name')

// Measure function
const result = await performanceMonitor.measure('fetch-data', async () => {
  return await fetchData()
})

// Get summary
performanceMonitor.logSummary()
```

### Enable/Disable

```typescript
// Enable in development
performanceMonitor.setEnabled(true)

// Disable in production (default)
performanceMonitor.setEnabled(false)
```

### Browser Console

```javascript
// Access in browser console
performanceMonitor.logSummary()
performanceMonitor.getMetrics()
performanceMonitor.measureWebVitals()
```

## Performance Targets

### Database
- Query time: < 100ms
- Analytics queries: < 500ms

### API
- Quiz fetch: < 500ms
- Answer submission: < 200ms
- Quiz completion: < 1s

### Frontend
- Initial load: < 2s
- Time to interactive: < 3s
- LCP: < 2.5s
- FID: < 100ms
- CLS: < 0.1

## Common Patterns

### Caching Query Results

```php
// Good - uses cache
$questions = Cache::remember('questions:active', 3600, function() {
    return Question::where('status', 'active')->get();
});

// Bad - no cache
$questions = Question::where('status', 'active')->get();
```

### Eager Loading

```php
// Good - single query
$questions = Question::with(['questionType', 'options'])->get();

// Bad - N+1 queries
$questions = Question::all();
foreach ($questions as $question) {
    $type = $question->questionType; // Extra query!
}
```

### Lazy Loading Components

```typescript
// Good - lazy loaded
const QuizEngine = defineAsyncComponent(() => 
  import('./QuizEngine.vue')
)

// Bad - always loaded
import QuizEngine from './QuizEngine.vue'
```

## Troubleshooting

### Slow Queries
1. Check if indexes exist: `SHOW INDEX FROM table_name`
2. Analyze query: `EXPLAIN SELECT ...`
3. Add missing indexes

### Cache Not Working
1. Check cache driver: `php artisan config:cache`
2. Verify Redis connection: `redis-cli ping`
3. Check cache keys: `Cache::get('key')`

### Large Bundle Size
1. Check bundle analysis: `npm run build`
2. Use lazy loading for large components
3. Split vendor chunks

### Memory Issues
1. Clear old metrics: `performanceMonitor.clear()`
2. Limit cached data
3. Use pagination

## Monitoring Commands

```bash
# Laravel
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Redis
redis-cli
> KEYS *
> GET key_name
> FLUSHALL

# NPM
npm run build
npm run build -- --report  # Bundle analysis
```

## Resources

- Full Guide: `PERFORMANCE_OPTIMIZATION_GUIDE.md`
- Implementation: `TASK_18_IMPLEMENTATION_SUMMARY.md`
- Laravel Cache: https://laravel.com/docs/cache
- Vue Performance: https://vuejs.org/guide/best-practices/performance.html
