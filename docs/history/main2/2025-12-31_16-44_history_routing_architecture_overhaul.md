# 2025-12-31 16:44 | Routing Architecture Overhaul

## Overview
Implemented a scalable, feature-based routing and navigation architecture for the Enterprise LMS. This overhaul moves away from role-based route files to a domain-driven structure, enforces strict controller separation, and introduces a database-driven menu system.

## Key Changes

### 1. Feature-Based Routing Architecture
- **New Directory**: `routes/modules/` to house feature-specific routes.
- **Auto-Loading**: Updated `routes/web.php` and `routes/api.php` to automatically load routes from the `modules` directory.
- **Example Implementation**: Created `routes/modules/Academics/` with `web.php` (Inertia) and `api.php` (JSON).

### 2. Controller Separation
Enforced strict separation of concerns:
- **Inertia Controllers**: `app/Http/Controllers/{Domain}/` (e.g., `Academics/SubjectController`) - Responsible for rendering views.
- **API Controllers**: `app/Http/Controllers/Api/{Domain}/` (e.g., `Api/Academics/SubjectApiController`) - Responsible for JSON data for Pinia/Frontend.

### 3. Database-Driven Navigation
- **Migration**: Created `menus` table with fields for `label`, `route`, `permission`, `module`, `parent_id`, `order`, `icon`, and `is_active`.
- **Model**: Created `App\Models\Menu` with self-referencing relationships (`parent`, `children`).
- **API Endpoint**: Implemented `NavigationController::index` (`/api/navigation/menu`) to serve filtered menu items based on server-side permissions (`can:`).

### 4. Frontend Integration
- **Pinia Store**: Created `useNavigationStore` to fetch and store menu state.
- **Logic**: Frontend is now purely reflective; it does not contain authority logic.

## Technical Details
- **Command**: `php artisan make:migration create_menus_table`
- **Command**: `php artisan make:model Menu`
- **File**: `routes/web.php`, `routes/api.php` updated for dynamic loading.

## Verification
- Verified route loading via `php artisan route:list`.
- Verified API response structure for navigation.
