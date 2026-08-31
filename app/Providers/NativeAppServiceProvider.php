<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider extends ServiceProvider implements ProvidesPhpIni
{
    public function __construct($app = null)
    {
        parent::__construct($app ?? app());
    }

    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        try {
            Window::open()
                ->title('AsramaApp - Aplikasi Desktop')
                ->width(1280)
                ->height(800)
                ->minWidth(800)
                ->minHeight(600)
                ->showDevTools(false)
                ->rememberState();
        } catch (\Throwable $e) {
            // Silently ignore connection exceptions when API server is not ready
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }
}
