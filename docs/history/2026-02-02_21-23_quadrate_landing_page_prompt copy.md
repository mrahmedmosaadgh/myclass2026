# Quadrate Landing Page - AI Build Prompt
**Created:** 2026-02-02 21:23:26
**Project Type:** Landing Page
**Tech Stack:** Laravel + Vue.js + Inertia.js

---

## 🎯 Project Overview

Create a stunning, modern landing page for **Quadrate** - a professional online presence platform. The landing page should showcase the product's value proposition, features, and drive conversions through an elegant, high-performance design.

---

## 🏗️ Technical Requirements

### Backend (Laravel)
- **Framework:** Laravel 11.x
- **Architecture:** Follow existing myclass2026 project structure
- **Route:** Create a public route `/quadrate` or root `/`
- **Controller:** `QuadrateController` with `index()` method
- **Inertia:** Return Inertia view with necessary props (features, testimonials, pricing, etc.)

### Frontend (Vue.js)
- **Framework:** Vue 3 Composition API
- **Component Location:** `resources/js/Pages/Quadrate/LandingPage.vue`
- **Styling:** Vanilla CSS with modern design tokens
- **Responsiveness:** Mobile-first, fully responsive design
- **Performance:** Optimized images, lazy loading, smooth animations

---

## 🎨 Design Requirements

### Visual Identity
- **Color Scheme:** 
  - Primary: Modern gradient (e.g., deep blue to cyan, or emerald to teal)
  - Accent: Vibrant complementary color (avoid purple/violet)
  - Background: Dark mode with glassmorphism effects
  - Text: High contrast for accessibility

- **Typography:**
  - Headings: Bold, modern font (e.g., Inter, Outfit, or Space Grotesk)
  - Body: Clean, readable font (e.g., Inter, Roboto)
  - Font sizes: Responsive scale (clamp() for fluid typography)

- **Effects:**
  - Smooth scroll animations (fade-in, slide-up)
  - Hover effects on CTAs and cards
  - Glassmorphism for feature cards
  - Gradient overlays on hero section
  - Subtle parallax effects

### Layout Sections

#### 1. Hero Section
- **Content:**
  - Compelling headline (e.g., "Build Your Digital Empire")
  - Subheadline explaining value proposition
  - Primary CTA button (e.g., "Get Started Free")
  - Secondary CTA (e.g., "Watch Demo")
  - Hero image/illustration (use AI-generated asset)
  
- **Design:**
  - Full viewport height
  - Animated gradient background
  - Floating elements or particles effect
  - Responsive hero image

#### 2. Features Section
- **Content:**
  - Section title: "Why Choose Quadrate?"
  - 6-8 key features with icons, titles, and descriptions
  - Features could include:
    - Professional Templates
    - Custom Domain Support
    - Analytics Dashboard
    - SEO Optimization
    - Mobile Responsive
    - 24/7 Support
    - Easy Integration
    - Secure Hosting

- **Design:**
  - Grid layout (3 columns desktop, 2 tablet, 1 mobile)
  - Glassmorphic cards with hover effects
  - Icon animations on scroll
  - Staggered fade-in animations

#### 3. How It Works Section
- **Content:**
  - 3-4 step process
  - Each step with number, title, description, and visual
  
- **Design:**
  - Timeline or stepped layout
  - Progressive reveal animation
  - Visual connectors between steps

#### 4. Showcase/Portfolio Section
- **Content:**
  - "Built with Quadrate" examples
  - 3-6 example websites/projects
  - Each with screenshot, title, category
  
- **Design:**
  - Masonry or grid layout
  - Hover zoom effects
  - Modal preview on click (optional)

#### 5. Testimonials Section
- **Content:**
  - 3-6 customer testimonials
  - Each with quote, name, role, company, avatar
  
- **Design:**
  - Card carousel or grid
  - Star ratings
  - Company logos (if available)

#### 6. Pricing Section (Optional)
- **Content:**
  - 3 pricing tiers (Free, Pro, Enterprise)
  - Feature comparison
  - CTA buttons for each tier
  
- **Design:**
  - Card layout with highlighted "Popular" tier
  - Toggle for monthly/yearly pricing
  - Smooth transitions

#### 7. CTA Section
- **Content:**
  - Final conversion message
  - Email signup or "Get Started" button
  - Trust indicators (users count, ratings, etc.)
  
- **Design:**
  - Contrasting background
  - Large, prominent CTA button
  - Social proof elements

#### 8. Footer
- **Content:**
  - Logo and tagline
  - Navigation links (Product, Company, Resources, Legal)
  - Social media icons
  - Copyright notice
  
- **Design:**
  - Multi-column layout
  - Subtle background
  - Hover effects on links

---

## 🚀 Performance Requirements

- **Core Web Vitals:**
  - LCP < 2.5s
  - FID < 100ms
  - CLS < 0.1

- **Optimization:**
  - Lazy load images below fold
  - Use WebP format for images
  - Minify CSS/JS in production
  - Implement critical CSS
  - Add loading skeletons for dynamic content

---

## ♿ Accessibility Requirements

- Semantic HTML5 elements
- ARIA labels where needed
- Keyboard navigation support
- Focus indicators
- Alt text for all images
- Sufficient color contrast (WCAG AA minimum)

---

## 📱 Responsive Breakpoints

```css
/* Mobile First */
- Mobile: 320px - 767px
- Tablet: 768px - 1023px
- Desktop: 1024px - 1439px
- Large Desktop: 1440px+
```

---

## 🔧 Implementation Steps

### Phase 1: Backend Setup
1. Create route in `routes/web.php`
2. Create `QuadrateController`
3. Prepare data structure for features, testimonials, etc.
4. Return Inertia view with props

### Phase 2: Frontend Structure
1. Create `LandingPage.vue` component
2. Set up component structure with all sections
3. Create reusable child components:
   - `FeatureCard.vue`
   - `TestimonialCard.vue`
   - `PricingCard.vue`
   - `CTAButton.vue`

### Phase 3: Styling
1. Create design tokens in CSS variables
2. Implement mobile-first responsive styles
3. Add animations and transitions
4. Implement glassmorphism effects

### Phase 4: Interactivity
1. Add scroll animations (Intersection Observer)
2. Implement smooth scroll navigation
3. Add form validation (if applicable)
4. Add micro-interactions

### Phase 5: Optimization
1. Generate/optimize images
2. Implement lazy loading
3. Add loading states
4. Test performance with Lighthouse

### Phase 6: Testing
1. Cross-browser testing
2. Responsive testing (all breakpoints)
3. Accessibility audit
4. Performance audit

---

## 🎯 Success Criteria

- [ ] Visually stunning first impression
- [ ] Smooth, performant animations
- [ ] Fully responsive across all devices
- [ ] Lighthouse score > 90 (all categories)
- [ ] Accessible (WCAG AA compliant)
- [ ] Fast load time (< 3s on 3G)
- [ ] Clear conversion path
- [ ] Professional, modern aesthetic

---

## 📦 Deliverables

1. **Backend:**
   - Route definition
   - Controller with data
   - Any necessary models/seeders

2. **Frontend:**
   - Main landing page component
   - Reusable child components
   - CSS with design system
   - Optimized assets

3. **Documentation:**
   - Component usage guide
   - Design token reference
   - Deployment notes

---

## 🎨 Design Inspiration Keywords

- Modern SaaS landing pages
- Glassmorphism UI
- Dark mode design
- Gradient backgrounds
- Micro-interactions
- Smooth scroll animations
- Premium web design
- Conversion-focused layout

---

## 💡 Additional Notes

- **No Purple/Violet:** Avoid purple and violet colors per design guidelines
- **No Templates:** Create unique, custom design (not generic templates)
- **Performance First:** Prioritize speed and smooth interactions
- **Mobile Priority:** Design mobile-first, enhance for desktop
- **Conversion Focus:** Every element should guide toward conversion

---

## 🔗 Integration with Existing System

- Follow myclass2026 project structure
- Use existing Laravel/Vue/Inertia setup
- Maintain consistency with project conventions
- Reuse existing utilities where applicable
- Follow clean code principles from project guidelines

---

**Ready to build?** Use this prompt with your AI assistant to create a world-class landing page that converts! 🚀
