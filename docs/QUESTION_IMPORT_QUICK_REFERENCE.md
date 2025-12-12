# Question Import - Quick Reference Guide

## 3 Easy Ways to Import Questions

### Method 1: Upload File 📁
**Best for:** Bulk imports from existing Excel/CSV files

1. Select question settings (type, difficulty, grade, etc.)
2. Click "Upload File" tab
3. Choose your Excel or CSV file
4. Review and import

**File Format:**
```
question_text,option_a,option_b,option_c,option_d,correct_answer
What is 2+2?,2,3,4,5,C
Capital of France?,London,Paris,Berlin,Madrid,B
```

---

### Method 2: Paste from Excel 📋
**Best for:** Quick imports from spreadsheets

1. Select question settings
2. Click "Paste from Excel" tab
3. Copy cells from Excel/Google Sheets (Ctrl+C)
4. Paste into the text area (Ctrl+V)
5. Click "Process Pasted Data"

**Example:**
```
What is 2+2?	2	3	4	5	C
Capital of France?	London	Paris	Berlin	Madrid	B
```

**Tips:**
- Include or exclude headers - both work!
- Tab-separated or comma-separated both supported
- Just select and copy from your spreadsheet

---

### Method 3: Paste JSON 💻
**Best for:** AI-generated questions or programmatic imports

1. Select question settings
2. Click "Paste JSON" tab
3. Click "Copy AI Prompt" button
4. Paste prompt into ChatGPT/Claude
5. Copy the AI's JSON response
6. Paste into the JSON text area
7. Click "Process JSON"

**JSON Format:**
```json
[
  {
    "question_text": "What is 2+2?",
    "option_a": "2",
    "option_b": "3",
    "option_c": "4",
    "option_d": "5",
    "correct_answer": "C"
  },
  {
    "question_text": "What is the capital of France?",
    "option_a": "London",
    "option_b": "Paris",
    "option_c": "Berlin",
    "option_d": "Madrid",
    "correct_answer": "B"
  }
]
```

---

## AI Prompt Feature 🤖

### How to Use AI to Generate Questions

1. **In the Import Page:**
   - Select your question type (Multiple Choice, True/False, etc.)
   - Select grade, subject, difficulty (optional)
   - Click "Paste JSON" tab
   - Click "Copy AI Prompt" button

2. **In ChatGPT/Claude:**
   - Paste the copied prompt
   - AI will generate questions in the correct JSON format

3. **Back in Import Page:**
   - Copy the AI's JSON response
   - Paste into the JSON text area
   - Click "Process JSON"
   - Review and import!

### Example AI Prompt

The system generates a prompt like this:

```
Generate 10 Multiple Choice questions in JSON format. Use this exact structure:

[
  {
    "question_text": "What is 2+2?",
    "option_a": "2",
    "option_b": "3",
    "option_c": "4",
    "option_d": "5",
    "correct_answer": "C"
  }
]

Requirements:
- Generate exactly 10 questions
- Each question must have 4 options (option_a, option_b, option_c, option_d)
- correct_answer must be A, B, C, or D
- Return ONLY the JSON array, no additional text
- Make questions appropriate for Grade 5
- Subject: Mathematics
- Difficulty: Medium
```

---

## What You Need to Provide

### In Step 1 (Settings):
✅ **Question Type** (Required) - Multiple Choice, True/False, etc.
✅ **Difficulty** (Optional) - Easy, Medium, Hard
✅ **Grade** (Optional) - Select from dropdown
✅ **Subject** (Optional) - Select from dropdown
✅ **Topic** (Optional) - Select from dropdown
✅ **Bloom Level** (Optional) - 1-6
✅ **Status** (Optional) - draft, active, archived

### In Step 2 (Questions):
For **Multiple Choice/True-False:**
- `question_text` - The question
- `option_a` - First option
- `option_b` - Second option
- `option_c` - Third option
- `option_d` - Fourth option
- `correct_answer` - A, B, C, or D

For **Short Answer/Essay:**
- `question_text` - The question only

---

## Benefits of Each Method

| Method | Speed | Ease | Best For |
|--------|-------|------|----------|
| **Upload File** | ⭐⭐⭐ | ⭐⭐⭐ | Existing files, large batches |
| **Paste Excel** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Quick imports, small batches |
| **Paste JSON** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | AI-generated, programmatic |

---

## Common Workflows

### Workflow 1: Quick Import from Excel
1. Open Excel with your questions
2. Select cells (including headers)
3. Copy (Ctrl+C)
4. Go to import page
5. Select settings in Step 1
6. Click "Paste from Excel" tab
7. Paste (Ctrl+V)
8. Click "Process Pasted Data"
9. Review and import!

**Time:** ~30 seconds ⚡

### Workflow 2: AI-Generated Questions
1. Go to import page
2. Select settings in Step 1
3. Click "Paste JSON" tab
4. Click "Copy AI Prompt"
5. Open ChatGPT
6. Paste prompt
7. Copy AI's JSON response
8. Paste into import page
9. Click "Process JSON"
10. Review and import!

**Time:** ~1 minute 🤖

### Workflow 3: Traditional File Upload
1. Prepare Excel/CSV file
2. Go to import page
3. Select settings in Step 1
4. Click "Upload File" tab
5. Choose file
6. Review and import!

**Time:** ~1 minute 📁

---

## Tips & Tricks

### Excel/Paste Tips:
- ✅ Headers are optional - system detects them
- ✅ Tab or comma separated both work
- ✅ Copy directly from Excel - no need to save as CSV
- ✅ Can paste from Google Sheets too

### JSON Tips:
- ✅ Use "Copy AI Prompt" for perfect format
- ✅ AI generates 10 questions by default
- ✅ Can modify AI output before importing
- ✅ Validate JSON at jsonlint.com if needed

### General Tips:
- ✅ Select question type FIRST (required)
- ✅ Other settings are optional but recommended
- ✅ Preview shows validation before import
- ✅ Can import multiple times with different settings

---

## Troubleshooting

### "Please select a question type first"
**Solution:** Go to Step 1 and select a question type from the dropdown

### "No valid questions found"
**Solution:** Check your data format - ensure question_text column exists

### "Invalid JSON format"
**Solution:** 
- Use "View JSON Example" to see correct format
- Validate JSON at jsonlint.com
- Ensure it's an array `[...]` not an object `{...}`

### "Failed to parse data"
**Solution:**
- For Excel paste: Ensure data is tab or comma separated
- For JSON: Check for syntax errors (missing commas, quotes)
- Try the "View Example" button to see correct format

---

## Need Help?

1. Click "View Example" to see correct format
2. Click "Download Template" for a starter file
3. Click "Copy AI Prompt" to generate with AI
4. Check this guide for common issues

---

**Pro Tip:** Start with the "Paste from Excel" method - it's the fastest and easiest! Just copy from your spreadsheet and paste. Done! ✨
