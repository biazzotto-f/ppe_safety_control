<?php
namespace App\Controllers;

use App\Models\Funcao;
use App\Models\Setor;
use App\Models\Categoria;

class FuncaoController extends BaseController
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
        $funcaoModel = new Funcao($db);
        $funcoes = $funcaoModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('funcoes/index', [
            'pageTitle' => 'Gerir Funções',
            'funcoes' => $funcoes
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $setorModel = new Setor($db);
        $categoriaModel = new Categoria($db);

        $setores = $setorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $classificacoes = $categoriaModel->getClassificacoes();
        $categorias = $categoriaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('funcoes/create', [
            'pageTitle' => 'Nova Função',
            'setores' => $setores,
            'classificacoes' => $classificacoes,
            'categorias' => $categorias
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $funcaoModel = new Funcao($db);

            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_setor = $_POST['id_setor'];
            $nome_funcao = $_POST['nome_funcao'];
            $riscos = isset($_POST['riscos']) ? implode(',', $_POST['riscos']) : null;
            $classificacao_ids = $_POST['classificacoes'] ?? [];
            $categoria_ids = isset($_POST['categoria_ids']) ? explode(',', $_POST['categoria_ids']) : [];

            if ($funcaoModel->create($id_empresa, $id_setor, $nome_funcao, $riscos, $classificacao_ids, $categoria_ids)) {
                registrarAcao("Criou a função '{$nome_funcao}'.");
                $_SESSION['success_message'] = 'Função criada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao criar a função.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/funcoes');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/funcoes');
            exit;
        }

        $db = getDbConnection();
        $funcaoModel = new Funcao($db);
        $setorModel = new Setor($db);
        $categoriaModel = new Categoria($db);

        $funcao = $funcaoModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
        $setores = $setorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $classificacoes = $categoriaModel->getClassificacoes();
        $categorias = $categoriaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $classificacoes_associadas_ids = $funcaoModel->getAssociatedClassificacaoIds($id);
        $categorias_associadas = $funcaoModel->getAssociatedCategorias($id);

        $this->view('funcoes/edit', [
            'pageTitle' => 'Editar Função',
            'funcao' => $funcao,
            'setores' => $setores,
            'classificacoes' => $classificacoes,
            'categorias' => $categorias,
            'classificacoes_associadas_ids' => $classificacoes_associadas_ids,
            'categorias_associadas' => $categorias_associadas
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $funcaoModel = new Funcao($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_setor = $_POST['id_setor'];
            $nome_funcao = $_POST['nome_funcao'];
            $riscos = isset($_POST['riscos']) ? implode(',', $_POST['riscos']) : null;
            $classificacao_ids = $_POST['classificacoes'] ?? [];
            $categoria_ids = isset($_POST['categoria_ids']) ? explode(',', $_POST['categoria_ids']) : [];

            if ($funcaoModel->update($id, $id_setor, $nome_funcao, $riscos, $classificacao_ids, $categoria_ids)) {
                registrarAcao("Atualizou a função '{$nome_funcao}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Função atualizada com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar a função.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/funcoes');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $funcaoModel = new Funcao($db);
                $funcao = $funcaoModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

                if ($funcaoModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Excluiu a função '{$funcao['nome_funcao']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Função excluída com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Não é possível excluir a função. Existem colaboradores associados a ela.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/funcoes');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $funcaoModel = new Funcao($db);
                $funcao = $funcaoModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
                $novo_status = $funcao['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($funcaoModel->toggleStatus($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Alterou o status da função '{$funcao['nome_funcao']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status da função alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status da função.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/funcoes');
            exit;
        }
    }
}
