<?php

namespace Modules\Exports\Providers;

use Illuminate\Support\ServiceProvider;

class ExportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Exports', 'routes/web.php'));
    }
}
