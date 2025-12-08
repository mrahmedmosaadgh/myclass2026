# Print Report - Filters and New Window Feature

## ✅ What Was Added

### 1. Filter System
**Filter incidents and students before printing**

#### Available Filters:

**Show Students Filter:**
- All Students (default)
- With Incidents Only
- Good Behavior Only

**Severity Filter:**
- All Severities (default)
- Minor Only
- Major Only

### 2. Open in New Window
**Print report opens in a clean new window**

#### Features:
- Clean, distraction-free view
- Print button in top-right corner
- Close button to exit
- Optimized for printing
- All styles included
- No extra UI elements

### 3. Filter Results Summary
**Shows what's currently displayed**
- Number of students shown
- Number of incidents shown
- Updates dynamically with filters

## 🎯 How to Use

### Filtering Before Print

#### Show Only Students with Incidents:
```
1. Select "With Incidents Only" from first dropdown
2. See count update: "Showing: 5 students | 12 incidents"
3. Click "Print" or "Open in New Window"
4. Only students with incidents appear in report
```

#### Show Only Good Behavior Students:
```
1. Select "Good Behavior Only" from first dropdown
2. See count update: "Showing: 8 students | 0 incidents"
3. Click "Print"
4. Only students with no incidents appear (with celebration message)
```

#### Filter by Severity:
```
1. Select "Minor Only" or "Major Only" from second dropdown
2. See filtered incident count
3. Students with no matching incidents are hidden
4. Print shows only filtered data
```

#### Combine Filters:
```
1. Select "With Incidents Only"
2. Select "Major Only"
3. See only students with major incidents
4. Perfect for serious behavior review
```

### Open in New Window

#### Steps:
```
1. Apply desired filters (optional)
2. Click "Open in New Window" button
3. New window opens with clean report
4. Click "Print" button in top-right
5. Or click "Close" to exit
```

#### Benefits:
- Clean, professional view
- No navigation bars
- No extra UI elements
- Easy to print
- Can keep window open for reference

## 📊 Filter Examples

### Example 1: Review Problem Students
```
Filter: "With Incidents Only" + "Major Only"
Result: Shows only students with major incidents
Use Case: Principal review, parent meetings
```

### Example 2: Celebrate Good Behavior
```
Filter: "Good Behavior Only"
Result: Shows only students with zero incidents
Use Case: Recognition certificates, positive reports
```

### Example 3: Minor Incidents Report
```
Filter: "With Incidents Only" + "Minor Only"
Result: Shows students with minor incidents only
Use Case: Teacher review, pattern identification
```

### Example 4: Complete Report
```
Filter: "All Students" + "All Severities"
Result: Shows everyone (with and without incidents)
Use Case: Comprehensive classroom report
```

## 🎨 UI Components

### Filter Bar
```
┌─────────────────────────────────────────────────────┐
│ 🔍 Filters:                                         │
│ [All Students ▼] [All Severities ▼]                │
│                          [Print] [Open in New Window]│
└─────────────────────────────────────────────────────┘
```

### Results Summary
```
┌─────────────────────────────────────────────────────┐
│ Showing: 12 students | 25 incidents                 │
└─────────────────────────────────────────────────────┘
```

### New Window
```
┌─────────────────────────────────────────────────────┐
│                              [🖨️ Print] [✕ Close]   │
├─────────────────────────────────────────────────────┤
│                                                      │
│         BEHAVIOR INCIDENT REPORT                     │
│         ═══════════════════════                      │
│                                                      │
│         [Report Content Here]                        │
│                                                      │
└─────────────────────────────────────────────────────┘
```

## 🔧 Technical Implementation

### Filter State
```javascript
const showStudentsFilter = ref('all')
const severityFilter = ref('all')
```

### Filtered Computed Property
```javascript
const filteredStudents = computed(() => {
  let filtered = studentsWithIncidents.value

  // Apply student filter
  if (showStudentsFilter.value === 'with-incidents') {
    filtered = filtered.filter(s => s.incidents.length > 0)
  } else if (showStudentsFilter.value === 'no-incidents') {
    filtered = filtered.filter(s => s.incidents.length === 0)
  }

  // Apply severity filter
  if (severityFilter.value !== 'all') {
    filtered = filtered.map(studentData => {
      const filteredIncidents = studentData.incidents.filter(incident => {
        if (severityFilter.value === 'minor') {
          return incident.severity === 'minor'
        } else if (severityFilter.value === 'major') {
          return incident.severity === 'major' || incident.severity === 'moderate'
        }
        return true
      })
      // Recalculate summary...
      return { ...studentData, incidents: filteredIncidents, summary }
    })
  }

  return filtered
})
```

### New Window Function
```javascript
function openInNewWindow() {
  const printContent = document.getElementById('printable-report')
  const newWindow = window.open('', '_blank', 'width=1200,height=800')
  
  // Write HTML with styles
  newWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Behavior Incident Report</title>
      ${styles}
    </head>
    <body>
      <button onclick="window.print()">Print</button>
      <button onclick="window.close()">Close</button>
      ${printContent.innerHTML}
    </body>
    </html>
  `)
}
```

## 📋 Filter Combinations

| Student Filter | Severity Filter | Result |
|----------------|-----------------|--------|
| All Students | All Severities | Everyone, all incidents |
| All Students | Minor Only | Everyone, only minor incidents |
| All Students | Major Only | Everyone, only major incidents |
| With Incidents Only | All Severities | Only students with any incidents |
| With Incidents Only | Minor Only | Only students with minor incidents |
| With Incidents Only | Major Only | Only students with major incidents |
| Good Behavior Only | Any | Only students with zero incidents |

## 🌍 Bilingual Labels

### English:
- Filters
- All Students
- With Incidents Only
- Good Behavior Only
- All Severities
- Minor Only
- Major Only
- Showing
- students
- incidents
- Print
- Open in New Window

### Arabic:
- التصفية
- جميع الطلاب
- الذين لديهم حوادث فقط
- السلوك الجيد فقط
- جميع المستويات
- بسيط فقط
- كبير فقط
- عرض
- طالب
- حادثة
- طباعة
- فتح في نافذة جديدة

## ✅ Benefits

### For Teachers:
1. **Flexible Reporting**: Filter exactly what you need
2. **Quick Access**: Open in new window for easy printing
3. **Professional**: Clean, distraction-free print view
4. **Efficient**: No need to print everything

### For Administrators:
1. **Targeted Reports**: Focus on specific severity levels
2. **Recognition**: Easy to print good behavior reports
3. **Documentation**: Professional appearance
4. **Flexibility**: Multiple filter combinations

### For Parents:
1. **Clear**: Only relevant information shown
2. **Professional**: Clean, organized layout
3. **Bilingual**: Read in preferred language

## 🎯 Use Cases

### Weekly Review:
```
Filter: With Incidents Only + All Severities
Purpose: Review all incidents from the week
```

### Principal Meeting:
```
Filter: With Incidents Only + Major Only
Purpose: Discuss serious behavior issues
```

### Recognition Ceremony:
```
Filter: Good Behavior Only
Purpose: Print certificates for good students
```

### Parent Conference:
```
Filter: All Students + All Severities
Purpose: Show complete classroom picture
```

### Intervention Planning:
```
Filter: With Incidents Only + Major Only
Purpose: Identify students needing support
```

## 📊 Statistics

The filter results summary shows:
- **Student Count**: How many students match filters
- **Incident Count**: How many incidents match filters
- **Real-time Updates**: Changes as you adjust filters

---

**Status**: ✅ Complete  
**Version**: 2.3.0  
**Last Updated**: November 24, 2025
