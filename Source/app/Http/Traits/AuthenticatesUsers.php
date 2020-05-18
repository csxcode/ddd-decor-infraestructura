<?php

namespace App\Http\Traits;

use App\Enums\AccessTypeEnum;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Session;
use App\Models\User;
use App\Models\UserStoreBranch;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            Session::LogSuccessLogin(Auth::user(), AccessTypeEnum::Web);
            return $this->sendLoginResponse($request);
        } else {
            $user = User::where('username', $request->input('username'))->first();
            if($user){
                Session::LogFailedLoginAttempts($user, AccessTypeEnum::Web);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $return = null;
        $credentials = $this->credentials($request);
        $credentials = array_merge($credentials, array('enabled' => 1));

        // Check Store and Branches
        $user = User::with('role')->where('username', $request->input('username'))->first();

        if($user){

            if (GlobalValidation::UserNeedToFilterData(($user))) {

                $have_sb = count(UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false)) > 0;

                if (!$have_sb) {
                    $return = false;
                }

            }

        }

        if($return === null){
            $return = $this->guard()->attempt(
                $credentials, $request->filled('remember')
            );
        }

        return $return;       
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
                    ? new Response('', 204)
                    : redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('auth.failed')];

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        if ( ! User::where('username', $request->input('username'))->first() ) {
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([
                    $this->username() => trans('auth.username'),
                ]);
        }

        $user = User::with('role')->where('username', $request->input('username'))->first();

        if(!Hash::check($request->input('password'), $user->password)) {
            return redirect()->back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'password' => trans('auth.password'),
                ]);
        }

        if ($user->enabled == 0) {
            return redirect()->back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'password' => trans('auth.user_disabled'),
                ]);
        }

        // Check Store and Branches
        if (GlobalValidation::UserNeedToFilterData(($user))) {

            $have_sb = count(UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false)) > 0;

            if (!$have_sb) {

                return redirect()->back()
                    ->withInput($request->only('username', 'remember'))
                    ->withErrors([
                        'no_branches' => trans('auth.no_sb'),
                    ]);
            }

        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);

        /*            
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
        */
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Session::LogLogout(AccessTypeEnum::Web);

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
