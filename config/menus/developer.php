<?php

return [
    [
        'id' => 'academics_group',
        'label' => ['en' => 'Academics Routes', 'ar' => 'مسارات Academics'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'academics_subjects_index',
                'label' => ['en' => 'Academics Subjects Index', 'ar' => 'Academics Subjects Index'],
                'route' => 'academics.subjects.index',
                'icon' => 'link',
             ],
             [
                'id' => 'academics_subjects_create',
                'label' => ['en' => 'Academics Subjects Create', 'ar' => 'Academics Subjects Create'],
                'route' => 'academics.subjects.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'acadimy_group',
        'label' => ['en' => 'Acadimy Routes', 'ar' => 'مسارات Acadimy'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'acadimy_admin_users_index',
                'label' => ['en' => 'Acadimy Admin Users Index', 'ar' => 'Acadimy Admin Users Index'],
                'route' => 'acadimy.admin.users.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'admin_group',
        'label' => ['en' => 'Admin Routes', 'ar' => 'مسارات Admin'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'admin_academic_calendar_index',
                'label' => ['en' => 'Admin Academic_Calendar Index', 'ar' => 'Admin Academic_Calendar Index'],
                'route' => 'admin.academic_calendar.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_academic_calendar_export_template',
                'label' => ['en' => 'Admin Academic_Calendar Export_Template', 'ar' => 'Admin Academic_Calendar Export_Template'],
                'route' => 'admin.academic_calendar.export_template',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_academic-year_index',
                'label' => ['en' => 'Admin Academic-Year Index', 'ar' => 'Admin Academic-Year Index'],
                'route' => 'admin.academic-year.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_academic-year_create',
                'label' => ['en' => 'Admin Academic-Year Create', 'ar' => 'Admin Academic-Year Create'],
                'route' => 'admin.academic-year.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_academic-year_export',
                'label' => ['en' => 'Admin Academic-Year Export', 'ar' => 'Admin Academic-Year Export'],
                'route' => 'admin.academic-year.export',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_behaviors',
                'label' => ['en' => 'Admin Behaviors', 'ar' => 'Admin Behaviors'],
                'route' => 'admin.behaviors',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_calendar_index',
                'label' => ['en' => 'Admin Calendar Index', 'ar' => 'Admin Calendar Index'],
                'route' => 'admin.calendar.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_calendar_create',
                'label' => ['en' => 'Admin Calendar Create', 'ar' => 'Admin Calendar Create'],
                'route' => 'admin.calendar.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom_index',
                'label' => ['en' => 'Admin Classroom Index', 'ar' => 'Admin Classroom Index'],
                'route' => 'admin.classroom.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom-subject-teacher_index',
                'label' => ['en' => 'Admin Classroom-Subject-Teacher Index', 'ar' => 'Admin Classroom-Subject-Teacher Index'],
                'route' => 'admin.classroom-subject-teacher.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom-subject-teacher_create',
                'label' => ['en' => 'Admin Classroom-Subject-Teacher Create', 'ar' => 'Admin Classroom-Subject-Teacher Create'],
                'route' => 'admin.classroom-subject-teacher.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom-subject-teachers_import-page',
                'label' => ['en' => 'Admin Classroom-Subject-Teachers Import-Page', 'ar' => 'Admin Classroom-Subject-Teachers Import-Page'],
                'route' => 'admin.classroom-subject-teachers.import-page',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom_create',
                'label' => ['en' => 'Admin Classroom Create', 'ar' => 'Admin Classroom Create'],
                'route' => 'admin.classroom.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_classroom_export',
                'label' => ['en' => 'Admin Classroom Export', 'ar' => 'Admin Classroom Export'],
                'route' => 'admin.classroom.export',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_curriculum_index',
                'label' => ['en' => 'Admin Curriculum Index', 'ar' => 'Admin Curriculum Index'],
                'route' => 'admin.curriculum.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_curriculum_create',
                'label' => ['en' => 'Admin Curriculum Create', 'ar' => 'Admin Curriculum Create'],
                'route' => 'admin.curriculum.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_curriculum_management',
                'label' => ['en' => 'Admin Curriculum Management', 'ar' => 'Admin Curriculum Management'],
                'route' => 'admin.curriculum.management',
                'icon' => 'link',
             ],
             [
                'id' => 'documentation_index',
                'label' => ['en' => 'Documentation Index', 'ar' => 'Documentation Index'],
                'route' => 'documentation.index',
                'icon' => 'link',
             ],
             [
                'id' => 'documentation-portal_index',
                'label' => ['en' => 'Documentation-Portal Index', 'ar' => 'Documentation-Portal Index'],
                'route' => 'documentation-portal.index',
                'icon' => 'link',
             ],
             [
                'id' => 'documentation-portal_file-content',
                'label' => ['en' => 'Documentation-Portal File-Content', 'ar' => 'Documentation-Portal File-Content'],
                'route' => 'documentation-portal.file-content',
                'icon' => 'link',
             ],
             [
                'id' => 'documentation-portal_search',
                'label' => ['en' => 'Documentation-Portal Search', 'ar' => 'Documentation-Portal Search'],
                'route' => 'documentation-portal.search',
                'icon' => 'link',
             ],
             [
                'id' => 'documentation_create',
                'label' => ['en' => 'Documentation Create', 'ar' => 'Documentation Create'],
                'route' => 'documentation.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_grade_index',
                'label' => ['en' => 'Admin Grade Index', 'ar' => 'Admin Grade Index'],
                'route' => 'admin.grade.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_grade-subject_index',
                'label' => ['en' => 'Admin Grade-Subject Index', 'ar' => 'Admin Grade-Subject Index'],
                'route' => 'admin.grade-subject.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_grade-subject_create',
                'label' => ['en' => 'Admin Grade-Subject Create', 'ar' => 'Admin Grade-Subject Create'],
                'route' => 'admin.grade-subject.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_grade_create',
                'label' => ['en' => 'Admin Grade Create', 'ar' => 'Admin Grade Create'],
                'route' => 'admin.grade.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_hr_index',
                'label' => ['en' => 'Admin Hr Index', 'ar' => 'Admin Hr Index'],
                'route' => 'admin.hr.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_hr_create',
                'label' => ['en' => 'Admin Hr Create', 'ar' => 'Admin Hr Create'],
                'route' => 'admin.hr.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_hr_setup-wizard',
                'label' => ['en' => 'Admin Hr Setup-Wizard', 'ar' => 'Admin Hr Setup-Wizard'],
                'route' => 'admin.hr.setup-wizard',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_hr_setup-wizard_default-data',
                'label' => ['en' => 'Admin Hr Setup-Wizard Default-Data', 'ar' => 'Admin Hr Setup-Wizard Default-Data'],
                'route' => 'admin.hr.setup-wizard.default-data',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_hr_my-schools_index',
                'label' => ['en' => 'Admin Hr My-Schools Index', 'ar' => 'Admin Hr My-Schools Index'],
                'route' => 'admin.hr.my-schools.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_period-details_index',
                'label' => ['en' => 'Admin Period-Details Index', 'ar' => 'Admin Period-Details Index'],
                'route' => 'admin.period-details.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_period-details_create',
                'label' => ['en' => 'Admin Period-Details Create', 'ar' => 'Admin Period-Details Create'],
                'route' => 'admin.period-details.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_question-banks_index',
                'label' => ['en' => 'Admin Question-Banks Index', 'ar' => 'Admin Question-Banks Index'],
                'route' => 'admin.question-banks.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_question-banks_create',
                'label' => ['en' => 'Admin Question-Banks Create', 'ar' => 'Admin Question-Banks Create'],
                'route' => 'admin.question-banks.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_roles_index',
                'label' => ['en' => 'Admin Roles Index', 'ar' => 'Admin Roles Index'],
                'route' => 'admin.roles.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedule-dailies_index',
                'label' => ['en' => 'Admin Schedule-Dailies Index', 'ar' => 'Admin Schedule-Dailies Index'],
                'route' => 'admin.schedule-dailies.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedule-dailies_create',
                'label' => ['en' => 'Admin Schedule-Dailies Create', 'ar' => 'Admin Schedule-Dailies Create'],
                'route' => 'admin.schedule-dailies.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedule-data',
                'label' => ['en' => 'Admin Schedule-Data', 'ar' => 'Admin Schedule-Data'],
                'route' => 'admin.schedule-data',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedules_load_data',
                'label' => ['en' => 'Admin Schedules Load_Data', 'ar' => 'Admin Schedules Load_Data'],
                'route' => 'admin.schedules.load_data',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedules_new',
                'label' => ['en' => 'Admin Schedules New', 'ar' => 'Admin Schedules New'],
                'route' => 'admin.schedules.new',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedules_index',
                'label' => ['en' => 'Admin Schedules Index', 'ar' => 'Admin Schedules Index'],
                'route' => 'admin.schedules.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedules_create',
                'label' => ['en' => 'Admin Schedules Create', 'ar' => 'Admin Schedules Create'],
                'route' => 'admin.schedules.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_schedules_dashboard',
                'label' => ['en' => 'Admin Schedules Dashboard', 'ar' => 'Admin Schedules Dashboard'],
                'route' => 'admin.schedules.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_school_index',
                'label' => ['en' => 'Admin School Index', 'ar' => 'Admin School Index'],
                'route' => 'admin.school.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_school-branding_admin_school-branding_index',
                'label' => ['en' => 'Admin School-Branding Admin School-Branding Index', 'ar' => 'Admin School-Branding Admin School-Branding Index'],
                'route' => 'admin.school-branding.admin.school-branding.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_school_create',
                'label' => ['en' => 'Admin School Create', 'ar' => 'Admin School Create'],
                'route' => 'admin.school.create',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::hfR8lv7frz6YSBLV',
                'label' => ['en' => 'Generated::Hfr8Lv7Frz6Ysblv', 'ar' => 'Generated::Hfr8Lv7Frz6Ysblv'],
                'route' => 'generated::hfR8lv7frz6YSBLV',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_semester_index',
                'label' => ['en' => 'Admin Semester Index', 'ar' => 'Admin Semester Index'],
                'route' => 'admin.semester.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_semester-test_index',
                'label' => ['en' => 'Admin Semester-Test Index', 'ar' => 'Admin Semester-Test Index'],
                'route' => 'admin.semester-test.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_semester-test_create',
                'label' => ['en' => 'Admin Semester-Test Create', 'ar' => 'Admin Semester-Test Create'],
                'route' => 'admin.semester-test.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_semester_create',
                'label' => ['en' => 'Admin Semester Create', 'ar' => 'Admin Semester Create'],
                'route' => 'admin.semester.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_skills_index',
                'label' => ['en' => 'Admin Skills Index', 'ar' => 'Admin Skills Index'],
                'route' => 'admin.skills.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_skills_categories_index',
                'label' => ['en' => 'Admin Skills Categories Index', 'ar' => 'Admin Skills Categories Index'],
                'route' => 'admin.skills.categories.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_skills_manage-questions',
                'label' => ['en' => 'Admin Skills Manage-Questions', 'ar' => 'Admin Skills Manage-Questions'],
                'route' => 'admin.skills.manage-questions',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_skills_skills_index',
                'label' => ['en' => 'Admin Skills Skills Index', 'ar' => 'Admin Skills Skills Index'],
                'route' => 'admin.skills.skills.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_stage_index',
                'label' => ['en' => 'Admin Stage Index', 'ar' => 'Admin Stage Index'],
                'route' => 'admin.stage.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_stage_create',
                'label' => ['en' => 'Admin Stage Create', 'ar' => 'Admin Stage Create'],
                'route' => 'admin.stage.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_student-parent_index',
                'label' => ['en' => 'Admin Student-Parent Index', 'ar' => 'Admin Student-Parent Index'],
                'route' => 'admin.student-parent.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_student-parent_create',
                'label' => ['en' => 'Admin Student-Parent Create', 'ar' => 'Admin Student-Parent Create'],
                'route' => 'admin.student-parent.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_student-parent_export',
                'label' => ['en' => 'Admin Student-Parent Export', 'ar' => 'Admin Student-Parent Export'],
                'route' => 'admin.student-parent.export',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_index',
                'label' => ['en' => 'Admin Students Index', 'ar' => 'Admin Students Index'],
                'route' => 'admin.students.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_classroom-mapping-suggestions',
                'label' => ['en' => 'Admin Students Classroom-Mapping-Suggestions', 'ar' => 'Admin Students Classroom-Mapping-Suggestions'],
                'route' => 'admin.students.classroom-mapping-suggestions',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_create',
                'label' => ['en' => 'Admin Students Create', 'ar' => 'Admin Students Create'],
                'route' => 'admin.students.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_download-template',
                'label' => ['en' => 'Admin Students Download-Template', 'ar' => 'Admin Students Download-Template'],
                'route' => 'admin.students.download-template',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_download-template-with-classroom',
                'label' => ['en' => 'Admin Students Download-Template-With-Classroom', 'ar' => 'Admin Students Download-Template-With-Classroom'],
                'route' => 'admin.students.download-template-with-classroom',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_students_filtered',
                'label' => ['en' => 'Admin Students Filtered', 'ar' => 'Admin Students Filtered'],
                'route' => 'admin.students.filtered',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_subject_index',
                'label' => ['en' => 'Admin Subject Index', 'ar' => 'Admin Subject Index'],
                'route' => 'admin.subject.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_subject_create',
                'label' => ['en' => 'Admin Subject Create', 'ar' => 'Admin Subject Create'],
                'route' => 'admin.subject.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_teacher_index',
                'label' => ['en' => 'Admin Teacher Index', 'ar' => 'Admin Teacher Index'],
                'route' => 'admin.teacher.index',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_teacher_create',
                'label' => ['en' => 'Admin Teacher Create', 'ar' => 'Admin Teacher Create'],
                'route' => 'admin.teacher.create',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_teacher_export',
                'label' => ['en' => 'Admin Teacher Export', 'ar' => 'Admin Teacher Export'],
                'route' => 'admin.teacher.export',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_',
                'label' => ['en' => 'Admin ', 'ar' => 'Admin '],
                'route' => 'admin.',
                'icon' => 'link',
             ],
             [
                'id' => 'teachers_import',
                'label' => ['en' => 'Teachers Import', 'ar' => 'Teachers Import'],
                'route' => 'teachers.import',
                'icon' => 'link',
             ],
             [
                'id' => 'teachers_import_schools',
                'label' => ['en' => 'Teachers Import Schools', 'ar' => 'Teachers Import Schools'],
                'route' => 'teachers.import.schools',
                'icon' => 'link',
             ],
             [
                'id' => 'admin_user_management',
                'label' => ['en' => 'Admin User_Management', 'ar' => 'Admin User_Management'],
                'route' => 'admin.user_management',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'all_classes_group',
        'label' => ['en' => 'All_classes Routes', 'ar' => 'مسارات All_classes'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::HlFiqwe7dbe4Xdg8',
                'label' => ['en' => 'Generated::Hlfiqwe7Dbe4Xdg8', 'ar' => 'Generated::Hlfiqwe7Dbe4Xdg8'],
                'route' => 'generated::HlFiqwe7dbe4Xdg8',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'all_subjects_group',
        'label' => ['en' => 'All_subjects Routes', 'ar' => 'مسارات All_subjects'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::FYS2QJ0KHDgOfFI0',
                'label' => ['en' => 'Generated::Fys2Qj0Khdgoffi0', 'ar' => 'Generated::Fys2Qj0Khdgoffi0'],
                'route' => 'generated::FYS2QJ0KHDgOfFI0',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'all_teachers_group',
        'label' => ['en' => 'All_teachers Routes', 'ar' => 'مسارات All_teachers'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::xachaVfzW6LOdqSQ',
                'label' => ['en' => 'Generated::Xachavfzw6Lodqsq', 'ar' => 'Generated::Xachavfzw6Lodqsq'],
                'route' => 'generated::xachaVfzW6LOdqSQ',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'all_teachers_with_classroom_subject_group',
        'label' => ['en' => 'All_teachers_with_classroom_subject Routes', 'ar' => 'مسارات All_teachers_with_classroom_subject'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::8G4w9D5HpdPEeOon',
                'label' => ['en' => 'Generated::8G4W9D5Hpdpeeoon', 'ar' => 'Generated::8G4W9D5Hpdpeeoon'],
                'route' => 'generated::8G4w9D5HpdPEeOon',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'api_group',
        'label' => ['en' => 'Api Routes', 'ar' => 'مسارات Api'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::WcqXQFZTT3FNsQgI',
                'label' => ['en' => 'Generated::Wcqxqfztt3Fnsqgi', 'ar' => 'Generated::Wcqxqfztt3Fnsqgi'],
                'route' => 'generated::WcqXQFZTT3FNsQgI',
                'icon' => 'link',
             ],
             [
                'id' => 'api_academics_subjects_index',
                'label' => ['en' => 'Api Academics Subjects Index', 'ar' => 'Api Academics Subjects Index'],
                'route' => 'api.academics.subjects.index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::PMWJxGvoyLRrNL7u',
                'label' => ['en' => 'Generated::Pmwjxgvoylrrnl7U', 'ar' => 'Generated::Pmwjxgvoylrrnl7U'],
                'route' => 'generated::PMWJxGvoyLRrNL7u',
                'icon' => 'link',
             ],
             [
                'id' => 'behavior-incidents_index',
                'label' => ['en' => 'Behavior-Incidents Index', 'ar' => 'Behavior-Incidents Index'],
                'route' => 'behavior-incidents.index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::9RepzsWsQyp4zZaG',
                'label' => ['en' => 'Generated::9Repzswsqyp4Zzag', 'ar' => 'Generated::9Repzswsqyp4Zzag'],
                'route' => 'generated::9RepzsWsQyp4zZaG',
                'icon' => 'link',
             ],
             [
                'id' => 'chatbot_history',
                'label' => ['en' => 'Chatbot History', 'ar' => 'Chatbot History'],
                'route' => 'chatbot.history',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::jWdGmkpqiOPPEwm4',
                'label' => ['en' => 'Generated::Jwdgmkpqioppewm4', 'ar' => 'Generated::Jwdgmkpqioppewm4'],
                'route' => 'generated::jWdGmkpqiOPPEwm4',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::ZPvAqMeC5vlFPbaM',
                'label' => ['en' => 'Generated::Zpvaqmec5Vlfpbam', 'ar' => 'Generated::Zpvaqmec5Vlfpbam'],
                'route' => 'generated::ZPvAqMeC5vlFPbaM',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::VAoN1yugKmNvlkjZ',
                'label' => ['en' => 'Generated::Vaon1Yugkmnvlkjz', 'ar' => 'Generated::Vaon1Yugkmnvlkjz'],
                'route' => 'generated::VAoN1yugKmNvlkjZ',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::CXv18qQMeeOdUD5o',
                'label' => ['en' => 'Generated::Cxv18Qqmeeodud5O', 'ar' => 'Generated::Cxv18Qqmeeodud5O'],
                'route' => 'generated::CXv18qQMeeOdUD5o',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::QmqrMHqMvzUfuYi6',
                'label' => ['en' => 'Generated::Qmqrmhqmvzufuyi6', 'ar' => 'Generated::Qmqrmhqmvzufuyi6'],
                'route' => 'generated::QmqrMHqMvzUfuYi6',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::hZCJc1mjms4fYgtm',
                'label' => ['en' => 'Generated::Hzcjc1Mjms4Fygtm', 'ar' => 'Generated::Hzcjc1Mjms4Fygtm'],
                'route' => 'generated::hZCJc1mjms4fYgtm',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::HuRe0hWowYZuBcrq',
                'label' => ['en' => 'Generated::Hure0Hwowyzubcrq', 'ar' => 'Generated::Hure0Hwowyzubcrq'],
                'route' => 'generated::HuRe0hWowYZuBcrq',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-plan-templates_index',
                'label' => ['en' => 'Lesson-Plan-Templates Index', 'ar' => 'Lesson-Plan-Templates Index'],
                'route' => 'lesson-plan-templates.index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::Yqii5igLUfVMxZ3l',
                'label' => ['en' => 'Generated::Yqii5Iglufvmxz3L', 'ar' => 'Generated::Yqii5Iglufvmxz3L'],
                'route' => 'generated::Yqii5igLUfVMxZ3l',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::1fkgrnVh1h9rxzM0',
                'label' => ['en' => 'Generated::1Fkgrnvh1H9Rxzm0', 'ar' => 'Generated::1Fkgrnvh1H9Rxzm0'],
                'route' => 'generated::1fkgrnVh1h9rxzM0',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::KBrhNDtKy5l19pCK',
                'label' => ['en' => 'Generated::Kbrhndtky5L19Pck', 'ar' => 'Generated::Kbrhndtky5L19Pck'],
                'route' => 'generated::KBrhNDtKy5l19pCK',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::RH5StoyZa3x0usoO',
                'label' => ['en' => 'Generated::Rh5Stoyza3X0Usoo', 'ar' => 'Generated::Rh5Stoyza3X0Usoo'],
                'route' => 'generated::RH5StoyZa3x0usoO',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::QpKjO0l1ctegZwI8',
                'label' => ['en' => 'Generated::Qpkjo0L1Ctegzwi8', 'ar' => 'Generated::Qpkjo0L1Ctegzwi8'],
                'route' => 'generated::QpKjO0l1ctegZwI8',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::04xjMCDf9COqbN95',
                'label' => ['en' => 'Generated::04Xjmcdf9Coqbn95', 'ar' => 'Generated::04Xjmcdf9Coqbn95'],
                'route' => 'generated::04xjMCDf9COqbN95',
                'icon' => 'link',
             ],
             [
                'id' => 'index',
                'label' => ['en' => 'Index', 'ar' => 'Index'],
                'route' => 'index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::Ju7mG6WFpX14Xf3F',
                'label' => ['en' => 'Generated::Ju7Mg6Wfpx14Xf3F', 'ar' => 'Generated::Ju7Mg6Wfpx14Xf3F'],
                'route' => 'generated::Ju7mG6WFpX14Xf3F',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::5Qdxf5MGZAAwyjUM',
                'label' => ['en' => 'Generated::5Qdxf5Mgzaawyjum', 'ar' => 'Generated::5Qdxf5Mgzaawyjum'],
                'route' => 'generated::5Qdxf5MGZAAwyjUM',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::Jw0wlb3U2Q3ubZWx',
                'label' => ['en' => 'Generated::Jw0Wlb3U2Q3Ubzwx', 'ar' => 'Generated::Jw0Wlb3U2Q3Ubzwx'],
                'route' => 'generated::Jw0wlb3U2Q3ubZWx',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::NKz2xNfftVHNokeS',
                'label' => ['en' => 'Generated::Nkz2Xnfftvhnokes', 'ar' => 'Generated::Nkz2Xnfftvhnokes'],
                'route' => 'generated::NKz2xNfftVHNokeS',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::GYEUPdY8mVnnCSmH',
                'label' => ['en' => 'Generated::Gyeupdy8Mvnncsmh', 'ar' => 'Generated::Gyeupdy8Mvnncsmh'],
                'route' => 'generated::GYEUPdY8mVnnCSmH',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::DkCCXtO2GZTr2SWT',
                'label' => ['en' => 'Generated::Dkccxto2Gztr2Swt', 'ar' => 'Generated::Dkccxto2Gztr2Swt'],
                'route' => 'generated::DkCCXtO2GZTr2SWT',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::ZUijU1ekaOrBVHrE',
                'label' => ['en' => 'Generated::Zuiju1Ekaorbvhre', 'ar' => 'Generated::Zuiju1Ekaorbvhre'],
                'route' => 'generated::ZUijU1ekaOrBVHrE',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::kwRP59CnSblZTE0x',
                'label' => ['en' => 'Generated::Kwrp59Cnsblzte0X', 'ar' => 'Generated::Kwrp59Cnsblzte0X'],
                'route' => 'generated::kwRP59CnSblZTE0x',
                'icon' => 'link',
             ],
             [
                'id' => 'questions_index',
                'label' => ['en' => 'Questions Index', 'ar' => 'Questions Index'],
                'route' => 'questions.index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::dIdjWjsN6ZgVFcXO',
                'label' => ['en' => 'Generated::Didjwjsn6Zgvfcxo', 'ar' => 'Generated::Didjwjsn6Zgvfcxo'],
                'route' => 'generated::dIdjWjsN6ZgVFcXO',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::jpHYFw6mYabZv25Y',
                'label' => ['en' => 'Generated::Jphyfw6Myabzv25Y', 'ar' => 'Generated::Jphyfw6Myabzv25Y'],
                'route' => 'generated::jpHYFw6mYabZv25Y',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::WXjBJZHcFllf0BjW',
                'label' => ['en' => 'Generated::Wxjbjzhcfllf0Bjw', 'ar' => 'Generated::Wxjbjzhcfllf0Bjw'],
                'route' => 'generated::WXjBJZHcFllf0BjW',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::LIFjUeZcVwEOW19q',
                'label' => ['en' => 'Generated::Lifjuezcvweow19Q', 'ar' => 'Generated::Lifjuezcvweow19Q'],
                'route' => 'generated::LIFjUeZcVwEOW19q',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::QSWexwep8BkwoRNY',
                'label' => ['en' => 'Generated::Qswexwep8Bkworny', 'ar' => 'Generated::Qswexwep8Bkworny'],
                'route' => 'generated::QSWexwep8BkwoRNY',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::2nn2bqv2vga3F2ZK',
                'label' => ['en' => 'Generated::2Nn2Bqv2Vga3F2Zk', 'ar' => 'Generated::2Nn2Bqv2Vga3F2Zk'],
                'route' => 'generated::2nn2bqv2vga3F2ZK',
                'icon' => 'link',
             ],
             [
                'id' => 'student-behaviors_index',
                'label' => ['en' => 'Student-Behaviors Index', 'ar' => 'Student-Behaviors Index'],
                'route' => 'student-behaviors.index',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::7Avcrf9x3A022acO',
                'label' => ['en' => 'Generated::7Avcrf9X3A022Aco', 'ar' => 'Generated::7Avcrf9X3A022Aco'],
                'route' => 'generated::7Avcrf9x3A022acO',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::WY5O2MN3DY9Vt2E5',
                'label' => ['en' => 'Generated::Wy5O2Mn3Dy9Vt2E5', 'ar' => 'Generated::Wy5O2Mn3Dy9Vt2E5'],
                'route' => 'generated::WY5O2MN3DY9Vt2E5',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::9a7eJpKAOP6FjDNo',
                'label' => ['en' => 'Generated::9A7Ejpkaop6Fjdno', 'ar' => 'Generated::9A7Ejpkaop6Fjdno'],
                'route' => 'generated::9a7eJpKAOP6FjDNo',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::Mh1KtRjn1q24RbQu',
                'label' => ['en' => 'Generated::Mh1Ktrjn1Q24Rbqu', 'ar' => 'Generated::Mh1Ktrjn1Q24Rbqu'],
                'route' => 'generated::Mh1KtRjn1q24RbQu',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::kvKr8JdgWiinuhvg',
                'label' => ['en' => 'Generated::Kvkr8Jdgwiinuhvg', 'ar' => 'Generated::Kvkr8Jdgwiinuhvg'],
                'route' => 'generated::kvKr8JdgWiinuhvg',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::K378Qpxqe7VdjViR',
                'label' => ['en' => 'Generated::K378Qpxqe7Vdjvir', 'ar' => 'Generated::K378Qpxqe7Vdjvir'],
                'route' => 'generated::K378Qpxqe7VdjViR',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::QSqbROM26huwf5lN',
                'label' => ['en' => 'Generated::Qsqbrom26Huwf5Ln', 'ar' => 'Generated::Qsqbrom26Huwf5Ln'],
                'route' => 'generated::QSqbROM26huwf5lN',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::NsBC7EdK6F5RDVNN',
                'label' => ['en' => 'Generated::Nsbc7Edk6F5Rdvnn', 'ar' => 'Generated::Nsbc7Edk6F5Rdvnn'],
                'route' => 'generated::NsBC7EdK6F5RDVNN',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::rUSKU7AXyMLfKiYw',
                'label' => ['en' => 'Generated::Rusku7Axymlfkiyw', 'ar' => 'Generated::Rusku7Axymlfkiyw'],
                'route' => 'generated::rUSKU7AXyMLfKiYw',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'auth_group',
        'label' => ['en' => 'Auth Routes', 'ar' => 'مسارات Auth'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::PqJWNMBkstvj8xyk',
                'label' => ['en' => 'Generated::Pqjwnmbkstvj8Xyk', 'ar' => 'Generated::Pqjwnmbkstvj8Xyk'],
                'route' => 'generated::PqJWNMBkstvj8xyk',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'barcode-scanner_group',
        'label' => ['en' => 'Barcode-scanner Routes', 'ar' => 'مسارات Barcode-scanner'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'barcode-scanner',
                'label' => ['en' => 'Barcode-Scanner', 'ar' => 'Barcode-Scanner'],
                'route' => 'barcode-scanner',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'classroom-subject-teacher_group',
        'label' => ['en' => 'Classroom-subject-teacher Routes', 'ar' => 'مسارات Classroom-subject-teacher'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'classroom-subject-teacher_import-page',
                'label' => ['en' => 'Classroom-Subject-Teacher Import-Page', 'ar' => 'Classroom-Subject-Teacher Import-Page'],
                'route' => 'classroom-subject-teacher.import-page',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'conversations_group',
        'label' => ['en' => 'Conversations Routes', 'ar' => 'مسارات Conversations'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'conversations_index',
                'label' => ['en' => 'Conversations Index', 'ar' => 'Conversations Index'],
                'route' => 'conversations.index',
                'icon' => 'link',
             ],
             [
                'id' => 'conversations_create',
                'label' => ['en' => 'Conversations Create', 'ar' => 'Conversations Create'],
                'route' => 'conversations.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'course-management_group',
        'label' => ['en' => 'Course-management Routes', 'ar' => 'مسارات Course-management'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'course-management_api_courses_with-structure',
                'label' => ['en' => 'Course-Management Api Courses With-Structure', 'ar' => 'Course-Management Api Courses With-Structure'],
                'route' => 'course-management.api.courses.with-structure',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_api_teacher_courses',
                'label' => ['en' => 'Course-Management Api Teacher Courses', 'ar' => 'Course-Management Api Teacher Courses'],
                'route' => 'course-management.api.teacher.courses',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_courses_index',
                'label' => ['en' => 'Course-Management Courses Index', 'ar' => 'Course-Management Courses Index'],
                'route' => 'course-management.courses.index',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_courses_create',
                'label' => ['en' => 'Course-Management Courses Create', 'ar' => 'Course-Management Courses Create'],
                'route' => 'course-management.courses.create',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_import_index',
                'label' => ['en' => 'Course-Management Import Index', 'ar' => 'Course-Management Import Index'],
                'route' => 'course-management.import.index',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_import_template',
                'label' => ['en' => 'Course-Management Import Template', 'ar' => 'Course-Management Import Template'],
                'route' => 'course-management.import.template',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_teachers_index',
                'label' => ['en' => 'Course-Management Teachers Index', 'ar' => 'Course-Management Teachers Index'],
                'route' => 'course-management.teachers.index',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_teachers_assign-by-course',
                'label' => ['en' => 'Course-Management Teachers Assign-By-Course', 'ar' => 'Course-Management Teachers Assign-By-Course'],
                'route' => 'course-management.teachers.assign-by-course',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_teachers_assign-by-teacher',
                'label' => ['en' => 'Course-Management Teachers Assign-By-Teacher', 'ar' => 'Course-Management Teachers Assign-By-Teacher'],
                'route' => 'course-management.teachers.assign-by-teacher',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_teachers_dashboard',
                'label' => ['en' => 'Course-Management Teachers Dashboard', 'ar' => 'Course-Management Teachers Dashboard'],
                'route' => 'course-management.teachers.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'course-management_teachers_preview-course',
                'label' => ['en' => 'Course-Management Teachers Preview-Course', 'ar' => 'Course-Management Teachers Preview-Course'],
                'route' => 'course-management.teachers.preview-course',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'courses_group',
        'label' => ['en' => 'Courses Routes', 'ar' => 'مسارات Courses'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'courses',
                'label' => ['en' => 'Courses', 'ar' => 'Courses'],
                'route' => 'courses',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'ct_group',
        'label' => ['en' => 'Ct Routes', 'ar' => 'مسارات Ct'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'ct',
                'label' => ['en' => 'Ct', 'ar' => 'Ct'],
                'route' => 'ct',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'daily-planner_group',
        'label' => ['en' => 'Daily-planner Routes', 'ar' => 'مسارات Daily-planner'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dp_daily_index',
                'label' => ['en' => 'Dp Daily Index', 'ar' => 'Dp Daily Index'],
                'route' => 'dp.daily.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'dashboard_group',
        'label' => ['en' => 'Dashboard Routes', 'ar' => 'مسارات Dashboard'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dashboard',
                'label' => ['en' => 'Dashboard', 'ar' => 'Dashboard'],
                'route' => 'dashboard',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'developer_group',
        'label' => ['en' => 'Developer Routes', 'ar' => 'مسارات Developer'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'developer_menu',
                'label' => ['en' => 'Developer Menu', 'ar' => 'Developer Menu'],
                'route' => 'developer.menu',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_SpeechRecognition',
                'label' => ['en' => 'Developer Speechrecognition', 'ar' => 'Developer Speechrecognition'],
                'route' => 'developer.SpeechRecognition',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_myproject-tasks',
                'label' => ['en' => 'Developer Myproject-Tasks', 'ar' => 'Developer Myproject-Tasks'],
                'route' => 'developer.myproject-tasks',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_pomodoro_recent',
                'label' => ['en' => 'Developer Pomodoro Recent', 'ar' => 'Developer Pomodoro Recent'],
                'route' => 'developer.pomodoro.recent',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_pomodoro_stats',
                'label' => ['en' => 'Developer Pomodoro Stats', 'ar' => 'Developer Pomodoro Stats'],
                'route' => 'developer.pomodoro.stats',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_project-tasks',
                'label' => ['en' => 'Developer Project-Tasks', 'ar' => 'Developer Project-Tasks'],
                'route' => 'developer.project-tasks',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_',
                'label' => ['en' => 'Developer ', 'ar' => 'Developer '],
                'route' => 'developer.',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_resume-system',
                'label' => ['en' => 'Developer Resume-System', 'ar' => 'Developer Resume-System'],
                'route' => 'developer.resume-system',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_system-routes',
                'label' => ['en' => 'Developer System-Routes', 'ar' => 'Developer System-Routes'],
                'route' => 'developer.system-routes',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_tasks_get',
                'label' => ['en' => 'Developer Tasks Get', 'ar' => 'Developer Tasks Get'],
                'route' => 'developer.tasks.get',
                'icon' => 'link',
             ],
             [
                'id' => 'developer_ticktick',
                'label' => ['en' => 'Developer Ticktick', 'ar' => 'Developer Ticktick'],
                'route' => 'developer.ticktick',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'drawing-demo_group',
        'label' => ['en' => 'Drawing-demo Routes', 'ar' => 'مسارات Drawing-demo'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'drawing_demo',
                'label' => ['en' => 'Drawing Demo', 'ar' => 'Drawing Demo'],
                'route' => 'drawing.demo',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'firebase-test_group',
        'label' => ['en' => 'Firebase-test Routes', 'ar' => 'مسارات Firebase-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'firebase_test',
                'label' => ['en' => 'Firebase Test', 'ar' => 'Firebase Test'],
                'route' => 'firebase.test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'forgot-password_group',
        'label' => ['en' => 'Forgot-password Routes', 'ar' => 'مسارات Forgot-password'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'password_request',
                'label' => ['en' => 'Password Request', 'ar' => 'Password Request'],
                'route' => 'password.request',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'gamification_group',
        'label' => ['en' => 'Gamification Routes', 'ar' => 'مسارات Gamification'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dp_gamification_index',
                'label' => ['en' => 'Dp Gamification Index', 'ar' => 'Dp Gamification Index'],
                'route' => 'dp.gamification.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'get-google-courses_group',
        'label' => ['en' => 'Get-google-courses Routes', 'ar' => 'مسارات Get-google-courses'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::Cl8iZuRiooR7BTpa',
                'label' => ['en' => 'Generated::Cl8Izurioor7Btpa', 'ar' => 'Generated::Cl8Izurioor7Btpa'],
                'route' => 'generated::Cl8iZuRiooR7BTpa',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'get_json_test_group',
        'label' => ['en' => 'Get_json_test Routes', 'ar' => 'مسارات Get_json_test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'get_json_test',
                'label' => ['en' => 'Get_Json_Test', 'ar' => 'Get_Json_Test'],
                'route' => 'get_json_test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'import_students_group',
        'label' => ['en' => 'Import_students Routes', 'ar' => 'مسارات Import_students'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'import_students_import_index',
                'label' => ['en' => 'Import_Students Import Index', 'ar' => 'Import_Students Import Index'],
                'route' => 'import_students.import.index',
                'icon' => 'link',
             ],
             [
                'id' => 'import_students_import_template',
                'label' => ['en' => 'Import_Students Import Template', 'ar' => 'Import_Students Import Template'],
                'route' => 'import_students.import.template',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'jsontablebuilder_group',
        'label' => ['en' => 'Jsontablebuilder Routes', 'ar' => 'مسارات Jsontablebuilder'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'JsonTableBuilder',
                'label' => ['en' => 'Jsontablebuilder', 'ar' => 'Jsontablebuilder'],
                'route' => 'JsonTableBuilder',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'lesson-presentation_group',
        'label' => ['en' => 'Lesson-presentation Routes', 'ar' => 'مسارات Lesson-presentation'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'lesson-presentation_index',
                'label' => ['en' => 'Lesson-Presentation Index', 'ar' => 'Lesson-Presentation Index'],
                'route' => 'lesson-presentation.index',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_edit',
                'label' => ['en' => 'Lesson-Presentation Edit', 'ar' => 'Lesson-Presentation Edit'],
                'route' => 'lesson-presentation.edit',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_list',
                'label' => ['en' => 'Lesson-Presentation List', 'ar' => 'Lesson-Presentation List'],
                'route' => 'lesson-presentation.list',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_section-template-manager',
                'label' => ['en' => 'Lesson-Presentation Section-Template-Manager', 'ar' => 'Lesson-Presentation Section-Template-Manager'],
                'route' => 'lesson-presentation.section-template-manager',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_section-templates_index',
                'label' => ['en' => 'Lesson-Presentation Section-Templates Index', 'ar' => 'Lesson-Presentation Section-Templates Index'],
                'route' => 'lesson-presentation.section-templates.index',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_student_lessons',
                'label' => ['en' => 'Lesson-Presentation Student Lessons', 'ar' => 'Lesson-Presentation Student Lessons'],
                'route' => 'lesson-presentation.student.lessons',
                'icon' => 'link',
             ],
             [
                'id' => 'lesson-presentation_teacher_grades',
                'label' => ['en' => 'Lesson-Presentation Teacher Grades', 'ar' => 'Lesson-Presentation Teacher Grades'],
                'route' => 'lesson-presentation.teacher.grades',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'live-focus_group',
        'label' => ['en' => 'Live-focus Routes', 'ar' => 'مسارات Live-focus'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dp_focus_index',
                'label' => ['en' => 'Dp Focus Index', 'ar' => 'Dp Focus Index'],
                'route' => 'dp.focus.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'login_group',
        'label' => ['en' => 'Login Routes', 'ar' => 'مسارات Login'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'login',
                'label' => ['en' => 'Login', 'ar' => 'Login'],
                'route' => 'login',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'master-schedule_group',
        'label' => ['en' => 'Master-schedule Routes', 'ar' => 'مسارات Master-schedule'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dp_master_index',
                'label' => ['en' => 'Dp Master Index', 'ar' => 'Dp Master Index'],
                'route' => 'dp.master.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'micro-component-test_group',
        'label' => ['en' => 'Micro-component-test Routes', 'ar' => 'مسارات Micro-component-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'micro-component-test_public',
                'label' => ['en' => 'Micro-Component-Test Public', 'ar' => 'Micro-Component-Test Public'],
                'route' => 'micro-component-test.public',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'my_classes_group',
        'label' => ['en' => 'My_classes Routes', 'ar' => 'مسارات My_classes'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::V3cxecDVo86PnOf4',
                'label' => ['en' => 'Generated::V3Cxecdvo86Pnof4', 'ar' => 'Generated::V3Cxecdvo86Pnof4'],
                'route' => 'generated::V3cxecDVo86PnOf4',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'my_classes_with_students_group',
        'label' => ['en' => 'My_classes_with_students Routes', 'ar' => 'مسارات My_classes_with_students'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::vMXegCG9gvwo0Ei2',
                'label' => ['en' => 'Generated::Vmxegcg9Gvwo0Ei2', 'ar' => 'Generated::Vmxegcg9Gvwo0Ei2'],
                'route' => 'generated::vMXegCG9gvwo0Ei2',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'my_data_group',
        'label' => ['en' => 'My_data Routes', 'ar' => 'مسارات My_data'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::iogleoOuIjPo6rf8',
                'label' => ['en' => 'Generated::Iogleoouijpo6Rf8', 'ar' => 'Generated::Iogleoouijpo6Rf8'],
                'route' => 'generated::iogleoOuIjPo6rf8',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'myclass2026_group',
        'label' => ['en' => 'Myclass2026 Routes', 'ar' => 'مسارات Myclass2026'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'parent_communication_messages_index',
                'label' => ['en' => 'Parent Communication Messages Index', 'ar' => 'Parent Communication Messages Index'],
                'route' => 'parent.communication.messages.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_curriculum_courses_index',
                'label' => ['en' => 'School-Admin Curriculum Courses Index', 'ar' => 'School-Admin Curriculum Courses Index'],
                'route' => 'school-admin.curriculum.courses.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_modules_gamification_index',
                'label' => ['en' => 'School-Admin Modules Gamification Index', 'ar' => 'School-Admin Modules Gamification Index'],
                'route' => 'school-admin.modules.gamification.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_modules_skills_index',
                'label' => ['en' => 'School-Admin Modules Skills Index', 'ar' => 'School-Admin Modules Skills Index'],
                'route' => 'school-admin.modules.skills.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_users_parents_index',
                'label' => ['en' => 'School-Admin Users Parents Index', 'ar' => 'School-Admin Users Parents Index'],
                'route' => 'school-admin.users.parents.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_users_students_index',
                'label' => ['en' => 'School-Admin Users Students Index', 'ar' => 'School-Admin Users Students Index'],
                'route' => 'school-admin.users.students.index',
                'icon' => 'link',
             ],
             [
                'id' => 'school-admin_users_teachers_index',
                'label' => ['en' => 'School-Admin Users Teachers Index', 'ar' => 'School-Admin Users Teachers Index'],
                'route' => 'school-admin.users.teachers.index',
                'icon' => 'link',
             ],
             [
                'id' => 'student_communication_messages_index',
                'label' => ['en' => 'Student Communication Messages Index', 'ar' => 'Student Communication Messages Index'],
                'route' => 'student.communication.messages.index',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_communication_messages_index',
                'label' => ['en' => 'Teacher Communication Messages Index', 'ar' => 'Teacher Communication Messages Index'],
                'route' => 'teacher.communication.messages.index',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_planning_daily-tasks_index',
                'label' => ['en' => 'Teacher Planning Daily-Tasks Index', 'ar' => 'Teacher Planning Daily-Tasks Index'],
                'route' => 'teacher.planning.daily-tasks.index',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_planning_weekly-plans_index',
                'label' => ['en' => 'Teacher Planning Weekly-Plans Index', 'ar' => 'Teacher Planning Weekly-Plans Index'],
                'route' => 'teacher.planning.weekly-plans.index',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_presentation_lessons_index',
                'label' => ['en' => 'Teacher Presentation Lessons Index', 'ar' => 'Teacher Presentation Lessons Index'],
                'route' => 'teacher.presentation.lessons.index',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_tools_vocabulary_index',
                'label' => ['en' => 'Teacher Tools Vocabulary Index', 'ar' => 'Teacher Tools Vocabulary Index'],
                'route' => 'teacher.tools.vocabulary.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'network-test_group',
        'label' => ['en' => 'Network-test Routes', 'ar' => 'مسارات Network-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'network_test',
                'label' => ['en' => 'Network Test', 'ar' => 'Network Test'],
                'route' => 'network.test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'notifications_group',
        'label' => ['en' => 'Notifications Routes', 'ar' => 'مسارات Notifications'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'notifications_index',
                'label' => ['en' => 'Notifications Index', 'ar' => 'Notifications Index'],
                'route' => 'notifications.index',
                'icon' => 'link',
             ],
             [
                'id' => 'notifications_settings',
                'label' => ['en' => 'Notifications Settings', 'ar' => 'Notifications Settings'],
                'route' => 'notifications.settings',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'oauth_group',
        'label' => ['en' => 'Oauth Routes', 'ar' => 'مسارات Oauth'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::oHLN6kIBNFZt7OKY',
                'label' => ['en' => 'Generated::Ohln6Kibnfzt7Oky', 'ar' => 'Generated::Ohln6Kibnfzt7Oky'],
                'route' => 'generated::oHLN6kIBNFZt7OKY',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::4Qgzd9NxaVN8X56R',
                'label' => ['en' => 'Generated::4Qgzd9Nxavn8X56R', 'ar' => 'Generated::4Qgzd9Nxavn8X56R'],
                'route' => 'generated::4Qgzd9NxaVN8X56R',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'ocr-comparison_group',
        'label' => ['en' => 'Ocr-comparison Routes', 'ar' => 'مسارات Ocr-comparison'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'ocr-comparison',
                'label' => ['en' => 'Ocr-Comparison', 'ar' => 'Ocr-Comparison'],
                'route' => 'ocr-comparison',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'ocr-test_group',
        'label' => ['en' => 'Ocr-test Routes', 'ar' => 'مسارات Ocr-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'ocr-test',
                'label' => ['en' => 'Ocr-Test', 'ar' => 'Ocr-Test'],
                'route' => 'ocr-test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'offline-test_group',
        'label' => ['en' => 'Offline-test Routes', 'ar' => 'مسارات Offline-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'offline_test_public',
                'label' => ['en' => 'Offline Test Public', 'ar' => 'Offline Test Public'],
                'route' => 'offline.test.public',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'page-test_group',
        'label' => ['en' => 'Page-test Routes', 'ar' => 'مسارات Page-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'page_test',
                'label' => ['en' => 'Page Test', 'ar' => 'Page Test'],
                'route' => 'page.test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'print_html_group',
        'label' => ['en' => 'Print_html Routes', 'ar' => 'مسارات Print_html'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'print_html_index',
                'label' => ['en' => 'Print_Html Index', 'ar' => 'Print_Html Index'],
                'route' => 'print_html.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'private-chat_group',
        'label' => ['en' => 'Private-chat Routes', 'ar' => 'مسارات Private-chat'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'private-chat_index',
                'label' => ['en' => 'Private-Chat Index', 'ar' => 'Private-Chat Index'],
                'route' => 'private-chat.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'puzzle1_group',
        'label' => ['en' => 'Puzzle1 Routes', 'ar' => 'مسارات Puzzle1'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'puzzle1',
                'label' => ['en' => 'Puzzle1', 'ar' => 'Puzzle1'],
                'route' => 'puzzle1',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'qdrat_group',
        'label' => ['en' => 'Qdrat Routes', 'ar' => 'مسارات Qdrat'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'qdrat_lesson-categories_index',
                'label' => ['en' => 'Qdrat Lesson-Categories Index', 'ar' => 'Qdrat Lesson-Categories Index'],
                'route' => 'qdrat.lesson-categories.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_lesson-categories_create',
                'label' => ['en' => 'Qdrat Lesson-Categories Create', 'ar' => 'Qdrat Lesson-Categories Create'],
                'route' => 'qdrat.lesson-categories.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_lessons_index',
                'label' => ['en' => 'Qdrat Lessons Index', 'ar' => 'Qdrat Lessons Index'],
                'route' => 'qdrat.lessons.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_lessons_create',
                'label' => ['en' => 'Qdrat Lessons Create', 'ar' => 'Qdrat Lessons Create'],
                'route' => 'qdrat.lessons.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_question-difficulties_index',
                'label' => ['en' => 'Qdrat Question-Difficulties Index', 'ar' => 'Qdrat Question-Difficulties Index'],
                'route' => 'qdrat.question-difficulties.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_question-difficulties_create',
                'label' => ['en' => 'Qdrat Question-Difficulties Create', 'ar' => 'Qdrat Question-Difficulties Create'],
                'route' => 'qdrat.question-difficulties.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_questions_index',
                'label' => ['en' => 'Qdrat Questions Index', 'ar' => 'Qdrat Questions Index'],
                'route' => 'qdrat.questions.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_questions_create',
                'label' => ['en' => 'Qdrat Questions Create', 'ar' => 'Qdrat Questions Create'],
                'route' => 'qdrat.questions.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_skill-levels_index',
                'label' => ['en' => 'Qdrat Skill-Levels Index', 'ar' => 'Qdrat Skill-Levels Index'],
                'route' => 'qdrat.skill-levels.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_skill-levels_create',
                'label' => ['en' => 'Qdrat Skill-Levels Create', 'ar' => 'Qdrat Skill-Levels Create'],
                'route' => 'qdrat.skill-levels.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_skills_index',
                'label' => ['en' => 'Qdrat Skills Index', 'ar' => 'Qdrat Skills Index'],
                'route' => 'qdrat.skills.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_skills_create',
                'label' => ['en' => 'Qdrat Skills Create', 'ar' => 'Qdrat Skills Create'],
                'route' => 'qdrat.skills.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qdrat_skills_downloadTemplate',
                'label' => ['en' => 'Qdrat Skills Downloadtemplate', 'ar' => 'Qdrat Skills Downloadtemplate'],
                'route' => 'qdrat.skills.downloadTemplate',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'qr-code-generator_group',
        'label' => ['en' => 'Qr-code-generator Routes', 'ar' => 'مسارات Qr-code-generator'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'qr-code-generator',
                'label' => ['en' => 'Qr-Code-Generator', 'ar' => 'Qr-Code-Generator'],
                'route' => 'qr-code-generator',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'qu_group',
        'label' => ['en' => 'Qu Routes', 'ar' => 'مسارات Qu'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'qu_analytics_index',
                'label' => ['en' => 'Qu Analytics Index', 'ar' => 'Qu Analytics Index'],
                'route' => 'qu.analytics.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_exams_index',
                'label' => ['en' => 'Qu Exams Index', 'ar' => 'Qu Exams Index'],
                'route' => 'qu.exams.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_exams_create',
                'label' => ['en' => 'Qu Exams Create', 'ar' => 'Qu Exams Create'],
                'route' => 'qu.exams.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_exams_questions_available',
                'label' => ['en' => 'Qu Exams Questions Available', 'ar' => 'Qu Exams Questions Available'],
                'route' => 'qu.exams.questions.available',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_exams_users_search',
                'label' => ['en' => 'Qu Exams Users Search', 'ar' => 'Qu Exams Users Search'],
                'route' => 'qu.exams.users.search',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_grading_index',
                'label' => ['en' => 'Qu Grading Index', 'ar' => 'Qu Grading Index'],
                'route' => 'qu.grading.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_questions_index',
                'label' => ['en' => 'Qu Questions Index', 'ar' => 'Qu Questions Index'],
                'route' => 'qu.questions.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_questions_create',
                'label' => ['en' => 'Qu Questions Create', 'ar' => 'Qu Questions Create'],
                'route' => 'qu.questions.create',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_quiz-builder',
                'label' => ['en' => 'Qu Quiz-Builder', 'ar' => 'Qu Quiz-Builder'],
                'route' => 'qu.quiz-builder',
                'icon' => 'link',
             ],
             [
                'id' => 'qu_student_exams_index',
                'label' => ['en' => 'Qu Student Exams Index', 'ar' => 'Qu Student Exams Index'],
                'route' => 'qu.student.exams.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'qu-exams_group',
        'label' => ['en' => 'Qu-exams Routes', 'ar' => 'مسارات Qu-exams'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'qu-exams_index',
                'label' => ['en' => 'Qu-Exams Index', 'ar' => 'Qu-Exams Index'],
                'route' => 'qu-exams.index',
                'icon' => 'link',
             ],
             [
                'id' => 'qu-exams_create',
                'label' => ['en' => 'Qu-Exams Create', 'ar' => 'Qu-Exams Create'],
                'route' => 'qu-exams.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'questions_group',
        'label' => ['en' => 'Questions Routes', 'ar' => 'مسارات Questions'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::RuLrXvlTlLpCoyqu',
                'label' => ['en' => 'Generated::Rulrxvltllpcoyqu', 'ar' => 'Generated::Rulrxvltllpcoyqu'],
                'route' => 'generated::RuLrXvlTlLpCoyqu',
                'icon' => 'link',
             ],
             [
                'id' => 'generated::zvtHGQ0HmxhDI7GB',
                'label' => ['en' => 'Generated::Zvthgq0Hmxhdi7Gb', 'ar' => 'Generated::Zvthgq0Hmxhdi7Gb'],
                'route' => 'generated::zvtHGQ0HmxhDI7GB',
                'icon' => 'link',
             ],
             [
                'id' => 'questions_import',
                'label' => ['en' => 'Questions Import', 'ar' => 'Questions Import'],
                'route' => 'questions.import',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'quiz_group',
        'label' => ['en' => 'Quiz Routes', 'ar' => 'مسارات Quiz'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'quiz_live_join',
                'label' => ['en' => 'Quiz Live Join', 'ar' => 'Quiz Live Join'],
                'route' => 'quiz.live.join',
                'icon' => 'link',
             ],
             [
                'id' => 'quiz_live_test',
                'label' => ['en' => 'Quiz Live Test', 'ar' => 'Quiz Live Test'],
                'route' => 'quiz.live.test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'quiz-management_group',
        'label' => ['en' => 'Quiz-management Routes', 'ar' => 'مسارات Quiz-management'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'quiz_management',
                'label' => ['en' => 'Quiz Management', 'ar' => 'Quiz Management'],
                'route' => 'quiz.management',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'quizzes_group',
        'label' => ['en' => 'Quizzes Routes', 'ar' => 'مسارات Quizzes'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'quizzes_index',
                'label' => ['en' => 'Quizzes Index', 'ar' => 'Quizzes Index'],
                'route' => 'quizzes.index',
                'icon' => 'link',
             ],
             [
                'id' => 'quizzes_create',
                'label' => ['en' => 'Quizzes Create', 'ar' => 'Quizzes Create'],
                'route' => 'quizzes.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'realtime-test_group',
        'label' => ['en' => 'Realtime-test Routes', 'ar' => 'مسارات Realtime-test'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'realtime_test',
                'label' => ['en' => 'Realtime Test', 'ar' => 'Realtime Test'],
                'route' => 'realtime.test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'register_group',
        'label' => ['en' => 'Register Routes', 'ar' => 'مسارات Register'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'register',
                'label' => ['en' => 'Register', 'ar' => 'Register'],
                'route' => 'register',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'register-school-admin_group',
        'label' => ['en' => 'Register-school-admin Routes', 'ar' => 'مسارات Register-school-admin'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'register_school_admin',
                'label' => ['en' => 'Register School_Admin', 'ar' => 'Register School_Admin'],
                'route' => 'register.school_admin',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'reports_group',
        'label' => ['en' => 'Reports Routes', 'ar' => 'مسارات Reports'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'dp_reports_index',
                'label' => ['en' => 'Dp Reports Index', 'ar' => 'Dp Reports Index'],
                'route' => 'dp.reports.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'reward-system_group',
        'label' => ['en' => 'Reward-system Routes', 'ar' => 'مسارات Reward-system'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'reward_system_drawing',
                'label' => ['en' => 'Reward System Drawing', 'ar' => 'Reward System Drawing'],
                'route' => 'reward.system.drawing',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'reward_sys_group',
        'label' => ['en' => 'Reward_sys Routes', 'ar' => 'مسارات Reward_sys'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'reward_sys',
                'label' => ['en' => 'Reward_Sys', 'ar' => 'Reward_Sys'],
                'route' => 'reward_sys',
                'icon' => 'link',
             ],
             [
                'id' => 'reward_sys_quiz',
                'label' => ['en' => 'Reward_Sys Quiz', 'ar' => 'Reward_Sys Quiz'],
                'route' => 'reward_sys.quiz',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'root_group',
        'label' => ['en' => 'Root Routes', 'ar' => 'مسارات Root'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'qudrat_landing',
                'label' => ['en' => 'Qudrat Landing', 'ar' => 'Qudrat Landing'],
                'route' => 'qudrat.landing',
                'icon' => 'link',
             ],
             [
                'id' => 'test_qudrat_landing',
                'label' => ['en' => 'Test Qudrat Landing', 'ar' => 'Test Qudrat Landing'],
                'route' => 'test.qudrat.landing',
                'icon' => 'link',
             ],
             [
                'id' => 'LandingPage',
                'label' => ['en' => 'Landingpage', 'ar' => 'Landingpage'],
                'route' => 'LandingPage',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'schedules_group',
        'label' => ['en' => 'Schedules Routes', 'ar' => 'مسارات Schedules'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'schedules_teacher_my-schedule',
                'label' => ['en' => 'Schedules Teacher My-Schedule', 'ar' => 'Schedules Teacher My-Schedule'],
                'route' => 'schedules.teacher.my-schedule',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'science_group',
        'label' => ['en' => 'Science Routes', 'ar' => 'مسارات Science'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'science_micro-component-test',
                'label' => ['en' => 'Science Micro-Component-Test', 'ar' => 'Science Micro-Component-Test'],
                'route' => 'science.micro-component-test',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'sitemap.xml_group',
        'label' => ['en' => 'Sitemap.xml Routes', 'ar' => 'مسارات Sitemap.xml'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::ZABisbhwoFTEODE5',
                'label' => ['en' => 'Generated::Zabisbhwofteode5', 'ar' => 'Generated::Zabisbhwofteode5'],
                'route' => 'generated::ZABisbhwoFTEODE5',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'skill-practice_group',
        'label' => ['en' => 'Skill-practice Routes', 'ar' => 'مسارات Skill-practice'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'skill-practice_awards',
                'label' => ['en' => 'Skill-Practice Awards', 'ar' => 'Skill-Practice Awards'],
                'route' => 'skill-practice.awards',
                'icon' => 'link',
             ],
             [
                'id' => 'skill-practice_categories_index',
                'label' => ['en' => 'Skill-Practice Categories Index', 'ar' => 'Skill-Practice Categories Index'],
                'route' => 'skill-practice.categories.index',
                'icon' => 'link',
             ],
             [
                'id' => 'skill-practice_progress_index',
                'label' => ['en' => 'Skill-Practice Progress Index', 'ar' => 'Skill-Practice Progress Index'],
                'route' => 'skill-practice.progress.index',
                'icon' => 'link',
             ],
             [
                'id' => 'skill-practice_skills_index',
                'label' => ['en' => 'Skill-Practice Skills Index', 'ar' => 'Skill-Practice Skills Index'],
                'route' => 'skill-practice.skills.index',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'student_group',
        'label' => ['en' => 'Student Routes', 'ar' => 'مسارات Student'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'student_attendance',
                'label' => ['en' => 'Student Attendance', 'ar' => 'Student Attendance'],
                'route' => 'student.attendance',
                'icon' => 'link',
             ],
             [
                'id' => 'student_grades',
                'label' => ['en' => 'Student Grades', 'ar' => 'Student Grades'],
                'route' => 'student.grades',
                'icon' => 'link',
             ],
             [
                'id' => 'student_home',
                'label' => ['en' => 'Student Home', 'ar' => 'Student Home'],
                'route' => 'student.home',
                'icon' => 'link',
             ],
             [
                'id' => 'student_schedule_index',
                'label' => ['en' => 'Student Schedule Index', 'ar' => 'Student Schedule Index'],
                'route' => 'student.schedule.index',
                'icon' => 'link',
             ],
             [
                'id' => 'student_schedule_current-week',
                'label' => ['en' => 'Student Schedule Current-Week', 'ar' => 'Student Schedule Current-Week'],
                'route' => 'student.schedule.current-week',
                'icon' => 'link',
             ],
             [
                'id' => 'student_schedule_data',
                'label' => ['en' => 'Student Schedule Data', 'ar' => 'Student Schedule Data'],
                'route' => 'student.schedule.data',
                'icon' => 'link',
             ],
             [
                'id' => 'student_schedule_next-week',
                'label' => ['en' => 'Student Schedule Next-Week', 'ar' => 'Student Schedule Next-Week'],
                'route' => 'student.schedule.next-week',
                'icon' => 'link',
             ],
             [
                'id' => 'student_schedule_print',
                'label' => ['en' => 'Student Schedule Print', 'ar' => 'Student Schedule Print'],
                'route' => 'student.schedule.print',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'student-qr-codes_group',
        'label' => ['en' => 'Student-qr-codes Routes', 'ar' => 'مسارات Student-qr-codes'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'student-qr-codes',
                'label' => ['en' => 'Student-Qr-Codes', 'ar' => 'Student-Qr-Codes'],
                'route' => 'student-qr-codes',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'tablemanager_group',
        'label' => ['en' => 'Tablemanager Routes', 'ar' => 'مسارات Tablemanager'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'TableManager',
                'label' => ['en' => 'Tablemanager', 'ar' => 'Tablemanager'],
                'route' => 'TableManager',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'teacher_group',
        'label' => ['en' => 'Teacher Routes', 'ar' => 'مسارات Teacher'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'teacher_attendance',
                'label' => ['en' => 'Teacher Attendance', 'ar' => 'Teacher Attendance'],
                'route' => 'teacher.attendance',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_classes',
                'label' => ['en' => 'Teacher Classes', 'ar' => 'Teacher Classes'],
                'route' => 'teacher.classes',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_dashboard_classrooms',
                'label' => ['en' => 'Teacher Dashboard Classrooms', 'ar' => 'Teacher Dashboard Classrooms'],
                'route' => 'teacher.dashboard.classrooms',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_getTeacherClasses',
                'label' => ['en' => 'Teacher Getteacherclasses', 'ar' => 'Teacher Getteacherclasses'],
                'route' => 'teacher.getTeacherClasses',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_grades',
                'label' => ['en' => 'Teacher Grades', 'ar' => 'Teacher Grades'],
                'route' => 'teacher.grades',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_home',
                'label' => ['en' => 'Teacher Home', 'ar' => 'Teacher Home'],
                'route' => 'teacher.home',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_lesson_presentation',
                'label' => ['en' => 'Teacher Lesson_Presentation', 'ar' => 'Teacher Lesson_Presentation'],
                'route' => 'teacher.lesson_presentation',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_my-weekly-plans',
                'label' => ['en' => 'Teacher My-Weekly-Plans', 'ar' => 'Teacher My-Weekly-Plans'],
                'route' => 'teacher.my-weekly-plans',
                'icon' => 'link',
             ],
             [
                'id' => 'period-activities_index',
                'label' => ['en' => 'Period-Activities Index', 'ar' => 'Period-Activities Index'],
                'route' => 'period-activities.index',
                'icon' => 'link',
             ],
             [
                'id' => 'period-activities_create',
                'label' => ['en' => 'Period-Activities Create', 'ar' => 'Period-Activities Create'],
                'route' => 'period-activities.create',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_presentation',
                'label' => ['en' => 'Teacher Presentation', 'ar' => 'Teacher Presentation'],
                'route' => 'teacher.presentation',
                'icon' => 'link',
             ],
             [
                'id' => 'question_types',
                'label' => ['en' => 'Question_Types', 'ar' => 'Question_Types'],
                'route' => 'question_types',
                'icon' => 'link',
             ],
             [
                'id' => 'teacher_timeline',
                'label' => ['en' => 'Teacher Timeline', 'ar' => 'Teacher Timeline'],
                'route' => 'teacher.timeline',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'teacher_classes_group',
        'label' => ['en' => 'Teacher_classes Routes', 'ar' => 'مسارات Teacher_classes'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::mMIOtvYwFGlxm2pt',
                'label' => ['en' => 'Generated::Mmiotvywfglxm2Pt', 'ar' => 'Generated::Mmiotvywfglxm2Pt'],
                'route' => 'generated::mMIOtvYwFGlxm2pt',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'teachers_group',
        'label' => ['en' => 'Teachers Routes', 'ar' => 'مسارات Teachers'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'myteachers_import',
                'label' => ['en' => 'Myteachers Import', 'ar' => 'Myteachers Import'],
                'route' => 'myteachers.import',
                'icon' => 'link',
             ],
             [
                'id' => 'myteachers_import_schools',
                'label' => ['en' => 'Myteachers Import Schools', 'ar' => 'Myteachers Import Schools'],
                'route' => 'myteachers.import.schools',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'two-factor-challenge_group',
        'label' => ['en' => 'Two-factor-challenge Routes', 'ar' => 'مسارات Two-factor-challenge'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'two-factor_login',
                'label' => ['en' => 'Two-Factor Login', 'ar' => 'Two-Factor Login'],
                'route' => 'two-factor.login',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'up_group',
        'label' => ['en' => 'Up Routes', 'ar' => 'مسارات Up'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::wKGl5mYqdJYBssD6',
                'label' => ['en' => 'Generated::Wkgl5Myqdjybssd6', 'ar' => 'Generated::Wkgl5Myqdjybssd6'],
                'route' => 'generated::wKGl5mYqdJYBssD6',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'user_group',
        'label' => ['en' => 'User Routes', 'ar' => 'مسارات User'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'password_confirm',
                'label' => ['en' => 'Password Confirm', 'ar' => 'Password Confirm'],
                'route' => 'password.confirm',
                'icon' => 'link',
             ],
             [
                'id' => 'password_confirmation',
                'label' => ['en' => 'Password Confirmation', 'ar' => 'Password Confirmation'],
                'route' => 'password.confirmation',
                'icon' => 'link',
             ],
             [
                'id' => 'profile_show',
                'label' => ['en' => 'Profile Show', 'ar' => 'Profile Show'],
                'route' => 'profile.show',
                'icon' => 'link',
             ],
             [
                'id' => 'two-factor_qr-code',
                'label' => ['en' => 'Two-Factor Qr-Code', 'ar' => 'Two-Factor Qr-Code'],
                'route' => 'two-factor.qr-code',
                'icon' => 'link',
             ],
             [
                'id' => 'two-factor_recovery-codes',
                'label' => ['en' => 'Two-Factor Recovery-Codes', 'ar' => 'Two-Factor Recovery-Codes'],
                'route' => 'two-factor.recovery-codes',
                'icon' => 'link',
             ],
             [
                'id' => 'two-factor_secret-key',
                'label' => ['en' => 'Two-Factor Secret-Key', 'ar' => 'Two-Factor Secret-Key'],
                'route' => 'two-factor.secret-key',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'user-messages_group',
        'label' => ['en' => 'User-messages Routes', 'ar' => 'مسارات User-messages'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'user-messages_index',
                'label' => ['en' => 'User-Messages Index', 'ar' => 'User-Messages Index'],
                'route' => 'user-messages.index',
                'icon' => 'link',
             ],
             [
                'id' => 'user-messages_users',
                'label' => ['en' => 'User-Messages Users', 'ar' => 'User-Messages Users'],
                'route' => 'user-messages.users',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'v2_group',
        'label' => ['en' => 'V2 Routes', 'ar' => 'مسارات V2'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'v2_parent_dashboard',
                'label' => ['en' => 'V2 Parent Dashboard', 'ar' => 'V2 Parent Dashboard'],
                'route' => 'v2.parent.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_student_dashboard',
                'label' => ['en' => 'V2 Student Dashboard', 'ar' => 'V2 Student Dashboard'],
                'route' => 'v2.student.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_super-system_config',
                'label' => ['en' => 'V2 Super-System Config', 'ar' => 'V2 Super-System Config'],
                'route' => 'v2.super-system.config',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_super-system_dashboard',
                'label' => ['en' => 'V2 Super-System Dashboard', 'ar' => 'V2 Super-System Dashboard'],
                'route' => 'v2.super-system.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_super-system_jobs',
                'label' => ['en' => 'V2 Super-System Jobs', 'ar' => 'V2 Super-System Jobs'],
                'route' => 'v2.super-system.jobs',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_super-system_logs',
                'label' => ['en' => 'V2 Super-System Logs', 'ar' => 'V2 Super-System Logs'],
                'route' => 'v2.super-system.logs',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_super-system_logs_download',
                'label' => ['en' => 'V2 Super-System Logs Download', 'ar' => 'V2 Super-System Logs Download'],
                'route' => 'v2.super-system.logs.download',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_system-admin_dashboard',
                'label' => ['en' => 'V2 System-Admin Dashboard', 'ar' => 'V2 System-Admin Dashboard'],
                'route' => 'v2.system-admin.dashboard',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_system-admin_schools_index',
                'label' => ['en' => 'V2 System-Admin Schools Index', 'ar' => 'V2 System-Admin Schools Index'],
                'route' => 'v2.system-admin.schools.index',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_system-admin_users_index',
                'label' => ['en' => 'V2 System-Admin Users Index', 'ar' => 'V2 System-Admin Users Index'],
                'route' => 'v2.system-admin.users.index',
                'icon' => 'link',
             ],
             [
                'id' => 'v2_teacher_dashboard',
                'label' => ['en' => 'V2 Teacher Dashboard', 'ar' => 'V2 Teacher Dashboard'],
                'route' => 'v2.teacher.dashboard',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'vapid_group',
        'label' => ['en' => 'Vapid Routes', 'ar' => 'مسارات Vapid'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'generated::5o1GhvA9hdW40Slk',
                'label' => ['en' => 'Generated::5O1Ghva9Hdw40Slk', 'ar' => 'Generated::5O1Ghva9Hdw40Slk'],
                'route' => 'generated::5o1GhvA9hdW40Slk',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'vocabulary-flashcards_group',
        'label' => ['en' => 'Vocabulary-flashcards Routes', 'ar' => 'مسارات Vocabulary-flashcards'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'vocabulary-flashcards',
                'label' => ['en' => 'Vocabulary-Flashcards', 'ar' => 'Vocabulary-Flashcards'],
                'route' => 'vocabulary-flashcards',
                'icon' => 'link',
             ],
             [
                'id' => 'vocabulary-flashcards_practice',
                'label' => ['en' => 'Vocabulary-Flashcards Practice', 'ar' => 'Vocabulary-Flashcards Practice'],
                'route' => 'vocabulary-flashcards.practice',
                'icon' => 'link',
             ],
             [
                'id' => 'vocabulary-flashcards_quiz',
                'label' => ['en' => 'Vocabulary-Flashcards Quiz', 'ar' => 'Vocabulary-Flashcards Quiz'],
                'route' => 'vocabulary-flashcards.quiz',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'weekly-plan-sessions_group',
        'label' => ['en' => 'Weekly-plan-sessions Routes', 'ar' => 'مسارات Weekly-plan-sessions'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'weekly-plan-sessions_index',
                'label' => ['en' => 'Weekly-Plan-Sessions Index', 'ar' => 'Weekly-Plan-Sessions Index'],
                'route' => 'weekly-plan-sessions.index',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-plan-sessions_create',
                'label' => ['en' => 'Weekly-Plan-Sessions Create', 'ar' => 'Weekly-Plan-Sessions Create'],
                'route' => 'weekly-plan-sessions.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'weekly-plans_group',
        'label' => ['en' => 'Weekly-plans Routes', 'ar' => 'مسارات Weekly-plans'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'weekly-plans_index',
                'label' => ['en' => 'Weekly-Plans Index', 'ar' => 'Weekly-Plans Index'],
                'route' => 'weekly-plans.index',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-plans_create',
                'label' => ['en' => 'Weekly-Plans Create', 'ar' => 'Weekly-Plans Create'],
                'route' => 'weekly-plans.create',
                'icon' => 'link',
             ],
        ]
    ],
    [
        'id' => 'weekly-system_group',
        'label' => ['en' => 'Weekly-system Routes', 'ar' => 'مسارات Weekly-system'],
        'icon' => 'build',
        'children' => [
             [
                'id' => 'weekly-system_api_drafts_index',
                'label' => ['en' => 'Weekly-System Api Drafts Index', 'ar' => 'Weekly-System Api Drafts Index'],
                'route' => 'weekly-system.api.drafts.index',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_school-data',
                'label' => ['en' => 'Weekly-System Api School-Data', 'ar' => 'Weekly-System Api School-Data'],
                'route' => 'weekly-system.api.school-data',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_slot-availability',
                'label' => ['en' => 'Weekly-System Api Slot-Availability', 'ar' => 'Weekly-System Api Slot-Availability'],
                'route' => 'weekly-system.api.slot-availability',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_sync-analysis',
                'label' => ['en' => 'Weekly-System Api Sync-Analysis', 'ar' => 'Weekly-System Api Sync-Analysis'],
                'route' => 'weekly-system.api.sync-analysis',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_teacher-conflicts',
                'label' => ['en' => 'Weekly-System Api Teacher-Conflicts', 'ar' => 'Weekly-System Api Teacher-Conflicts'],
                'route' => 'weekly-system.api.teacher-conflicts',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_my-schedule',
                'label' => ['en' => 'Weekly-System Api My-Schedule', 'ar' => 'Weekly-System Api My-Schedule'],
                'route' => 'weekly-system.api.my-schedule',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_my-weekly-plans',
                'label' => ['en' => 'Weekly-System Api My-Weekly-Plans', 'ar' => 'Weekly-System Api My-Weekly-Plans'],
                'route' => 'weekly-system.api.my-weekly-plans',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_weekly-plans',
                'label' => ['en' => 'Weekly-System Api Weekly-Plans', 'ar' => 'Weekly-System Api Weekly-Plans'],
                'route' => 'weekly-system.api.weekly-plans',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_api_teacher-stats',
                'label' => ['en' => 'Weekly-System Api Teacher-Stats', 'ar' => 'Weekly-System Api Teacher-Stats'],
                'route' => 'weekly-system.api.teacher-stats',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_my-schedule',
                'label' => ['en' => 'Weekly-System My-Schedule', 'ar' => 'Weekly-System My-Schedule'],
                'route' => 'weekly-system.my-schedule',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_my-weekly-plans',
                'label' => ['en' => 'Weekly-System My-Weekly-Plans', 'ar' => 'Weekly-System My-Weekly-Plans'],
                'route' => 'weekly-system.my-weekly-plans',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_school-browser',
                'label' => ['en' => 'Weekly-System School-Browser', 'ar' => 'Weekly-System School-Browser'],
                'route' => 'weekly-system.school-browser',
                'icon' => 'link',
             ],
             [
                'id' => 'weekly-system_weekly-plans-manager',
                'label' => ['en' => 'Weekly-System Weekly-Plans-Manager', 'ar' => 'Weekly-System Weekly-Plans-Manager'],
                'route' => 'weekly-system.weekly-plans-manager',
                'icon' => 'link',
             ],
        ]
    ],
];
