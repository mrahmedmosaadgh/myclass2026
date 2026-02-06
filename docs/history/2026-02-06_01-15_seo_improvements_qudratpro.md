# SEO Improvements for Qudrat Pro Landing Page

**Date:** 2026-02-06
**Task:** Improve SEO meta tags, add structured data, and enable Arabic/English search visibility.

## Context
The user reported that Google search results for "qudratpro" were basic and didn't clearly show the site, especially for Arabic searches.

## Actions Taken
1.  **Text Analysis:** Identified the dedicated landing page component at `resources/js/Pages/Qudrat/LandingPage.vue`.
2.  **SEO Implementation:**
    -   **Title:** Updated to "Qudrat Pro - قدرات برو | Master Quantitative Reasoning | General Aptitude Test" to target both languages.
    -   **Meta Description:** Added a bilingual description emphasizing the 8-week course and score goals.
    -   **Keywords:** Added comprehensive keywords: "Qudrat Pro, Qudrat, GAT, General Aptitude Test, قدرات, قياس".
    -   **Open Graph Tags:** Added `og:title`, `og:description`, `og:image`, `og:locale` for better social sharing.
    -   **Structured Data (JSON-LD):** Implemented `WebSite` and `EducationalOrganization` schema to help Google understand the entity structure.
3.  **Accessibility:** Added `aria-label` and `sr-only` text to the logo link.
4.  **Verification:** Checked `robots.txt` to ensure crawling is allowed and sitemap is linked.

## Next Steps
-   User should verify the changes by deploying.
-   User can Inspect the URL in Google Search Console to request re-indexing.
-   Monitor search results over the next few weeks as indexing updates.
