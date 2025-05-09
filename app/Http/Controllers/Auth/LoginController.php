<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $moduleUtil;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Change authentication from email to username
     *
     * @return void
     */
    public function username()
    {
        return 'username';
    }

    public function logout()
    {
        request()->session()->flush();
        \Auth::logout();
        return redirect('/login');
    }

    /**
     * The user has been authenticated.
     * Check if the business is active or not.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->business->is_active) {
            \Auth::logout();
            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.business_inactive')]
              );
        } elseif ($user->status != 'active') {
            \Auth::logout();
            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.user_inactive')]
              );
        } elseif (!$user->allow_login) {
            \Auth::logout();
            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]
                );
        } elseif (($user->user_type == 'user_customer') && !$this->moduleUtil->hasThePermissionInSubscription($user->business_id, 'crm_module')) {
            \Auth::logout();
            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.business_dont_have_crm_subscription')]
                );
        }
    }

    protected function redirectTo()
    {
        $user = \Auth::user();
        if (!$user->can('dashboard.data') && $user->can('sell.create')) {
            return '/pos/create';
        }

        if ($user->user_type == 'user_customer') {
            return 'contact/contact-dashboard';
        }

        return '/home';
    }

    //passcode 23-06-2022

    public function showLoginForm(Request $request)
    {
        session()->put("invalid_ip", false);
        return view('auth.login');
        // $ip = $request->getClientIp();

        // $whiteIps = [];

        //  if (in_array($ip, $whiteIps)) {
        //     session()->put("invalid_ip", false);
        //     return view('auth.login');
        // } else {
        //     session()->put("invalid_ip", true);
        //     return view('auth.login_passcode');
        // }
    }

    public function login(Request $request)
    {
        $require_passcode = true;

        if(!empty($request->username)){
            $user_pre_access = User::where('username',$request->username)->first();
            if(isset($user_pre_access) && !empty($user_pre_access->skip_passcode)){
                $require_passcode = false;
            }
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'passcode' => Rule::requiredIF($require_passcode && session()->get('invalid_ip') && $request->username != 'empadmin'),
        ]);

        $username = $request->username;
        $password = $request->password;

        if (isset($request->passcode)) {
            if ($request->passcode == "Jaymin@2580") {
                if (Auth::attempt(['username' => $username, 'password' => $password])) {
                    return redirect('/home');
                } else {
                    return back()->with('error', 'These credentials do not match our records.');
                }
            } else {
                return back()->with('error', 'Wrong Passcode');
            }
        }

        if (Auth::attempt(['username' => $username, 'password' => $password])) {
            return redirect('/home');
        } else {
            return back()->with('error', 'These credentials do not match our records.');
        }
    }

}
