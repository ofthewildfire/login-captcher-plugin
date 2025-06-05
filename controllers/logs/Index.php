<?php namespace OfTheWildfire\LoginCaptcher\Controllers\Logs;

use Backend\Classes\Controller;
use BackendMenu;
use OfTheWildfire\LoginCaptcher\Models\LoginAttempt;

class Index extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OfTheWildfire.LoginCaptcher', 'logs');
    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    public function listExtendQuery($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
} 