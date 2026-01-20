# Resolving Image 404 Errors with Direct Public Storage

## Objective
Resolve persistent 404 errors for school branding images (logo and background) on the production server. The issue was occurring despite the files existing on the disk, pointing to a failure in the symbolic link setup or server configuration for the `storage` directory.

## Investigation & Solution Evolution
1.  **Initial Diagnosis**: Tried to fix the symlink using a PHP script (`symlink.php`) and by adding a fallback route in `web.php` to serve files through Laravel if the web server failed.
2.  **Server Constraints**: The shared hosting environment continued to block or mishandle the requests to `storage/`, likely due to Apache protocols or directory permissions that were difficult to modify safely.
3.  **Final Solution**: Decided to refactor the storage logic to bypass the `storage` directory entirely for these assets.

## Implementation Details

### 1. Controller Refactor (`SchoolBrandingController.php`)
-   **Method**: `uploadLogo` and `uploadBackground`
-   **Change**: Replaced `storeAs` (which uses the default filesystem disk) with explicit `move()` commands.
-   **Destination**: Files are now saved directly to `public/uploads/school_branding/{id}/`.
-   **Cleanup**: Added logic to attempt deletion of old files from both the new `public/uploads` path and the legacy `storage/` path.

### 2. Model Accessor Update (`School.php`)
-   **Attributes**: `getLogoUrlAttribute` and `getBackgroundUrlAttribute`.
-   **Logic**: Updated to check if the path starts with `uploads/`.
    -   If yes (new system): Return `asset($path)` (direct public link).
    -   If no (legacy): Fallback to `asset('storage/' . $path)` to maintain compatibility until old images are replaced.

## Status
-   **Completed**: Code refactored and pushed.
-   **Action Required**: User needs to re-upload logos/backgrounds on the production server for the new system to take effect.
