<?php
namespace App\Controllers;

use App\Models\Empresa;

class EmpresaController extends BaseController
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
        $empresaModel = new Empresa($db);
        $empresas = $empresaModel->getAll();

        $this->view('empresas/index', [
            'pageTitle' => 'Gerir Empresas',
            'empresas' => $empresas
        ]);
    }

    public function create()
    {
        $this->view('empresas/create', [
            'pageTitle' => 'Nova Empresa'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $empresaModel = new Empresa($db);

            $foto_path = null;
            if (isset($_FILES['foto_empresa']) && $_FILES['foto_empresa']['error'] == 0) {
                $target_dir = "uploads/empresas/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_empresa"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('empresa_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_empresa"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['foto_empresa'] = $foto_path;

            $id_nova_empresa = $empresaModel->create($dados);

            if ($id_nova_empresa) {
                $empresaModel->syncUnidades($id_nova_empresa, $_POST['unidades'] ?? []);
                registrarAcao("Criou a empresa '{$dados['nome_empresa']}' (ID: {$id_nova_empresa}).");
                $_SESSION['success_message'] = 'Empresa cadastrada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao cadastrar empresa.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/empresas');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/empresas');
            exit;
        }

        $db = getDbConnection();
        $empresaModel = new Empresa($db);
        $empresa = $empresaModel->findById($id);
        $unidades = $empresaModel->getUnidadesByEmpresaId($id);

        $this->view('empresas/edit', [
            'pageTitle' => 'Editar Empresa',
            'empresa' => $empresa,
            'unidades' => $unidades
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $empresaModel = new Empresa($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $foto_path = $_POST['foto_atual'];

            if (isset($_FILES['foto_empresa']) && $_FILES['foto_empresa']['error'] == 0) {
                $target_dir = "uploads/empresas/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_empresa"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('empresa_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_empresa"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['foto_empresa'] = $foto_path;

            if ($empresaModel->update($id, $dados)) {
                $empresaModel->syncUnidades($id, $_POST['unidades'] ?? []);
                registrarAcao("Atualizou os dados da empresa '{$dados['nome_empresa']}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Empresa atualizada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar empresa.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/empresas');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $empresaModel = new Empresa($db);
                $empresa = $empresaModel->findById($id);

                if ($empresaModel->delete($id)) {
                    registrarAcao("Excluiu a empresa '{$empresa['nome_empresa']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Empresa excluída com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Não é possível excluir a empresa. Existem dados associados a ela.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/empresas');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $empresaModel = new Empresa($db);
                $empresa = $empresaModel->findById($id);
                $novo_status = $empresa['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($empresaModel->toggleStatus($id)) {
                    registrarAcao("Alterou o status da empresa '{$empresa['nome_empresa']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status da empresa alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status da empresa.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/empresas');
            exit;
        }
    }
}
