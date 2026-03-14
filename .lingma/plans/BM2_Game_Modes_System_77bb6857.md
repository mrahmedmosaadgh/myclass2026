# BM2 Game Modes System Implementation Plan

## Overview
Create 3 distinct game modes for BM2 assessments, each with unique mechanics, visual styles, and gameplay. Users select their preferred mode before starting the assessment.

## Game Modes

### 1. **Falling Questions (Arcade Style)**
- Questions fall from top of screen
- Answer options float randomly across screen
- User clicks falling question to "catch" it
- Must answer before question disappears off-screen
- Mouse cursor acts as a "catcher"
- Power-ups appear randomly (time freeze, extra life, double points)

### 2. **Orbiting Options (Solar System)**
- Question stays centered
- Answer options orbit around question like planets
- User clicks correct orbiting option
- Options move at different speeds
- Time limit = shrinking orbit ring
- Power-ups: slow orbit, highlight correct answer, freeze time

### 3. **Space Adventure (Shooter Style)**
- Questions appear as floating cards in space
- Options are planets/asteroids
- User has a "shooter" at bottom
- Aim and click to shoot at correct answer
- Questions have health bars (multiple parts)
- Combo system for consecutive hits

## Implementation Files

### 1. Game Mode Selector Component
**File**: `/resources/js/Components/Courses/bm2/GameModeSelector.vue`
- Display 3 game mode cards with previews
- Mode selection UI
- Difficulty settings
- Instructions for each mode

### 2. Game Mode 1: Falling Questions
**File**: `/resources/js/Components/Courses/bm2/GameModes/FallingQuestionsGame.vue`
- Falling animation system
- Random floating options
- Collision detection
- Lives/health system
- Power-up spawns

### 3. Game Mode 2: Orbiting Options
**File**: `/resources/js/Components/Courses/bm2/GameModes/OrbitingGame.vue`
- Circular orbit mechanics
- Options rotation logic
- Center question display
- Time ring countdown
- Orbit-based power-ups

### 4. Game Mode 3: Space Adventure
**File**: `/resources/js/Components/Courses/bm2/GameModes/SpaceAdventureGame.vue`
- Space theme background
- Shooting mechanic
- Aim and click controls
- Planet/asteroid options
- Combo meter

### 5. Shared Game Composables
**File**: `/resources/js/composables/useGameEngine.js`
- Shared game state management
- Points/lives calculation
- Timer logic
- Power-up system
- Collision detection utilities

**File**: `/resources/js/composables/useGameAudio.js`
- Game-specific sound effects
- Background music control
- Power-up sounds
- Achievement sounds

### 6. Update Start Page
**File**: `/resources/js/Pages/Courses/bm2/Assessment/Start.vue`
- Add game mode selection section
- Show/hide game mode selector based on preference
- Store selected mode before starting

### 7. Update Take Assessment Page
**File**: `/resources/js/Pages/Courses/bm2/Assessment/Take.vue`
- Add game mode toggle
- Switch between normal mode and selected game mode
- Game mode HUD (heads-up display)

### 8. Backend Updates
**File**: `/app/Http/Controllers/Bm2AssessmentController.php`
- Store game mode preference
- Track game-specific metrics (combos, power-ups used)
- Adjust scoring for game mode

**File**: `/routes/api_v2.php`
- Add game mode start endpoint
- Power-up usage tracking

## Data Flow
1. User selects game mode on Start page
2. Mode stored in localStorage/session
3. Take.vue checks mode on load
4. If game mode → render game component instead of normal UI
5. Game component handles question display and answer collection
6. Answers submitted in bulk at end (existing flow)
7. Results page shows game achievements + normal results

## Technical Details
- All games use Canvas API for smooth animations
- RequestAnimationFrame for 60fps rendering
- Responsive design (mobile-friendly)
- Accessibility options (reduce motion, colorblind mode)
- Audio toggle for each game
- Pause functionality
- Save game state to localStorage (resume support)

## Performance
- Lazy load game components
- Optimize animations for mobile
- Throttle power-up spawns
- Clean up event listeners on destroy

Let me know if you want me to proceed with this plan or adjust any game mechanics!