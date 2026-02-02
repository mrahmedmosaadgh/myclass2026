# 2026-02-01 22:20 | Business Roadmap & Referral System Design

**Summary**: Comprehensive strategic plan to transform the existing LMS into a profitable QudratPro business, including a detailed referral growth system.

---

# Part 1: QudratPro Strategic Business Roadmap

**Transformation Goal**: Convert battle-tested LMS into a revenue-generating Qudrat Quantitative platform within 90 days.

## Executive Summary

### Your Unfair Advantages
1. **🎓 Founder-Market Fit**: 6+ years math teaching expertise
2. **✅ Working Product**: Production-ready Laravel/Vue LMS (134+ controllers)
3. **🚀 Technical Foundation**: Offline-first, AI-integrated, Qudrat module ready
4. **💡 Unique IP**: Skill hierarchy, question bank, established curriculum

### Phase 0: Asset Inventory & Gap Analysis
**Already Built (Asset)**:
- Complete Authentication & User Management
- Qudrat Quantitative Module (Skills, Lessons, Questions)
- Weekly Planning, Schedules, Attendance
- Real-time Quiz System & Chatbot

**Missing (To Build)**:
- ❌ Public Landing Page (Marketing Site)
- ❌ Payment Gateway Integration (Moyasar)
- ❌ Direct Student Enrollment Flow
- ❌ Course Packaging UI

---

## Phase 1: Product Positioning & MVP (Days 1-30)

### 1.1 Strategy: B2C Online Course ("QudratPro")
**Focus**: Sell directly to students/parents. Fastest path to revenue.

### 1.2 The Product: "Foundation to Mastery" Course
**Content**:
- 6 Modules: Algebra, Geometry, Statistics, Logic, Word Problems, Mock Exams
- Format: Pre-recorded video lessons + Interactive Quizzes

### 1.3 Technical Execution (Need to Build)
1. **Public Landing Page** (Week 1-2): Value prop, curriculum, pricing.
2. **Payment Integration** (Week 3): Moyasar/Hyperpay.
3. **Student Portal Lite** (Week 3-4): Remove school clutter, focus on course progress.

### 1.4 "Do NOT Build" List
- ❌ Mobile App (Use PWA)
- ❌ Live Sessions (Start with recorded)
- ❌ Parent Dashboard (Keep it simple)

---

## Phase 2: Marketing & Growth (Days 30-60)

### 2.1 Pricing Model
- **Recommended**: One-Time Purchase **299 SAR** (Early Bird) → **349 SAR** (Standard)
- **Alternative**: Monthly Subscription **149 SAR/mo**

### 2.2 Marketing Funnel
- **Awareness**: TikTok/Reels ("Solve in 60s"), Free Diagnostic Test
- **Engagement**: 5-part Email Sequence, Free Mini-Course
- **Conversion**: Time-limited offer, Money-back guarantee

### 2.3 Referral Program (Growth Multiplier)
**Launch**: Week 7 (after 50+ users)
- **Tier 1**: 1 Referral = 50 SAR Discount
- **Tier 2**: 3 Referrals = 1 Month Free
- **Tier 3**: 5 Referrals = VIP Status (1-on-1 Session)
- **Channel**: WhatsApp-first strategy

---

## Phase 3: Scale & Expand (Days 60-90+)

### 3.1 Financial Targets
- **Month 1 (Beta)**: 20 users × 299 SAR = ~6,000 SAR
- **Month 3 (Scale)**: 100 users × 349 SAR = ~35,000 SAR
- **Month 6 (Aggressive)**: 500 users = ~175,000 SAR

### 3.2 Expansion Options
1. **Verbal Qudrat**: Add section (easiest expansion)
2. **Live Cohorts**: High-ticket (899 SAR)
3. **School Licensing**: B2B sales

---

# Part 2: Detailed Referral System Design (For Week 7)

## 1. Technical Architecture
**Database Changes**:
```sql
ALTER TABLE users ADD COLUMN referral_code VARCHAR(10) UNIQUE;
ALTER TABLE users ADD COLUMN referred_by VARCHAR(10) NULL;
ALTER TABLE users ADD COLUMN referral_count INT DEFAULT 0;

CREATE TABLE referrals (
    referrer_code VARCHAR(10),
    referee_email VARCHAR(255),
    status ENUM('pending', 'paid', 'verified'),
    payment_amount DECIMAL(10,2),
    created_at TIMESTAMP
);
```

**Validation Logic**:
- Referee must match `status='paid'` (no free trials)
- 7-day cooling period (anti-fraud)
- Self-referral detection (IP/Device checks)

## 2. User Experience
**Dashboard Widget**:
- Unique Link: `qudratpro.com/ref/USER123`
- Progress Bar: "1 referral away from Free Month!"
- Share Buttons: WhatsApp (Pre-filled message), Telegram

**Reward Tiers**:
1. **First Referral**: 50 SAR Discount
2. **Growth Advocate (3 refs)**: 1 Month Free Subscription
3. **Champion (5 refs)**: 3 Months Free + Personal Q&A

## 3. Operations
- **MVP**: Manual daily review by Admin (Phase 1)
- **Automated**: Auto-approve low-risk referrals (Phase 2)
- **Metrics**: Track Virality (K-factor > 0.15) & CAC Reduction

---

# 30-90 Day Action Plan Checklist

## ✅ Completed (Assets in Hand)
- [x] Core Learning Management System
- [x] Qudrat Question Bank & Skill Engine
- [x] Offline-first Architecture
- [x] AI Chatbot Integration

## 🚀 Month 1: Build & Prep
- [ ] Record First 10 Lessons
- [ ] Buy Domain (qudratpro.com)
- [ ] Build & Deploy Public Landing Page
- [ ] Integrate Payment Gateway (Moyasar)
- [ ] Recruit 10 Beta Testers (Free Access)

## 🚀 Month 2: Launch
- [ ] **Launch Beta** (Week 5)
- [ ] **Public Launch** (Week 6)
- [ ] **Activate Referral System** (Week 7)
- [ ] Start Paid Ads Experiment (Week 8)

## 🚀 Month 3: Scale
- [ ] Add Premium "Live" Tier
- [ ] Automate Email Funnels
- [ ] Hire Part-time Content Editor
