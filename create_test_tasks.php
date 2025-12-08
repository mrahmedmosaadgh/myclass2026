<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MyProjectTask;

// Create some test tasks with hierarchy
$project = MyProjectTask::create([
    'title' => 'Website Redesign Project',
    'description' => 'Complete redesign of company website',
    'status' => 'in_progress',
    'priority' => 'high',
    'due_date' => '2025-09-15',
    'sort_order' => 1
]);

$frontend = MyProjectTask::create([
    'title' => 'Frontend Development',
    'description' => 'Build the user interface',
    'status' => 'in_progress',
    'priority' => 'high',
    'parent_id' => $project->id,
    'sort_order' => 1
]);

$backend = MyProjectTask::create([
    'title' => 'Backend API',
    'description' => 'Develop REST API endpoints',
    'status' => 'pending',
    'priority' => 'medium',
    'parent_id' => $project->id,
    'sort_order' => 2
]);

$homepage = MyProjectTask::create([
    'title' => 'Homepage Design',
    'description' => 'Create responsive homepage',
    'status' => 'completed',
    'priority' => 'high',
    'parent_id' => $frontend->id,
    'sort_order' => 1
]);

$contact = MyProjectTask::create([
    'title' => 'Contact Form',
    'description' => 'Build contact form with validation',
    'status' => 'in_progress',
    'priority' => 'medium',
    'parent_id' => $frontend->id,
    'sort_order' => 2
]);

echo "Created test tasks with hierarchy:\n";
echo "- Project: {$project->title} (ID: {$project->id})\n";
echo "  - Frontend: {$frontend->title} (ID: {$frontend->id})\n";
echo "    - Homepage: {$homepage->title} (ID: {$homepage->id})\n";
echo "    - Contact: {$contact->title} (ID: {$contact->id})\n";
echo "  - Backend: {$backend->title} (ID: {$backend->id})\n";