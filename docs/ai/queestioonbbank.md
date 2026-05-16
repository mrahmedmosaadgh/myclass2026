# بناء نموذج JSON لبنك الأسئلة - الإصدار 1
# Building JSON Model for Question Bank - Version 1

سأساعدك في بناء نموذج JSON احترافي لأسئلة الاختيار من متعدد (MCQ) يتوافق مع جميع المعايير المذكورة.

---

## 📋 نموذج JSON للسؤال الواحد (Single Question JSON Model)

```json
{
  "question_id": "Q001_MATH_ALG_2024_001",
  "question_type": "multiple_choice",
  
  "metadata": {
    "subject": "Mathematics",
    "subject_ar": "الرياضيات",
    "grade_level": "Grade 10",
    "unit": "Algebra",
    "unit_ar": "الجبر",
    "topic": "Quadratic Equations",
    "topic_ar": "المعادلات التربيعية",
    "sub_topic": "Solving by Factoring",
    "sub_topic_ar": "الحل بالتحليل",
    
    "cognitive_level": {
      "bloom_taxonomy": "Apply",
      "bloom_taxonomy_ar": "تطبيق",
      "level_number": 3,
      "description": "Student applies learned concepts to solve problems"
    },
    
    "difficulty": {
      "level": "Medium",
      "level_ar": "متوسط",
      "difficulty_index": 0.55,
      "target_range": "0.30-0.70"
    },
    
    "learning_outcomes": [
      "LO_MATH_10_ALG_001: Solve quadratic equations using factoring method",
      "LO_MATH_10_ALG_002: Identify factors of quadratic expressions"
    ],
    
    "standards_alignment": [
      "CCSS.MATH.CONTENT.HSA.REI.B.4",
      "Saudi_Math_Standards_10_3.2"
    ]
  },
  
  "question_content": {
    "language": "en",
    "text": "What are the solutions to the equation x² - 5x + 6 = 0?",
    "text_ar": "ما هي حلول المعادلة س² - ٥س + ٦ = ٠؟",
    
    "instructions": "Choose the correct answer",
    "instructions_ar": "اختر الإجابة الصحيحة",
    
    "media": {
      "has_image": false,
      "image_url": null,
      "has_audio": false,
      "audio_url": null,
      "has_video": false,
      "video_url": null
    }
  },
  
  "options": [
    {
      "option_id": "A",
      "text": "x = 2 or x = 3",
      "text_ar": "س = ٢ أو س = ٣",
      "is_correct": true,
      "media_url": null
    },
    {
      "option_id": "B",
      "text": "x = -2 or x = -3",
      "text_ar": "س = -٢ أو س = -٣",
      "is_correct": false,
      "media_url": null
    },
    {
      "option_id": "C",
      "text": "x = 1 or x = 6",
      "text_ar": "س = ١ أو س = ٦",
      "is_correct": false,
      "media_url": null
    },
    {
      "option_id": "D",
      "text": "x = -1 or x = -6",
      "text_ar": "س = -١ أو س = -٦",
      "is_correct": false,
      "media_url": null
    }
  ],
  
  "answer_key": {
    "correct_option": "A",
    "explanation": "Factor the equation: (x-2)(x-3) = 0. Therefore, x = 2 or x = 3",
    "explanation_ar": "حلل المعادلة: (س-٢)(س-٣) = ٠. إذاً، س = ٢ أو س = ٣",
    
    "solution_steps": [
      "Step 1: Write the equation in standard form: x² - 5x + 6 = 0",
      "Step 2: Find two numbers that multiply to 6 and add to -5: -2 and -3",
      "Step 3: Factor: (x - 2)(x - 3) = 0",
      "Step 4: Apply zero product property: x - 2 = 0 or x - 3 = 0",
      "Step 5: Solve: x = 2 or x = 3"
    ],
    
    "common_mistakes": [
      "Forgetting to change signs when factoring",
      "Confusing addition and multiplication of roots"
    ]
  },
  
  "scoring": {
    "points": 1,
    "partial_credit": false,
    "time_limit_seconds": 120,
    "penalties": {
      "wrong_answer": 0,
      "no_answer": 0
    }
  },
  
  "statistics": {
    "times_used": 45,
    "correct_responses": 28,
    "incorrect_responses": 17,
    
    "facility_index": 0.62,
    "discrimination_index": 0.42,
    
    "distractor_analysis": {
      "A": {"count": 28, "percentage": 62.2, "note": "Correct answer"},
      "B": {"count": 8, "percentage": 17.8, "note": "Common sign error"},
      "C": {"count": 6, "percentage": 13.3, "note": "Incorrect factoring"},
      "D": {"count": 3, "percentage": 6.7, "note": "Multiple errors"}
    },
    
    "performance_by_group": {
      "high_achievers": {"correct": 0.89, "selected_option": "A"},
      "medium_achievers": {"correct": 0.64, "selected_option": "A"},
      "low_achievers": {"correct": 0.31, "selected_option": "B"}
    }
  },
  
  "quality_control": {
    "status": "approved",
    "created_by": "Dr. Ahmed Ali",
    "created_date": "2024-01-15",
    "reviewed_by": "Dr. Sarah Mohammed",
    "review_date": "2024-01-20",
    "last_modified": "2024-02-10",
    "modification_reason": "Updated Arabic translation",
    
    "validation_checks": {
      "linguistic_review": true,
      "content_accuracy": true,
      "bias_check": true,
      "statistical_validation": true
    },
    
    "reviewer_comments": "Question meets all quality standards. Good distractor effectiveness."
  },
  
  "tags": [
    "algebra",
    "quadratic_equations",
    "factoring",
    "polynomial",
    "grade_10"
  ],
  
  "accessibility": {
    "screen_reader_compatible": true,
    "alt_text_available": true,
    "large_print_version": true,
    "translation_available": ["ar", "en"]
  },
  
  "version": "1.0",
  "schema_version": "1.0.0"
}
```

---

## 📊 نموذج JSON لبنك الأسئلة الكامل (Complete Question Bank JSON)

```json
{
  "question_bank": {
    "bank_id": "QB_MATH_2024_V1",
    "bank_name": "Mathematics Question Bank - Grade 10",
    "bank_name_ar": "بنك أسئلة الرياضيات - الصف العاشر",
    
    "bank_metadata": {
      "version": "1.0",
      "created_date": "2024-01-01",
      "last_updated": "2024-05-14",
      "academic_year": "2024-2025",
      "institution": "Ministry of Education",
      "institution_ar": "وزارة التعليم",
      
      "total_questions": 500,
      "active_questions": 485,
      "retired_questions": 15,
      
      "subjects_covered": ["Mathematics"],
      "grade_levels": ["Grade 10"],
      "languages": ["en", "ar"]
    },
    
    "bank_statistics": {
      "overall_reliability": 0.87,
      "average_difficulty": 0.55,
      "average_discrimination": 0.38,
      
      "distribution_by_difficulty": {
        "easy": {"count": 95, "percentage": 19.6},
        "medium": {"count": 295, "percentage": 60.8},
        "hard": {"count": 95, "percentage": 19.6}
      },
      
      "distribution_by_bloom": {
        "remember": {"count": 75, "percentage": 15.5},
        "understand": {"count": 125, "percentage": 25.8},
        "apply": {"count": 150, "percentage": 30.9},
        "analyze": {"count": 85, "percentage": 17.5},
        "evaluate": {"count": 35, "percentage": 7.2},
        "create": {"count": 15, "percentage": 3.1}
      }
    },
    
    "questions": [
      {
        "question_id": "Q001_MATH_ALG_2024_001",
        "question_type": "multiple_choice",
        "...": "... (السؤال الكامل كما في النموذج أعلاه)"
      },
      {
        "question_id": "Q002_MATH_ALG_2024_002",
        "question_type": "multiple_choice",
        "...": "... (سؤال آخر)"
      }
    ]
  }
}
```

---

## 🎯 أنواع الأسئلة الإضافية (Additional Question Types)

### 1️⃣ أسئلة صح/خطأ (True/False)

```json
{
  "question_id": "Q150_MATH_GEO_2024_001",
  "question_type": "true_false",
  
  "question_content": {
    "text": "The sum of angles in any triangle is always 180 degrees.",
    "text_ar": "مجموع زوايا أي مثلث يساوي دائماً ١٨٠ درجة."
  },
  
  "options": [
    {
      "option_id": "T",
      "text": "True",
      "text_ar": "صح",
      "is_correct": true
    },
    {
      "option_id": "F",
      "text": "False",
      "text_ar": "خطأ",
      "is_correct": false
    }
  ],
  
  "answer_key": {
    "correct_option": "T",
    "explanation": "This is a fundamental property of Euclidean geometry.",
    "explanation_ar": "هذه خاصية أساسية في الهندسة الإقليدية."
  }
}
```

### 2️⃣ أسئلة الإجابة القصيرة (Short Answer)

```json
{
  "question_id": "Q250_MATH_ALG_2024_050",
  "question_type": "short_answer",
  
  "question_content": {
    "text": "Simplify the expression: 3x + 5x - 2x",
    "text_ar": "بسّط المقدار: ٣س + ٥س - ٢س"
  },
  
  "answer_key": {
    "acceptable_answers": [
      "6x",
      "٦س",
      "6 x",
      "x*6"
    ],
    "exact_match_required": false,
    "case_sensitive": false,
    
    "explanation": "Combine like terms: 3 + 5 - 2 = 6, so the answer is 6x",
    "explanation_ar": "اجمع الحدود المتشابهة: ٣ + ٥ - ٢ = ٦، إذاً الإجابة ٦س"
  },
  
  "scoring": {
    "points": 2,
    "partial_credit_rules": [
      {"answer_pattern": "x6", "points": 1, "note": "Correct but wrong order"},
      {"answer_pattern": "6", "points": 1, "note": "Forgot variable"}
    ]
  }
}
```

---

## 💾 هل تريد مني إنشاء ملف JSON كامل؟

Would you like me to create a complete JSON file with:
1. ✅ 10-20 sample questions
2. ✅ Multiple question types (MCQ, True/False, Short Answer)
3. ✅ Complete metadata and statistics
4. ✅ Both Arabic and English content
5. ✅ Ready to import into a database

دعني أعرف وسأقوم بإنشاء الملف الكامل لك! 🚀