<?php

namespace Modules\Kuliah\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class KuliahServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Kuliah';

    protected string $nameLower = 'kuliah';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
