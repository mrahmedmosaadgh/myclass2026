# Debugging 404 Image Errors

## Objective
The user reported 404 errors when accessing school branding images (logo and background) that were confirmed to exist on the disk.

## Investigation
1.  **Verification**: Verified that the files existed in `storage/app/public/school_branding/`.
2.  **Configuration Check**: Checked `.env` and `config/filesystems.php`. The configuration pointed `local` disk to `storage/app/private` and `public` disk to `storage/app/public`.
3.  **Root Cause Analysis**: The URLs were correct (pointing to `/storage/...`), but the web server returned 404. This indicated that the symbolic link from `public/storage` to `storage/app/public` was missing or broken on the production server.
4.  **Secondary Issue**: The fallback route in `routes/web.php` (`Route::get('/storage/{path}...')`) was using the default `local` disk (private storage) instead of the `public` disk. This meant even when the web server couldn't serve the file (passing it to Laravel), Laravel also failed to find it.

## Solution
1.  **Symlink Creation**: Created a PHP script `public/symlink.php` to generate the symlink programmatically (alternative to SSH).
2.  **Code Fix**: Updated `routes/web.php` to explicitly use `Storage::disk('public')` in the fallback route. This ensures images are served by Laravel even if the symlink is missing or misconfigured.
3.  **Cleanup**: Removed `public/symlink.php`.

## How to Apply
1.  Pull the latest changes to the server.
2.  The update to `routes/web.php` will automatically handle serving the files if the symlink fails.
