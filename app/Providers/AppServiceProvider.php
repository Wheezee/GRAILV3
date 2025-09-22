<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure default Academic Year exists in DB and set session AY on authenticated requests
        try {
            $nowYear = (int)date('Y');
            $defaultAy = $nowYear.'-'.($nowYear+1);
            if (class_exists(\App\Models\AcademicYear::class)) {
                \App\Models\AcademicYear::firstOrCreate(['year' => $defaultAy]);
            }
            if (auth()->check()) {
                if (!session()->has('academic_year')) {
                    session(['academic_year' => $defaultAy]);
                }
                if (!session()->has('semester')) {
                    session(['semester' => '1']);
                }
            }
        } catch (\Throwable $e) {
            // silent
        }
    }
}
