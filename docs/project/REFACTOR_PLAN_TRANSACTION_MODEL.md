# 🔄 Refactoring Plan: Transaction-Based Reward System

## 📊 Current vs. Target Architecture

### Current Model (Simple but Limited)
```
POST /api/student-behaviors/quick-create
  ↓
Creates StudentBehaviorsMain (session)
Creates StudentBehavior (with static points_plus/minus)
  ↗ Points are hard-coded from behavior.points
  
Result: No individual transaction history, can't cancel/revoke
```

### Target Model (Transaction-Based - Recommended)
```
POST /api/student-behaviors/quick-create
  ↓
1. Create/Find StudentBehaviorsMain (session)
2. Find/Create StudentBehavior (transaction container)
3. Create StudentBehaviorsPointAction (individual transaction)
   ├─ student_behaviors_id (which student record)
   ├─ reason_id (which behavior - "Did homework")
   ├─ value (the points: +5, -3)
   ├─ action_type ("plus" or "minus")
   ├─ created_by (who applied it)
   └─ canceled (false initially)
4. Recalculate StudentBehavior totals
   ├─ points_plus = SUM(value WHERE action_type='plus' AND canceled=false)
   ├─ points_minus = SUM(ABS(value) WHERE action_type='minus' AND canceled=false)
   └─ Update StudentBehavior table

Result: Full audit trail, can cancel/revoke, history tracking
```

---

## 🛠️ Changes Required

### **1. Backend: Update StudentBehavior Model**

**File:** `app/Models/StudentBehavior.php`

Add method to recalculate totals from point actions:

```php
public function recalculateTotals()
{
    $actions = $this->pointActions()
        ->where('canceled', false)
        ->get();

    $this->points_plus = $actions
        ->where('action_type', 'plus')
        ->sum('value');

    $this->points_minus = $actions
        ->where('action_type', 'minus')
        ->sum(fn($action) => abs($action->value));

    $this->save();

    return $this;
}

// Accessor to get calculated total
public function getTotalPointsAttribute()
{
    return $this->points_plus - $this->points_minus;
}
```

### **2. Backend: Update StudentBehaviorsPointAction Model**

**File:** `app/Models/StudentBehaviorsPointAction.php`

Add method to auto-recalculate parent totals:

```php
protected static function booted()
{
    static::created(function ($model) {
        $model->studentBehavior->recalculateTotals();
    });

    static::updated(function ($model) {
        if ($model->isDirty('canceled')) {
            $model->studentBehavior->recalculateTotals();
        }
    });
}
```

### **3. Backend: Refactor quickCreate Endpoint**

**File:** `app/Http/Controllers/StudentBehaviorController.php`

Change from creating static StudentBehavior to transaction-based:

```php
public function quickCreate(Request $request): JsonResponse
{
    // ... validation ...

    try {
        // 1. Create/Find StudentBehaviorsMain
        $behaviorMain = StudentBehaviorsMain::firstOrCreate(
            [
                'school_id' => $school->id,
                'year_id' => $year->id,
                'date' => $validated['date'],
                'period_code' => $validated['period_code'] ?? '',
                'classroom_id' => $classroomId,
                'subject_id' => $subjectId,
            ],
            [
                'teacher_id' => $teacher->id,
                'period_code_main' => 'auto-' . uniqid(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // 2. Find/Create StudentBehavior (transaction container)
        $studentBehavior = StudentBehavior::firstOrCreate(
            [
                'student_behaviors_mains_id' => $behaviorMain->id,
                'student_id' => $validated['student_id'],
            ],
            [
                'school_id' => $school->id,
                'attend' => true,
                'points_plus' => 0,
                'points_minus' => 0,
                'points_details' => json_encode([
                    'created_at' => now(),
                    'sessions' => 1,
                ]),
            ]
        );

        // 3. Create StudentBehaviorsPointAction (transaction)
        $pointAction = StudentBehaviorsPointAction::create([
            'student_behaviors_id' => $studentBehavior->id,
            'reason_id' => $validated['behavior_id'],
            'value' => $type === 'positive' ? $points : -$points,
            'action_type' => $type === 'positive' ? 'plus' : 'minus',
            'created_by' => $teacher->user_id,
            'note' => $validated['notes'] ?? null,
        ]);

        // 4. Recalculate parent totals (happens auto via observer)
        // $studentBehavior->recalculateTotals(); // Observer does this

        return response()->json($studentBehavior->load(['pointActions']), 201);
    }
    catch (\Exception $e) {
        // ... error handling ...
    }
}
```

### **4. Backend: Add Cancel Endpoint**

**File:** `app/Http/Controllers/StudentBehaviorsPointActionController.php`

```php
public function cancel(Request $request, StudentBehaviorsPointAction $action): JsonResponse
{
    $validated = $request->validate([
        'cancel_reason' => 'required|string',
    ]);

    $action->update([
        'canceled' => true,
        'canceled_by' => auth()->id(),
        'canceled_at' => now(),
        'cancel_reason' => $validated['cancel_reason'],
    ]);

    // Observer auto-recalculates parent totals
    
    return response()->json($action->studentBehavior);
}
```

### **5. Route: Add Cancel Endpoint**

**File:** `routes/api.php`

```php
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    // Existing routes...
    
    // Cancel a point action
    Route::post('/student-behaviors-point-actions/{action}/cancel', 
        [StudentBehaviorsPointActionController::class, 'cancel']);
});
```

---

## 📱 Frontend: Updated Flow

### **Vue Component State Change**

```javascript
// BEFORE (sending full behavior)
{
  student_id: 1,
  behavior_id: 3,
  date: "2025-11-14",
}

// AFTER (sending transaction details - optional, backend extracts from behavior)
// Same payload, but backend handles transaction creation
{
  student_id: 1,
  behavior_id: 3,  // This is reason_id for the point action
  date: "2025-11-14",
}
```

The key difference is **backend-side**: instead of storing `points_plus: 5`, it:
1. Creates `StudentBehaviorsPointAction` with `value: 5, action_type: 'plus'`
2. Sums those to get `points_plus: 5`
3. If you later cancel the action, `points_plus` recalculates to 0

### **Frontend: Cancel Point UI**

Add a cancel button in the summary view:

```vue
<div class="space-y-2">
  <div v-for="action in studentBehavior.pointActions" :key="action.id">
    <div class="flex justify-between items-center p-2 border rounded">
      <span>{{ action.note || `${action.action_type} ${action.value} pts` }}</span>
      <q-btn
        v-if="!action.canceled"
        size="sm"
        color="negative"
        label="Cancel"
        @click="cancelAction(action.id)"
      />
      <span v-else class="text-gray-500 italic">Canceled</span>
    </div>
  </div>
</div>
```

---

## 🔄 Data Flow Comparison

### **Current (MVP)**
```
User clicks "Apply behavior" on 3 students
  ↓
POST /api/student-behaviors/quick-create × 3
  ↓ (each request)
StudentBehaviorsMain created
StudentBehavior created with points_plus=5, points_minus=0
  ↓
Frontend displays: +5 points (from StudentBehavior)

If we need to change: Have to update StudentBehavior directly
  ↓ Problem: No history of what actually happened
```

### **Target (Transaction-Based)**
```
User clicks "Apply behavior" on 3 students
  ↓
POST /api/student-behaviors/quick-create × 3
  ↓ (each request)
1. StudentBehaviorsMain created/found
2. StudentBehavior created/found with points_plus=0, points_minus=0
3. StudentBehaviorsPointAction created
   ├─ value: 5
   ├─ action_type: 'plus'
   ├─ created_by: teacher_id
   └─ canceled: false
4. Observer triggers recalculation
   └─ StudentBehavior.points_plus = SUM(non-canceled plus actions) = 5
  ↓
Frontend displays: +5 points (from StudentBehavior)

If teacher needs to cancel: Just mark the point action as canceled
  ↓
Observer recalculates StudentBehavior.points_plus = 0
  ↓ Benefit: Full audit trail of what happened when
```

---

## 📈 Benefits of Transaction Model

| Feature | Current MVP | Transaction Model |
|---------|-------------|------------------|
| **Single student history** | ❌ Can't see individual actions | ✅ Every action tracked |
| **Cancel/Revoke** | ❌ Have to delete record | ✅ Mark as canceled, keeps history |
| **Audit trail** | ❌ No who/when/why | ✅ created_by, timestamps, reasons |
| **Recalculate totals** | ❌ Manual update needed | ✅ Automatic via observer |
| **Analytics** | ❌ Can't analyze actions | ✅ Can aggregate, filter by reason |
| **Undo** | ❌ Lost forever | ✅ Cancel and restore (revoke cancel) |

---

## 🚀 Implementation Priority

### **Phase 1 (MVP - Current)**
- ✅ Bulk apply behavior to students
- ✅ View summaries (total points)
- ✅ Leaderboard

### **Phase 2 (Transaction Model - Recommended)**
- Add StudentBehaviorsPointAction creation
- Auto-recalculate totals via observer
- Add cancel endpoint

### **Phase 3 (UI Enhancements)**
- View individual transaction history
- Cancel/restore UI
- Transaction details modal

### **Phase 4 (Analytics)**
- Reports by behavior type
- Trend analysis
- Student engagement metrics

---

## 🔧 Migration Path

If you want to implement the transaction model:

1. Keep current `/api/student-behaviors/quick-create` API contract
2. Change backend to create StudentBehaviorsPointAction instead
3. Add observer to recalculate totals
4. No frontend changes needed immediately
5. Optional: Add UI for viewing/canceling actions

---

## 💡 Recommendation

**For an MVP, the current model is fine**, but I recommend implementing the transaction model because:

1. **No extra complexity** - Logic moves to backend (good separation)
2. **Future-proof** - Already have the tables and schema
3. **Non-breaking change** - API stays same, only backend changes
4. **Better data integrity** - Automatic recalculation prevents inconsistencies

Would you like me to implement Phase 2 (refactor to transaction model)?
