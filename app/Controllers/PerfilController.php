<?php
namespace App\Controllers;

use App\Models\User;

class PerfilController extends BaseController
{

    public function index()
    {
        $db = getDbConnection();
        $userModel = new User($db);
        $usuario = $userModel->findById($_SESSION['id_usuario']);

        $this->view('perfil/index', [
            'pageTitle' => 'Meu Perfil',
            'usuario' => $usuario
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appUrl = $_ENV['APP_URL'];
            $db = getDbConnection();
            $userModel = new User($db);
            $id_usuario = $_SESSION['id_usuario'];
            $success_message = '';

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                $target_dir = "uploads/avatars/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $file_extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . $id_usuario . '.' . $file_extension;

                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    $userModel->updateProfilePicture($id_usuario, $target_file);
                    $_SESSION['foto_perfil'] = $target_file;
                    registrarAcao("Atualizou a sua foto de perfil.");
                    $success_message = 'Foto de perfil atualizada com sucesso!';
                }
            }

            if (isset($_POST['nome'])) {
                $nome = $_POST['nome'];
                $username = $_POST['username'];
                $userModel->updateProfile($id_usuario, $nome, $username);
                $_SESSION['nome_usuario'] = $nome;
                registrarAcao("Atualizou os seus dados de perfil.");
                $success_message = 'Perfil atualizado com sucesso!';
            }

            if (isset($_POST['nova_senha']) && !empty($_POST['nova_senha'])) {
                if ($_POST['nova_senha'] === $_POST['confirmar_senha']) {
                    $userModel->updatePassword($id_usuario, $_POST['nova_senha']);
                    registrarAcao("Alterou a sua própria senha.");
                    $success_message = 'Perfil e senha atualizados com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'As senhas não coincidem.';
                }
            }

            if (!empty($success_message)) {
                $_SESSION['success_message'] = $success_message;
            }

            header('Location: ' . $appUrl . '/perfil');
            exit;
        }
    }
}
