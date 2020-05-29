<?php

namespace App\Http;

use \Fruitcake\Cors\HandleCors;
use \Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use \Illuminate\Session\Middleware\StartSession;
use \Illuminate\View\Middleware\ShareErrorsFromSession;
use \Illuminate\Routing\Middleware\SubstituteBindings;
use \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use \Illuminate\Http\Middleware\SetCacheHeaders;
use \Illuminate\Auth\Middleware\Authorize;
use \Illuminate\Auth\Middleware\RequirePassword;
use \Illuminate\Routing\Middleware\ValidateSignature;
use \Illuminate\Routing\Middleware\ThrottleRequests;
use \Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Support\Middleware\AuthApi;
use Support\Middleware\Authenticate;
use Support\Middleware\AuthWeb;
use Support\Middleware\CheckForMaintenanceMode;
use Support\Middleware\EncryptCookies;
use Support\Middleware\RedirectIfAuthenticated;
use Support\Middleware\TrimStrings;
use Support\Middleware\TrustProxies;
use Support\Middleware\VerifyCsrfToken;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ValidatePostSize::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'auth.api' => AuthApi::class,
        'auth.web' => AuthWeb::class,
    ];
}
