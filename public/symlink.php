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
