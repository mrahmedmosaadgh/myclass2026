# Live Quiz System - Flow Diagrams

## Complete System Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                        LIVE QUIZ SYSTEM                              │
│                                                                       │
│  ┌──────────────┐                              ┌──────────────┐     │
│  │   TEACHER    │                              │   STUDENT    │     │
│  │   (Vue.js)   │                              │   (Vue.js)   │     │
│  └──────┬───────┘                              └──────┬───────┘     │
│         │                                             │              │
│         │ 1. Create Session                           │              │
│         ├──────────────────────┐                      │              │
│         │                      ▼                      │              │
│         │              ┌───────────────┐              │              │
│         │              │   Laravel     │              │              │
│         │              │   Backend     │              │              │
│         │              │   (API)       │              │              │
│         │              └───────┬───────┘              │              │
│         │                      │                      │              │
│         │                      │ 2. Store in MySQL    │              │
│         │                      ▼                      │              │
│         │              ┌───────────────┐              │              │
│         │              │    MySQL      │              │              │
│         │              │   Database    │              │              │
│         │              └───────────────┘              │              │
│         │                                             │              │
│         │ 3. Initialize Firebase                      │              │
│         ├──────────────────────┐                      │              │
│         │                      ▼                      │              │
│         │              ┌───────────────┐              │              │
│         │              │   Firebase    │              │              │
│         │              │   Realtime    │◄─────────────┤              │
│         │              │   Database    │              │ 4. Listen    │
│         │              └───────┬───────┘              │              │
│         │                      │                      │              │
│         │ 5. Broadcast Question│                      │              │
│         ├──────────────────────┤                      │              │
│         │                      │                      │              │
│         │                      ├──────────────────────┤              │
│         │                      │  6. Receive Question │              │
│         │                      │                      ▼              │
│         │                      │              ┌───────────────┐     │
│         │                      │              │  Display Q    │     │
│         │                      │              │  Start Timer  │     │
│         │                      │              └───────┬───────┘     │
│         │                      │                      │              │
│         │                      │                      │ 7. Submit    │
│         │                      │◄─────────────────────┤              │
│         │                      │                      │              │
│         │                      │ 8. Validate & Score  │              │
│         │                      ├──────────────────────►              │
│         │                      │  9. Return Feedback  │              │
│         │                      │                      │              │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Teacher Session Creation Flow

```
START
  │
  ▼
┌─────────────────────┐
│ Teacher opens page  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Load questions from │
│ API                 │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Configure settings: │
│ - Timer duration    │
│ - Auto-submit       │
│ - Show answers      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Select question     │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Click "Create"      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ POST /api/quiz-     │
│ sessions            │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Backend:            │
│ 1. Generate code    │
│ 2. Save to MySQL    │
│ 3. Return session   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Initialize Firebase │
│ node with code      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Setup Firebase      │
│ listeners for       │
│ participants        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Display access code │
│ Wait for students   │
└──────────┬──────────┘
           │
           ▼
         READY
```

---

## Student Join Flow

```
START
  │
  ▼
┌─────────────────────┐
│ Student opens page  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Enter access code   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Click "Join"        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ POST /api/quiz-     │
│ sessions/join       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Backend:            │
│ 1. Validate code    │
│ 2. Check status     │
│ 3. Create/get       │
│    participant      │
└──────────┬──────────┘
           │
           ▼
      ┌────┴────┐
      │ Valid?  │
      └────┬────┘
           │
     ┌─────┴─────┐
     │           │
    YES         NO
     │           │
     ▼           ▼
┌─────────┐  ┌─────────┐
│ Setup   │  │ Show    │
│ Firebase│  │ error   │
│ listener│  └─────────┘
└────┬────┘
     │
     ▼
┌─────────────────────┐
│ Listen to:          │
│ - Session status    │
│ - Current question  │
│ - Participant count │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Show waiting screen │
└──────────┬──────────┘
           │
           ▼
        WAITING
```

---

## Question Broadcast Flow

```
TEACHER                    FIREBASE                    STUDENTS
   │                          │                           │
   │ 1. Select question       │                           │
   │ 2. Click "Start"         │                           │
   │                          │                           │
   ├─────────────────────────►│                           │
   │ POST /api/.../state      │                           │
   │ action: start            │                           │
   │ question_id: 123         │                           │
   │                          │                           │
   │◄─────────────────────────┤                           │
   │ 200 OK                   │                           │
   │                          │                           │
   ├─────────────────────────►│                           │
   │ Firebase.set()           │                           │
   │ {                        │                           │
   │   status: 'active',      │                           │
   │   current_question: {...}│                           │
   │ }                        │                           │
   │                          │                           │
   │                          ├──────────────────────────►│
   │                          │ onValue() triggers        │
   │                          │                           │
   │                          │                           ├─┐
   │                          │                           │ │ Receive
   │                          │                           │ │ question
   │                          │                           │ │ data
   │                          │                           │◄┘
   │                          │                           │
   │                          │                           ├─┐
   │                          │                           │ │ Display
   │                          │                           │ │ question
   │                          │                           │◄┘
   │                          │                           │
   │                          │                           ├─┐
   │                          │                           │ │ Start
   │                          │                           │ │ timer
   │                          │                           │◄┘
   │                          │                           │
```

---

## Answer Submission Flow

```
STUDENT                    BACKEND                     DATABASE
   │                          │                           │
   │ 1. Select answer         │                           │
   │ 2. Click "Submit"        │                           │
   │                          │                           │
   ├─────────────────────────►│                           │
   │ POST /api/.../answers    │                           │
   │ {                        │                           │
   │   question_id: 123,      │                           │
   │   answer: 2              │                           │
   │ }                        │                           │
   │                          │                           │
   │                          ├──────────────────────────►│
   │                          │ Load question with        │
   │                          │ options                   │
   │                          │                           │
   │                          │◄──────────────────────────┤
   │                          │ Question data             │
   │                          │                           │
   │                          ├─┐                         │
   │                          │ │ Validate answer         │
   │                          │ │ Check if correct        │
   │                          │◄┘                         │
   │                          │                           │
   │                          ├──────────────────────────►│
   │                          │ UPDATE participant        │
   │                          │ SET score = score + 10    │
   │                          │ (if correct)              │
   │                          │                           │
   │◄─────────────────────────┤                           │
   │ {                        │                           │
   │   is_correct: true,      │                           │
   │   participant: {         │                           │
   │     score: 10            │                           │
   │   }                      │                           │
   │ }                        │                           │
   │                          │                           │
   ├─┐                        │                           │
   │ │ Display feedback       │                           │
   │ │ Update score           │                           │
   │◄┘                        │                           │
   │                          │                           │
```

---

## Session State Machine

```
                    ┌─────────────┐
                    │   WAITING   │
                    │             │
                    │ - Students  │
                    │   joining   │
                    │ - No active │
                    │   question  │
                    └──────┬──────┘
                           │
                           │ Teacher clicks "Start"
                           │
                           ▼
                    ┌─────────────┐
                    │   ACTIVE    │
                    │             │
                    │ - Question  │
                    │   displayed │
                    │ - Timer     │
                    │   running   │
                    └──────┬──────┘
                           │
                ┌──────────┼──────────┐
                │          │          │
    Teacher     │          │          │ Teacher
    clicks      │          │          │ clicks
    "Pause"     │          │          │ "End"
                │          │          │
                ▼          │          ▼
         ┌──────────┐      │   ┌──────────┐
         │  PAUSED  │      │   │COMPLETED │
         │          │      │   │          │
         │ - No     │      │   │ - Final  │
         │   active │      │   │   scores │
         │   question│     │   │ - No more│
         └────┬─────┘      │   │   joins  │
              │            │   └──────────┘
              │ Teacher    │
              │ clicks     │
              │ "Resume"   │
              │            │
              └────────────┘
```

---

## Timer Synchronization Flow

```
TEACHER                    FIREBASE                    STUDENT A        STUDENT B
   │                          │                           │                │
   │ Start question           │                           │                │
   ├─────────────────────────►│                           │                │
   │ {                        │                           │                │
   │   timer: {               │                           │                │
   │     started_at: NOW,     │                           │                │
   │     duration: 60         │                           │                │
   │   }                      │                           │                │
   │ }                        │                           │                │
   │                          │                           │                │
   │                          ├──────────────────────────►│                │
   │                          │ Receive timer data        │                │
   │                          │                           │                │
   │                          ├───────────────────────────┼───────────────►│
   │                          │                           │ Receive timer  │
   │                          │                           │                │
   │                          │                           ├─┐              │
   │                          │                           │ │ Calculate    │
   │                          │                           │ │ remaining    │
   │                          │                           │ │ = duration - │
   │                          │                           │ │ (now-start)  │
   │                          │                           │◄┘              │
   │                          │                           │                │
   │                          │                           │                ├─┐
   │                          │                           │                │ │ Calculate
   │                          │                           │                │ │ remaining
   │                          │                           │                │◄┘
   │                          │                           │                │
   │                          │                           ├─┐              ├─┐
   │                          │                           │ │ Start local  │ │ Start local
   │                          │                           │ │ countdown    │ │ countdown
   │                          │                           │◄┘              │◄┘
   │                          │                           │                │
   │                          │                           ▼                ▼
   │                          │                        60...59...58     60...59...58
```

---

## Participant Tracking Flow

```
STUDENT                    FIREBASE                    TEACHER
   │                          │                           │
   │ Join session             │                           │
   │                          │                           │
   │                          │                           │
   │                          │◄──────────────────────────┤
   │                          │ Listening to              │
   │                          │ participants/             │
   │                          │                           │
   ├─────────────────────────►│                           │
   │ Backend adds to MySQL    │                           │
   │                          │                           │
   │                          ├──────────────────────────►│
   │                          │ onChildAdded() triggers   │
   │                          │                           │
   │                          │                           ├─┐
   │                          │                           │ │ Update
   │                          │                           │ │ participant
   │                          │                           │ │ count
   │                          │                           │◄┘
   │                          │                           │
   │ Submit answer            │                           │
   ├─────────────────────────►│                           │
   │ Backend updates score    │                           │
   │                          │                           │
   │                          ├──────────────────────────►│
   │                          │ onChildChanged() triggers │
   │                          │                           │
   │                          │                           ├─┐
   │                          │                           │ │ Update
   │                          │                           │ │ score in
   │                          │                           │ │ real-time
   │                          │                           │◄┘
   │                          │                           │
```

---

## Error Handling Flow

```
                    ┌─────────────┐
                    │   ACTION    │
                    │  INITIATED  │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │   TRY API   │
                    │   REQUEST   │
                    └──────┬──────┘
                           │
                    ┌──────┴──────┐
                    │             │
                SUCCESS        ERROR
                    │             │
                    ▼             ▼
            ┌──────────┐   ┌──────────┐
            │ Update   │   │ Catch    │
            │ UI       │   │ Error    │
            └────┬─────┘   └────┬─────┘
                 │              │
                 │              ▼
                 │       ┌──────────┐
                 │       │ Log to   │
                 │       │ Console  │
                 │       └────┬─────┘
                 │            │
                 │            ▼
                 │       ┌──────────┐
                 │       │ Show     │
                 │       │ Toast    │
                 │       │ Notif    │
                 │       └────┬─────┘
                 │            │
                 │            ▼
                 │       ┌──────────┐
                 │       │ Rollback │
                 │       │ UI State │
                 │       └────┬─────┘
                 │            │
                 └────────────┴─────►
                           │
                           ▼
                    ┌─────────────┐
                    │   FINALLY   │
                    │  Clear      │
                    │  Loading    │
                    └─────────────┘
```

---

## Data Consistency Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA CONSISTENCY                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  MySQL (Source of Truth)          Firebase (Real-time)      │
│  ┌──────────────────┐             ┌──────────────────┐     │
│  │                  │             │                  │     │
│  │ - Sessions       │             │ - Live state     │     │
│  │ - Participants   │             │ - Current Q      │     │
│  │ - Scores         │             │ - Participants   │     │
│  │ - Questions      │             │                  │     │
│  │                  │             │                  │     │
│  └────────┬─────────┘             └────────┬─────────┘     │
│           │                                │               │
│           │                                │               │
│           │    ┌───────────────────┐       │               │
│           └───►│   WRITE FLOW      │◄──────┘               │
│                │                   │                       │
│                │ 1. Write to MySQL │                       │
│                │ 2. On success,    │                       │
│                │    update Firebase│                       │
│                │ 3. Firebase       │                       │
│                │    broadcasts     │                       │
│                └───────────────────┘                       │
│                                                              │
│           ┌────────────────────────────┐                    │
│           │   CONSISTENCY RULES        │                    │
│           ├────────────────────────────┤                    │
│           │ - MySQL = Persistent       │                    │
│           │ - Firebase = Ephemeral     │                    │
│           │ - Always write MySQL first │                    │
│           │ - Firebase for signaling   │                    │
│           │ - Rebuild from MySQL if    │                    │
│           │   Firebase data lost       │                    │
│           └────────────────────────────┘                    │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Cleanup and Lifecycle

```
COMPONENT MOUNT
      │
      ▼
┌─────────────┐
│ Initialize  │
│ State       │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Setup       │
│ Firebase    │
│ Listeners   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Start       │
│ Timers      │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Component   │
│ Active      │
└──────┬──────┘
       │
       │ User navigates away
       │ or closes tab
       │
       ▼
┌─────────────┐
│ onUnmounted │
│ Hook        │
└──────┬──────┘
       │
       ├──────────────┐
       │              │
       ▼              ▼
┌─────────────┐ ┌─────────────┐
│ Unsubscribe │ │ Clear       │
│ Firebase    │ │ Intervals   │
│ Listeners   │ │             │
└──────┬──────┘ └──────┬──────┘
       │              │
       └──────┬───────┘
              │
              ▼
       ┌─────────────┐
       │ Cleanup     │
       │ Complete    │
       └─────────────┘
```

---

## Multi-Question Session Flow

```
SESSION START
      │
      ▼
┌─────────────┐
│ Question 1  │
│ Broadcast   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Students    │
│ Answer      │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Teacher     │
│ Reviews     │
│ Results     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Teacher     │
│ Clicks      │
│ "Next"      │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Question 2  │
│ Broadcast   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Students    │
│ Answer      │
└──────┬──────┘
       │
       ▼
       ...
       │
       ▼
┌─────────────┐
│ Teacher     │
│ Clicks      │
│ "End"       │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Show Final  │
│ Results     │
└─────────────┘
```

---

## Leaderboard Update Flow (Future Feature)

```
STUDENT A          STUDENT B          FIREBASE          TEACHER
    │                  │                  │                │
    │ Submit answer    │                  │                │
    ├─────────────────►│                  │                │
    │ Score: 10        │                  │                │
    │                  │                  │                │
    │                  │ Submit answer    │                │
    │                  ├─────────────────►│                │
    │                  │ Score: 20        │                │
    │                  │                  │                │
    │                  │                  ├───────────────►│
    │                  │                  │ Update         │
    │                  │                  │ leaderboard    │
    │                  │                  │                │
    │◄─────────────────┼──────────────────┤                │
    │ Receive rank: 2  │                  │                │
    │                  │                  │                │
    │                  │◄─────────────────┤                │
    │                  │ Receive rank: 1  │                │
    │                  │                  │                │
```

This comprehensive set of flow diagrams should help you understand every aspect of the live quiz system's operation!
