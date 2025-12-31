
**Role:**
You are a senior Laravel developer and software architect designing a **multi-school, year-specific teacher management system**.

**Goal:**
Design a system where **teachers are added both to the `teachers` table and the `users` table** automatically, supporting **Excel imports**, **school selection**, **academic year assignments**, and **soft-delete handling**.

---

### 1️⃣ Functional Requirements

1. Admin selects:

   * School (`school_id`)
   * Academic Year (`academic_year_id`)
2. Admin uploads Excel with columns:

   * Classroom
   * Subject
   * Teacher Name
   * Periods_per_Week
3. System must:

   * Create classrooms, subjects, and teachers if not exists
   * Add teacher to `teachers` table
   * Add corresponding user in `users` table

     * Role = `teacher`
     * Password = random or default
     * Active = 1 by default
   * Assign teacher to classroom + subject + academic year
   * Sync large Excel files efficiently
   * Support soft-delete on teachers (also disable user login)
   * Keep classroom_subject_teacher assignments as historical facts (not soft-deleted)

---

### 2️⃣ Database Tables

* `schools` (id, name)
* `academic_years` (id, school_id, name, start_date, end_date, is_active)
* `teachers` (id, t_id, name, user_id, school_id, active)
* `users` (id, name, email, password, role, active)
* `classrooms` (id, school_id, name)
* `subjects` (id, school_id, name)
* `classroom_subject_teachers` (id, school_id, academic_year_id, classroom_id, subject_id, teacher_id, periods_per_week)
* Unique constraint on classroom_subject_teachers:
  `school_id + academic_year_id + classroom_id + subject_id + teacher_id`

---

### 3️⃣ Teacher Model Requirements

* On **creating a teacher**:

  * Generate a unique `t_id`
  * Check if a `user` exists for this `t_id`:

    * If not, create a new `user`
  * Link `teacher.user_id = user.id`
* On **updating teacher.active**:

  * Update `user.active` to match
* On **soft-deleting teacher**:

  * Set `user.active = 0`
* Historical classroom assignments must remain untouched

---

### 4️⃣ Excel Import + Sync Logic

* Preload existing classrooms, subjects, teachers, and users **by school**
* For each Excel row:

  1. Create classroom if not exists
  2. Create subject if not exists
  3. Create teacher + user if not exists
  4. Assign teacher to classroom + subject + academic year
  5. Track inserted/updated keys
* Optional: Full sync deletes missing rows for the same school + academic year
* Must support **chunk reading** and **queue processing** for large files
* Generate a **summary report** after import:

  * Rows processed
  * Created
  * Updated
  * Deleted
  * Errors

---

### 5️⃣ Validation Rules

* Classroom, Subject, Teacher must not be empty
* Periods_per_Week must be numeric > 0
* Teacher cannot be assigned if inactive
* No cross-school assignments allowed
* Same subject can have multiple teachers per class/year

---

### 6️⃣ Expected AI Deliverables

* **Laravel migration schema** including schools, teachers, users, classrooms, subjects, classroom_subject_teacher
* **Teacher.php model** with boot() logic for user sync and soft-delete
* **Excel import class** (with chunking, validation, school/year scoping)
* **Sync algorithm** (insert/update/delete)
* Best practices for **soft-delete, historical integrity, and large dataset performance**
* Admin UX flow for importing Excel and managing teachers

---
if anyting not clear ask first before coding
