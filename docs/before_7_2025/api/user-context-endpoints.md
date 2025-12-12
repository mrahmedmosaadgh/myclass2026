# 🔌 User Context API Endpoints

RESTful API endpoints for segmented user context management with offline-first caching support.

## 📋 Table of Contents

- [Authentication](#authentication)
- [Base URL](#base-url)
- [Response Format](#response-format)
- [Endpoints](#endpoints)
- [Error Handling](#error-handling)
- [Usage Examples](#usage-examples)

## 🔐 Authentication

All endpoints require authentication using Laravel Sanctum:

```javascript
// Headers required
{
  'Authorization': 'Bearer {token}',
  'X-CSRF-TOKEN': '{csrf_token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

## 🌐 Base URL

```
/api/user-context
```

## 📄 Response Format

All endpoints return JSON responses in this format:

```json
{
  "success": true,
  "data": { /* response data */ },
  "meta": {
    "user_id": 123,
    "timestamp": "2024-01-15T10:30:00.000Z",
    "expires_at": "2024-01-22T10:30:00.000Z"
  },
  "message": "Optional message"
}
```

## 🛠️ Endpoints

### 1. Get All Context

Get all user context segments in one request.

**Endpoint:** `GET /api/user-context/`

**Response:**
```json
{
  "success": true,
  "data": {
    "user_profile": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "user_role": "teacher"
    },
    "user_permissions": {
      "roles": ["teacher", "admin"]
    },
    "user_school": {
      "school": [...],
      "schools": [...]
    },
    "user_classroom": {
      "teacher": {...},
      "classroom": [...]
    },
    "user_schedule": {
      "schedule": [...]
    }
  }
}
```

### 2. Get Specific Segments

Get multiple specific segments.

**Endpoint:** `POST /api/user-context/segments`

**Request Body:**
```json
{
  "segments": ["profile", "permissions", "school"]
}
```

**Response:** Same format as "Get All Context" but only with requested segments.

### 3. Individual Segment Endpoints

#### Get Profile
**Endpoint:** `GET /api/user-context/profile`

#### Get Permissions
**Endpoint:** `GET /api/user-context/permissions`

#### Get School
**Endpoint:** `GET /api/user-context/school`

#### Get Classroom
**Endpoint:** `GET /api/user-context/classroom`

#### Get Schedule
**Endpoint:** `GET /api/user-context/schedule`

**Response Format (for all individual segments):**
```json
{
  "success": true,
  "data": { /* segment-specific data */ },
  "meta": {
    "segment": "profile",
    "user_id": 123,
    "timestamp": "2024-01-15T10:30:00.000Z",
    "expires_at": "2024-01-22T10:30:00.000Z"
  }
}
```

### 4. Update Context

Force refresh all context data and clear cache.

**Endpoint:** `POST /api/user-context/update`

**Response:**
```json
{
  "success": true,
  "message": "User context updated successfully",
  "data": { /* fresh context data */ }
}
```

### 5. Clear Cache

Clear all cached user context data.

**Endpoint:** `DELETE /api/user-context/cache`

**Response:**
```json
{
  "success": true,
  "message": "User context cache cleared successfully",
  "data": {
    "cleared_keys": 5,
    "total_keys": 5
  }
}
```

### 6. Cache Status

Get current cache status and health information.

**Endpoint:** `GET /api/user-context/cache-status`

**Response:**
```json
{
  "success": true,
  "data": {
    "segments": {
      "profile": {
        "cached": true,
        "ttl_seconds": 604800,
        "expires_at": "2024-01-22T10:30:00.000Z"
      },
      // ... other segments
    },
    "summary": {
      "total_segments": 5,
      "cached_segments": 4,
      "cache_health": 80.0
    }
  }
}
```

### 7. Health Check

Check the health of the user context system.

**Endpoint:** `GET /api/user-context/health`

**Response:**
```json
{
  "success": true,
  "data": {
    "overall_status": "healthy",
    "segments": {
      "profile": {
        "status": "healthy",
        "response_time_ms": 12.5,
        "data_available": true,
        "error": null
      },
      // ... other segments
    },
    "summary": {
      "total_segments": 5,
      "healthy_segments": 5,
      "health_percentage": 100.0
    }
  }
}
```

## ❌ Error Handling

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message (in debug mode)"
}
```

### Common HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request (invalid segments, etc.) |
| 401 | Unauthorized (not authenticated) |
| 403 | Forbidden (insufficient permissions) |
| 500 | Internal Server Error |

### Error Examples

**Invalid Segments (400):**
```json
{
  "success": false,
  "message": "Invalid segments: invalid_segment",
  "valid_segments": ["profile", "permissions", "school", "classroom", "schedule"]
}
```

**Authentication Required (401):**
```json
{
  "success": false,
  "message": "User not authenticated"
}
```

## 💡 Usage Examples

### JavaScript/Axios

```javascript
import axios from 'axios';

// Get all context
const response = await axios.get('/api/user-context/', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'X-CSRF-TOKEN': csrfToken
  }
});

// Get specific segments
const segments = await axios.post('/api/user-context/segments', {
  segments: ['profile', 'school']
}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'X-CSRF-TOKEN': csrfToken
  }
});

// Update context
const updated = await axios.post('/api/user-context/update', {}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'X-CSRF-TOKEN': csrfToken
  }
});
```

### Using the API Client

```javascript
import { userContextApi } from '@/api/userContextApi.js';

// Get all context
const context = await userContextApi.getAllContext();

// Get specific segment
const profile = await userContextApi.getProfile();

// Update context
const updated = await userContextApi.updateContext();

// Check health
const health = await userContextApi.healthCheck();
```

### Vue Composable Integration

```javascript
import { useUserContext } from '@/composables/useUserContext.js';

const { refreshSegment, clearCache, getCacheStats } = useUserContext();

// Refresh specific segment (uses API internally)
await refreshSegment('profile');

// Clear cache (uses API internally)
await clearCache();

// Get cache stats (combines local + API data)
const stats = await getCacheStats();
```

## 🔧 Rate Limiting

- No specific rate limits currently implemented
- Standard Laravel rate limiting applies
- Consider implementing segment-specific caching to reduce API calls

## 📊 Monitoring

Use the health check endpoint for monitoring:

```bash
# Simple health check
curl -H "Authorization: Bearer {token}" \
     -H "X-CSRF-TOKEN: {csrf}" \
     /api/user-context/health

# Cache status monitoring
curl -H "Authorization: Bearer {token}" \
     -H "X-CSRF-TOKEN: {csrf}" \
     /api/user-context/cache-status
```

## 🚀 Best Practices

1. **Cache First**: Always check local cache before making API calls
2. **Batch Requests**: Use `/segments` endpoint for multiple segments
3. **Error Handling**: Always handle network errors gracefully
4. **Health Monitoring**: Regularly check system health
5. **Selective Loading**: Only load segments you actually need

## 🔄 Integration with Offline System

The API endpoints integrate seamlessly with the offline-first system:

- **Online**: API calls provide fresh data
- **Offline**: Local cache serves data
- **Sync**: Automatic sync when connection restored
- **Fallback**: Graceful degradation to cached data
