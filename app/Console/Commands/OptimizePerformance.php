<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class OptimizePerformance extends Command
{
    




    protected $signature = 'optimize:performance {--clear : Clear all caches first}';

    




    protected $description = 'Optimize application performance by caching routes, config, and views';

    


    public function handle()
    {
        $this->info('🚀 Starting performance optimization...');

        if ($this->option('clear')) {
            $this->info('🧹 Clearing existing caches...');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->info('✅ Caches cleared');
        }

        $this->info('📦 Caching configuration...');
        Artisan::call('config:cache');
        $this->info('✅ Configuration cached');

        $this->info('🛣️  Caching routes...');
        Artisan::call('route:cache');
        $this->info('✅ Routes cached');

        $this->info('👁️  Caching views...');
        Artisan::call('view:cache');
        $this->info('✅ Views cached');

        $this->info('💾 Optimizing autoloader...');
        Artisan::call('optimize');
        $this->info('✅ Autoloader optimized');

        $this->info('');
        $this->info('✨ Performance optimization completed!');
        $this->info('💡 Tip: Run this command after deployment or when routes/config change');
        
        return Command::SUCCESS;
    }
}

