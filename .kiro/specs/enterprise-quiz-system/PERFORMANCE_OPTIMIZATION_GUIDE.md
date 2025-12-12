# Performance Optimization Guide

This document outlines the performance optimizations implemented for the Enterprise Quiz System.

## Database Optimizations

### Indexes Added

The following indexes have been added to improve query performance:

#### Questions Table
- `idx_questions_status_grade` - Composite index for filtering by status and grade
- `idx_questions_subject_difficulty` - Composite index for filtering by subject and difficulty
- `idx_questions_analytics` - Composite index for analytics queries (usage_count, avg_success_rate)
- `idx_questions_bloom_level` - Index for Bloom level filtering
- `idx_questions_difficulty_level` - Index for difficulty level filtering

#### Question Options Table
- `idx_options_question_correct` - Composite index for finding correct options by question
- `idx_options_question_order` - Composite index for ordering options

#### Quiz Attempts Table
- `idx_attempts_user_completed` - Composite index for user's completed attempts
- `idx_attempts_user_started` - Composite index for recent attempts
- `idx_attempts_quiz` - Index for quiz_id lookups

#### Quiz Attempt Answers Table
- `idx_answers_attempt_correct` - Composite index for attempt answers with correctness
- `idx_answers_question_correct` - Composite index for question performance analytics
- `idx_answers_answered_at` - Index for time-based analytics

### Query Optimization Tips

1. **Always use indexed columns in WHERE clauses**
   ```php
   // Good - uses indexed columns
   Question::where('status', 'active')
       ->where('grade_id', $gradeId)
       ->get();
   
   // Bad - full table scan
   Question::where('question_text', 'LIKE', '%keyword%')->get();
   ```

2. **Use eager loading to avoid N+1 queries**
   ```php
   // Good - single query with joins
   Question::with(['questionType', 'options'])->get();
   
   // Bad - N+1 queries
   $questions = Question::all();
   foreach ($questions as $question) {
       $type = $question->questionType; // Additional query per question
   }
   ```

3. **Limit result sets**
   ```php
   // Always use pagination or limits
   Question::where('status', 'active')->paginate(20);
   ```

## Caching Strategy

### Cache Configuration

The quiz system uses Laravel's cache system with the following TTLs:

- **Questions**: 1 hour (3600 seconds)
- **Question Types**: 24 hours (86400 seconds)
- **User Attempts**: 5 minutes (300 seconds)
- **Quiz Results**: 30 minutes (1800 seconds)

### Cache Keys

Cache keys follow a consistent naming pattern:

- `questions:{hash}` - Questions by IDs
- `question_types:all` - All question types
- `user_attempts:{userId}:recent:{limit}` - User's recent attempts
- `quiz_results:{attemptId}` - Quiz results

### Cache Invalidation

Cache is automatically invalidated when:

1. **Question is updated** - Invalidates question cache
2. **Quiz attempt is created** - Invalidates user attempts cache
3. **Quiz attempt is completed** - Invalidates user attempts cache
4. **Question analytics are updated** - Invalidates question cache

### Using the Cache Service

```php
use App\Services\QuizCacheService;

$cacheService = new QuizCacheService();

// Get cached questions
$questions = $cacheService->remember(
    $cacheService->getQuestionsKey($questionIds),
    QuizCacheService::CACHE_TTL_QUESTIONS,
    function () use ($questionIds) {
        return Question::whereIn('id', $questionIds)->get();
    }
);

// Invalidate cache
$cacheService->invalidateQuestionCaches($questionIds);
```

## Frontend Performance Optimizations

### Code Splitting

The quiz system uses Vite's code splitting to reduce initial bundle size:

1. **Vendor chunks** - Separate chunks for Vue, Inertia, and Quasar
2. **Quiz engine chunk** - Lazy loaded when needed
3. **Quiz components chunk** - Lazy loaded with the engine

### Lazy Loading Components

Use dynamic imports for components that aren't immediately needed:

```typescript
// Lazy load quiz components
const QuizEngine = defineAsyncComponent(() => 
  import('./quiz/QuizEngine.vue')
)

const QuestionRenderer = defineAsyncComponent(() => 
  import('./quiz/components/QuestionRenderer.vue')
)
```

### Image Optimization

1. **Use appropriate image formats**
   - WebP for photos (smaller file size)
   - SVG for icons and logos (scalable)
   - PNG for images requiring transparency

2. **Implement lazy loading for images**
   ```html
   <img src="image.jpg" loading="lazy" alt="Description">
   ```

3. **Use responsive images**
   ```html
   <img 
     srcset="image-320w.jpg 320w,
             image-640w.jpg 640w,
             image-1280w.jpg 1280w"
     sizes="(max-width: 640px) 100vw, 640px"
     src="image-640w.jpg"
     alt="Description"
   >
   ```

### Bundle Size Optimization

Current optimizations in `vite.config.js`:

1. **Vendor chunk splitting** - Separates third-party libraries
2. **Manual chunk splitting** - Groups related components
3. **Tree shaking** - Removes unused code
4. **Minification** - Reduces file size
5. **Console removal** - Removes console.log in production

### Performance Monitoring

Monitor these metrics:

1. **Initial Load Time** - Target: < 2 seconds
2. **Time to Interactive** - Target: < 3 seconds
3. **First Contentful Paint** - Target: < 1.5 seconds
4. **Largest Contentful Paint** - Target: < 2.5 seconds

Use browser DevTools Performance tab or Lighthouse for analysis.

## API Performance

### Response Time Targets

- **Quiz fetch**: < 500ms
- **Answer submission**: < 200ms
- **Quiz completion**: < 1 second
- **Question import**: < 5 seconds per 100 questions

### Optimization Techniques

1. **Use database transactions** - Ensures atomicity and improves performance
2. **Batch operations** - Process multiple items in single query
3. **Async processing** - Use queues for heavy operations (analytics, imports)
4. **Response compression** - Enable gzip/brotli compression
5. **API rate limiting** - Prevent abuse and ensure fair usage

### Example: Batch Question Creation

```php
// Good - single transaction
DB::transaction(function () use ($questions) {
    Question::insert($questions);
});

// Bad - multiple transactions
foreach ($questions as $question) {
    Question::create($question); // Separate transaction per question
}
```

## Mobile Performance

### Responsive Design Optimizations

1. **Touch-friendly targets** - Minimum 44x44px for buttons
2. **Reduced animations** - Simpler animations on mobile
3. **Optimized images** - Serve smaller images to mobile devices
4. **Lazy loading** - Load content as user scrolls

### Mobile-Specific Considerations

1. **Network conditions** - Handle slow/intermittent connections
2. **Battery usage** - Minimize background processing
3. **Memory constraints** - Limit cached data on mobile
4. **Touch gestures** - Support swipe navigation

## Production Deployment Checklist

- [ ] Run database migrations with indexes
- [ ] Configure cache driver (Redis recommended)
- [ ] Enable response compression
- [ ] Set up CDN for static assets
- [ ] Configure asset versioning
- [ ] Enable production mode in Laravel
- [ ] Build frontend assets with production flag
- [ ] Set up monitoring and logging
- [ ] Configure rate limiting
- [ ] Test performance with realistic data volumes

## Monitoring and Profiling

### Laravel Telescope

Use Laravel Telescope to monitor:
- Database queries
- Cache hits/misses
- Request duration
- Memory usage

### Browser DevTools

Use Chrome DevTools to analyze:
- Network waterfall
- JavaScript execution time
- Memory leaks
- Rendering performance

### Performance Testing Tools

- **Lighthouse** - Overall performance score
- **WebPageTest** - Detailed performance analysis
- **GTmetrix** - Performance and optimization recommendations
- **Apache JMeter** - Load testing

## Common Performance Issues

### Issue: Slow Quiz Loading

**Symptoms**: Quiz takes > 2 seconds to load

**Solutions**:
1. Check if questions are cached
2. Verify database indexes are in place
3. Use eager loading for relationships
4. Reduce number of questions loaded at once

### Issue: High Memory Usage

**Symptoms**: Browser becomes sluggish during quiz

**Solutions**:
1. Implement virtual scrolling for long option lists
2. Clear unused data from memory
3. Optimize image sizes
4. Use pagination for large question sets

### Issue: Slow Answer Submission

**Symptoms**: Answer submission takes > 500ms

**Solutions**:
1. Check database indexes on quiz_attempt_answers
2. Optimize analytics calculation
3. Use queues for non-critical updates
4. Implement optimistic UI updates

## Future Optimizations

Potential improvements for future releases:

1. **Service Workers** - Offline support and faster loading
2. **HTTP/2 Server Push** - Push critical resources
3. **GraphQL** - Reduce over-fetching of data
4. **Edge Caching** - Cache at CDN edge locations
5. **Database Read Replicas** - Distribute read load
6. **Redis Cluster** - Scale cache horizontally
7. **WebAssembly** - Performance-critical calculations
8. **Progressive Web App** - App-like experience

## Resources

- [Laravel Performance Best Practices](https://laravel.com/docs/performance)
- [Vue.js Performance Guide](https://vuejs.org/guide/best-practices/performance.html)
- [Web.dev Performance](https://web.dev/performance/)
- [Vite Performance](https://vitejs.dev/guide/performance.html)
