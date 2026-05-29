<?php

namespace Modules\Imports\Providers;

use Illuminate\Support\ServiceProvider;

class ImportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Imports', 'database/migrations'));
        $this->loadRoutesFrom(module_path('Imports', 'routes/web.php'));
    }
}
