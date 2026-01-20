# Debugging 404 Image Errors

## Objective
The user reported 404 errors when accessing school branding images (logo and background) that were confirmed to exist on the disk.

## Investigation
1.  **Verification**: Verified that the files existed in `storage/app/public/school_branding/`.
2.  **Configuration Check**: Checked `.env` and `config/filesystems.php`. The configuration pointed `local` disk to `storage/app/private` and `public` disk to `storage/app/public`.
3.  **Root Cause Analysis**: The URLs were correct (pointing to `/storage/...`), but the web server returned 404. This indicated that the symbolic link from `public/storage` to `storage/app/public` was missing or broken on the production server.
4.  **Workaround**: The user requested a way to fix this without SSH access (i.e., without running `php artisan storage:link`).

## Solution
Created a PHP script `public/symlink.php` that can be executed via the browser to create the symbolic link programmatically.

### `public/symlink.php`
```php
<?php

$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "Target: " . $target . "<br>";
echo "Link: " . $link . "<br>";

if(file_exists($link)) {
    echo "<h1>Link already exists</h1>";
    echo "path: " . readlink($link);
} else {
    if(symlink($target, $link)) {
        echo "<h1>Symlink created successfully</h1>";
        echo "$link -> $target";
    } else {
        echo "<h1>Failed to create symlink</h1>";
        echo "Check permissions.";
    }
}
```

## How to use
1.  Upload `symlink.php` to the `public_html` or `public` directory on the server.
2.  Visit `https://domain.com/symlink.php` in the browser.
3.  Once the symlink is created, delete the file.
