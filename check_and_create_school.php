<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Checking schools...\n\n";

$schoolCount = \App\Models\School::count();
echo "School count: $schoolCount\n\n";

if ($schoolCount > 0) {
    echo "Existing schools:\n";
    foreach (\App\Models\School::all(['id', 'name', 'school_slug']) as $school) {
        echo "  - {$school->school_slug} ({$school->name})\n";
    }
} else {
    echo "NO SCHOOLS FOUND!\n\n";
    echo "Creating demo school for local development...\n";
    
    $demoSchool = \App\Models\School::create([
        'name' => 'Demo School',
        'school_slug' => 'demo-school',
        'is_active' => true,
        'academic_year_id' => 1,
        'semester_id' => 1,
    ]);
    
    echo "✓ Demo school created!\n";
    echo "  Slug: {$demoSchool->school_slug}\n";
    echo "  Name: {$demoSchool->name}\n";
    echo "\nYou can now visit: http://127.0.0.1:8000/login\n";
}

echo "\n✅ Done!\n";
