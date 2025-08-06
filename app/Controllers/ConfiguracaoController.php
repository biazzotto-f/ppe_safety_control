<?php
namespace App\Controllers;

class ConfiguracaoController extends BaseController
{

    public function __construct()
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] !== 'admin') {
            header('Location: ' . $_ENV['APP_URL'] . '/dashboard');
            exit;
        }
    }

    public function index()
    {
        $this->view('configuracoes/index', [
            'pageTitle' => 'Configurações'
        ]);
    }
}
