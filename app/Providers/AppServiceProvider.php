<?php

namespace App\Providers;

use App\Support\AppTimezone;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AppTimezone::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SECURE PRODUCTIONS HTTPS
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Infrastruktur dipaksa UTC, terlepas dari zona waktu OS server.
        date_default_timezone_set(AppTimezone::STORAGE);
        Date::use(\Carbon\CarbonImmutable::class);

        Password::defaults(fn() => Password::min(8));

        // {{ $tanggal|waktuLokal }} → tampilkan datetime UTC dalam zona
        // waktu pengguna. Satu-satunya jalur konversi untuk tampilan.
        Blade::directive('waktuLokal', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Waktu::tampil({$expression})); ?>";
        });
    }
}
