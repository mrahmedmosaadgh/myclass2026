I'll help you create a comprehensive documentation for your project. First, let's fix the `formFields` definition in your code, then I'll provide the documentation.

````javascript path=resources\js\Pages\GradeSubjects\Index.vue mode=EDIT
const formFields = [
    {
        name: 'grade_id',
        label: 'Grade',
        type: 'select',
        required: true,
        placeholder: 'Select a grade',
        options: computed(() => props.options?.grades?.map(grade => ({
            value: grade.id,
            label: grade.name
        })) || [])
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        placeholder: 'Select a subject',
        options: computed(() => props.options?.subjects?.map(subject => ({
            value: subject.id,
            label: subject.name
        })) || [])
    }
];
````

Now, here's the documentation for your project:

# منصة القدرات الرسمية - Developer Documentation

## 1. Introduction

### Overview
منصة القدرات الرسمية is a modern web application built with Laravel 12 and Vue.js, using Inertia.js for seamless single-page application functionality while maintaining the benefits of server-side rendering.

### Tech Stack
- Backend: Laravel 12
- Frontend: Vue.js 3 with `<script setup>`
- Bridge: Inertia.js
- UI: TailwindCSS
- Authentication: Jetstream
- HTTP Client: Axios
- Package Management: Composer (PHP) & NPM (JavaScript)

## 2. Installation & Setup

### System Requirements
```bash
PHP >= 8.2
Node.js >= 16
MySQL >= 8.0
Composer
```

### Local Setup
```bash
# Clone repository
git clone [repository-url]
cd [project-directory]

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Start development servers
npm run dev
php artisan serve
```

## 3. Project Structure

### Backend Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── GradeSubjectController.php
│   └── Middleware/
├── Models/
│   ├── Grade.php
│   ├── Subject.php
│   └── GradeSubject.php
└── Services/
```

### Frontend Structure
```
resources/
├── js/
│   ├── Components/
│   │   └── Common/
│   │       ├── DataTable.vue
│   │       ├── FormModal.vue
│   │       └── ImportExcel.vue
│   ├── Pages/
│   │   └── GradeSubjects/
│   │       └── Index.vue
│   └── app.js
```

### Key Components Example
```vue
<!-- FormModal Usage -->
<FormModal
    :show="modalOpen"
    :title="modelName"
    :fields="formFields"
    :editing="editing"
    @close="closeModal"
    @submitted="handleSubmit"
/>
```

### API Calls Example
```javascript
const handleSubmit = async ({ form, onSuccess, onError }) => {
    try {
        const response = await axios.post(url, formData);
        onSuccess();
    } catch (error) {
        onError(error.response.data.errors);
    }
};
```

## 4. Authentication & Authorization

### Jetstream Setup
```php
// config/jetstream.php
return [
    'features' => [
        Features::profilePhotos(),
        Features::api(),
        Features::teams(),
    ],
];
```

### Authorization Example
```php
// app/Policies/GradeSubjectPolicy.php
public function view(User $user, GradeSubject $gradeSubject)
{
    return $user->hasPermissionTo('view grade-subjects');
}
```

## 5. API Documentation

### Grade Subject Endpoints
```php
Route::prefix('admin')->group(function () {
    Route::resource('grade-subject', GradeSubjectController::class);
    Route::post('grade-subject/import', [GradeSubjectController::class, 'import']);
    Route::get('grade-subject/export', [GradeSubjectController::class, 'export']);
});
```

### Example API Response
```json
{
    "data": [
        {
            "id": 1,
            "grade": {
                "id": 1,
                "name": "Grade 1"
            },
            "subject": {
                "id": 1,
                "name": "Mathematics"
            }
        }
    ],
    "links": {}
}
```

## 6. Deployment Guide

### Production Preparation
```bash
# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
VITE_APP_NAME="منصة القدرات الرسمية"
```

## 7. Common Issues & Troubleshooting

### Common Issues
1. **Form Validation Errors**
   - Ensure form field names match backend validation rules
   - Check for proper error handling in components

2. **API 422 Errors**
   - Verify request payload structure
   - Check backend validation rules

### Debugging Tips
```javascript
// Add debug logging
const handleSubmit = async ({ form }) => {
    console.log('Form Data:', form);
    // ... rest of the code
};
```

## 8. Best Practices

### Code Standards
- Use `<script setup>` for Vue components
- Follow Laravel conventions for controllers and models
- Use TypeScript interfaces for complex data structures
- Implement proper error handling

### Component Structure
```vue
<template>
  <!-- Template first -->
</template>

<script setup>
// Imports
import { ref, computed } from 'vue';

// Props and emits
const props = defineProps({...});
const emit = defineEmits(['update']);

// State and computed
const state = ref({});
const computedValue = computed(() => {...});

// Methods
const handleEvent = () => {...};
</script>
```

This documentation provides a solid foundation for developers working on your project. Let me know if you need any section expanded or have specific areas you'd like to add!
