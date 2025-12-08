export default {
    documentation: {
        new: 'New Documentation',
        edit: 'Edit Documentation',
        title: 'Title',
        type: 'Type',
        status: 'Status',
        tags: 'Tags',
        enter_tags: 'Enter tags',
        content: 'Content'
    },
    // Common translations
    common: {
        switchToDark: 'Switch to Dark Mode',
        switchToLight: 'Switch to Light Mode',
        chat: 'Chat',
        language: 'Language',
        english: 'English',
        arabic: 'Arabic',
        save: 'Save',
        cancel: 'Cancel',
        close: 'Close',
        loading: 'Loading...',
        search: 'Search',
        searchPlaceholder: 'Search...',
        searching: 'Searching...',
        noSearchResults: 'No search results found',
        noResults: 'No results found',
        settings: 'Settings',
        profile: 'Profile',
        logout: 'Logout',
        create: 'Create',
        update: 'Update',
        delete: 'Delete',
        edit: 'Edit',
        navigation: 'Navigation',
        dashboard: 'Dashboard',
        calendar: 'Calendar',
        messages: 'Messages',
        home: 'Home',
        pages: 'Pages',
        users: 'Users',
        documents: 'Documents',
        allRightsReserved: 'All rights reserved'
    },
    permissions: {
        dashboard: 'Permissions Dashboard',
        label: 'Permissions',
        create: 'Create Permission',
        edit: 'Edit Permission',
        name: 'Permission Name',
        confirm_delete: 'Are you sure you want to delete the permission "{name}"?'
    },
    permission: {
        create: 'Create Permission',
        edit: 'Edit Permission',
        name: 'Permission Name',
        confirm_delete: 'Are you sure you want to delete the permission "{name}"?'
    },
    user: {
        management_dashboard: 'User Management Dashboard',
        export_users: 'Export Users',
        create: 'Create User',
        edit: 'Edit User',
        delete: 'Delete User',
        confirm_delete: 'Are you sure you want to delete this user?',
        reset_password: 'Reset Password',
        confirm_reset: 'Are you sure you want to reset the password for {name} to 12345678?',
        name: 'Name',
        email: 'Email',
        password: 'Password',
        confirm_password: 'Confirm Password',
        roles: 'Roles',
        manage_roles_for: 'Manage Roles for {name}',
        save_roles: 'Save Roles'
    },
    role: {
        create: 'Create Role',
        edit: 'Edit Role',
        name: 'Role Name',
        confirm_delete: 'Are you sure you want to delete the role "{name}"?'
    },
    tabs: {
        users_management: 'Users Management',
        roles: 'Roles',
        permissions: 'Permissions',
        users_roles: 'Users & Roles'
    },
    quiz: {
        // Progress and navigation
        progress: 'Question {current} of {total}',
        complete: '{percentage}% Complete',
        questionNumber: 'Question {number}',
        
        // Actions
        submit: 'Submit Answer',
        next: 'Next Question',
        previous: 'Previous Question',
        finish: 'Finish Quiz',
        review: 'Review Answers',
        startQuiz: 'Start Quiz',
        retakeQuiz: 'Retake Quiz',
        
        // Feedback
        correct: 'Correct!',
        incorrect: 'Incorrect',
        explanation: 'Explanation:',
        hint: 'Hint:',
        rationale: 'Rationale:',
        correctAnswer: 'Correct Answer:',
        yourAnswer: 'Your Answer:',
        
        // Results
        results: {
            title: 'Quiz Results',
            score: 'You scored {correct} out of {total}',
            percentage: '{percentage}% correct',
            passed: 'Congratulations! You passed!',
            failed: 'Keep practicing!',
            timeSpent: 'Time spent: {time}',
            completedAt: 'Completed at: {time}',
            summary: 'Summary',
            correctAnswers: 'Correct Answers',
            incorrectAnswers: 'Incorrect Answers',
            unanswered: 'Unanswered',
            reviewAnswers: 'Review Your Answers'
        },
        
        // Question types
        questionTypes: {
            multipleChoice: 'Multiple Choice',
            trueFalse: 'True/False',
            fillBlank: 'Fill in the Blank',
            multiSelect: 'Multiple Select',
            shortAnswer: 'Short Answer',
            essay: 'Essay'
        },
        
        // Options
        options: {
            optionA: 'Option A',
            optionB: 'Option B',
            optionC: 'Option C',
            optionD: 'Option D',
            optionE: 'Option E',
            true: 'True',
            false: 'False',
            selectAll: 'Select all that apply'
        },
        
        // Status messages
        status: {
            loading: 'Loading quiz...',
            submitting: 'Submitting answer...',
            calculating: 'Calculating results...',
            answered: 'Answered',
            unanswered: 'Not answered',
            current: 'Current question'
        },
        
        // Errors
        errors: {
            loadFailed: 'Failed to load quiz. Please try again.',
            submitFailed: 'Failed to submit answer. Please try again.',
            noQuestions: 'No questions available.',
            invalidAnswer: 'Please select a valid answer.',
            networkError: 'Network error. Please check your connection.',
            timeout: 'Request timed out. Please try again.',
            selectAnswer: 'Please select an answer before continuing.',
            fillAnswer: 'Please provide an answer before continuing.',
            connectionError: 'Connection Error',
            timeoutError: 'Request Timeout',
            validationError: 'Validation Error',
            unauthorizedAccess: 'Access Denied',
            notFound: 'Not Found',
            unknownError: 'Error',
            unexpectedError: 'An unexpected error occurred'
        },
        
        // Validation
        validation: {
            required: 'This field is required',
            minLength: 'Answer must be at least {min} characters',
            maxLength: 'Answer must not exceed {max} characters',
            selectAtLeastOne: 'Please select at least one option'
        },
        
        // Configuration
        config: {
            reviewMode: 'Review Mode',
            autoAdvance: 'Auto Advance',
            showRationale: 'Show Rationale',
            timeLimit: 'Time Limit',
            shuffleQuestions: 'Shuffle Questions',
            shuffleOptions: 'Shuffle Options',
            enabled: 'Enabled',
            disabled: 'Disabled'
        },
        
        // Time
        time: {
            seconds: '{count} second | {count} seconds',
            minutes: '{count} minute | {count} minutes',
            hours: '{count} hour | {count} hours',
            remaining: 'Time remaining: {time}',
            expired: 'Time expired',
            warning: 'Only {time} remaining!'
        },
        
        // Accessibility
        a11y: {
            quizRegion: 'Quiz Assessment',
            progressBar: 'Quiz progress: {percentage}% complete',
            questionOptions: 'Question {number} options',
            selectedOption: 'Selected option',
            correctOption: 'Correct option',
            incorrectOption: 'Incorrect option',
            navigationControls: 'Quiz navigation controls',
            questionNavigator: 'Question navigator',
            skipToResults: 'Skip to results',
            announceCorrect: 'Your answer is correct',
            announceIncorrect: 'Your answer is incorrect',
            announceProgress: 'Question {current} of {total}',
            announceComplete: 'Quiz completed. You scored {percentage}%'
        },
        
        // Form
        form: {
            createQuestion: 'Create Question',
            editQuestion: 'Edit Question',
            updateQuestion: 'Update Question',
            questionType: 'Question Type',
            selectQuestionType: 'Select a question type',
            questionText: 'Question Text',
            enterQuestion: 'Enter your question here...',
            charactersCount: '{count} / {max} characters',
            answerOptions: 'Answer Options',
            correctAnswer: 'Correct answer',
            hints: 'Hints (Optional)',
            explanation: 'Explanation (Optional)',
            provideExplanation: 'Provide an explanation for this question...',
            saving: 'Saving...',
            gradeLevel: 'Grade Level',
            subject: 'Subject',
            topic: 'Topic',
            bloomLevel: 'Bloom Level',
            difficultyLevel: 'Difficulty Level',
            estimatedTime: 'Estimated Time (seconds)',
            status: 'Status'
        },
        
        // Import
        import: {
            title: 'Import Questions',
            selectFile: 'Select File',
            uploadFile: 'Upload File',
            uploadInstructions: 'Upload a CSV or Excel file to bulk import questions. The file should include the following columns:',
            fileFormat: 'File Format',
            csvFormat: 'CSV Format',
            excelFormat: 'Excel Format',
            templateDownload: 'Download Template',
            importing: 'Importing questions...',
            importQuestions: 'Import Questions',
            success: 'Successfully imported {count} questions',
            partialSuccess: 'Imported {success} questions with {errors} errors',
            failed: 'Import failed. Please check your file.',
            validationErrors: 'Validation Errors',
            rowError: 'Row {row}: {error}',
            clickToSelect: 'Click to select a file or drag and drop',
            fileHint: 'CSV, TXT, XLSX, or XLS',
            removeFile: 'Remove file',
            technicalDetails: 'Technical Details',
            retry: 'Retry',
            retrying: 'Retrying...',
            dismiss: 'Dismiss'
        }
    }
};







