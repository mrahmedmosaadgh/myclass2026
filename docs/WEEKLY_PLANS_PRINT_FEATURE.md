# Weekly Plans - Print Preview Feature

## Overview
Added a complete print preview system for Weekly Plans with A4 paper dimensions and professional printing capabilities.

## Features Added

### 1. **Preview & Print Button**
- Located in the filter controls section (below classroom and day filters)
- Only appears when at least one classroom is selected
- Opens a maximized modal with A4 preview

### 2. **Print Preview Modal**
- **A4 Paper Format**: Displays content in standard A4 dimensions (210mm × 297mm)
- **Live Preview**: Shows exactly what will be printed
- **Toolbar**: Contains:
  - Title: "Print Preview - A4 Format"
  - Print Button: Opens browser print dialog
  - Close Button: Closes the preview modal

### 3. **Printed Content**
The A4 page includes:

#### Header Section
- Title: "Weekly Plans"
- Week & Semester information
- Selected classroom names
- Current date

#### Data Table
| Column | Content |
|--------|---------|
| Day | Day of the week (Sunday, Monday, etc.) |
| Period | Period number (P1, P2, etc.) |
| Subject | Subject name |
| Classroom | Classroom name |
| Classwork (CW) | Classwork content |
| Homework (HW) | Homework content |
| Notes | Teacher notes |

#### Footer Section
- Generation timestamp

### 4. **Print Styling**
- **Responsive**: Works on all screen sizes
- **A4 Optimized**: Perfect page break handling
- **Professional Design**: Clean table layout with alternating row colors
- **Content Preservation**: Text wrapping and word breaks for long content
- **Print Media**: Special CSS for print output matches preview exactly

## Technical Implementation

### Vue Component Updates
**File**: [resources/js/Pages/my_table_mnger/weekly_system/teacher/MyWeeklyPlans.vue](resources/js/Pages/my_table_mnger/weekly_system/teacher/MyWeeklyPlans.vue)

#### New Template Elements
1. **Print Preview Button**
   - Conditional rendering based on `selectedClassrooms.length > 0`
   - Icon: `print`
   - Color: Primary

2. **Print Preview Dialog**
   - Maximized q-dialog with `showPrintPreview` state
   - Toolbar with print and close buttons
   - A4-sized preview container with `id="print-area"`
   - Professional table layout with filtered plans

#### New Reactive State
```javascript
const showPrintPreview = ref(false)
```

#### New Method: `printPreview()`
- Creates a new browser window
- Writes HTML content with embedded CSS
- Opens native print dialog
- Handles page setup and teardown

#### New CSS Classes
- `.print-preview-card`: Dialog styling
- `.preview-container`: Scrollable container
- `.a4-page`: A4 dimensions and styling
- `.print-header`: Header section styling
- `.print-table`: Professional table styling
- `.print-footer`: Footer styling
- `@media print`: Print-specific optimizations

## Usage

### For Teachers
1. Navigate to "My Plans" page
2. Select one or more classrooms using the classroom filter
3. Click **"Preview & Print"** button
4. Review the preview (displayed in A4 format)
5. Click **"Print"** in the toolbar to:
   - Open browser print dialog
   - Adjust printer settings if needed
   - Print or save as PDF

### Print Options
- **Print to Printer**: Physical printout
- **Save as PDF**: Click printer dropdown → "Save as PDF"
- **Preview**: Review before printing

## Features & Benefits

✅ **WYSIWYG Printing**: What you see in preview is exactly what prints  
✅ **A4 Standard**: Perfect for standard paper sizes worldwide  
✅ **Professional Layout**: Clean, readable table format  
✅ **Easy Filtering**: Print only selected classrooms/days  
✅ **Date Stamped**: Automatic generation timestamp  
✅ **No Special Software**: Uses browser native printing  
✅ **PDF Export**: Save directly as PDF from browser print dialog  
✅ **Responsive Design**: Works on all screen sizes  
✅ **Page Breaks**: Intelligent handling of large data  
✅ **Print Media Optimized**: Hides UI, shows content only  

## Browser Compatibility
- Chrome/Chromium ✅
- Firefox ✅
- Safari ✅
- Edge ✅

## Customization Options

If you need to adjust the appearance:

### A4 Page Size
```css
.a4-page {
  width: 210mm;  /* A4 width */
  height: 297mm; /* A4 height */
  padding: 20mm; /* Margins */
}
```

### Table Font Size
```css
.print-table {
  font-size: 11px; /* Adjust as needed */
}
```

### Color Scheme
```css
.print-header {
  border-bottom: 2px solid var(--q-primary); /* Primary color */
  color: var(--q-primary);
}
```

## Notes
- Filtered plans are shown based on week, semester, classroom, and day selections
- The preview automatically updates when selections change
- Print dialog allows further customization before printing
- Content wraps intelligently for long text
- Table rows avoid page breaks when possible
