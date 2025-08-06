<?php
namespace App\Controllers;

use App\Models\Fornecedor;

class FornecedorController extends BaseController
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
        $fornecedorModel = new Fornecedor($db);
        $fornecedores = $fornecedorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('fornecedores/index', [
            'pageTitle' => 'Gerir Fornecedores',
            'fornecedores' => $fornecedores
        ]);
    }

    public function create()
    {
        $this->view('fornecedores/create', [
            'pageTitle' => 'Novo Fornecedor'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $fornecedorModel = new Fornecedor($db);

            $foto_path = null;
            if (isset($_FILES['foto_fornecedor']) && $_FILES['foto_fornecedor']['error'] == 0) {
                $target_dir = "uploads/fornecedores/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_fornecedor"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('fornecedor_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_fornecedor"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['id_empresa'] = $_SESSION['id_empresa_ativa'];
            $dados['foto_fornecedor'] = $foto_path;

            if ($fornecedorModel->create($dados)) {
                registrarAcao("Cadastrou o fornecedor '{$dados['nome_fornecedor']}'.");
                $_SESSION['success_message'] = 'Fornecedor cadastrado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao cadastrar o fornecedor.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }

        $db = getDbConnection();
        $fornecedorModel = new Fornecedor($db);
        $fornecedor = $fornecedorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

        if (!$fornecedor) {
            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }

        $this->view('fornecedores/edit', [
            'pageTitle' => 'Editar Fornecedor',
            'fornecedor' => $fornecedor
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $fornecedorModel = new Fornecedor($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $foto_path = $_POST['foto_atual'];

            if (isset($_FILES['foto_fornecedor']) && $_FILES['foto_fornecedor']['error'] == 0) {
                $target_dir = "uploads/fornecedores/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_fornecedor"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('fornecedor_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_fornecedor"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['id_empresa'] = $_SESSION['id_empresa_ativa'];
            $dados['foto_fornecedor'] = $foto_path;

            if ($fornecedorModel->update($id, $dados)) {
                registrarAcao("Atualizou os dados do fornecedor '{$dados['nome_fornecedor']}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Fornecedor atualizado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar o fornecedor.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $fornecedorModel = new Fornecedor($db);
                $fornecedor = $fornecedorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

                if ($fornecedorModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Excluiu o fornecedor '{$fornecedor['nome_fornecedor']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Fornecedor excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao excluir o fornecedor.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $fornecedorModel = new Fornecedor($db);
                $fornecedor = $fornecedorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
                $novo_status = $fornecedor['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($fornecedorModel->toggleStatus($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Alterou o status do fornecedor '{$fornecedor['nome_fornecedor']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status do fornecedor alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status do fornecedor.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/fornecedores');
            exit;
        }
    }
}
