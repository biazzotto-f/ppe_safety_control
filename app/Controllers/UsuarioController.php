<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Empresa;

class UsuarioController extends BaseController
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
        $userModel = new User($db);
        $usuarios = $userModel->getAllAdmins();

        $this->view('usuarios/index', [
            'pageTitle' => 'Gerir Usuários Admin',
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $empresaModel = new Empresa($db);
        $empresas = $empresaModel->getAll();

        $this->view('usuarios/create', [
            'pageTitle' => 'Novo Usuário Admin',
            'empresas' => $empresas
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $userModel = new User($db);

            $nome = $_POST['nome'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $empresa_ids = $_POST['empresas'] ?? [];
            $foto_path = null;

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                $target_dir = "uploads/avatars/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('user_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $id_novo_usuario = $userModel->createUser($nome, $username, $password, 'admin', $foto_path);

            if ($id_novo_usuario) {
                $userModel->associateEmpresas($id_novo_usuario, $empresa_ids);
                registrarAcao("Criou o usuário admin '{$nome}' (ID: {$id_novo_usuario}).");
                $_SESSION['success_message'] = 'Usuário Admin criado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao criar usuário. O nome de usuário já pode existir.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/usuarios');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/usuarios');
            exit;
        }

        $db = getDbConnection();
        $userModel = new User($db);
        $empresaModel = new Empresa($db);

        $usuario = $userModel->findById($id);
        $empresas = $empresaModel->getAll();
        $empresas_associadas_ids = $userModel->getEmpresaIdsForUser($id);

        $this->view('usuarios/edit', [
            'pageTitle' => 'Editar Usuário Admin',
            'usuario' => $usuario,
            'empresas' => $empresas,
            'empresas_associadas_ids' => $empresas_associadas_ids
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $userModel = new User($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $nome = $_POST['nome'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $empresa_ids = $_POST['empresas'] ?? [];
            $foto_path = $_POST['foto_atual'];

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                $target_dir = "uploads/avatars/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('user_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $userModel->updateProfile($id, $nome, $username, $foto_path);

            if (!empty($password)) {
                $userModel->updatePassword($id, $password);
            }

            $userModel->associateEmpresas($id, $empresa_ids);

            if ($id == $_SESSION['id_usuario']) {
                $_SESSION['nome_usuario'] = $nome;
                $_SESSION['foto_perfil'] = $foto_path;
            }

            registrarAcao("Atualizou os dados do usuário '{$nome}' (ID: {$id}).");
            $_SESSION['success_message'] = 'Usuário atualizado com sucesso!';
            header('Location: ' . $_ENV['APP_URL'] . '/usuarios');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $userModel = new User($db);
                $usuario = $userModel->findById($id);

                if ($userModel->deleteUser($id)) {
                    registrarAcao("Excluiu o usuário '{$usuario['nome']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Usuário excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao excluir usuário.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/usuarios');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $userModel = new User($db);
                $usuario = $userModel->findById($id);
                $novo_status = $usuario['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($userModel->toggleStatus($id)) {
                    registrarAcao("Alterou o status do usuário '{$usuario['nome']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status do usuário alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status do usuário.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/usuarios');
            exit;
        }
    }
}
