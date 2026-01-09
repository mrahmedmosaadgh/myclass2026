here try to add new paths for my class system vergin 2026
for admin  
all avaliable paths for admin system as learning system as tree

## Ideal Admin System Path Tree for MyClass2026

### System Management
- **Users**
  - `GET /admin/users` - List all users
  - `POST /admin/users` - Create a new user
  - `GET /admin/users/{id}` - View user details
  - `PUT /admin/users/{id}` - Update user details
  - `DELETE /admin/users/{id}` - Delete a user
  - `PUT /admin/users/{id}/restore` - Restore a deleted user
  - `PUT /admin/users/{id}/roles` - Assign roles to a user

- **Roles and Permissions**
  - `GET /admin/roles` - List all roles
  - `POST /admin/roles` - Create a new role
  - `PUT /admin/roles/{id}` - Update role details
  - `DELETE /admin/roles/{id}` - Delete a role
  - `GET /admin/permissions` - List all permissions
  - `POST /admin/permissions` - Create a new permission
  - `PUT /admin/permissions/{id}` - Update permission details
  - `DELETE /admin/permissions/{id}` - Delete a permission

### Academic Management
- **Schools**
  - `GET /admin/schools` - List all schools
  - `POST /admin/schools` - Create a new school
  - `GET /admin/schools/{id}` - View school details
  - `PUT /admin/schools/{id}` - Update school details
  - `DELETE /admin/schools/{id}` - Delete a school

- **Classrooms**
  - `GET /admin/classrooms` - List all classrooms
  - `POST /admin/classrooms` - Create a new classroom
  - `GET /admin/classrooms/{id}` - View classroom details
  - `PUT /admin/classrooms/{id}` - Update classroom details
  - `DELETE /admin/classrooms/{id}` - Delete a classroom

- **Teachers**
  - `GET /admin/teachers` - List all teachers
  - `POST /admin/teachers` - Add a new teacher
  - `GET /admin/teachers/{id}` - View teacher details
  - `PUT /admin/teachers/{id}` - Update teacher details
  - `DELETE /admin/teachers/{id}` - Remove a teacher

### Content Management
- **Lessons**
  - `GET /admin/lessons` - List all lessons
  - `POST /admin/lessons` - Create a new lesson
  - `GET /admin/lessons/{id}` - View lesson details
  - `PUT /admin/lessons/{id}` - Update lesson details
  - `DELETE /admin/lessons/{id}` - Delete a lesson

- **Quizzes**
  - `GET /admin/quizzes` - List all quizzes
  - `POST /admin/quizzes` - Create a new quiz
  - `GET /admin/quizzes/{id}` - View quiz details
  - `PUT /admin/quizzes/{id}` - Update quiz details
  - `DELETE /admin/quizzes/{id}` - Delete a quiz

### Behavior and Reports
- **Behavior Incidents**
  - `GET /admin/behavior-incidents` - List all behavior incidents
  - `POST /admin/behavior-incidents` - Report a new incident
  - `GET /admin/behavior-incidents/{id}` - View incident details
  - `PUT /admin/behavior-incidents/{id}` - Update incident details
  - `DELETE /admin/behavior-incidents/{id}` - Delete an incident

- **Reports**
  - `GET /admin/reports` - List all reports
  - `POST /admin/reports` - Generate a new report
  - `GET /admin/reports/{id}` - View report details
  - `DELETE /admin/reports/{id}` - Delete a report