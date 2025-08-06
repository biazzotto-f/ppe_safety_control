<?php
namespace App\Controllers;

use App\Models\Log;

class LogController extends BaseController
{

    public function __construct()
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] !== 'superadmin') {
            header('Location: ' . $_ENV['APP_URL'] . '/dashboard');
            exit;
        }
    }

    public function index()
    {
        $db = getDbConnection();
        $logModel = new Log($db);
        $logs = $logModel->getAllLogs();

        $this->view('logs/index', [
            'pageTitle' => 'Log de Ações do Sistema',
            'logs' => $logs
        ]);
    }
}
