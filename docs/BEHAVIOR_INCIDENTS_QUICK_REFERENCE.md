# Behavior Incidents - Quick Reference

## 🚀 Quick Start

### For Teachers

1. Open Reward System → Select Classroom → Initialize Session
2. Click "Behavior Incidents" tab
3. Click "Record New Incident"
4. Fill form and submit
5. Student loses 1 point automatically

### For Developers

```bash
# Run migration
php artisan migrate

# Test API
POST /api/behavior-incidents
GET /api/behavior-incidents?classroom_id=10
GET /api/behavior-incidents/student/123/report
```

## 📁 Files Created

```
database/migrations/
  └── 2025_11_24_061553_create_behavior_incidents_table.php

app/Models/
  └── BehaviorIncident.php

app/Http/Controllers/
  └── BehaviorIncidentController.php

resources/js/Pages/my_table_mnger/reward_sys/
  ├── reward_sys.vue (modified)
  └── reward_sys_comp/
      └── BehaviorIncidents.vue

routes/
  └── api.php (modified)

docs/
  ├── BEHAVIOR_INCIDENTS_FEATURE_GUIDE.md
  ├── BEHAVIOR_INCIDENTS_IMPLEMENTATION.md
  └── BEHAVIOR_INCIDENTS_QUICK_REFERENCE.md
```

## 🔑 Key Features

- ✅ Bilingual (EN/AR)
- ✅ Auto -1 point per incident
- ✅ Production-grade schema
- ✅ Fast queries (indexed)
- ✅ Soft deletes
- ✅ Audit trail
- ✅ Parent communication ready
- ✅ Critical alerts
- ✅ Comprehensive reports

## 📊 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/behavior-incidents` | List incidents |
| POST | `/api/behavior-incidents` | Create incident |
| GET | `/api/behavior-incidents/{id}` | View incident |
| PUT | `/api/behavior-incidents/{id}` | Update incident |
| DELETE | `/api/behavior-incidents/{id}` | Delete incident |
| GET | `/api/behavior-incidents/student/{id}/report` | Student report |

## 🎯 Common Queries

```sql
-- Today's incidents
SELECT * FROM behavior_incidents 
WHERE DATE(occurred_at) = CURDATE();

-- Student timeline
SELECT * FROM behavior_incidents 
WHERE student_id = 123 
ORDER BY occurred_at DESC;

-- Major incidents needing follow-up
SELECT * FROM behavior_incidents 
WHERE severity = 'major' 
AND follow_up_needed = true;
```

## 🔧 Quick Customization

### Change Points Value
`BehaviorIncidentController.php` line ~150:
```php
'points_deducted' => 2, // Change from 1 to 2
```

### Add Custom Behavior
`BehaviorIncidents.vue` line ~300:
```javascript
const behaviorOptions = [
  'Disruption',
  'Your Custom Behavior', // Add here
]
```

### Add Custom Location
`BehaviorIncidents.vue` line ~290:
```javascript
const locations = [
  'Classroom',
  'Your Custom Location', // Add here
]
```

## 📱 UI Components

- **Language Toggle**: Top right corner
- **Record Button**: "Record New Incident"
- **Incident Cards**: Color-coded by severity
- **View Icon**: Eye icon for details
- **Delete Icon**: Trash icon with confirmation

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Table not found | Run `php artisan migrate` |
| Unauthorized | Check user is logged in |
| Student not found | Initialize classroom session first |
| Points not deducting | Check browser console for errors |

## 📖 Full Documentation

See `docs/BEHAVIOR_INCIDENTS_FEATURE_GUIDE.md` for complete details.

---

**Version**: 1.0.0  
**Status**: ✅ Production Ready  
**Last Updated**: November 24, 2025
