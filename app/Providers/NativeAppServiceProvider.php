<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Illuminate\Support\Facades\Log;

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
        // Hanya buka window saat inisialisasi boot awal dari Electron (_native/api/booted)
        if (app()->runningInConsole() || ! request()->is('_native/api/booted')) {
            return;
        }

        $this->openWindowWithRetry();
    }

    /**
     * Coba buka window dengan retry, karena PHP server internal NativePHP
     * kadang butuh waktu lebih lama untuk siap (race condition saat boot).
     */
    protected function openWindowWithRetry(int $maxAttempts = 8, int $delayMs = 500): void
    {
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                Window::open()
                    ->title('AsramaApp - Aplikasi Desktop')
                    ->width(1280)
                    ->height(800)
                    ->minWidth(800)
                    ->minHeight(600)
                    ->showDevTools((bool) config('app.debug', false))
                    ->rememberState();

                Log::info("NativePHP Window berhasil dibuka pada percobaan ke-{$attempt}.");
                return; // Berhasil, keluar dari loop

            } catch (\Throwable $e) {
                Log::warning("NativePHP Window gagal dibuka (percobaan {$attempt}/{$maxAttempts}): " . $e->getMessage());

                if ($attempt >= $maxAttempts) {
                    Log::error('NativePHP Window Boot Error: gagal setelah semua percobaan. ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return;
                }

                // Tunggu sebentar sebelum mencoba lagi, beri waktu PHP server internal untuk siap
                usleep($delayMs * 1000);
            }
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
