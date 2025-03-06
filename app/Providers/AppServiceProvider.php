<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\OpenApi;

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
        Scramble::configure()
            ->routes(function (Route    $route) {
                return Str::startsWith($route->uri, 'api/');
            });
        Gate::define('viewApiDocs', function (User $user) {
            return $user->email == 'johndoe@example.com';
        });
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });


        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->components->securitySchemes['bearer'] = SecurityScheme::http('bearer');

                $openApi->security[] = new SecurityRequirement([
                    'bearer' => [],
                ]);
            });
    }
}
