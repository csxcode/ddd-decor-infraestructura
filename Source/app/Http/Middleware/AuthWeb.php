<?php namespace App\Http\Middleware;

use App\Enums\AccessTypeEnum;
use App\Models\Session;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('auth/login');
            }
        }

        Session::TrackingLastActivity(AccessTypeEnum::Web);

        return $next($request);
    }
}
