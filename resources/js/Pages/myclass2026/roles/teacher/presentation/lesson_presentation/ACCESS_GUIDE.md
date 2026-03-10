# Quick Access Guide - Course Progression System

## 🎓 For Students

### Access Your Lessons
**URL:** `http://your-domain.com/lesson-presentation/student/lessons`

**What you'll see:**
- All your lessons as color-coded cards
- Progress percentage at the top
- Lock icon on lessons not opened by teacher
- Status badges (Ready, In Progress, Completed, etc.)

### View a Specific Lesson
**URL:** `http://your-domain.com/lesson-presentation/student/{lessonId}`

**Example:** `/lesson-presentation/student/1`

**What you can do:**
1. View lesson slides
2. Click "Complete Learn" after last slide
3. Submit practice (upload OR draw)
4. Take quiz (when unlocked)

---

## 👨‍🏫 For Teachers

### View Student Progress for a Lesson
**URL:** `http://your-domain.com/lesson-presentation/teacher/progress/{lessonId}`

**Example:** `/lesson-presentation/teacher/progress/1`

**What you'll see:**
- Table of all students
- Their progress status
- Color indicators
- Learn/Practice/Quiz completion

**What you can do:**
- Open/Lock lessons for students
- Grade practice submissions
- View uploaded images or drawings
- Grant extra quiz attempts
- Force pass students
- Reset progress
- Bulk open for all students

### Manage Lessons (Existing)
**URL:** `http://your-domain.com/lesson-presentation/dashboard`

**What you can do:**
- Create new lessons
- Edit existing lessons
- Add slides
- Manage content

---

## 🔗 Quick Links Summary

| Role | Action | URL |
|------|--------|-----|
| Student | View all lessons | `/lesson-presentation/student/lessons` |
| Student | View specific lesson | `/lesson-presentation/student/{id}` |
| Teacher | Manage student progress | `/lesson-presentation/teacher/progress/{lessonId}` |
| Teacher | Manage lessons | `/lesson-presentation/dashboard` |
| Teacher | Edit lesson | `/lesson-presentation/edit` |

---

## 🚀 First Time Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Create Storage Link
```bash
php artisan storage:link
```

### 3. Test with Sample Data

**Create a test lesson:**
```bash
php artisan tinker
```
```php
$lesson = App\Models\free\LessonPresentation::create([
    'school_id' => 1,
    'teacher_id' => 1,
    'subject_id' => 1,
    'grade_id' => 1,
    'name' => 'Test Lesson',
    'description' => 'This is a test lesson',
    'order' => 1,
    'is_active' => true
]);
```

**Open lesson for a student:**
```php
$progress = App\Models\LessonStudentProgress::create([
    'lesson_presentation_id' => $lesson->id,
    'student_id' => 1,
    'status' => 'opened',
    'color_status' => 'light_blue',
    'opened_by_teacher_id' => 1,
    'opened_at' => now()
]);
```

---

## 📱 Navigation Flow

### Student Flow:
1. Go to `/lesson-presentation/student/lessons`
2. See all lessons (locked ones show lock icon)
3. Click on an opened lesson
4. View slides → Complete Learn
5. Submit Practice (upload or draw)
6. Wait for teacher to grade
7. Take Quiz when unlocked

### Teacher Flow:
1. Go to `/lesson-presentation/dashboard`
2. Create/edit lessons
3. Go to `/lesson-presentation/teacher/progress/{lessonId}`
4. Click "Open for All" or open individually
5. Students can now access the lesson
6. Grade practice submissions as they come in
7. Monitor quiz attempts
8. Use Force Pass if needed

---

## 🔐 Authentication Notes

**Current Setup (Development):**
- Routes use `Teacher::first()` and `Student::first()`
- This is for testing only

**Production Setup:**
Replace with proper authentication:

```php
// For Teacher
$teacher = Auth::user()->teacher;

// For Student  
$student = Auth::user()->student;
```

Add middleware:
```php
Route::middleware(['auth', 'role:teacher'])->group(function () {
    // Teacher routes
});

Route::middleware(['auth', 'role:student'])->group(function () {
    // Student routes
});
```

---

## 🎨 Visual Guide

### Student View Colors:
- **Gray** = Locked (teacher hasn't opened it)
- **Light Blue** = Ready to start
- **Blue** = In progress
- **Purple** = Practice submitted, waiting for teacher
- **Green** = Passed on 1st attempt ⭐
- **Yellow** = Passed on 2nd attempt
- **Dark Yellow** = Passed on 3rd attempt
- **Orange** = Passed on 4th attempt (extra)
- **Red** = Failed all attempts (need teacher help)

### Teacher Dashboard Icons:
- 🔓 = Open Lesson
- 🔒 = Lock Lesson
- ✅ = Grade Practice
- 👁️ = View Practice
- ➕ = Grant Extra Attempt
- ✔️✔️ = Force Pass
- 🔄 = Reset Progress

---

## 📞 Support

If you encounter issues:
1. Check browser console for errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database migrations ran successfully
4. Ensure storage link is created
5. Check file permissions on `storage/` directory

---

## 🎯 Quick Test Scenario

1. **Teacher:** Open `/lesson-presentation/teacher/progress/1`
2. **Teacher:** Click "Open for All"
3. **Student:** Open `/lesson-presentation/student/lessons`
4. **Student:** Click on the lesson
5. **Student:** Complete slides, click "Complete Learn"
6. **Student:** Submit practice (try both upload and drawing!)
7. **Teacher:** Refresh dashboard, click "Grade"
8. **Teacher:** Give score ≥ 6
9. **Student:** Refresh, see "Take Quiz" button
10. **Success!** ✨

---

## 📝 Notes

- Lesson order is controlled by the `order` field in `lesson_presentations`
- Practice submissions are stored in `storage/app/public/practice_submissions/`
- All progress data is in `lesson_student_progress` table
- Quiz system integration is pending (quiz_id field is ready)
