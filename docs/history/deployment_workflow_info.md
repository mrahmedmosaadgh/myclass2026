# Deployment Workflow Analysis

## Overview
Based on the analysis of `public/build/git_info.md`, the project utilizes a **split-repository strategy** for source code and deployment assets.

- **Main Repository**: `myclass2026` (Source code, Laravel backend, Vue frontend)
- **Build Repository**: `myclass2026_build` (Compiled assets only)

## Repository Details
- **URL**: `https://github.com/mrahmedmosaadgh/myclass2026_build.git`
- **Branch**: `main`
- **Content**: The contents of the `public/build` directory from the main project.

## Deployment Process
The deployment workflow involves the following steps:

1.  **Generate Build**: Run `npm run build` in the main project root. This populates the `public/build` directory with:
    - `assets/css/` (Hashed CSS files)
    - `assets/js/` (Hashed JavaScript modules)
    - `assets/fonts/` (Font files)
    - `manifest.json`

2.  **Push to Build Repo**:
    - The `public/build` directory acts as a separate initialized Git repository linked to `myclass2026_build`.
    - **Step 1**: Navigate to the build directory (`cd public/build`).
    - **Step 2**: Stage all new/modified assets (`git add .`).
    - **Step 3**: Commit changes with a descriptive message (e.g., "Update build assets and manifest").
    - **Step 4**: Push to the remote repository (`git push origin main`).

## Key Characteristics
- **Hash-Based Caching**: Filenames contain unique hashes (e.g., `app-BwCflFkV.js`) to ensure browsers load the latest versions after deployment (cache busting).
- **Separation of Concerns**: Keeps the source code history clean from massive binary/minified asset updates.
- **Iterative Updates**: The history shows frequent, incremental updates corresponding to development sessions.
