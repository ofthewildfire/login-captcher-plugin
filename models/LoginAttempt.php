<?php namespace OfTheWildfire\LoginCaptcher\Models;

use Model;
use Backend\Models\User as BackendUser;

class LoginAttempt extends Model
{
    protected $table = 'ofthewildfire_logincaptcher_attempts';

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'attempt_type',
        'success'
    ];

    public $belongsTo = [
        'user' => ['Backend\Models\User']
    ];

    public function getAttemptTypeOptions()
    {
        return [
            'login' => 'Backend Login Attempt',
            'password_reset' => 'Backend Password Reset Request'
        ];
    }

    public function getSuccessTextAttribute()
    {
        return $this->success ? 'Yes' : 'No';
    }

    public function getAttemptTypeTextAttribute()
    {
        return $this->getAttemptTypeOptions()[$this->attempt_type] ?? $this->attempt_type;
    }
} 