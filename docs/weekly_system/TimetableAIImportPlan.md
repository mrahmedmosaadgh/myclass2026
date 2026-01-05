# Weekly Timetable AI Import Plan

## Goal

Enable quick paste-in import/update of a classroom schedule via "Dialog Z" in timetable-editor.

## Workflow

1. Click "Generate via AI" → copy prompt.
2. Paste into external AI → get JSON.
3. Adjust if needed → paste back into dialog.
4. Preview → Validate → Apply (create/update schedule).

## Accepted JSON Format

```json
{
  "classroom_id": 123,
  "entries": [
    {
      "day": "Sunday",
      "period": 1,
      "subject": "Math",
      "teacher": "TBD",
      "room": null,
      "notes": null
    }
  ]
}
```

### Field Specifications

- **day**: Sunday–Thursday
- **period**: integer (1..N)
- **subject**: string (e.g., "English NAFS", "Science NAFS")
- **teacher/room/notes**: optional

## Prompt Template (copy to AI)

```
Provide JSON only matching the schema below. No prose. Map subjects to periods for the given classroom and week.

Classroom: <classroom_id>
Subjects by day:
- Sunday: Math, Arabic, Science, Islamic, English
- Monday: Islamic, Capstone, ICT, French, English, Science
- Tuesday: Islamic, Math, SSE, Arabic, English NAFS, Noor Albian, Saudi culture
- Wednesday: PE, Islamic, Arabic, Robot, English, Math, Science
- Thursday: Islamic, SSE, Math NAFS, English, Science NAFS, Arabic, Saudi culture

Assign sequential periods starting at 1. Output:

{
  "classroom_id": <classroom_id>,
  "entries": [
    { "day": "Sunday", "period": 1, "subject": "Math" }
    ...
  ]
}
```

## Validation in Dialog

### Required Fields
- `classroom_id`
- `entries[].day`
- `entries[].period`
- `entries[].subject`

### Validation Rules
- No duplicate (day, period) combinations
- Days must be in allowed set (Sunday–Thursday)
- Periods must be positive integers

## Apply Modes

1. **Import** (replace whole week)
2. **Update** (merge by day+period)

## Error Handling

- Show first 5 issues with line/index hints
- Allow download of rejected JSON for fix
