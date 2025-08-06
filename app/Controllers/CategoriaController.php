<?php
namespace App\Controllers;

use App\Models\Categoria;

class CategoriaController extends BaseController
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
        $categoriaModel = new Categoria($db);
        $categorias = $categoriaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('categorias/index', [
            'pageTitle' => 'Gerir Categorias de EPIs',
            'categorias' => $categorias
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $categoriaModel = new Categoria($db);
        $classificacoes = $categoriaModel->getClassificacoes();

        $this->view('categorias/create', [
            'pageTitle' => 'Nova Categoria de EPI',
            'classificacoes' => $classificacoes
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $categoriaModel = new Categoria($db);

            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_classificacao = $_POST['id_classificacao'];
            $nome_categoria = $_POST['nome_categoria'];

            if ($categoriaModel->create($id_empresa, $id_classificacao, $nome_categoria)) {
                registrarAcao("Criou a categoria de EPI '{$nome_categoria}'.");
                $_SESSION['success_message'] = 'Categoria criada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao criar a categoria.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/categorias');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/categorias');
            exit;
        }

        $db = getDbConnection();
        $categoriaModel = new Categoria($db);
        $categoria = $categoriaModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
        $classificacoes = $categoriaModel->getClassificacoes();

        if (!$categoria) {
            header('Location: ' . $_ENV['APP_URL'] . '/categorias');
            exit;
        }

        $this->view('categorias/edit', [
            'pageTitle' => 'Editar Categoria de EPI',
            'categoria' => $categoria,
            'classificacoes' => $classificacoes
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $categoriaModel = new Categoria($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_classificacao = $_POST['id_classificacao'];
            $nome_categoria = $_POST['nome_categoria'];

            if ($categoriaModel->update($id, $id_empresa, $id_classificacao, $nome_categoria)) {
                registrarAcao("Atualizou a categoria de EPI '{$nome_categoria}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Categoria atualizada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar a categoria.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/categorias');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $categoriaModel = new Categoria($db);
                $categoria = $categoriaModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

                if ($categoriaModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Excluiu a categoria de EPI '{$categoria['nome_categoria']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Categoria excluída com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Não é possível excluir a categoria. Existem EPIs ou funções associadas a ela.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/categorias');
            exit;
        }
    }
}
