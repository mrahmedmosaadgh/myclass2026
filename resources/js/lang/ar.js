export default {
    documentation: {
        new: 'وثائق جديدة',
        edit: 'تعديل الوثائق',
        title: 'العنوان',
        type: 'النوع',
        status: 'الحالة',
        tags: 'العلامات',
        enter_tags: 'أدخل العلامات',
        content: 'المحتوى'
    },
    // Common translations
    common: {
        switchToDark: 'التبديل إلى الوضع الداكن',
        switchToLight: 'التبديل إلى الوضع الفاتح',
        chat: 'الدردشة',
        language: 'اللغة',
        english: 'الإنجليزية',
        arabic: 'العربية',
        save: 'حفظ',
        cancel: 'إلغاء',
        close: 'إغلاق',
        loading: 'جاري التحميل...',
        search: 'بحث',
        searchPlaceholder: 'بحث...',
        searching: 'جاري البحث...',
        noSearchResults: 'لم يتم العثور على نتائج للبحث',
        noResults: 'لم يتم العثور على نتائج',
        settings: 'الإعدادات',
        profile: 'الملف الشخصي',
        logout: 'تسجيل الخروج',
        create: 'إنشاء',
        update: 'تحديث',
        delete: 'حذف',
        edit: 'تعديل',
        navigation: 'التنقل',
        dashboard: 'لوحة التحكم',
        calendar: 'التقويم',
        messages: 'الرسائل',
        home: 'الرئيسية',
        pages: 'الصفحات',
        users: 'المستخدمين',
        documents: 'المستندات',
        allRightsReserved: 'جميع الحقوق محفوظة'
    },
    permissions: {
        dashboard: 'لوحة الصلاحيات',
        label: 'الصلاحيات',
        create: 'إنشاء صلاحية',
        edit: 'تعديل صلاحية',
        name: 'اسم الصلاحية',
        confirm_delete: 'هل أنت متأكد من حذف الصلاحية "{name}"؟'
    },
    permission: {
        create: 'إنشاء صلاحية',
        edit: 'تعديل صلاحية',
        name: 'اسم الصلاحية',
        confirm_delete: 'هل أنت متأكد من حذف الصلاحية "{name}"؟'
    },
    user: {
        management_dashboard: 'لوحة إدارة المستخدمين',
        export_users: 'تصدير المستخدمين',
        create: 'إنشاء مستخدم',
        edit: 'تعديل مستخدم',
        delete: 'حذف مستخدم',
        confirm_delete: 'هل أنت متأكد من حذف هذا المستخدم؟',
        reset_password: 'إعادة تعيين كلمة المرور',
        confirm_reset: 'هل أنت متأكد من إعادة تعيين كلمة المرور لـ {name} إلى 12345678؟',
        name: 'الاسم',
        email: 'البريد الإلكتروني',
        password: 'كلمة المرور',
        confirm_password: 'تأكيد كلمة المرور',
        roles: 'الأدوار',
        manage_roles_for: 'إدارة أدوار {name}',
        save_roles: 'حفظ الأدوار'
    },
    role: {
        create: 'إنشاء دور',
        edit: 'تعديل دور',
        name: 'اسم الدور',
        confirm_delete: 'هل أنت متأكد من حذف الدور "{name}"؟'
    },
    tabs: {
        users_management: 'إدارة المستخدمين',
        roles: 'الأدوار',
        permissions: 'الصلاحيات',
        users_roles: 'المستخدمين والأدوار'
    },
    quiz: {
        // التقدم والتنقل
        progress: 'السؤال {current} من {total}',
        complete: '{percentage}٪ مكتمل',
        questionNumber: 'السؤال {number}',
        
        // الإجراءات
        submit: 'إرسال الإجابة',
        next: 'السؤال التالي',
        previous: 'السؤال السابق',
        finish: 'إنهاء الاختبار',
        review: 'مراجعة الإجابات',
        startQuiz: 'بدء الاختبار',
        retakeQuiz: 'إعادة الاختبار',
        
        // التعليقات
        correct: 'صحيح!',
        incorrect: 'خطأ',
        explanation: 'التفسير:',
        hint: 'تلميح:',
        rationale: 'السبب:',
        correctAnswer: 'الإجابة الصحيحة:',
        yourAnswer: 'إجابتك:',
        
        // النتائج
        results: {
            title: 'نتائج الاختبار',
            score: 'حصلت على {correct} من {total}',
            percentage: '{percentage}٪ صحيح',
            passed: 'تهانينا! لقد نجحت!',
            failed: 'استمر في التدريب!',
            timeSpent: 'الوقت المستغرق: {time}',
            completedAt: 'اكتمل في: {time}',
            summary: 'الملخص',
            correctAnswers: 'الإجابات الصحيحة',
            incorrectAnswers: 'الإجابات الخاطئة',
            unanswered: 'غير مجاب',
            reviewAnswers: 'راجع إجاباتك'
        },
        
        // أنواع الأسئلة
        questionTypes: {
            multipleChoice: 'اختيار من متعدد',
            trueFalse: 'صح/خطأ',
            fillBlank: 'املأ الفراغ',
            multiSelect: 'اختيار متعدد',
            shortAnswer: 'إجابة قصيرة',
            essay: 'مقال'
        },
        
        // الخيارات
        options: {
            optionA: 'الخيار أ',
            optionB: 'الخيار ب',
            optionC: 'الخيار ج',
            optionD: 'الخيار د',
            optionE: 'الخيار هـ',
            true: 'صح',
            false: 'خطأ',
            selectAll: 'اختر كل ما ينطبق'
        },
        
        // رسائل الحالة
        status: {
            loading: 'جاري تحميل الاختبار...',
            submitting: 'جاري إرسال الإجابة...',
            calculating: 'جاري حساب النتائج...',
            answered: 'تمت الإجابة',
            unanswered: 'لم تتم الإجابة',
            current: 'السؤال الحالي'
        },
        
        // الأخطاء
        errors: {
            loadFailed: 'فشل تحميل الاختبار. يرجى المحاولة مرة أخرى.',
            submitFailed: 'فشل إرسال الإجابة. يرجى المحاولة مرة أخرى.',
            noQuestions: 'لا توجد أسئلة متاحة.',
            invalidAnswer: 'يرجى اختيار إجابة صحيحة.',
            networkError: 'خطأ في الشبكة. يرجى التحقق من اتصالك.',
            timeout: 'انتهت مهلة الطلب. يرجى المحاولة مرة أخرى.',
            selectAnswer: 'يرجى اختيار إجابة قبل المتابعة.',
            fillAnswer: 'يرجى تقديم إجابة قبل المتابعة.',
            connectionError: 'خطأ في الاتصال',
            timeoutError: 'انتهت مهلة الطلب',
            validationError: 'خطأ في التحقق',
            unauthorizedAccess: 'تم رفض الوصول',
            notFound: 'غير موجود',
            unknownError: 'خطأ',
            unexpectedError: 'حدث خطأ غير متوقع'
        },
        
        // التحقق
        validation: {
            required: 'هذا الحقل مطلوب',
            minLength: 'يجب أن تكون الإجابة {min} حرفًا على الأقل',
            maxLength: 'يجب ألا تتجاوز الإجابة {max} حرفًا',
            selectAtLeastOne: 'يرجى اختيار خيار واحد على الأقل'
        },
        
        // التكوين
        config: {
            reviewMode: 'وضع المراجعة',
            autoAdvance: 'التقدم التلقائي',
            showRationale: 'إظهار السبب',
            timeLimit: 'حد الوقت',
            shuffleQuestions: 'خلط الأسئلة',
            shuffleOptions: 'خلط الخيارات',
            enabled: 'مفعّل',
            disabled: 'معطّل'
        },
        
        // الوقت
        time: {
            seconds: '{count} ثانية | {count} ثانية',
            minutes: '{count} دقيقة | {count} دقيقة',
            hours: '{count} ساعة | {count} ساعة',
            remaining: 'الوقت المتبقي: {time}',
            expired: 'انتهى الوقت',
            warning: 'فقط {time} متبقي!'
        },
        
        // إمكانية الوصول
        a11y: {
            quizRegion: 'تقييم الاختبار',
            progressBar: 'تقدم الاختبار: {percentage}٪ مكتمل',
            questionOptions: 'خيارات السؤال {number}',
            selectedOption: 'الخيار المحدد',
            correctOption: 'الخيار الصحيح',
            incorrectOption: 'الخيار الخاطئ',
            navigationControls: 'عناصر التحكم في التنقل في الاختبار',
            questionNavigator: 'متصفح الأسئلة',
            skipToResults: 'الانتقال إلى النتائج',
            announceCorrect: 'إجابتك صحيحة',
            announceIncorrect: 'إجابتك خاطئة',
            announceProgress: 'السؤال {current} من {total}',
            announceComplete: 'اكتمل الاختبار. حصلت على {percentage}٪'
        },
        
        // النموذج
        form: {
            createQuestion: 'إنشاء سؤال',
            editQuestion: 'تعديل سؤال',
            updateQuestion: 'تحديث السؤال',
            questionType: 'نوع السؤال',
            selectQuestionType: 'اختر نوع السؤال',
            questionText: 'نص السؤال',
            enterQuestion: 'أدخل سؤالك هنا...',
            charactersCount: '{count} / {max} حرف',
            answerOptions: 'خيارات الإجابة',
            correctAnswer: 'الإجابة الصحيحة',
            hints: 'التلميحات (اختياري)',
            explanation: 'التفسير (اختياري)',
            provideExplanation: 'قدم تفسيرًا لهذا السؤال...',
            saving: 'جاري الحفظ...',
            gradeLevel: 'المستوى الدراسي',
            subject: 'المادة',
            topic: 'الموضوع',
            bloomLevel: 'مستوى بلوم',
            difficultyLevel: 'مستوى الصعوبة',
            estimatedTime: 'الوقت المقدر (بالثواني)',
            status: 'الحالة'
        },
        
        // الاستيراد
        import: {
            title: 'استيراد الأسئلة',
            selectFile: 'اختر ملف',
            uploadFile: 'رفع الملف',
            uploadInstructions: 'قم بتحميل ملف CSV أو Excel لاستيراد الأسئلة بشكل جماعي. يجب أن يتضمن الملف الأعمدة التالية:',
            fileFormat: 'تنسيق الملف',
            csvFormat: 'تنسيق CSV',
            excelFormat: 'تنسيق Excel',
            templateDownload: 'تحميل النموذج',
            importing: 'جاري استيراد الأسئلة...',
            importQuestions: 'استيراد الأسئلة',
            success: 'تم استيراد {count} سؤال بنجاح',
            partialSuccess: 'تم استيراد {success} سؤال مع {errors} أخطاء',
            failed: 'فشل الاستيراد. يرجى التحقق من ملفك.',
            validationErrors: 'أخطاء التحقق',
            rowError: 'الصف {row}: {error}',
            clickToSelect: 'انقر لتحديد ملف أو اسحب وأفلت',
            fileHint: 'CSV أو TXT أو XLSX أو XLS',
            removeFile: 'إزالة الملف',
            technicalDetails: 'التفاصيل الفنية',
            retry: 'إعادة المحاولة',
            retrying: 'جاري إعادة المحاولة...',
            dismiss: 'إغلاق'
        }
    }
};



