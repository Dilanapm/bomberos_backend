<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Redirección post-login por rol (evita /home 404).
         * Nota: para admin/instructor, tu middleware 2fa.required hará el "gate"
         * y enviará a /2fa-setup si falta confirmar 2FA.
         */
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user->hasRole('admin')) {
                        return redirect('/admin/zone');
                    }

                    return redirect('/login')->withErrors([
                        'email' => 'Este usuario no tiene acceso a la plataforma web',
                    ]);
                }
            };
        });

        // Redirección después de confirmar 2FA
        $this->app->singleton(
            \Laravel\Fortify\Contracts\TwoFactorLoginResponse::class,
            \App\Actions\Fortify\Responses\ConfirmedTwoFactorAuthenticationResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Deshabilitar rutas por defecto de Fortify para registrarlas manualmente con throttling
        Fortify::ignoreRoutes();

        /*
        |--------------------------------------------------------------------------
        | Fortify Actions
        |--------------------------------------------------------------------------
        */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        /*
        |--------------------------------------------------------------------------
        | Rate limiting (anti brute-force)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())).'|'.$request->ip()
            );

            return Limit::perMinute(3)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(3)->by(($request->session()->get('login.id') ?? 'guest').'|'.$request->ip());
        });
        RateLimiter::for('password-email', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(3)->by(Str::lower($email).'|'.$request->ip()),
                Limit::perMinute(10)->by($request->ip()),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(3)->by(Str::lower($email).'|'.$request->ip()),
                Limit::perMinute(10)->by($request->ip()),
            ];
        });


        /*
        |--------------------------------------------------------------------------
        | Views (Blade mínimas)
        |--------------------------------------------------------------------------
        | Importante: si no defines estas vistas, Fortify lanza errores
        | "Target [...] is not instantiable".
        */
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));

        // Solo si vas a permitir registro (si no, puedes comentar estas 2 líneas)
        Fortify::registerView(fn () => view('auth.register'));
    }
}
