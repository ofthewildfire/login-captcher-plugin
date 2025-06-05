<?php namespace OfTheWildfire\LoginCaptcher;

use Backend;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUser;
use BackendAuth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use OfTheWildfire\LoginCaptcher\Models\LoginAttempt;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Login Captcher',
            'description' => 'Captures and logs backend admin login attempts and password reset requests with IP addresses',
            'author'      => 'Of The Wildfire',
            'icon'        => 'icon-list'
        ];
    }

    public function boot()
    {
        // Register the login attempt model
        BackendUser::extend(function($model) {
            $model->hasMany['loginAttempts'] = [
                'OfTheWildfire\LoginCaptcher\Models\LoginAttempt'
            ];
        });

        // Listen for backend authentication attempts
        Event::listen('backend.auth.attempt', function($credentials) {
            $attempt = new LoginAttempt;
            $attempt->email = $credentials['login'] ?? null;
            $attempt->ip_address = request()->ip();
            $attempt->user_agent = request()->header('User-Agent');
            $attempt->attempt_type = 'login';
            $attempt->success = false; // Will be updated if login succeeds
            $attempt->save();

            // Store attempt ID in session to update on success
            session(['login_attempt_id' => $attempt->id]);
        });

        // Listen for successful backend login
        Event::listen('backend.user.login', function($user) {
            if ($attemptId = session('login_attempt_id')) {
                $attempt = LoginAttempt::find($attemptId);
                if ($attempt) {
                    $attempt->user_id = $user->id;
                    $attempt->success = true;
                    $attempt->save();
                }
                session()->forget('login_attempt_id');
            }
        });

        // Listen for failed backend login
        Event::listen('backend.auth.failed', function($credentials) {
            if ($attemptId = session('login_attempt_id')) {
                $attempt = LoginAttempt::find($attemptId);
                if ($attempt) {
                    // Send email notification for failed attempts
                    Mail::send('ofthewildfire.logincaptcher::mail.failed_attempt', [
                        'attempt' => $attempt
                    ], function($message) {
                        $message->to('fascailtkirsten@gmail.com', 'Security Admin')
                               ->subject('Failed Backend Login Attempt Detected');
                    });
                }
                session()->forget('login_attempt_id');
            }
        });

        // Listen for password reset requests
        Event::listen('backend.auth.resetPassword', function($user) {
            $attempt = new LoginAttempt;
            $attempt->user_id = $user->id;
            $attempt->email = $user->email;
            $attempt->ip_address = request()->ip();
            $attempt->user_agent = request()->header('User-Agent');
            $attempt->attempt_type = 'password_reset';
            $attempt->success = true;
            $attempt->save();

            // Send email notification for password reset
            Mail::send('ofthewildfire.logincaptcher::mail.password_reset', [
                'attempt' => $attempt
            ], function($message) {
                $message->to('fascailtkirsten@gmail.com', 'Security Admin')
                       ->subject('Backend Password Reset Request');
            });
        });
    }

    public function registerPermissions()
    {
        return [
            'ofthewildfire.logincaptcher.access_logs' => [
                'tab' => 'Login Captcher',
                'label' => 'Access login attempt logs'
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'logs' => [
                'label'       => 'Login Attempts',
                'url'         => Backend::url('ofthewildfire/logincaptcher/logs'),
                'icon'        => 'icon-list',
                'permissions' => ['ofthewildfire.logincaptcher.access_logs'],
                'order'       => 500
            ]
        ];
    }
} 