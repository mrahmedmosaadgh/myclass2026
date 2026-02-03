# Skill-Based Adaptive Practice System

## Overview

The Skill-Based Adaptive Practice System is an intelligent learning platform designed to help students master specific skills through adaptive practice sessions. Unlike traditional exam systems, this system focuses on gradual skill development with personalized difficulty adjustment based on student performance.

## Key Features

### Adaptive Learning Algorithm
- **SmartScore System**: A dynamic scoring system that adjusts based on correctness, difficulty, streaks, and time taken
- **Difficulty Adaptation**: Questions adapt to the student's current ability level
- **Performance Tracking**: Detailed analytics on progress, accuracy, and mastery

### User Experience
- **Skill Browser**: Intuitive interface to browse and select skills by grade, subject, and category
- **Real-time Feedback**: Immediate feedback on answers with explanations
- **Progress Visualization**: Visual indicators of progress and mastery levels
- **Achievement System**: Badges and awards to motivate continued practice

### Administrative Tools
- **Skill Management**: Create and organize skills into categories
- **Question Linking**: Associate existing questions with specific skills
- **Difficulty Rating**: Set difficulty levels for each question
- **Progress Monitoring**: Track student progress across all skills

## Architecture

### Database Schema

The system consists of 7 main tables:

1. **skill_categories**: Organizes skills by grade and subject
2. **skills**: Individual skills with difficulty ranges and descriptions
3. **skill_questions**: Pivot table linking skills to questions with difficulty overrides
4. **user_skill_progress**: Tracks individual student progress per skill
5. **skill_practice_sessions**: Records practice sessions with metrics
6. **skill_practice_answers**: Stores individual answer records with metadata
7. **skill_awards**: Achievement system for recognizing milestones

### Backend Services

1. **SmartScoreService**: Calculates score changes and recommends difficulty levels
2. **AdaptiveQuestionService**: Selects appropriate questions based on user ability
3. **SkillProgressService**: Manages progress tracking and achievement recognition

### Frontend Components

1. **Skill Browser**: For browsing and selecting skills
2. **Practice Session**: Main practice interface
3. **SmartScore Bar**: Visual progress indicator
4. **Feedback Modal**: Immediate answer feedback
5. **Progress Dashboard**: Detailed analytics
6. **Awards Gallery**: Achievement showcase
7. **Question Counter**: Session statistics

## API Endpoints

### Student Endpoints

- `GET /skill-practice/categories` - List skill categories
- `GET /skill-practice/skills` - List available skills
- `GET /skill-practice/skills/{skill}` - Get skill details
- `POST /skill-practice/skills/{skill}/start` - Start a practice session
- `POST /skill-practice/next-question` - Get next adaptive question
- `POST /skill-practice/submit-answer` - Submit an answer
- `POST /skill-practice/end-session/{session}` - End practice session
- `GET /skill-practice/progress` - Get overall progress
- `GET /skill-practice/progress/{skill}` - Get skill-specific progress
- `GET /skill-practice/awards` - Get earned awards

### Admin Endpoints

- `GET /admin/skills` - View all skills and categories
- `POST /admin/skills` - Create a new skill
- `PUT /admin/skills/{skill}` - Update a skill
- `DELETE /admin/skills/{skill}` - Delete a skill
- `POST /admin/skills/{skill}/link-questions` - Link questions to skill
- `DELETE /admin/skills/{skill}/unlink-question/{question}` - Unlink question from skill
- `GET /admin/skills/{skill}/linked-questions` - Get linked questions

## SmartScore Algorithm

The SmartScore system uses the following formula to calculate score changes:

- Base points for correct answer: Based on question difficulty (1-10 scale)
- Streak bonus: Additional points for consecutive correct answers
- Time bonus: Bonus points for quick responses (within time threshold)
- Penalty: Points deducted for incorrect answers

Mastery levels are defined as:
- Beginner: 0-29
- Developing: 30-49
- Proficient: 50-79
- Mastery: 80-100

## Configuration

The system behavior can be customized through the `config/skills.php` file:

```php
return [
    // Thresholds for different mastery levels
    'mastery_levels' => [
        'beginner' => ['min' => 0, 'max' => 29],
        'developing' => ['min' => 30, 'max' => 49],
        'proficient' => ['min' => 50, 'max' => 79],
        'mastery' => ['min' => 80, 'max' => 100],
    ],

    // Scoring parameters
    'scoring' => [
        'base_correct_easy' => 3,      // Points for easy question
        'base_correct_medium' => 5,    // Points for medium question
        'base_correct_hard' => 8,      // Points for hard question
        'incorrect_answer' => -2,      // Penalty for incorrect answer
        'streak_bonus' => 2,           // Bonus every N correct answers
        'streak_length' => 3,          // How many correct answers trigger bonus
    ],

    // Adaptive selection settings
    'adaptive' => [
        'exclude_recent_count' => 5,   // Number of recent questions to exclude
        'time_bonus_threshold' => 20,  // Seconds to earn time bonus
        'time_bonus_points' => 1,      // Bonus points for fast answers
    ],

    // Awards configuration
    'awards' => [
        'milestones' => [
            'streak_5' => '5-in-a-row',
            'first_mastery' => 'First Mastery',
            'speed_demon' => 'Quick Answer',
            'perfect_session' => 'Perfect Session',
        ],
    ],
];
```

## Role-Based Access

- **Students**: Can browse skills, start practice sessions, view personal progress and awards
- **Teachers**: All student features plus create skills, link questions to skills, view class progress
- **Administrators**: All features including managing skill categories and bulk operations

## Performance Considerations

- Questions are cached during sessions to reduce database queries
- Progress updates are batched for efficiency
- Recent question exclusions use efficient lookups
- SmartScore calculations are optimized for real-time feedback

## Testing

Comprehensive test coverage includes:

- Unit tests for core services (SmartScore, AdaptiveQuestion)
- Feature tests for API endpoints
- Integration tests for practice session flow
- Progress tracking verification
- E2E tests for complete user journeys

## Future Enhancements

- Personalized study recommendations
- Collaborative learning features
- Advanced analytics and reporting
- Integration with curriculum standards
- Offline practice capabilities
- Multi-language support