<?php
/**
 * Temporary script to run the seeder for user ID 19
 * DELETE THIS FILE AFTER USE!
 */

// Security: Only allow if logged in as user ID 19 or with secret key
$secretKey = $_GET['key'] ?? '';
$expectedKey = 'run-seeder-2025'; // Change this!

if ($secretKey !== $expectedKey) {
    http_response_code(403);
    die('Unauthorized. Use: ?key=run-seeder-2025');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Run the seeder
    Artisan::call('db:seed', [
        '--class' => 'AssignAllRolesToUser19Seeder',
        '--force' => true
    ]);
    
    $output = Artisan::output();
    
    echo "<pre>";
    echo "✅ Seeder executed successfully!\n\n";
    echo "Output:\n";
    echo $output;
    echo "</pre>";
    echo "<hr>";
    echo "<p><strong>⚠️ IMPORTANT: Delete this file (run_seeder.php) immediately!</strong></p>";
    
} catch (Exception $e) {
    echo "<pre>❌ Error: " . $e->getMessage() . "</pre>";
}
