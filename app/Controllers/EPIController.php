<?php
namespace App\Controllers;

use App\Models\EPI;
use App\Models\Categoria;
use App\Models\Fornecedor;

class EPIController extends BaseController
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
        $epiModel = new EPI($db);
        $epis = $epiModel->getAllByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('epis/index', [
            'pageTitle' => 'Catálogo de EPIs',
            'epis' => $epis
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $categoriaModel = new Categoria($db);
        $fornecedorModel = new Fornecedor($db);

        $classificacoes = $categoriaModel->getClassificacoes();
        $categorias = $categoriaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $fornecedores = $fornecedorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('epis/create', [
            'pageTitle' => 'Novo EPI no Catálogo',
            'classificacoes' => $classificacoes,
            'categorias' => $categorias,
            'fornecedores' => $fornecedores
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $epiModel = new EPI($db);

            $foto_path = null;
            if (isset($_FILES['foto_epi']) && $_FILES['foto_epi']['error'] == 0) {
                $target_dir = "uploads/epis/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_epi"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('epi_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_epi"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['id_empresa'] = $_SESSION['id_empresa_ativa'];
            $dados['fornecedor_ids'] = isset($_POST['fornecedor_ids']) && !empty($_POST['fornecedor_ids']) ? explode(',', $_POST['fornecedor_ids']) : [];
            $dados['foto_epi'] = $foto_path;
            $dados['validade_ca'] = !empty($_POST['validade_ca']) ? $_POST['validade_ca'] : null;

            if ($epiModel->create($dados)) {
                registrarAcao("Cadastrou o EPI '{$dados['nome_epi']}' (C.A: {$dados['ca']}).");
                $_SESSION['success_message'] = 'EPI cadastrado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao cadastrar EPI.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }

        $db = getDbConnection();
        $epiModel = new EPI($db);
        $categoriaModel = new Categoria($db);
        $fornecedorModel = new Fornecedor($db);

        $epi = $epiModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
        $classificacoes = $categoriaModel->getClassificacoes();
        $categorias = $categoriaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $fornecedores = $fornecedorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);
        $fornecedores_associados_ids = $epiModel->getAssociatedFornecedorIds($id);

        if (!$epi) {
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }

        $this->view('epis/edit', [
            'pageTitle' => 'Editar EPI do Catálogo',
            'epi' => $epi,
            'classificacoes' => $classificacoes,
            'categorias' => $categorias,
            'fornecedores' => $fornecedores,
            'fornecedores_associados_ids' => $fornecedores_associados_ids
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $epiModel = new EPI($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $foto_path = $_POST['foto_atual'];

            if (isset($_FILES['foto_epi']) && $_FILES['foto_epi']['error'] == 0) {
                $target_dir = "uploads/epis/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0755, true);
                $file_extension = pathinfo($_FILES["foto_epi"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . uniqid('epi_', true) . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto_epi"]["tmp_name"], $target_file)) {
                    $foto_path = $target_file;
                }
            }

            $dados = $_POST;
            $dados['id_empresa'] = $_SESSION['id_empresa_ativa'];
            $dados['fornecedor_ids'] = isset($_POST['fornecedor_ids']) && !empty($_POST['fornecedor_ids']) ? explode(',', $_POST['fornecedor_ids']) : [];
            $dados['foto_epi'] = $foto_path;
            $dados['validade_ca'] = !empty($_POST['validade_ca']) ? $_POST['validade_ca'] : null;

            if ($epiModel->update($id, $dados)) {
                registrarAcao("Atualizou o EPI '{$dados['nome_epi']}' (C.A: {$dados['ca']}).");
                $_SESSION['success_message'] = 'EPI atualizado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar EPI.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $epiModel = new EPI($db);
                $epi = $epiModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

                if ($epiModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Excluiu o EPI '{$epi['nome_epi']}' (C.A: {$epi['ca']}) do catálogo.");
                    $_SESSION['success_message'] = 'EPI excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Não é possível excluir o EPI. Existem lotes ou entregas associadas a ele.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $epiModel = new EPI($db);
                $epi = $epiModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
                $novo_status = $epi['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($epiModel->toggleStatus($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Alterou o status do EPI '{$epi['nome_epi']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status do EPI alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status do EPI.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/epis');
            exit;
        }
    }
}
