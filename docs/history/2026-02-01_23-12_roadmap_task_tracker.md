# 2026-02-01 23:12 | QudratPro Business Roadmap - Task Tracker

> **Purpose**: Complete task checklist for transforming MyClass2026 LMS into profitable QudratPro business.
> **Update this file**: Check boxes as you complete tasks. This is your single source of truth for progress tracking.

---

## 📊 Current Status Overview

**Phase**: [ ] Phase 0 → [ ] Phase 1 → [ ] Phase 2 → [ ] Phase 3 → [ ] Phase 4  
**Current Week**: Week ___  
**Revenue to Date**: ___ SAR  
**Active Users**: ___  
**Last Updated**: 2026-02-01

---

## Phase 0: Asset Inventory & Strategic Planning ✅

### Planning & Documentation
- [x] Review existing LMS codebase (134+ controllers identified)
- [x] Document Qudrat Quantitative module capabilities
- [x] Identify monetization gaps
- [x] Create comprehensive business roadmap
- [x] Design referral system strategy
- [x] Save consolidated plan to `docs/history/`

### Strategic Decisions Made
- [x] **Business Model**: B2C Student Course Platform
- [x] **Product Name**: QudratPro (قدرات برو)
- [x] **Pricing Strategy**: 299-349 SAR one-time OR 149 SAR/month
- [x] **Launch Timeline**: 90-day roadmap (3 months to revenue)

---

## Phase 1: Product Positioning & MVP (Days 1-30)

### Week 1: Content & Curriculum
**Goal**: Create foundational course content

- [ ] **Finalize Course Outline**
  - [ ] Module 1: Algebra & Equations (قدرات المعادلات) - List 5-7 lessons
  - [ ] Module 2: Geometry & Measurement (الهندسة والقياس) - List 5-7 lessons
  - [ ] Module 3: Statistics & Probability (الإحصاء والاحتمالات) - List 5-7 lessons
  - [ ] Module 4: Logic & Patterns (المنطق والأنماط) - List 5-7 lessons
  - [ ] Module 5: Word Problems (المسائل اللفظية) - List 5-7 lessons
  - [ ] Module 6: Final Mock Exams (الاختبارات التجريبية) - 3 full exams
  - [ ] Total: 30-40 lessons planned

- [ ] **Record First 10 Lessons**
  - [ ] Set up recording environment (camera, mic, lighting)
  - [ ] Create lesson slides/materials
  - [ ] Record Lesson 1-3 (Module 1)
  - [ ] Record Lesson 4-6 (Module 2)
  - [ ] Record Lesson 7-10 (Module 3)
  - [ ] Edit videos (trim, add intro/outro)
  - [ ] Upload to hosting (YouTube Private or Vimeo)

- [ ] **Create Practice Questions**
  - [ ] Extract 50 questions from existing question bank
  - [ ] Categorize by skill level (Easy/Medium/Hard)
  - [ ] Map questions to lessons
  - [ ] Test questions in existing LMS quiz system

- [ ] **Design Lesson Flow in LMS**
  - [ ] Create "QudratPro Course" in course management
  - [ ] Link video lessons to course structure
  - [ ] Attach quizzes to each lesson
  - [ ] Set up progress tracking

**Deliverable**: 10 recorded lessons + 50 practice questions ready

---

### Week 2: Landing Page & Design
**Goal**: Build public-facing marketing site

- [ ] **Domain & Hosting**
  - [ ] Buy domain: `qudratpro.com` or `قدرات.sa` (Namecheap/GoDaddy)
  - [ ] Set up DNS to point to Laravel app
  - [ ] Configure SSL certificate (Let's Encrypt)
  - [ ] Test domain accessibility

- [ ] **Design Course Branding**
  - [ ] Design logo (Canva or hire on Fiverr: 50-100 SAR)
  - [ ] Choose color scheme (avoid purple per design rules)
  - [ ] Select Arabic + English fonts (Google Fonts: Tajawal, Inter)
  - [ ] Create brand guidelines doc

- [ ] **Build Landing Page** (`resources/views/landing.blade.php`)
  - [ ] **Hero Section**:
    - [ ] Headline: "احصل على 90+ في القدرات الكمي" (Get 90+ in Qudrat Quantitative)
    - [ ] Subheadline: Your credentials (6 years teaching)
    - [ ] CTA Button: "ابدأ التجربة المجانية" (Start Free Trial)
    - [ ] Hero image/video (student success)
  
  - [ ] **Course Curriculum Section**:
    - [ ] List 6 modules with icons
    - [ ] Show lesson count per module
    - [ ] Highlight mock exams
  
  - [ ] **Pricing Table**:
    - [ ] Display chosen pricing (299 SAR early bird)
    - [ ] Show what's included (lessons, quizzes, support)
    - [ ] Add "Money-back guarantee" badge
  
  - [ ] **Social Proof Section**:
    - [ ] Add placeholder for testimonials (fill after beta)
    - [ ] Show "Join 100+ students" counter
  
  - [ ] **FAQ Section**:
    - [ ] "How long is the course?" → 8 weeks
    - [ ] "Do I need prior knowledge?" → No
    - [ ] "What if I don't improve?" → Money-back guarantee
    - [ ] "Is it live or recorded?" → Pre-recorded + support
  
  - [ ] **Footer**:
    - [ ] Terms & Conditions link
    - [ ] Privacy Policy link
    - [ ] Contact email/WhatsApp

- [ ] **Create Marketing Materials**
  - [ ] Instagram post templates (3-5 designs)
  - [ ] TikTok thumbnail template
  - [ ] WhatsApp share image

**Deliverable**: Live landing page at qudratpro.com

---

### Week 3: Payment & Backend
**Goal**: Enable student enrollment and payment

- [ ] **Payment Gateway Integration**
  - [ ] **Research & Choose**:
    - [ ] Compare Moyasar vs Hyperpay vs Stripe
    - [ ] Check fees (Moyasar: ~3.5%, Hyperpay: ~2.75%)
    - [ ] Verify MADA card support
    - [ ] Decision: _____________ (write chosen gateway)
  
  - [ ] **Setup Account**:
    - [ ] Register business account with gateway
    - [ ] Get API keys (Test + Production)
    - [ ] Configure webhook URL: `https://qudratpro.com/api/payment/webhook`
  
  - [ ] **Backend Implementation**:
    - [ ] Install payment SDK: `composer require moyasar/moyasar-php` (or equivalent)
    - [ ] Create `PaymentController.php`
    - [ ] Route: `POST /api/payment/create` (initiate payment)
    - [ ] Route: `POST /api/payment/webhook` (handle success/failure)
    - [ ] Route: `GET /api/payment/verify/{id}` (check status)
  
  - [ ] **Database Schema**:
    ```sql
    CREATE TABLE payments (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT,
        amount DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'SAR',
        status ENUM('pending', 'completed', 'failed', 'refunded'),
        gateway_transaction_id VARCHAR(255),
        created_at TIMESTAMP
    );
    
    CREATE TABLE subscriptions (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT,
        plan_type ENUM('monthly', 'one_time'),
        status ENUM('active', 'expired', 'cancelled'),
        started_at TIMESTAMP,
        expires_at TIMESTAMP NULL
    );
    ```
  
  - [ ] **Test in Sandbox**:
    - [ ] Create test payment (100 SAR)
    - [ ] Verify webhook receives success
    - [ ] Check subscription created in DB
    - [ ] Test failed payment handling

- [ ] **Build Enrollment Flow**
  - [ ] **Registration Page** (`/register`):
    - [ ] Form: Name, Email, Phone, Password
    - [ ] Capture referral code if present (`?ref=CODE`)
    - [ ] Store `referred_by` in users table
  
  - [ ] **Plan Selection Page** (`/plans`):
    - [ ] Show pricing options (299 SAR one-time)
    - [ ] "Select Plan" button → Payment page
  
  - [ ] **Payment Page** (`/checkout`):
    - [ ] Display order summary
    - [ ] Embed payment gateway form
    - [ ] Show secure payment badges
  
  - [ ] **Success Page** (`/welcome`):
    - [ ] "Payment successful!" message
    - [ ] "Access your course" button → Student dashboard
    - [ ] Send welcome email

- [ ] **Set Up Automated Emails**
  - [ ] Configure Laravel Mail (SMTP: Gmail/SendGrid)
  - [ ] **Email 1**: Welcome email (immediate after payment)
  - [ ] **Email 2**: "Start your first lesson" (Day 1)
  - [ ] **Email 3**: "How's it going?" (Day 3)
  - [ ] **Email 4**: "Complete Module 1" (Day 7)
  - [ ] Set up queue worker: `php artisan queue:work`

**Deliverable**: Working payment flow from registration to course access

---

### Week 4: Content Completion & Beta Prep
**Goal**: Finish all course content and recruit testers

- [ ] **Finish Remaining Lessons**
  - [ ] Record Lesson 11-20 (Modules 4-5)
  - [ ] Record Lesson 21-30 (Module 5 continued)
  - [ ] Record bonus lessons (if time permits)
  - [ ] Edit all videos
  - [ ] Upload and link to LMS

- [ ] **Create 3 Mock Exams**
  - [ ] **Mock Exam 1**: 50 questions (Easy-Medium)
  - [ ] **Mock Exam 2**: 50 questions (Medium-Hard)
  - [ ] **Mock Exam 3**: 50 questions (Exam-level difficulty)
  - [ ] Set time limit: 60 minutes per exam
  - [ ] Configure auto-grading
  - [ ] Create score report template

- [ ] **Build Student Progress Dashboard**
  - [ ] **Dashboard Widgets**:
    - [ ] Course completion percentage
    - [ ] Lessons completed (X / 30)
    - [ ] Quiz average score
    - [ ] Mock exam scores (if taken)
    - [ ] Next recommended lesson
  
  - [ ] **My Lessons Page**:
    - [ ] List all lessons with status (locked/unlocked/completed)
    - [ ] Video player integration
    - [ ] "Mark as complete" button
    - [ ] Quiz access after lesson
  
  - [ ] **Quiz History Page**:
    - [ ] List all quiz attempts
    - [ ] Show score, date, time taken
    - [ ] "Review answers" option

- [ ] **Recruit 10 Beta Testers**
  - [ ] Create list of 20 potential testers (former students, friends)
  - [ ] Send personal message: "I'm beta testing QudratPro. Want free access?"
  - [ ] Offer: Free lifetime access in exchange for feedback
  - [ ] Create Google Form for feedback collection
  - [ ] Set up private WhatsApp group for beta testers

**Deliverable**: Complete course (30+ lessons, 3 mock exams) + 10 beta testers recruited

---

## Phase 2: Launch & Validation (Days 31-60)

### Week 5: Beta Launch
**Goal**: Onboard 10 beta users and collect feedback

- [ ] **Onboard Beta Testers**
  - [ ] Send login credentials to 10 testers
  - [ ] Create welcome video explaining beta process
  - [ ] Share feedback form link
  - [ ] Set expectation: "Complete at least 5 lessons in 2 weeks"

- [ ] **Monitor Engagement**
  - [ ] Track daily logins (Google Analytics)
  - [ ] Monitor lesson completion rates
  - [ ] Check quiz scores
  - [ ] Note where students drop off

- [ ] **Collect Detailed Feedback**
  - [ ] Send mid-week check-in (Day 3): "How's it going?"
  - [ ] Schedule 1-on-1 calls with 3-5 testers
  - [ ] Ask:
    - [ ] Which lessons were confusing?
    - [ ] Was video quality good?
    - [ ] Were quizzes too easy/hard?
    - [ ] Would you pay 299 SAR for this?

- [ ] **Fix Critical Bugs**
  - [ ] List all reported bugs
  - [ ] Prioritize (P0: Blocks learning, P1: Annoying, P2: Nice to fix)
  - [ ] Fix P0 bugs immediately
  - [ ] Fix P1 bugs before public launch

- [ ] **Measure Success Metrics**
  - [ ] Course completion rate: ___% (Target: >60%)
  - [ ] Average quiz score: ___% (Track improvement)
  - [ ] NPS Score: ___ (Ask: "Would you recommend?" 0-10)
  - [ ] Testimonials collected: ___ (Target: 3-5 strong ones)

**Deliverable**: 10 beta users tested, feedback collected, critical bugs fixed

---

### Week 6: Public Launch
**Goal**: Get first 20-30 paying customers

- [ ] **Pre-Launch Preparation**
  - [ ] Add best testimonials to landing page
  - [ ] Create launch announcement graphics
  - [ ] Prepare email to contact list
  - [ ] Set early bird price: 299 SAR (50% off)
  - [ ] Set deadline: "Offer ends in 7 days"

- [ ] **Launch Day (Choose specific date: ______)**
  - [ ] **Morning**:
    - [ ] Send email to personal contact list (50-100 people)
    - [ ] Post on Instagram: "QudratPro is LIVE!"
    - [ ] Post on WhatsApp Status
  
  - [ ] **Afternoon**:
    - [ ] Share in relevant WhatsApp groups (education, Qudrat prep)
    - [ ] Post on Telegram channel
    - [ ] Create TikTok announcement video
  
  - [ ] **Evening**:
    - [ ] Respond to all comments/questions
    - [ ] Monitor first signups
    - [ ] Send thank you message to first customers

- [ ] **Week 6 Daily Tasks**
  - [ ] Post 1 TikTok video per day (Qudrat tips)
  - [ ] Share 1 Instagram Reel per day
  - [ ] Answer DMs within 2 hours
  - [ ] Track signups in spreadsheet

- [ ] **Email Campaign**
  - [ ] Day 1: Launch announcement
  - [ ] Day 3: "Only 4 days left for 50% off"
  - [ ] Day 5: "Last 48 hours!"
  - [ ] Day 7: "Final hours - price goes up tomorrow"

**Deliverable**: 20-30 paying users, 5,000-10,000 SAR revenue

---

### Week 7: Marketing Push & Referral System Launch
**Goal**: Scale to 50+ users and activate referral growth

- [ ] **Content Marketing (Daily)**
  - [ ] Post 3-5 TikTok videos per week
    - [ ] "Solve this Qudrat question in 60 seconds"
    - [ ] "Common mistake students make"
    - [ ] "Quick tip for [topic]"
  - [ ] Post 3-5 Instagram Reels per week
  - [ ] Engage with comments (reply within 1 hour)

- [ ] **Free Diagnostic Test Funnel**
  - [ ] Create 20-question diagnostic test
  - [ ] Build test landing page: `/free-test`
  - [ ] Automated email after test:
    - [ ] "Your score: X/20"
    - [ ] "Your weaknesses: [Skills]"
    - [ ] "Get personalized study plan → Join QudratPro"
  - [ ] Promote test on social media

- [ ] **Referral System Implementation**
  
  **Database Setup**:
  - [ ] Run migration:
    ```sql
    ALTER TABLE users ADD COLUMN referral_code VARCHAR(10) UNIQUE;
    ALTER TABLE users ADD COLUMN referred_by VARCHAR(10) NULL;
    ALTER TABLE users ADD COLUMN referral_count INT DEFAULT 0;
    
    CREATE TABLE referrals (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        referrer_code VARCHAR(10),
        referee_email VARCHAR(255),
        referee_user_id BIGINT NULL,
        status ENUM('pending', 'registered', 'paid', 'verified'),
        payment_amount DECIMAL(10,2) NULL,
        reward_claimed BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP,
        verified_at TIMESTAMP NULL
    );
    ```
  - [ ] Generate referral codes for existing users
  
  **Backend Logic**:
  - [ ] Create `ReferralController.php`
  - [ ] Function: `generateReferralCode(User $user)`
  - [ ] Function: `trackReferral($referrerCode, $refereeEmail)`
  - [ ] Payment webhook: Update referral status to 'paid'
  - [ ] Cron job (daily): Validate referrals after 7 days
  - [ ] Function: `incrementReferralCount($referrerCode)`
  
  **Frontend Components**:
  - [ ] **Dashboard Widget** (`ReferralWidget.vue`):
    - [ ] Display unique link: `qudratpro.com/ref/USER123`
    - [ ] "Copy Link" button
    - [ ] Progress bar: "2 / 3 referrals"
    - [ ] Next reward preview
  
  - [ ] **Referral Detail Page** (`/dashboard/referrals`):
    - [ ] List all referrals (status, email, date)
    - [ ] Total earnings display
    - [ ] "Redeem Reward" button
  
  - [ ] **Share Templates**:
    - [ ] Pre-written WhatsApp message (Arabic)
    - [ ] Pre-written WhatsApp message (English)
    - [ ] "Share on WhatsApp" button (mobile)
    - [ ] "Copy Message" button
  
  **Admin Panel**:
  - [ ] Create `/admin/referrals` page
  - [ ] Table: Pending referrals needing verification
  - [ ] Columns: Referrer, Referee Email, Amount, Date
  - [ ] Actions: [Approve] [Reject] buttons
  - [ ] Fraud score indicator (optional)
  
  **Launch Referral Program**:
  - [ ] Announce to existing users via email
  - [ ] Add referral widget to all student dashboards
  - [ ] Create Instagram post explaining program
  - [ ] Monitor first referrals

- [ ] **Reach Out to Education Influencers**
  - [ ] List 10 Saudi education influencers (Instagram/TikTok)
  - [ ] Send DM: "Would you review QudratPro for your audience?"
  - [ ] Offer: Free access + affiliate commission (optional)

**Deliverable**: Referral system live, 50+ total users, first referrals tracked

---

### Week 8: Optimization
**Goal**: Improve conversion and retention based on data

- [ ] **Analyze User Behavior**
  - [ ] Install Microsoft Clarity or Hotjar
  - [ ] Review heatmaps: Where do users click?
  - [ ] Check funnel: Landing → Register → Payment (where's drop-off?)
  - [ ] Identify: Which lessons have low completion?

- [ ] **Improve Weak Lessons**
  - [ ] List lessons with <50% completion
  - [ ] Re-record with better explanation
  - [ ] Add visual aids (animations, diagrams)
  - [ ] Shorten if too long (>15 min)

- [ ] **A/B Test Pricing**
  - [ ] Test A: 299 SAR one-time
  - [ ] Test B: 349 SAR one-time
  - [ ] Test C: 399 SAR one-time
  - [ ] Run for 1 week, measure conversion rate
  - [ ] Choose winning price

- [ ] **Set Up Retargeting Ads**
  - [ ] Install Facebook Pixel on landing page
  - [ ] Create custom audience: "Visited landing page but didn't buy"
  - [ ] Create ad: "You're one step away from 90+ score"
  - [ ] Budget: 500 SAR for 1 week
  - [ ] Monitor ROAS (Return on Ad Spend)

**Deliverable**: Optimized funnel, improved lessons, data-driven pricing

---

## Phase 3: Scale & Expand (Days 61-90+)

### Week 9: Content Enhancement
**Goal**: Add value to retain existing users

- [ ] **Add 10 Bonus Lessons**
  - [ ] Survey students: "What topics do you want?"
  - [ ] Record top 10 requested lessons
  - [ ] Announce: "New content added!"

- [ ] **Create Downloadable Resources**
  - [ ] Cheat sheet: "Top 20 Qudrat formulas"
  - [ ] PDF: "Common mistakes and how to avoid them"
  - [ ] Printable: "8-week study plan"

- [ ] **Record FAQ Video Series**
  - [ ] "How to manage time in Qudrat exam"
  - [ ] "What to do the night before"
  - [ ] "How to handle difficult questions"

- [ ] **Develop Certificate Template**
  - [ ] Design certificate (Canva)
  - [ ] Auto-generate on course completion
  - [ ] Email certificate to student

**Deliverable**: Enhanced course value, higher retention

---

### Week 10: Marketing Scaling
**Goal**: Increase customer acquisition

- [ ] **Paid Ads Experiment**
  - [ ] **Snapchat Ads** (Budget: 1,000 SAR):
    - [ ] Target: Saudi Arabia, Age 16-18
    - [ ] Creative: Video testimonial
    - [ ] CTA: "Start Free Trial"
  
  - [ ] **Instagram/Facebook Ads** (Budget: 1,000 SAR):
    - [ ] Target: Parents of high school students
    - [ ] Creative: "Your child can score 90+"
  
  - [ ] **Google Ads** (Budget: 1,000 SAR):
    - [ ] Keywords: "قدرات كمي", "Qudrat preparation"
    - [ ] Landing page: qudratpro.com
  
  - [ ] Measure ROAS: If >3x, increase budget

- [ ] **Content Partnerships**
  - [ ] Partner with 2-3 education blogs
  - [ ] Write guest post: "How to prepare for Qudrat"
  - [ ] Include link to QudratPro

- [ ] **Podcast Appearances**
  - [ ] List 5 Saudi education podcasts
  - [ ] Pitch: "I can share Qudrat prep tips"
  - [ ] Book 1-2 appearances

- [ ] **Run First Webinar**
  - [ ] Topic: "How to score 90+ in Qudrat Quantitative"
  - [ ] Promote on social media
  - [ ] At end: "Join QudratPro for full course"
  - [ ] Target: 50-100 attendees

**Deliverable**: Scaled marketing, 100+ total users

---

### Week 11: Community Building
**Goal**: Build engaged student community

- [ ] **Launch Premium WhatsApp Group**
  - [ ] Create group: "QudratPro Students"
  - [ ] Only paid members allowed
  - [ ] Rules: Respectful, no spam, help each other

- [ ] **Weekly Live Q&A Sessions**
  - [ ] Schedule: Every Friday 8 PM
  - [ ] Platform: Zoom or Instagram Live
  - [ ] Answer student questions (30-45 min)
  - [ ] Record and share replay

- [ ] **Student Success Stories**
  - [ ] Interview 3-5 students who improved
  - [ ] Record video testimonials
  - [ ] Share on Instagram/TikTok
  - [ ] Add to landing page

- [ ] **Create Leaderboard/Competition**
  - [ ] Display top 10 quiz scores
  - [ ] Prize: Free 1-on-1 session for #1
  - [ ] Announce weekly winners

**Deliverable**: Engaged community, social proof content

---

### Week 12: Financial Review & Planning
**Goal**: Assess progress and plan next phase

- [ ] **Revenue & Expense Review**
  - [ ] Total revenue: _____ SAR
  - [ ] Total expenses: _____ SAR (ads, tools, domain)
  - [ ] Net profit: _____ SAR
  - [ ] Profit margin: _____%

- [ ] **Calculate Key Metrics**
  - [ ] Customer Acquisition Cost (CAC): _____ SAR
    - Formula: Total marketing spend / New customers
  - [ ] Customer Lifetime Value (LTV): _____ SAR
    - Formula: Average revenue per customer
  - [ ] LTV/CAC Ratio: _____ (Target: >3)

- [ ] **User Metrics**
  - [ ] Total paying users: _____
  - [ ] Active users (logged in last 7 days): _____
  - [ ] Course completion rate: _____%
  - [ ] Refund rate: _____%
  - [ ] Referral rate: _____%

- [ ] **Plan Month 4 Expansion**
  - [ ] Decision: Add Verbal Qudrat? [ ] Yes [ ] No
  - [ ] Decision: Launch live cohorts? [ ] Yes [ ] No
  - [ ] Decision: Approach schools for licensing? [ ] Yes [ ] No
  - [ ] Decision: Hire first team member? [ ] Yes [ ] No

**Deliverable**: Financial clarity, expansion decision made

---

## Phase 4: Team Building & Scaling (Month 4+)

### When Revenue Hits 30,000 SAR/month

- [ ] **Hire Video Editor** (Part-time, 2,000 SAR/month)
  - [ ] Post job on Upwork/Freelancer
  - [ ] Test with 3 sample edits
  - [ ] Hire best candidate
  - [ ] Delegate: All video editing, YouTube/TikTok content

- [ ] **Hire Customer Support** (Part-time, 3,000 SAR/month)
  - [ ] Post job locally or remote
  - [ ] Requirements: Fluent Arabic, patient, tech-savvy
  - [ ] Responsibilities: Answer WhatsApp, manage group, collect feedback

- [ ] **Hire Marketing VA** (Remote, 2,500 SAR/month)
  - [ ] Post on OnlineJobs.ph or Upwork
  - [ ] Responsibilities: Post daily content, manage ads, email campaigns
  - [ ] Tools: Give access to social media, email platform

---

## Phase 5: Legal & Operations

### Phase 1 (Day 1-30) - Minimum Setup
- [ ] Use personal bank account initially
- [ ] Create Terms & Conditions page (use template)
- [ ] Create Privacy Policy page (GDPR-compliant template)
- [ ] Create Refund Policy (14-day money-back guarantee)
- [ ] Check Saudi VAT requirements (consult accountant)

### Phase 2 (After 50,000 SAR Revenue)
- [ ] Register official business entity
  - [ ] Apply for Individual Institution License (مؤسسة فردية)
  - [ ] Ministry of Commerce registration
  - [ ] Get CR number (Commercial Registration)

- [ ] Open business bank account
  - [ ] Choose bank (Al Rajhi, SNB, etc.)
  - [ ] Transfer funds from personal account

- [ ] Upgrade payment gateway
  - [ ] Negotiate better rates (if processing >50k SAR/month)

- [ ] Formal contracts (if hiring)
  - [ ] Employment contracts for team members
  - [ ] Freelance agreements

---

## Success Metrics Dashboard

### Financial KPIs
- [ ] **Revenue**: _____ SAR (Target: 50,000 SAR by Day 90)
- [ ] **MRR** (Monthly Recurring Revenue): _____ SAR (Target: 30,000 SAR by Day 90)
- [ ] **CAC** (Customer Acquisition Cost): _____ SAR (Target: <500 SAR)
- [ ] **LTV** (Customer Lifetime Value): _____ SAR (Target: >1,500 SAR)
- [ ] **Refund Rate**: _____% (Target: <5%)

### User Metrics
- [ ] **Total Signups**: _____ (Target: 500 by Month 3)
- [ ] **Free Trial → Paid**: _____% (Target: 15-20%)
- [ ] **Course Completion**: _____% (Target: >60%)
- [ ] **Average Quiz Score**: _____ (Track improvement)
- [ ] **Student Satisfaction**: _____ / 5 stars (Target: 4.5+)

### Marketing Metrics
- [ ] **Landing Page Conversion**: _____% (Target: 5-10%)
- [ ] **Email Open Rate**: _____% (Target: 30-40%)
- [ ] **Email Click Rate**: _____% (Target: 10-15%)
- [ ] **Social Media Engagement**: _____% (Target: 5-8%)
- [ ] **Referral Rate**: _____% (Target: 20-30% of new users)
- [ ] **Referral Conversion**: _____% (Target: 15%)
- [ ] **Viral Coefficient (K-factor)**: _____ (Target: 0.15+)

---

## Quick Reference: Key Decisions

### Pricing (Choose One)
- [ ] **Option A**: 299 SAR one-time (Early Bird) → 349 SAR (Standard)
- [ ] **Option B**: 149 SAR/month subscription
- [ ] **Decision**: ________________

### Payment Gateway (Choose One)
- [ ] **Moyasar** (Easiest, 3.5% fees)
- [ ] **Hyperpay** (Better rates, 2.75% fees)
- [ ] **Stripe** (International, 2.9% + 1 SAR)
- [ ] **Decision**: ________________

### Content Format (Choose One)
- [ ] **Pre-recorded only** (Easier to scale)
- [ ] **Pre-recorded + Weekly Live Q&A** (Higher engagement)
- [ ] **Decision**: ________________

### Launch Date
- [ ] **Beta Launch Date**: ________________
- [ ] **Public Launch Date**: ________________

---

## Risk Mitigation Checklist

### If Low Initial Signups (<20 in Week 6)
- [ ] Extend free trial (7 → 14 days)
- [ ] Deeper discount (50% → 70% off)
- [ ] Add money-back guarantee
- [ ] Personal outreach to warm leads

### If High Refund Rate (>10%)
- [ ] Improve onboarding (welcome video + checklist)
- [ ] Send weekly check-in emails
- [ ] Better lesson pacing
- [ ] Add more practice questions

### If Content Quality Issues
- [ ] Hire freelance video editor
- [ ] Re-record confusing lessons
- [ ] Add visual aids/animations
- [ ] Shorten lesson length (10 min → 7 min)

### If Payment Processing Issues
- [ ] Test thoroughly before launch
- [ ] Have backup payment processor ready
- [ ] Offer manual payment (bank transfer)
- [ ] Clear support contact

---

## Notes & Learnings

### What Worked Well
- 
- 
- 

### What Didn't Work
- 
- 
- 

### Key Insights
- 
- 
- 

### Next Steps
- 
- 
- 

---

## Contact & Resources

**Domain**: qudratpro.com  
**Email**: support@qudratpro.com  
**WhatsApp**: +966 _____________  

**Key Files**:
- Business Roadmap: `docs/history/2026-02-01_22-20_business_roadmap_plan.md`
- This Tracker: `docs/history/2026-02-01_23-12_roadmap_task_tracker.md`

**Tools**:
- Payment Gateway: ________________
- Email Service: ________________
- Analytics: Google Analytics + Microsoft Clarity
- Hosting: ________________

---

**Last Updated**: 2026-02-01 23:12  
**Next Review Date**: ________________
