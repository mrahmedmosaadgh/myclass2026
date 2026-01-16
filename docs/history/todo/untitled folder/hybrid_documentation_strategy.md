## 📚 Documentation Strategy: Hybrid Approach

> [!NOTE]
> **Best Practice:** Separate developer documentation from user-facing help content

### 🔹 Markdown Docs (Developer/Technical)

**Location:** `resources/docs/` (versioned in Git)

**Purpose:**
- Technical reference for developers
- API documentation
- Implementation guides
- Migration procedures
- Architecture decisions

**Structure:**
```
resources/docs/
├── system/
│   ├── overview.md
│   ├── architecture.md
│   └── tech_stack.md
├── features/
│   ├── schedule_editor.md
│   ├── lessons.md
│   ├── quizzes.md
│   ├── attendance.md
│   └── behavior_tracking.md
├── backend/
│   ├── api_endpoints.md
│   ├── controllers.md
│   ├── models.md
│   └── services.md
├── migration/
│   ├── v2_migration_guide.md
│   ├── v1_to_v2_differences.md
│   └── rollback_procedures.md
└── deployment/
    ├── server_setup.md
    ├── environment_config.md
    └── ci_cd.md
```

**Benefits:**
- ✅ Version controlled with code
- ✅ Safe migration reference
- ✅ Code review for doc changes
- ✅ Easy to reference in commits/PRs
- ✅ Works offline

---

### 🔹 Database Docs (User-Facing Help)

**Purpose:**
- Role-specific user guides
- In-app help articles
- Tutorials and walkthroughs
- Dynamic, editable content
- Searchable help center

**Database Schema:**

```sql
CREATE TABLE documentation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL COMMENT 'Markdown or HTML',
    role VARCHAR(50) NULL COMMENT 'SystemAdmin, SchoolAdmin, Teacher, Student, Parent, null=all',
    category VARCHAR(100) NOT NULL COMMENT 'Lessons, Scheduling, Reports, etc.',
    published BOOLEAN DEFAULT FALSE,
    featured BOOLEAN DEFAULT FALSE COMMENT 'Show in featured section',
    view_count INT DEFAULT 0,
    helpful_count INT DEFAULT 0,
    search_keywords TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    
    INDEX idx_role (role),
    INDEX idx_category (category),
    INDEX idx_published (published),
    INDEX idx_slug (slug),
    FULLTEXT idx_search (title, content, search_keywords)
);

CREATE TABLE documentation_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documentation_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (documentation_id) REFERENCES documentation(id) ON DELETE CASCADE
);

CREATE TABLE documentation_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documentation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    was_helpful BOOLEAN NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (documentation_id) REFERENCES documentation(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_doc (user_id, documentation_id)
);
```

**Documentation Categories by Role:**

```javascript
const documentationCategories = {
  SuperSystem: [
    'System Configuration',
    'Developer Tools',
    'Database Management',
    'Server Maintenance',
    'Troubleshooting'
  ],
  SystemAdmin: [
    'School Management',
    'User Administration',
    'Role & Permissions',
    'Audit & Compliance',
    'System Settings'
  ],
  SchoolAdmin: [
    'Academic Structure',
    'People Management',
    'Scheduling',
    'Attendance',
    'Behavior Tracking',
    'Reports & Analytics'
  ],
  Teacher: [
    'Creating Lessons',
    'Managing Quizzes',
    'Taking Attendance',
    'Grading Students',
    'Weekly Planning',
    'Behavior Incidents'
  ],
  Student: [
    'Viewing Schedule',
    'Accessing Lessons',
    'Taking Quizzes',
    'Submitting Assignments',
    'Checking Grades'
  ],
  Parent: [
    'Viewing Child Progress',
    'Attendance Records',
    'Behavior Reports',
    'Academic Reports',
    'Communication'
  ]
};
```

**Dashboard Integration:**

```
SuperSystem/Documentation/
├── Index.vue              # Browse all docs
├── Create.vue             # Create new doc
├── Edit.vue               # Edit doc
└── View.vue               # View doc with feedback

Each Role Dashboard:
├── HelpCenter.vue         # Role-specific help
├── Search.vue             # Search docs
└── QuickGuides.vue        # Featured tutorials
```

---

### 🔹 Implementation in V2

#### **1. Documentation Model** (Laravel)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'role',
        'category',
        'published',
        'featured',
        'search_keywords',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope: Published only
     */
    public function scopePublished($query)
    {
        return $query->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: By role
     */
    public function scopeForRole($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('role', $role)
              ->orWhereNull('role'); // Docs for all roles
        });
    }

    /**
     * Scope: By category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Search documentation
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->whereRaw(
            'MATCH(title, content, search_keywords) AGAINST(? IN NATURAL LANGUAGE MODE)',
            [$searchTerm]
        );
    }

    /**
     * Relationships
     */
    public function attachments()
    {
        return $this->hasMany(DocumentationAttachment::class);
    }

    public function feedback()
    {
        return $this->hasMany(DocumentationFeedback::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

#### **2. Help Center Component** (Vue)

```vue
<!-- resources/js/myclass_v2/core/components/HelpCenter.vue -->
<template>
  <q-dialog v-model="isOpen" maximized>
    <q-card>
      <q-toolbar class="bg-primary text-white">
        <q-toolbar-title>
          <q-icon name="help" size="sm" class="q-mr-sm" />
          Help Center
        </q-toolbar-title>
        <q-btn flat round dense icon="close" @click="isOpen = false" />
      </q-toolbar>

      <q-card-section>
        <!-- Search -->
        <q-input
          v-model="searchQuery"
          outlined
          placeholder="Search for help..."
          @update:model-value="searchDocs"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>

        <q-separator class="q-my-md" />

        <!-- Categories -->
        <div class="q-gutter-sm">
          <q-chip
            v-for="category in categories"
            :key="category"
            clickable
            :color="selectedCategory === category ? 'primary' : 'grey-3'"
            @click="selectCategory(category)"
          >
            {{ category }}
          </q-chip>
        </div>

        <!-- Documentation List -->
        <q-list>
          <q-item
            v-for="doc in filteredDocs"
            :key="doc.id"
            clickable
            @click="viewDoc(doc)"
          >
            <q-item-section avatar>
              <q-icon :name="doc.featured ? 'star' : 'description'" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ doc.title }}</q-item-label>
              <q-item-label caption>{{ doc.category }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-badge>{{ doc.view_count }} views</q-badge>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useDocumentationStore } from '@/myclass_v2/stores/documentation';

const docStore = useDocumentationStore();
const isOpen = ref(false);
const searchQuery = ref('');
const selectedCategory = ref<string | null>(null);

const categories = computed(() => docStore.categories);
const filteredDocs = computed(() => docStore.filteredDocs);

const searchDocs = (query: string) => {
  docStore.search(query);
};

const selectCategory = (category: string) => {
  selectedCategory.value = category;
  docStore.filterByCategory(category);
};

const viewDoc = (doc: any) => {
  docStore.viewDoc(doc.id);
};

defineExpose({ isOpen });
</script>
```

#### **3. Documentation Store** (Pinia)

```typescript
// resources/js/myclass_v2/stores/documentation.ts
import { defineStore } from 'pinia';
import axios from 'axios';

interface Doc {
  id: number;
  title: string;
  slug: string;
  content: string;
  role: string | null;
  category: string;
  published: boolean;
  featured: boolean;
  view_count: number;
}

export const useDocumentationStore = defineStore('documentation', {
  state: () => ({
    docs: [] as Doc[],
    categories: [] as string[],
    currentDoc: null as Doc | null,
    isLoading: false,
  }),

  getters: {
    filteredDocs: (state) => state.docs,
    featuredDocs: (state) => state.docs.filter(doc => doc.featured),
  },

  actions: {
    async fetchDocs(role?: string) {
      this.isLoading = true;
      try {
        const response = await axios.get('/api/v2/documentation', {
          params: { role }
        });
        this.docs = response.data.data;
        this.categories = response.data.categories;
      } catch (error) {
        console.error('Failed to fetch docs:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async search(query: string) {
      if (!query) {
        return this.fetchDocs();
      }
      
      try {
        const response = await axios.get('/api/v2/documentation/search', {
          params: { q: query }
        });
        this.docs = response.data.data;
      } catch (error) {
        console.error('Search failed:', error);
      }
    },

    async viewDoc(id: number) {
      try {
        const response = await axios.get(`/api/v2/documentation/${id}`);
        this.currentDoc = response.data.data;
        
        // Increment view count
        await axios.post(`/api/v2/documentation/${id}/view`);
      } catch (error) {
        console.error('Failed to load doc:', error);
      }
    },

    async submitFeedback(docId: number, wasHelpful: boolean, comment?: string) {
      try {
        await axios.post(`/api/v2/documentation/${docId}/feedback`, {
          was_helpful: wasHelpful,
          comment
        });
      } catch (error) {
        console.error('Feedback submission failed:', error);
      }
    },
  }
});
```

---

### 🔹 Benefits Summary

| Aspect | Markdown (Dev Docs) | Database (User Help) |
|--------|---------------------|----------------------|
| **Audience** | Developers, DevOps | End users (Teachers, Students, etc.) |
| **Version Control** | ✅ Git versioned | ❌ Not in Git |
| **Dynamic Editing** | ❌ Requires code deploy | ✅ Edit via dashboard |
| **Search** | ❌ File search only | ✅ Full-text search |
| **Role-Specific** | ❌ Manual filtering | ✅ Automatic by role |
| **Attachments** | ❌ Limited | ✅ Images, videos, PDFs |
| **Analytics** | ❌ No tracking | ✅ Views, helpfulness |
| **Offline Access** | ✅ Always available | ❌ Requires connection |

---

### 🔹 Migration to V2

**Phase 1:** Set up structure
- Create `resources/docs/` folders
- Move existing technical docs to Markdown
- Create `documentation` table migration

**Phase 2:** Seed initial help content
- Create seed data for common help topics
- Organize by role and category
- Add featured tutorials

**Phase 3:** Build UI
- Documentation management in SuperSystem
- Help Center component in each role dashboard
- Search and feedback features

**Phase 4:** Migrate content
- Convert old help content to database
- Create role-specific guides
- Add video/image attachments

---

This hybrid approach gives you **the best of both worlds**: 
- Developers have versioned, reliable technical docs
- Users get contextual, searchable, role-specific help

Ready to proceed with this documentation strategy? 🚀
