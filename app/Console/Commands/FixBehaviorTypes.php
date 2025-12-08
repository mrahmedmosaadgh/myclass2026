<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Behavior;

class FixBehaviorTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'behaviors:fix-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix behavior types to match their point values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking behaviors...');
        
        $behaviors = Behavior::all();
        $fixed = 0;
        $correct = 0;
        
        foreach ($behaviors as $behavior) {
            $originalType = $behavior->type;
            $originalPoints = $behavior->points;
            
            // Determine correct type based on points
            if ($originalPoints < 0) {
                $correctType = 'negative';
            } else {
                $correctType = 'positive';
            }
            
            // Check if type matches
            if ($originalType !== $correctType) {
                $this->warn("❌ Behavior #{$behavior->id} '{$behavior->name}': type='{$originalType}' but points={$originalPoints}");
                $behavior->type = $correctType;
                $behavior->save();
                $this->info("   ✅ Fixed to type='{$correctType}'");
                $fixed++;
            } else {
                $this->line("✅ Behavior #{$behavior->id} '{$behavior->name}': Correct (type='{$originalType}', points={$originalPoints})");
                $correct++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Total behaviors: " . $behaviors->count());
        $this->info("   Already correct: {$correct}");
        $this->info("   Fixed: {$fixed}");
        
        if ($fixed > 0) {
            $this->newLine();
            $this->info('🎉 All behaviors have been fixed!');
        } else {
            $this->info('✨ All behaviors were already correct!');
        }
        
        return 0;
    }
}
