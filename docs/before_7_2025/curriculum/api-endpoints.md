# Curriculum Management API Documentation

## Base URL
```
/api/curriculum/
```

## Authentication
All endpoints require authentication using Laravel Sanctum with web middleware.

**Headers Required:**
```
Authorization: Bearer {token}
X-CSRF-TOKEN: {csrf_token}
Content-Type: application/json
```

## Endpoints

### 1. Get User Schools

**Endpoint:** `GET /user-schools`

**Description:** Retrieve schools accessible to the authenticated user.

**Response:**
```json
[
  {
    "id": 1,
    "name": "Springfield Elementary",
    "name_ar": "مدرسة سبرينغفيلد الابتدائية"
  },
  {
    "id": 2,
    "name": "Riverside High School",
    "name_ar": "مدرسة ريفرسايد الثانوية"
  }
]
```

**Status Codes:**
- `200`: Success
- `401`: Unauthorized
- `500`: Server Error

---

### 2. Get School Subjects

**Endpoint:** `GET /school/{school_id}/subjects`

**Description:** Retrieve active subjects for a specific school.

**Parameters:**
- `school_id` (integer, required): School ID

**Response:**
```json
[
  {
    "id": 1,
    "name": "Mathematics",
    "description": "Basic mathematics curriculum"
  },
  {
    "id": 2,
    "name": "Science",
    "description": "General science curriculum"
  }
]
```

**Status Codes:**
- `200`: Success
- `404`: School not found
- `401`: Unauthorized

---

### 3. Get School Grades

**Endpoint:** `GET /school/{school_id}/grades`

**Description:** Retrieve grades for a specific school.

**Parameters:**
- `school_id` (integer, required): School ID

**Response:**
```json
[
  {
    "id": 1,
    "name": "Grade 1",
    "stage_id": 1,
    "stage": {
      "id": 1,
      "name": "Primary"
    }
  },
  {
    "id": 2,
    "name": "Grade 2",
    "stage_id": 1,
    "stage": {
      "id": 1,
      "name": "Primary"
    }
  }
]
```

**Status Codes:**
- `200`: Success
- `404`: School not found
- `401`: Unauthorized

---

### 4. Get Curricula

**Endpoint:** `GET /curricula`

**Description:** Retrieve curricula with optional filtering and pagination.

**Query Parameters:**
- `school_id` (integer, optional): Filter by school
- `subject_id` (integer, optional): Filter by subject
- `grade_id` (integer, optional): Filter by grade
- `active` (integer, optional): Filter by status (0=inactive, 1=active)
- `page` (integer, optional): Page number for pagination
- `per_page` (integer, optional): Items per page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Math Curriculum 2024",
      "description": "Updated mathematics curriculum for 2024",
      "school_id": 1,
      "subject_id": 1,
      "grade_id": 1,
      "active": 1,
      "created_at": "2024-01-15T10:30:00.000000Z",
      "school": {
        "id": 1,
        "name": "Springfield Elementary"
      },
      "subject": {
        "id": 1,
        "name": "Mathematics"
      },
      "grade": {
        "id": 1,
        "name": "Grade 1"
      }
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 15,
  "total": 1
}
```

**Status Codes:**
- `200`: Success
- `401`: Unauthorized

---

### 5. Create Curriculum

**Endpoint:** `POST /curricula`

**Description:** Create a new curriculum.

**Request Body:**
```json
{
  "name": "Science Curriculum 2024",
  "description": "Updated science curriculum for grade 2",
  "school_id": 1,
  "subject_id": 2,
  "grade_id": 2,
  "active": true
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `description`: optional, string
- `school_id`: required, must exist in schools table
- `subject_id`: required, must exist in subjects table
- `grade_id`: required, must exist in grades table
- `active`: optional, boolean

**Response:**
```json
{
  "success": true,
  "message": "Curriculum created successfully",
  "curriculum": {
    "id": 2,
    "name": "Science Curriculum 2024",
    "description": "Updated science curriculum for grade 2",
    "school_id": 1,
    "subject_id": 2,
    "grade_id": 2,
    "active": 1,
    "created_at": "2024-01-15T11:00:00.000000Z",
    "school": {
      "id": 1,
      "name": "Springfield Elementary"
    },
    "subject": {
      "id": 2,
      "name": "Science"
    },
    "grade": {
      "id": 2,
      "name": "Grade 2"
    }
  }
}
```

**Status Codes:**
- `200`: Success
- `422`: Validation Error
- `500`: Server Error

---

### 6. Update Curriculum

**Endpoint:** `PUT /curricula/{curriculum_id}`

**Description:** Update an existing curriculum.

**Parameters:**
- `curriculum_id` (integer, required): Curriculum ID

**Request Body:**
```json
{
  "name": "Updated Math Curriculum 2024",
  "description": "Revised mathematics curriculum",
  "active": false
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `description`: optional, string
- `active`: optional, boolean

**Response:**
```json
{
  "success": true,
  "message": "Curriculum updated successfully",
  "curriculum": {
    "id": 1,
    "name": "Updated Math Curriculum 2024",
    "description": "Revised mathematics curriculum",
    "active": 0,
    "updated_at": "2024-01-15T11:30:00.000000Z"
  }
}
```

**Status Codes:**
- `200`: Success
- `404`: Curriculum not found
- `422`: Validation Error
- `500`: Server Error

---

### 7. Activate Curriculum

**Endpoint:** `POST /curricula/{curriculum_id}/activate`

**Description:** Activate a curriculum and automatically deactivate conflicting ones.

**Parameters:**
- `curriculum_id` (integer, required): Curriculum ID

**Response:**
```json
{
  "success": true,
  "message": "Curriculum activated successfully. Other curricula for the same subject and grade have been deactivated."
}
```

**Status Codes:**
- `200`: Success
- `404`: Curriculum not found
- `500`: Server Error

---

### 8. Deactivate Curriculum

**Endpoint:** `POST /curricula/{curriculum_id}/deactivate`

**Description:** Deactivate a curriculum.

**Parameters:**
- `curriculum_id` (integer, required): Curriculum ID

**Response:**
```json
{
  "success": true,
  "message": "Curriculum deactivated successfully"
}
```

**Status Codes:**
- `200`: Success
- `404`: Curriculum not found
- `500`: Server Error

---

### 9. Delete Curriculum

**Endpoint:** `DELETE /curricula/{curriculum_id}`

**Description:** Soft delete a curriculum.

**Parameters:**
- `curriculum_id` (integer, required): Curriculum ID

**Response:**
```json
{
  "success": true,
  "message": "Curriculum deleted successfully"
}
```

**Status Codes:**
- `200`: Success
- `404`: Curriculum not found
- `500`: Server Error

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "school_id": ["The selected school id is invalid."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Curriculum] 999"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Error creating curriculum: Database connection failed"
}
```

## Rate Limiting

API endpoints are subject to Laravel's default rate limiting:
- **Authenticated requests**: 60 requests per minute
- **Guest requests**: 10 requests per minute

## Testing Examples

### Using cURL

**Get User Schools:**
```bash
curl -X GET \
  http://localhost:8000/api/curriculum/user-schools \
  -H 'Authorization: Bearer your-token-here' \
  -H 'X-CSRF-TOKEN: your-csrf-token'
```

**Create Curriculum:**
```bash
curl -X POST \
  http://localhost:8000/api/curriculum/curricula \
  -H 'Authorization: Bearer your-token-here' \
  -H 'X-CSRF-TOKEN: your-csrf-token' \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Test Curriculum",
    "school_id": 1,
    "subject_id": 1,
    "grade_id": 1,
    "active": true
  }'
```

### Using JavaScript (Axios)

```javascript
// Get curricula with filters
const response = await axios.get('/api/curriculum/curricula', {
  params: {
    school_id: 1,
    subject_id: 2,
    active: 1
  }
});

// Create new curriculum
const newCurriculum = await axios.post('/api/curriculum/curricula', {
  name: 'New Curriculum',
  description: 'Curriculum description',
  school_id: 1,
  subject_id: 1,
  grade_id: 1,
  active: true
});
```

---

*Last Updated: [Current Date]*
*API Version: 1.0*
