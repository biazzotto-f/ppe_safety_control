<?php
namespace App\Controllers;

use App\Models\Compra;
use App\Models\EPI;
use App\Models\Fornecedor; // Importa o modelo Fornecedor

class CompraController extends BaseController
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
        $compraModel = new Compra($db);
        $lotes = $compraModel->getLotesByEmpresa($_SESSION['id_empresa_ativa']);

        $this->view('compras/index', [
            'pageTitle' => 'Registo de Compras (Lotes)',
            'lotes' => $lotes
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $epiModel = new EPI($db);
        $fornecedorModel = new Fornecedor($db);

        $epis = $epiModel->getAllByEmpresaId($_SESSION['id_empresa_ativa']);
        $fornecedores = $fornecedorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('compras/create', [
            'pageTitle' => 'Registar Nova Compra',
            'epis' => $epis,
            'fornecedores' => $fornecedores
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $compraModel = new Compra($db);

            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_epi = $_POST['id_epi'];
            $id_fornecedor = !empty($_POST['id_fornecedor']) ? $_POST['id_fornecedor'] : null;
            $quantidade = $_POST['quantidade'];
            $data_compra = $_POST['data_compra'];
            $data_vencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;
            $nota_fiscal = $_POST['nota_fiscal'];
            $custo_unitario = $_POST['custo_unitario'];
            $custo_total = $_POST['custo_total'];

            $id_novo_lote = $compraModel->createLote($id_empresa, $id_epi, $id_fornecedor, $quantidade, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total);

            if ($id_novo_lote) {
                registrarAcao("Registou uma nova compra (Lote ID: {$id_novo_lote}) de {$quantidade} unidades para o EPI ID {$id_epi}.");
                $_SESSION['success_message'] = 'Compra registada e estoque atualizado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao registar a compra.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/compras');
            exit;
        }
    }

    public function edit()
    {
        $id_lote = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id_lote) {
            header('Location: ' . $_ENV['APP_URL'] . '/compras');
            exit;
        }

        $db = getDbConnection();
        $compraModel = new Compra($db);
        $fornecedorModel = new Fornecedor($db);

        $lote = $compraModel->findLoteById($_SESSION['id_empresa_ativa'], $id_lote);
        $fornecedores = $fornecedorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        if (!$lote) {
            header('Location: ' . $_ENV['APP_URL'] . '/compras');
            exit;
        }

        $this->view('compras/edit', [
            'pageTitle' => 'Editar Registo de Compra',
            'lote' => $lote,
            'fornecedores' => $fornecedores
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $compraModel = new Compra($db);

            $id_lote = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_fornecedor = !empty($_POST['id_fornecedor']) ? $_POST['id_fornecedor'] : null;
            $data_compra = $_POST['data_compra'];
            $data_vencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;
            $nota_fiscal = $_POST['nota_fiscal'];
            $custo_unitario = $_POST['custo_unitario'];
            $custo_total = $_POST['custo_total'];

            if ($compraModel->updateLote($id_lote, $id_empresa, $id_fornecedor, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total)) {
                registrarAcao("Atualizou o registo de compra do Lote ID {$id_lote}.");
                $_SESSION['success_message'] = 'Registo de compra atualizado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar o registo.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/compras');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_lote = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];

            if ($id_lote) {
                $db = getDbConnection();
                $compraModel = new Compra($db);

                if ($compraModel->deleteLote($id_lote, $id_empresa)) {
                    registrarAcao("Excluiu o registo de compra do Lote ID {$id_lote}.");
                    $_SESSION['success_message'] = 'Registo de compra excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao excluir. Este lote não pode ser apagado pois já foram efetuadas entregas a partir dele.';
                }
            }

            header('Location: ' . $_ENV['APP_URL'] . '/compras');
            exit;
        }
    }
}
