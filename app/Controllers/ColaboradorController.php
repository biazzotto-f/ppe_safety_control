<?php
namespace App\Controllers;

use App\Models\Colaborador;
use App\Models\User;
use App\Models\Setor;
use App\Models\Funcao;
use App\Models\Empresa;

class ColaboradorController extends BaseController
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
        $db = getDbConnection();
        $colaboradorModel = new Colaborador($db);
        $colaboradores = $colaboradorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('colaboradores/index', [
            'pageTitle' => 'Gerir Colaboradores',
            'colaboradores' => $colaboradores
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $setorModel = new Setor($db);
        $funcaoModel = new Funcao($db);
        $empresaModel = new Empresa($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $setores = $setorModel->getByEmpresaId($id_empresa);
        $funcoes = $funcaoModel->getAllByEmpresaId($id_empresa);
        $unidades = $empresaModel->getUnidadesByEmpresaId($id_empresa);

        $this->view('colaboradores/create', [
            'pageTitle' => 'Novo Colaborador',
            'setores' => $setores,
            'funcoes' => $funcoes,
            'unidades' => $unidades
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $userModel = new User($db);
            $colaboradorModel = new Colaborador($db);

            $id_empresa = $_SESSION['id_empresa_ativa'];
            $nome = $_POST['nome_completo'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $matricula = $_POST['matricula'];
            $id_funcao = $_POST['id_funcao'];
            $id_setor = $_POST['id_setor'];
            $id_unidade = !empty($_POST['id_unidade']) ? $_POST['id_unidade'] : null;
            $foto_path = null;

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                $target_dir = "uploads/colaboradores/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('colab_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $id_novo_usuario = $userModel->createUser($nome, $username, $password, 'funcionario', $foto_path);

            if ($id_novo_usuario) {
                if ($colaboradorModel->create($id_empresa, $id_novo_usuario, $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $foto_path)) {
                    registrarAcao("Criou o colaborador '{$nome}' (Matrícula: {$matricula}).");
                    $_SESSION['success_message'] = 'Colaborador e usuário criados com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao criar o registo do colaborador.';
                }
            } else {
                $_SESSION['error_message'] = 'Erro ao criar a conta de usuário. O nome de usuário já pode existir.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }

        $db = getDbConnection();
        $colaboradorModel = new Colaborador($db);
        $setorModel = new Setor($db);
        $funcaoModel = new Funcao($db);
        $empresaModel = new Empresa($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $colaborador = $colaboradorModel->findByIdAndEmpresaId($id, $id_empresa);
        if (!$colaborador) {
            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }

        $setores = $setorModel->getByEmpresaId($id_empresa);
        $funcoes = $funcaoModel->getAllByEmpresaId($id_empresa);
        $unidades = $empresaModel->getUnidadesByEmpresaId($id_empresa);

        $this->view('colaboradores/edit', [
            'pageTitle' => 'Editar Colaborador',
            'colaborador' => $colaborador,
            'setores' => $setores,
            'funcoes' => $funcoes,
            'unidades' => $unidades
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $userModel = new User($db);
            $colaboradorModel = new Colaborador($db);

            $id_colaborador = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];

            $nome = $_POST['nome_completo'];
            $username = $_POST['username'];
            $matricula = $_POST['matricula'];
            $id_funcao = $_POST['id_funcao'];
            $id_setor = $_POST['id_setor'];
            $id_unidade = !empty($_POST['id_unidade']) ? $_POST['id_unidade'] : null;
            $status = $_POST['status'];
            $nova_senha = $_POST['password'];
            $foto_path = $_POST['foto_atual'];

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
                $target_dir = "uploads/colaboradores/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('colab_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $userModel->updateProfile($id_usuario, $nome, $username, $foto_path);
            $colaboradorModel->update($id_colaborador, $id_empresa, $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $status, $foto_path);

            if (!empty($nova_senha)) {
                $userModel->updatePassword($id_usuario, $nova_senha);
            }

            registrarAcao("Atualizou os dados do colaborador '{$nome}' (ID: {$id_colaborador}).");
            $_SESSION['success_message'] = 'Colaborador atualizado com sucesso!';
            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
                exit;
            }

            $db = getDbConnection();
            $colaboradorModel = new Colaborador($db);
            $colaborador = $colaboradorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

            if ($colaboradorModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                registrarAcao("Excluiu o colaborador '{$colaborador['nome_completo']}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Colaborador excluído com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao excluir colaborador. Verifique se há entregas vinculadas a ele.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $colaboradorModel = new Colaborador($db);
                $colaborador = $colaboradorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
                $novo_status = $colaborador['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($colaboradorModel->toggleStatus($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Alterou o status do colaborador '{$colaborador['nome_completo']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status do colaborador alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status do colaborador.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/colaboradores');
            exit;
        }
    }
}
