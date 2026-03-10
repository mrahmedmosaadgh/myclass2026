# MyClass 2026 — Project Guide

> **Status:** Phase 3 Execution complete. Roles scaffolded.

## 📐 Folder Structure Convention
```
myclass2026/
├── GUIDE.md              ← You are here
├── STRUCTURE.md          ← Full folder tree with status
├── FEATURES.md           ← All platform features with details
│
├── _shared/              ← Shared across all roles
│   ├── components/       (AppCard, AppButton, AppModal)
│   ├── layouts/          (RoleLayout)
│   └── composables/      (useAuth, useRole)
│
├── roles/                ← One folder per user role
│   ├── super-system/
│   ├── system-admin/
│   ├── school-admin/
│   ├── teacher/
│   │   ├── planning/
│   │   ├── assessment/
│   │   ├── live/
│   │   ├── records/
│   │   ├── time-management/
│   │   └── presentation/
│   ├── student/
│   ├── parent/
│   ├── hr/
│   └── developer/
└── features/             ← Cross-role standalone features
    ├── smart-scanner/    🆕 NEW (Priority 1)
    ├── notifications/
    ├── chat/
    └── qr-tools/
```

## 🗂️ Legacy Folder Map

> Existing code was built in scattered folders. Do NOT move them — import or reference from there.

| Feature Area | Legacy Location | New Scaffold Location |
|---|---|---|
| School Admin | `my_class/admin/` | `roles/school-admin/` |
| Teacher tools | `my_class/teacher/` | `roles/teacher/` |
| Question Bank & Exams | `my_class/QuQuestionBankSystem/` | `roles/teacher/assessment/` |
| HR features | `my_class/hr/` | `roles/hr/` |
| Component Dev | `MicroComponentTest/` | `roles/developer/` |

---

## ✅ Completion Rules
When marking a feature done:
- Build it in the appropriate `roles/` folder
- Update `STRUCTURE.md` and `FEATURES.md`
- Add a reference link to the actual file path here
