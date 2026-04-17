<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create timeline storage directory
        $timelinePath = storage_path('app/timeline_users');
        
        if (!is_dir($timelinePath)) {
            mkdir($timelinePath, 0755, true);
        }
        
        // Create .gitignore to prevent tracking user data
        $gitignorePath = $timelinePath . '/.gitignore';
        if (!file_exists($gitignorePath)) {
            file_put_contents($gitignorePath, "# Ignore all user data\n*\n!.gitignore\n");
        }
        
        // Create index.php to prevent directory listing
        $indexPath = $timelinePath . '/index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, "<?php\n// Silence is golden.\nheader('Location: /');\n");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We don't remove the directory in production
        // as it might contain user data
        // In development, you could remove it manually if needed
    }
};
