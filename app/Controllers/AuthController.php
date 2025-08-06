<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Empresa;

class AuthController extends BaseController
{

    public function showLoginForm()
    {
        $this->view('auth/login', ['appUrl' => $_ENV['APP_URL']]);
    }

    public function login()
    {
        $appUrl = $_ENV['APP_URL'];
        $db = getDbConnection();
        $userModel = new User($db);

        $user = $userModel->findByUsername($_POST['username']);

        if ($user && $_POST['password'] === $user['password']) { // Em produção, usar password_verify
            $_SESSION['loggedin'] = true;
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['nome_usuario'] = $user['nome'];
            $_SESSION['nivel_acesso'] = $user['nivel_acesso'];
            $_SESSION['foto_perfil'] = $user['foto_perfil'];

            if ($user['nivel_acesso'] == 'admin') {
                $empresas = $userModel->getEmpresasAcesso($user['id']);
                $_SESSION['empresas_acesso'] = $empresas;

                if (!empty($empresas)) {
                    $_SESSION['id_empresa_ativa'] = $empresas[0]['id'];
                    $_SESSION['empresa_ativa_nome'] = $empresas[0]['nome_empresa'];
                    $_SESSION['empresa_ativa_logo'] = $empresas[0]['foto_empresa'];
                } else {
                    $_SESSION['error'] = 'Acesso administrativo negado. Nenhuma empresa associada.';
                    header('Location: ' . $appUrl . '/login');
                    exit;
                }
            } elseif ($user['nivel_acesso'] == 'superadmin') {
                $empresaModel = new Empresa($db);
                $_SESSION['todas_empresas'] = $empresaModel->getAll();
            }

            registrarAcao("Efetuou login no sistema.");
            header('Location: ' . $appUrl . '/dashboard');
        } else {
            $_SESSION['error'] = 'Usuário ou senha inválidos.';
            header(header: 'Location: ' . $appUrl . '/login');
        }
        exit;
    }

    public function switchEmpresa()
    {
        $appUrl = $_ENV['APP_URL'];
        $id_empresa_nova = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $redirectRoute = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : 'dashboard';

        if ($id_empresa_nova && isset($_SESSION['empresas_acesso'])) {
            $empresa_encontrada = null;
            foreach ($_SESSION['empresas_acesso'] as $empresa) {
                if ($empresa['id'] == $id_empresa_nova) {
                    $empresa_encontrada = $empresa;
                    break;
                }
            }

            if ($empresa_encontrada) {
                $_SESSION['id_empresa_ativa'] = $empresa_encontrada['id'];
                $_SESSION['empresa_ativa_nome'] = $empresa_encontrada['nome_empresa'];
                $_SESSION['empresa_ativa_logo'] = $empresa_encontrada['foto_empresa'];
                registrarAcao("Alterou a visualização para a empresa '{$empresa_encontrada['nome_empresa']}'.");
            }
        }

        header('Location: ' . $appUrl . '/' . $redirectRoute);
        exit;
    }

    public function logout()
    {
        registrarAcao("Efetuou logout do sistema.");
        session_start();
        session_unset();
        session_destroy();
        header('Location: ' . $_ENV['APP_URL'] . '/login');
        exit;
    }
}
