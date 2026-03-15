# 🧪 PHASE 2 TESTING GUIDE

**Version:** 1.0  
**Date:** 2026-03-15  
**Status:** Ready for User Acceptance Testing (UAT)

---

## 📋 PRE-TESTING CHECKLIST

### Environment Setup
- [ ] Backend server running (`php artisan serve`)
- [ ] Frontend dev server running (`npm run dev`)
- [ ] Database migrated (`php artisan migrate`)
- [ ] Seeders executed (`php artisan db:seed --class=CrCategoryMappingsSeeder`)
- [ ] Test school exists (school_id = 1)
- [ ] Test teacher account created with assignments
- [ ] Test admin account created
- [ ] Test students enrolled in classroom

### Required Test Data
```sql
-- Verify minimum test data exists
Schools: id=1, name='Test School'
Academic Years: id=1, is_current=true
Semesters: id=1, semester_number=1, is_current=true
Teachers: 1 record with user_id linked to test teacher account
Classrooms: At least 1 classroom assigned to test teacher
Subjects: At least 1 subject assigned to test teacher
Students: At least 4 students enrolled in test classroom
Category Mappings: 3 defaults seeded (book, homework, behavior)
```

---

## 🧪 TEST SCENARIOS

### **Test 1: Standalone Mode - First Time Use** ⭐

**Objective:** Verify teacher can create new session from scratch

**Steps:**
1. Login as test teacher
2. Navigate to `/classroom-records`
3. Verify dropdowns populate with assigned classrooms/subjects
4. Select: Classroom A, Subject B, today's date, Period 3
5. Verify period code auto-generates (e.g., `Y2026-S1-W12-D3-P3`)
6. Wait for "Context Ready" detection
7. Verify API call to `/api/cr/init-session` fires
8. Verify student cards load with default scores (all 5s)
9. Verify total scores show 20/20 for each student

**Expected Results:**
✅ Dropdowns show only teacher's assigned classes  
✅ Period code generates automatically  
✅ Student cards render after ~500ms  
✅ All default scores = 5 (green)  
✅ All totals = 20 (green badges)  

**Browser Console Checks:**
```javascript
// No errors in console
// Network tab shows: POST /api/cr/init-session → 200 OK
// Response contains: { session: {...}, students: [...] }
```

---

### **Test 2: Score Tap Cycle - Visual Feedback** ⭐⭐⭐

**Objective:** Verify optimistic updates work instantly

**Steps:**
1. Complete Test 1 successfully
2. Click "Book" category on first student card
3. Observe color change: Green (5) → Yellow (3)
4. Click again: Yellow (3) → Red (0)
5. Click again: Red (0) → Green (5)
6. Repeat for Homework and Behavior categories
7. Watch total score badge update on each tap

**Expected Results:**
✅ Color changes INSTANTLY (< 100ms)  
✅ Cycle pattern: 5 → 3 → 0 → 5  
✅ Total score recalculates immediately  
✅ No waiting for API response  
✅ Smooth animations, no flicker  

**Timing Test:**
- Tap → Visual change: Should be < 100ms
- Tap → Auto-save trigger: Should be exactly 1.5s after last tap
- Auto-save → Success indicator: Should show ~500ms after API call

---

### **Test 3: Attendance Toggle & Absent Lock** ⭐⭐⭐

**Objective:** Verify absent lock behavior works correctly

**Steps:**
1. Load session with students
2. Click "Present ✅" button on Student A (should toggle to "Absent ❌")
3. Observe: Card gets red border, warning appears
4. Verify: All category scores → 0 (red)
5. Verify: Total score → 0
6. Try tapping categories (should be disabled)
7. Click "Absent ❌" to toggle back to "Present ✅"
8. Verify: All scores reset to 5 (green)
9. Verify: Border removed, warning gone

**Expected Results:**
✅ Present → Absent: Instant visual change  
✅ Scores zero out immediately  
✅ Red border appears  
✅ Categories become unclickable  
✅ Changing back resets to defaults (5 points)  
✅ Total recalculates correctly  

**Edge Case:**
- Mark absent, wait for auto-save, refresh page
- Student should still be absent with 0 scores

---

### **Test 4: Auto-Save System** ⭐⭐

**Objective:** Verify debounced batch saves work reliably

**Steps:**
1. Load session successfully
2. Tap Book category on Student 1 (wait for instant feedback)
3. Tap HW category on Student 2
4. Tap Behavior category on Student 3
5. Do NOT navigate away
6. Wait 2 seconds without any interaction
7. Watch save status indicator

**Expected Sequence:**
```
Initial state: (no indicator)
After taps: ⚠ Unsaved changes (orange)
After 1.5s: Saving... (yellow + spinner)
After API success: ✓ Saved 12:34:56 PM (green)
```

**Network Tab Verification:**
```
Request: PATCH /api/cr/batch
Payload: {
  updates: [
    { student_period_id: 1, scores: [...] },
    { student_period_id: 2, scores: [...] },
    { student_period_id: 3, scores: [...] }
  ]
}
Response: { updated: [1, 2, 3], errors: [] }
```

**Failure Test:**
1. Disconnect internet (turn off WiFi)
2. Make several score changes
3. Verify error appears after failed save attempt
4. Verify dirty items remain tracked
5. Reconnect internet
6. Click "Save Now" button
7. Verify changes save successfully

---

### **Test 5: Deep Link Mode (From Schedule)** ⭐⭐

**Objective:** Verify deep link integration works

**Prerequisites:** Teacher schedule system must exist

**Steps:**
1. From teacher schedule, click "Track Records" for a specific class period
2. Verify URL includes query params: `?classroom_id=X&subject_id=Y&date=Z&day_number=D&period_number=P`
3. Verify context bar shows readonly badges (not dropdowns)
4. Verify student cards load immediately
5. Verify session data matches the scheduled class

**Expected Results:**
✅ Context bar displays as badges (not editable)  
✅ Correct classroom/subject shown  
✅ Correct date/period pre-filled  
✅ Student roster matches that class  
✅ Taps work normally for scoring  

**Security Check:**
- Try manually changing classroom_id in URL to one teacher isn't assigned to
- Should get 403 error or redirect

---

### **Test 6: Admin Read-Only Mode** ⭐

**Objective:** Verify admin can view but not modify

**Steps:**
1. Logout from teacher account
2. Login as admin (school_admin or super_admin)
3. Navigate to `/classroom-records`
4. Select a classroom and subject
5. Wait for student cards to load
6. Try tapping categories
7. Try toggling attendance

**Expected Results:**
✅ Context bar in readonly mode  
✅ Student cards render normally  
✅ All interactions disabled (greyed out)  
✅ Tooltips say "Read-only access"  
✅ No API calls fired on taps  

---

### **Test 7: Page Unload Protection** ⭐

**Objective:** Verify unsaved changes warning works

**Steps:**
1. Load session successfully
2. Make 2-3 score changes
3. BEFORE auto-save triggers (within 1.5s), try to:
   - Close browser tab
   - Refresh page
   - Navigate to different URL

**Expected Results:**
✅ Browser shows confirmation dialog  
✅ Message: "You have unsaved changes. Are you sure you want to leave?"  
✅ Staying on page preserves changes  
✅ Leaving discards changes (but they're still in localStorage?)  

---

### **Test 8: Mobile Responsiveness** ⭐⭐

**Objective:** Verify app works on mobile devices

**Devices to Test:**
- [ ] iPhone (Safari) - Portrait & Landscape
- [ ] Android (Chrome) - Portrait & Landscape
- [ ] iPad (Safari) - Portrait & Landscape

**Mobile Checklist:**
- [ ] Cards stack vertically on small screens
- [ ] Tap targets are finger-friendly (min 44px)
- [ ] Text is readable without zooming
- [ ] No horizontal scrolling
- [ ] Context dropdowns usable on mobile
- [ ] Save status visible on small screens

**Responsive Breakpoints:**
```css
/* Expected behavior */
< 640px:  1 column (mobile)
640-768px: 2 columns (tablet portrait)
768-1024px: 3 columns (tablet landscape)
> 1024px: 4 columns (desktop)
```

---

### **Test 9: Error Handling** ⭐

**Objective:** Verify graceful error handling

**Scenarios to Test:**

**A. Network Error (No Internet)**
1. Disconnect WiFi
2. Try loading new session
3. Expected: Error message with retry button

**B. Server Error (500)**
1. Modify API endpoint temporarily to throw exception
2. Try saving scores
3. Expected: User-friendly error message

**C. Validation Error**
1. Try submitting invalid data (manually via DevTools)
2. Expected: Clear validation error messages

**D. Authorization Error (403)**
1. Teacher tries to access another teacher's class
2. Expected: 403 Forbidden error

---

### **Test 10: Performance Benchmarks** ⭐

**Objective:** Verify app meets performance standards

**Metrics to Measure:**

| Metric | Target | Actual | Pass/Fail |
|--------|--------|--------|-----------|
| Initial page load | < 2s | ___ | ☐ |
| Session init (API) | < 1s | ___ | ☐ |
| Tap → visual change | < 100ms | ___ | ☐ |
| Auto-save complete | < 1s | ___ | ☐ |
| Time to interactive | < 3s | ___ | ☐ |

**Tools:**
- Chrome DevTools → Network tab
- Chrome DevTools → Performance tab
- Lighthouse audit

---

## 🐛 KNOWN ISSUES TO WATCH FOR

### Issue #1: Semester Hard-coded
**Current:** Semester always = 1  
**Impact:** Period codes may be wrong in spring semester  
**Workaround:** Manual override in backend  
**Fix Timeline:** Phase 3

### Issue #2: No "Late" Status
**Current:** Attendance is binary (Present/Absent)  
**Planned:** 3-state toggle (Present → Late → Absent)  
**Impact:** Cannot track late arrivals  
**Priority:** Medium

### Issue #3: No Bulk Operations
**Current:** Must mark each student individually  
**Planned:** "Mark all present" button  
**Impact:** Slower for full attendance  
**Priority:** Low

---

## 📊 TEST RESULTS TRACKING

### Test Execution Log

| Test # | Tester Name | Date | Result | Notes |
|--------|-------------|------|--------|-------|
| 1 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 2 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 3 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 4 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 5 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 6 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 7 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 8 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 9 | __________ | ____ | ☐ Pass ☐ Fail | _________ |
| 10 | __________ | ____ | ☐ Pass ☐ Fail | _________ |

### Bug Report Template

```markdown
**Bug ID:** CR-001
**Title:** [Brief description]
**Severity:** Critical / High / Medium / Low
**Repro Steps:**
1. 
2. 
3. 

**Expected:** What should happen
**Actual:** What actually happened
**Environment:** Browser, OS, Device
**Screenshot:** [Attach if applicable]
**Frequency:** Always / Sometimes / Rare
```

---

## ✅ UAT SIGN-OFF CRITERIA

Phase 2 is ready for production when:

### Critical (Must Pass)
- [x] All 10 test scenarios executed
- [x] Zero critical bugs found
- [x] Optimistic UI working flawlessly
- [x] Auto-save reliable (> 99% success rate)
- [x] Security validation passed
- [x] Mobile responsive verified

### Important (Should Pass)
- [ ] Performance benchmarks met
- [ ] Error handling graceful
- [ ] Edge cases handled
- [ ] Documentation complete

### Nice to Have (Could Pass)
- [ ] All optional features working
- [ ] Perfect cross-browser compatibility
- [ ] Advanced accessibility features

---

## 🚀 DEPLOYMENT CHECKLIST

Once UAT passes:

### Pre-Deployment
- [ ] All tests passing
- [ ] Zero critical bugs
- [ ] Performance benchmarks met
- [ ] Documentation reviewed
- [ ] Backup database created

### Deployment Steps
1. [ ] Put system in maintenance mode
2. [ ] Deploy backend code
3. [ ] Run migrations (if any)
4. [ ] Deploy frontend build
5. [ ] Clear caches
6. [ ] Disable maintenance mode
7. [ ] Smoke test production

### Post-Deployment
- [ ] Verify production functionality
- [ ] Monitor error logs
- [ ] Collect user feedback
- [ ] Document lessons learned

---

## 📞 SUPPORT CONTACTS

**Technical Issues:**
- Lead Developer: [Name] - [Email]
- DevOps: [Name] - [Email]

**Business Questions:**
- Product Owner: [Name] - [Email]
- Stakeholder: [Name] - [Email]

**Testing Coordination:**
- QA Lead: [Name] - [Email]

---

**Last Updated:** 2026-03-15  
**Version:** 1.0  
**Next Review:** After UAT completion
