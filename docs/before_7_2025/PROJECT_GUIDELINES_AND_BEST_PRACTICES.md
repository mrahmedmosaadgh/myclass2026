# Project Guidelines and Best Practices

## 🎯 **Core Development Principles**

### 1. **User Preferences (CRITICAL)**
- **Vue 3**: Always use Composition API with `<script setup>` syntax
- **Promises**: Use `.then()` style instead of async/await
- **Architecture**: Clean/scalable components with separate form components and reusable composables
- **Layout**: Avoid q-page components (use regular divs/sections instead)
- **Dialogs**: Prefer full-screen dialogs for detailed views
- **Database**: All interactive features MUST persist to database (no mock data)
- **Forms**: Never include manual user ID fields (backend handles authenticated user IDs)

### 2. **Authentication & Security**
- **Session-Based Auth**: Laravel Sanctum with web sessions (NOT token-based)
- **CSRF Protection**: All API calls require CSRF tokens
- **Login Requirement**: Users must be logged in through web interface
- **Permission Checks**: Always verify user permissions before operations

### 3. **Error Prevention Strategies**
- **Field Name Consistency**: Frontend and backend must use identical field names
- **API Response Validation**: Always check response structure matches frontend expectations
- **Import Path Accuracy**: Use correct relative paths (same directory = `./`, parent = `../`)
- **Method Name Matching**: Ensure API method names match between frontend and backend

## 🚨 **Common Error Patterns & Solutions**

### Authentication Errors
```
❌ 419 CSRF Token Mismatch
✅ Solution: Ensure user logged in via web session, not API tokens

❌ 401 Unauthorized
✅ Solution: Check Laravel Sanctum session authentication
```

### Field Name Mismatches
```
❌ Frontend: { comment: "text" } → Backend expects: { text: "text" }
✅ Solution: Match field names exactly in validation rules

❌ Frontend: answer.rating_count → Backend returns: ratings_count
✅ Solution: Use consistent plural/singular naming
```

### Import/Module Errors
```
❌ Failed to fetch dynamically imported module
✅ Solution: Start Vite dev server (npm run dev)

❌ resumeApi.addAnswerComment is not a function
✅ Solution: Check method exists in API module with correct name
```

### Database Persistence Issues
```
❌ Data not saving to database
✅ Solution: Replace TODO/mock code with real API calls

❌ Validation errors on save
✅ Solution: Match frontend data structure with backend validation
```

## 🏗️ **Architecture Standards**

### Component Structure
```
Pages/modules/[feature]/
├── MainComponent.vue          # Primary container
├── DetailDialog.vue          # Full-screen details
├── ItemCard.vue              # Individual item display
├── ItemForm.vue              # Add/edit forms
├── CommentsSection.vue       # Comments functionality
├── VoiceRecorder.vue         # Media components
└── apiModule.js              # Centralized API calls
```

### Vue 3 Component Pattern
```vue
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'

// Props & Emits
const props = defineProps({
  item: Object,
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'refresh'])

// State
const loading = ref(false)
const data = ref([])

// Computed
const hasData = computed(() => data.value.length > 0)

// Methods (use .then() style)
const loadData = () => {
  apiModule.getData()
    .then(response => {
      data.value = response
    })
    .catch(error => {
      console.error('Error:', error)
      $q.notify({
        type: 'negative',
        message: 'Failed to load data'
      })
    })
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>
```

### API Module Pattern
```javascript
// apiModule.js
import axios from 'axios'

const apiModule = {
  // GET operations
  getData(params = {}) {
    return axios.get('/api/endpoint', { params })
      .then(response => response.data)
      .catch(error => {
        console.error('API Error:', error)
        throw error
      })
  },

  // POST operations
  createItem(data) {
    return axios.post('/api/endpoint', data)
      .then(response => response.data)
  },

  // PUT operations
  updateItem(id, data) {
    return axios.put(`/api/endpoint/${id}`, data)
      .then(response => response.data)
  },

  // DELETE operations
  deleteItem(id) {
    return axios.delete(`/api/endpoint/${id}`)
      .then(response => response.data)
  }
}

export default apiModule
```

## 🗄️ **Database Best Practices**

### Migration Standards
```php
// Always use proper foreign key constraints
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('parent_id')->constrained('table_name')->onDelete('cascade');
    $table->string('field_name');
    $table->text('content');
    $table->timestamps();
    
    // Add indexes for performance
    $table->index(['user_id', 'created_at']);
    $table->unique(['user_id', 'parent_id']);
});
```

### Model Relationships
```php
// Always define inverse relationships
class Answer extends Model
{
    // Forward relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Inverse relationship
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    // Helper methods for UI state
    public function getUserRating($userId)
    {
        return $this->ratings()->where('user_id', $userId)->first()?->rating;
    }
    
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
```

## 🔌 **API Development Standards**

### Controller Pattern
```php
class ApiController extends Controller
{
    public function index($parentId)
    {
        $items = Model::with('user')
            ->where('parent_id', $parentId)
            ->orderBy('created_at', 'desc')
            ->get();

        $userId = request()->user()->id ?? null;

        // Transform to include user interaction data
        $transformedItems = $items->map(function ($item) use ($userId) {
            $itemData = $item->toArray();
            $itemData['user_rating'] = $userId ? $item->getUserRating($userId) : null;
            $itemData['user_liked'] = $userId ? $item->isLikedByUser($userId) : false;
            return $itemData;
        });

        return response()->json($transformedItems);
    }

    public function store(Request $request, $parentId)
    {
        $data = $request->validate([
            'text' => 'required|string|max:1000',  // Use 'text' not 'comment'
            'media_type' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;  // Auto-assign user
        $data['parent_id'] = $parentId;

        $item = Model::create($data);
        
        return response()->json($item->load('user'), 201);
    }
}
```

### Route Organization
```php
// Group related routes with middleware
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    // Parent resource routes
    Route::get('/parents/{parent}/children', [Controller::class, 'index']);
    Route::post('/parents/{parent}/children', [Controller::class, 'store']);
    
    // Child resource routes
    Route::get('/children/{child}', [Controller::class, 'show']);
    Route::put('/children/{child}', [Controller::class, 'update']);
    Route::delete('/children/{child}', [Controller::class, 'destroy']);
    
    // Interaction routes
    Route::post('/children/{child}/like', [Controller::class, 'toggleLike']);
    Route::post('/children/{child}/rate', [Controller::class, 'rate']);
});
```

## 🎨 **Frontend Standards**

### Quasar Component Usage
```vue
<template>
  <!-- Use q-card for containers -->
  <q-card class="q-ma-md">
    <q-card-section>
      <div class="text-h6">{{ title }}</div>
    </q-card-section>
    
    <!-- Use q-separator for visual breaks -->
    <q-separator />
    
    <q-card-section>
      <!-- Use q-btn-group for related actions -->
      <q-btn-group flat>
        <q-btn 
          icon="thumb_up" 
          :color="userLiked ? 'positive' : 'grey-7'"
          @click="toggleLike"
        >
          {{ likesCount }}
        </q-btn>
        <q-btn icon="comment" @click="showComments = !showComments">
          {{ commentsCount }}
        </q-btn>
      </q-btn-group>
    </q-card-section>
  </q-card>
</template>
```

### State Management
```javascript
// Use refs for reactive data
const items = ref([])
const loading = ref(false)
const selectedItem = ref(null)

// Use computed for derived state
const hasItems = computed(() => items.value.length > 0)
const filteredItems = computed(() => 
  items.value.filter(item => item.status === 'active')
)

// Use watchers for side effects
watch(() => props.parentId, (newId) => {
  if (newId) {
    loadItems()
  }
}, { immediate: true })
```

## 🧪 **Testing Guidelines**

### Manual Testing Checklist
- [ ] User authentication works (login via web)
- [ ] All CRUD operations persist to database
- [ ] Interactive features (likes, ratings, comments) save correctly
- [ ] Field names match between frontend and backend
- [ ] Error handling displays user-friendly messages
- [ ] Responsive design works on mobile
- [ ] Page refresh preserves user interactions

### API Testing
```bash
# Test authentication
curl -X GET http://localhost:8000/api/endpoint \
  -H "Accept: application/json" \
  -b "laravel_session=your_session_cookie"

# Test data creation
curl -X POST http://localhost:8000/api/endpoint \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -d '{"text": "test content"}'
```

## 🚀 **Development Workflow**

### Before Starting Development
1. **Check Vite Server**: Ensure `npm run dev` is running
2. **Verify Authentication**: Confirm user is logged in via web
3. **Review API Docs**: Check existing endpoints and field names
4. **Plan Component Structure**: Design component hierarchy

### During Development
1. **Start with Backend**: Create migrations, models, controllers
2. **Define API Endpoints**: Test with Postman/curl
3. **Build Frontend Components**: Start with basic structure
4. **Integrate API Calls**: Use centralized API module
5. **Test Interactions**: Verify database persistence

### Before Deployment
1. **Run Tests**: Execute manual testing checklist
2. **Check Console**: Ensure no JavaScript errors
3. **Verify Database**: Confirm all data persists correctly
4. **Test Authentication**: Verify session-based auth works
5. **Performance Check**: Ensure reasonable load times

## 📋 **Code Review Checklist**

### Vue Components
- [ ] Uses `<script setup>` syntax
- [ ] Implements proper error handling
- [ ] Uses `.then()` style promises
- [ ] Includes user feedback (notifications)
- [ ] Follows naming conventions

### API Integration
- [ ] Field names match backend validation
- [ ] Includes proper error handling
- [ ] Uses centralized API module
- [ ] Handles loading states
- [ ] Provides user feedback

### Backend Code
- [ ] Includes user interaction data in responses
- [ ] Uses consistent field naming
- [ ] Implements proper validation
- [ ] Includes authentication checks
- [ ] Updates related statistics

## 🔧 **Package Management Standards**

### Always Use Package Managers
```bash
# JavaScript/Node.js
npm install package-name
npm uninstall package-name
yarn add package-name
pnpm add package-name

# PHP/Laravel
composer require vendor/package
composer remove vendor/package

# Never manually edit package.json, composer.json, etc.
```

### Dependency Installation Workflow
1. **Identify Need**: Determine exact package required
2. **Use Package Manager**: Install via appropriate command
3. **Verify Installation**: Check package.json/composer.json updated
4. **Test Integration**: Ensure package works as expected
5. **Commit Changes**: Include lock files in version control

## 🎯 **Performance Optimization**

### Frontend Optimization
```javascript
// Lazy load components
const HeavyComponent = defineAsyncComponent(() =>
  import('./HeavyComponent.vue')
)

// Debounce search inputs
import { debounce } from 'quasar'
const debouncedSearch = debounce((query) => {
  performSearch(query)
}, 300)

// Use computed for expensive operations
const expensiveCalculation = computed(() => {
  return heavyProcessing(data.value)
})
```

### Backend Optimization
```php
// Eager load relationships
$answers = Answer::with(['user', 'comments.user', 'ratings'])
    ->where('question_id', $questionId)
    ->get();

// Use database indexes
Schema::table('answers', function (Blueprint $table) {
    $table->index(['question_id', 'created_at']);
    $table->index(['user_id', 'status']);
});

// Cache expensive queries
$popularAnswers = Cache::remember('popular_answers', 3600, function () {
    return Answer::orderByPopularity()->take(10)->get();
});
```

## 🐛 **Debugging Strategies**

### Frontend Debugging
```javascript
// Console debugging with context
console.group('API Call Debug')
console.log('Request:', requestData)
console.log('Response:', response)
console.log('Error:', error)
console.groupEnd()

// Vue DevTools usage
// Install Vue DevTools browser extension
// Use $refs to inspect component state
// Check reactive data in Components tab
```

### Backend Debugging
```php
// Laravel debugging
Log::info('User action', [
    'user_id' => $user->id,
    'action' => 'create_answer',
    'data' => $request->all()
]);

// Database query debugging
DB::enableQueryLog();
// ... perform operations
dd(DB::getQueryLog());

// Use Laravel Telescope for request monitoring
```

## 📱 **Mobile Responsiveness**

### Quasar Responsive Classes
```vue
<template>
  <!-- Responsive grid -->
  <div class="row q-gutter-md">
    <div class="col-12 col-md-6 col-lg-4">
      <q-card>Content</q-card>
    </div>
  </div>

  <!-- Responsive visibility -->
  <q-btn
    class="gt-sm"
    label="Desktop Only"
  />
  <q-btn
    class="lt-md"
    icon="menu"
    dense
  />
</template>
```

### Mobile-First Design
- Start with mobile layout
- Use touch-friendly button sizes (minimum 44px)
- Implement swipe gestures where appropriate
- Test on actual devices, not just browser dev tools

## 🔐 **Security Best Practices**

### Input Validation
```php
// Always validate and sanitize input
$request->validate([
    'title' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
    'content' => 'required|string|max:5000',
    'email' => 'required|email|max:255',
    'rating' => 'required|integer|min:1|max:5'
]);

// Use prepared statements (Eloquent does this automatically)
// Never concatenate user input into SQL queries
```

### XSS Prevention
```vue
<template>
  <!-- Safe: Vue automatically escapes -->
  <div>{{ userContent }}</div>

  <!-- Dangerous: Only use v-html with trusted content -->
  <div v-html="trustedHtmlContent"></div>

  <!-- Safe: Sanitize before displaying -->
  <div v-html="sanitizeHtml(userHtmlContent)"></div>
</template>
```

### CSRF Protection
```javascript
// Ensure CSRF token in meta tag
<meta name="csrf-token" content="{{ csrf_token() }}">

// Axios automatically includes CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]').getAttribute('content')
```

## 📊 **Monitoring & Analytics**

### Error Tracking
```javascript
// Frontend error handling
window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
  // Send to error tracking service
})

// Vue error handling
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue error:', err, info)
  // Send to error tracking service
}
```

### Performance Monitoring
```php
// Laravel performance monitoring
use Illuminate\Support\Facades\DB;

// Monitor slow queries
DB::listen(function ($query) {
    if ($query->time > 1000) { // Log queries over 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings
        ]);
    }
});
```

## 🚀 **Deployment Checklist**

### Pre-Deployment
- [ ] All tests pass
- [ ] No console errors in browser
- [ ] Database migrations run successfully
- [ ] Environment variables configured
- [ ] Assets compiled for production
- [ ] Cache cleared and optimized

### Production Environment
```bash
# Laravel optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Frontend build
npm run build

# Database
php artisan migrate --force
```

### Post-Deployment
- [ ] Verify all features work in production
- [ ] Check error logs for issues
- [ ] Monitor performance metrics
- [ ] Test user authentication flows
- [ ] Verify database connections

This comprehensive guide ensures consistent, error-free development across the entire project.
