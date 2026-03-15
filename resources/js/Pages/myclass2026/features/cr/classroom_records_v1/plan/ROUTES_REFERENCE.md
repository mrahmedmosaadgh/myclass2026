# 🗺️ CLASSROOM RECORDS v1 — ROUTES REFERENCE

**Version:** 1.0  
**Last Updated:** March 15, 2026  
**Status:** ✅ Production Ready

---

## 📍 MAIN ACCESS ROUTES

### **Primary Route (Teacher Access)**

```
URL: /classroom-records
Method: GET
Route Name: classroom-records.index
Controller: ClassroomRecordsPageController@__invoke
Middleware: auth:sanctum, web, verified
```

**Access Modes:**

#### 1. Standalone Mode (Default)
```
URL: /classroom-records
Example: https://yourapp.com/classroom-records

Behavior:
- Shows dropdowns for classroom, subject, date, period
- Teacher selects context manually
- Period code auto-generates
```

#### 2. Deep Link Mode (From Schedule)
```
URL: /classroom-records?classroom_id=X&subject_id=Y&date=YYYY-MM-DD&day_number=D&period_number=P

Example: 
https://yourapp.com/classroom-records?classroom_id=5&subject_id=3&date=2026-03-15&day_number=2&period_number=3

Required Query Parameters:
- classroom_id (integer) - Classroom ID
- subject_id (integer) - Subject ID

Optional Query Parameters:
- date (YYYY-MM-DD) - Defaults to today
- day_number (integer) - Day of week (1-7)
- period_number (integer) - Period in day (1-8)
- classroom_name (string) - For display
- subject_name (string) - For display

Behavior:
- Shows readonly badges instead of dropdowns
- Auto-loads session immediately
- Context cannot be changed
```

---

## 🔧 API ENDPOINTS

### **1. Initialize Session**

```
URL: /api/cr/init-session
Method: POST
Auth: Required (Sanctum)
Content-Type: application/json
```

**Request Body:**
```json
{
  "classroom_id": 1,
  "subject_id": 2,
  "teacher_id": 3,
  "date": "2026-03-15",
  "period_code": "Y2026-S1-W12-D2-P3",
  "day_number": 2,
  "period_number": 3
}
```

**Response (Success - 200):**
```json
{
  "session": {
    "id": 10,
    "classroom_id": 1,
    "subject_id": 2,
    "teacher_id": 3,
    "date": "2026-03-15",
    "day_number": 2,
    "period_number": 3,
    "period_code": "Y2026-S1-W12-D2-P3"
  },
  "students": [
    {
      "id": 101,
      "name": "John Doe",
      "student_period_id": 500,
      "period": {
        "attendance_status": "present",
        "attendance_score": 5,
        "total_score": 20,
        "locked": false
      },
      "scores": [
        {
          "mapping_id": 1,
          "mapping_key": "book_participation",
          "label": "Book & Participation",
          "numeric_value": 5,
          "max_value": 5
        },
        {
          "mapping_id": 2,
          "mapping_key": "homework",
          "label": "Homework",
          "numeric_value": 5,
          "max_value": 5
        },
        {
          "mapping_id": 3,
          "mapping_key": "behavior",
          "label": "Behavior",
          "numeric_value": 5,
          "max_value": 5
        }
      ]
    }
  ]
}
```

**Response (Error - 403):**
```json
{
  "error": "You are not assigned to teach this classroom-subject combination"
}
```

**Response (Error - 422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "classroom_id": ["The classroom id field is required."],
    "subject_id": ["The subject id field is required."]
  }
}
```

---

### **2. Batch Update Scores**

```
URL: /api/cr/batch
Method: PATCH
Auth: Required (Sanctum)
Content-Type: application/json
```

**Request Body:**
```json
{
  "updates": [
    {
      "student_period_id": 500,
      "attendance_status": "absent",
      "attendance_score": 0,
      "attendance_note": "Sick",
      "scores": [
        {
          "mapping_id": 1,
          "numeric_value": 0
        },
        {
          "mapping_id": 2,
          "numeric_value": 0
        },
        {
          "mapping_id": 3,
          "numeric_value": 0
        }
      ]
    },
    {
      "student_period_id": 501,
      "scores": [
        {
          "mapping_id": 1,
          "numeric_value": 3
        }
      ]
    }
  ]
}
```

**Response (Success - 200):**
```json
{
  "updated": [500, 501],
  "errors": []
}
```

**Response (Partial Success - 200):**
```json
{
  "updated": [501],
  "errors": [
    {
      "student_period_id": 500,
      "message": "Cannot modify scores for absent student (locked). Change attendance status first."
    }
  ]
}
```

---

## 🎭 ROLE-BASED ACCESS

### **Teacher Role**

**Can Access:**
- ✅ `/classroom-records` (full access)
- ✅ Can create/modify own sessions
- ✅ Can view assigned classrooms only
- ✅ Can score students in assigned classes

**Cannot Access:**
- ❌ Other teachers' sessions
- ❌ Unassigned classrooms
- ❌ Admin-only features

**Authorization Checks:**
1. Must have teacher record
2. Must be assigned to classroom+subject
3. School scoping enforced
4. Year scoping enforced

---

### **Admin Roles** (admin, school_admin, super_admin)

**Can Access:**
- ✅ `/classroom-records` (read-only)
- ✅ Can view ALL sessions in school
- ✅ Cannot create or modify records
- ✅ Oversight and reporting only

**Cannot Do:**
- ❌ Create new sessions
- ❌ Modify existing scores
- ❌ Change attendance records

**UI Behavior:**
- Context bar shows readonly badges
- Student cards rendered but disabled
- All interactions greyed out
- Tooltips indicate "Read-only access"

---

## 🧪 TESTING URLS

### **Test Scenario 1: First Time Use**
```
URL: /classroom-records
Expected: Dropdowns populate with teacher's assignments
```

### **Test Scenario 2: Deep Link from Schedule**
```
URL: /classroom-records?classroom_id=1&subject_id=2&date=2026-03-15&day_number=2&period_number=3
Expected: Readonly badges, auto-load session
```

### **Test Scenario 3: Admin View**
```
URL: /classroom-records (logged in as admin)
Expected: Readonly mode, all classrooms available
```

### **Test Scenario 4: Invalid Assignment**
```
URL: /classroom-records?classroom_id=999&subject_id=999
Expected: 403 error or redirect (not assigned)
```

---

## 🔐 SECURITY VALIDATION

### **What's Protected:**

#### **Web Route (/classroom-records)**
- ✅ Auth required (Sanctum)
- ✅ School scoping
- ✅ Teacher assignment validation
- ✅ Admin read-only enforcement

#### **API Endpoints**
- ✅ Auth required (Sanctum)
- ✅ Input validation
- ✅ Teacher assignment verification
- ✅ School/year scoping
- ✅ ID spoofing prevention

### **Common Attack Vectors Blocked:**

**1. ID Spoofing**
```
Attack: Send different teacher_id in request
Prevention: Backend resolves from authenticated user
Result: ✅ Blocked
```

**2. Cross-School Access**
```
Attack: Try to access another school's data
Prevention: School scoping on all queries
Result: ✅ Blocked
```

**3. Unauthorized Assignment**
```
Attack: Teacher accesses unassigned class
Prevention: Assignment validation server-side
Result: ✅ Blocked (403 error)
```

**4. Admin Write Access**
```
Attack: Admin tries to modify scores
Prevention: Read-only enforcement in backend
Result: ✅ Blocked (403 error)
```

---

## 🚨 ERROR CODES

### **HTTP Status Codes**

| Code | Meaning | When |
|------|---------|------|
| 200 | OK | Successful operation |
| 302 | Found | Redirect (e.g., to login) |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Authorized but no access |
| 404 | Not Found | Resource doesn't exist |
| 419 | CSRF Mismatch | Token expired/invalid |
| 422 | Validation Error | Invalid input data |
| 500 | Server Error | Unexpected exception |

---

## 📊 ROUTE REGISTRATION

### **Verify Routes Are Registered**

```bash
# Check main web route
php artisan route:list --name=classroom-records.index

# Check API endpoints
php artisan route:list --path=api/cr

# List all CR routes
php artisan route:list | findstr "classroom-records"
```

**Expected Output:**
```
GET|HEAD    /classroom-records .................... classroom-records.index
POST        /api/cr/init-session .................. cr.init-session
PATCH       /api/cr/batch ......................... cr.batch
```

---

## 🎯 QUICK START FOR TESTING

### **Step 1: Login as Teacher**
```
1. Navigate to: /login
2. Enter teacher credentials
3. Verify successful login
```

### **Step 2: Access Classroom Records**
```
URL: /classroom-records
Expected: Page loads with dropdowns
```

### **Step 3: Create New Session**
```
1. Select classroom from dropdown
2. Select subject from dropdown
3. Choose date (default: today)
4. Select period number
5. Wait for period code to generate
6. Verify student cards load
```

### **Step 4: Test Scoring**
```
1. Tap "Book" category on first student
2. Verify color changes (green → yellow → red)
3. Verify total score updates
4. Wait 1.5 seconds
5. Verify "✓ Saved" indicator appears
```

### **Step 5: Test Absent Lock**
```
1. Click "Present ✅" button (toggle to "Absent ❌")
2. Verify red border appears
3. Verify all scores → 0
4. Try tapping categories (should be disabled)
5. Click "Absent ❌" again (toggle back to "Present ✅")
6. Verify scores reset to 5
```

---

## 📞 TROUBLESHOOTING

### **Issue: Route Not Found (404)**
```
Check: Route is registered
Command: php artisan route:list --name=classroom-records.index
Fix: Clear route cache → php artisan route:clear
```

### **Issue: Unauthorized (401)**
```
Check: Logged in with valid account
Check: Account has teacher or admin role
Fix: Login again or create test account
```

### **Issue: Forbidden (403)**
```
Check: Teacher is assigned to selected classroom+subject
Check: Not trying to access other teacher's data
Fix: Use correct test data or assign teacher
```

### **Issue: CSRF Token Mismatch (419)**
```
Check: Using Laravel forms correctly
Check: Axios includes XSRF-TOKEN header
Fix: Add meta tag: <meta name="csrf-token" content="{{ csrf_token() }}">
```

---

## ✅ COMPLETE ROUTE CHECKLIST

Use this to verify all routes are working:

### **Web Routes**
- [ ] `/classroom-records` loads successfully
- [ ] Requires authentication
- [ ] Dropdowns populate (standalone mode)
- [ ] Deep link works (query params)
- [ ] Admin sees readonly mode

### **API Routes**
- [ ] `POST /api/cr/init-session` returns 200
- [ ] Response contains session + students
- [ ] `PATCH /api/cr/batch` saves changes
- [ ] Partial errors handled correctly
- [ ] Authorization enforced

### **Security**
- [ ] Teacher can only see assigned classes
- [ ] Admin can see all (read-only)
- [ ] Cannot spoof teacher_id
- [ ] School scoping works
- [ ] Year scoping works

---

**Last Updated:** March 15, 2026  
**Maintained By:** Development Team  
**Next Review:** After production deployment
