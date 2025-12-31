# 2025-12-30 18:00 | Expanded School Profile via JSON column

## Overview
Expanded the School Management system to support a detailed profile (Personnel, Address, Contact, Logo) using the existing `data` JSON column in the `schools` table. This avoids unnecessary schema migrations while providing full structured data support.

## 🛠️ Key Changes

### 1. Expanded School Profile (JSON Strategy)
-   **Model**: Updated `App\Models\School.php` to include `data` in `$fillable` and cast it to an `array`.
-   **Controller**: Enhanced `SchoolHrAdminRegistrationController`:
    -   `mySchool`: Automatically initializes missing structure in the `data` column with default empty/null values to ensure the frontend form has stable bindings.
    -   `update`: Validates and saves the entire `data` array alongside the school name.

### 2. Revamped School Manager UI
-   **Path**: `resources/js/Pages/my_table_mnger/SchoolManager.vue`
-   **Features**:
    -   **Tabbed Navigation**: Splits the large form into organized sections.
    -   **General**: Official/Short names, School Code, Type, Gender, Ownership, Year Established, and Education Levels (KG/Primary/Middle/Secondary).
    -   **Contact**: Primary/Secondary phone, Official Email, Website.
    -   **Address**: Country, City, District, Street, Region.
    -   **Key Personnel**: Individual sections for Principal, Vice Principal, Academic Coordinator, and Administrative Contact (Name, Phone, Email for each).
    -   **Emergency**: Dedicated Emergency Contact Number field.
    -   **Logo**: URL input with integrated live preview and version tracking.

### 3. Backend & Core Support
-   **User Relationship**: Fixed `User` model to resolve schools via the new `school_id` column for HR Admins.
-   **Registration Core**: Maintained single-transaction registration for HR, User, and School records.

## ✅ Verification
-   Verified that the `data` column correctly stores nested JSON structures.
-   Verified that the UI preserves complex states (e.g., checkboxes, nested objects) across saves and refreshes.
-   Verified that Key Personnel fields are correctly mapped and saved.
