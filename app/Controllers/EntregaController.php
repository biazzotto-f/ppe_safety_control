<?php

namespace App\Controllers;

use App\Models\Entrega;
use App\Models\Colaborador;
use App\Models\EPI;

class EntregaController extends BaseController
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
        $entregaModel = new Entrega($db);
        $entregas = $entregaModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('entregas/index', [
            'pageTitle' => 'Histórico de Entregas',
            'entregas' => $entregas
        ]);
    }

    public function create()
    {
        $db = getDbConnection();
        $id_empresa = $_SESSION['id_empresa_ativa'];

        $colaboradorModel = new Colaborador($db);
        $colaboradores = $colaboradorModel->getActiveByEmpresaId($id_empresa);

        $this->view('entregas/create', [
            'pageTitle' => 'Registar Nova Entrega',
            'colaboradores' => $colaboradores
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_empresa = $_SESSION['id_empresa_ativa'];
            $id_usuario_entrega = $_SESSION['id_usuario'];
            $id_colaborador = $_POST['id_colaborador'];
            $items_json = $_POST['entrega_items'] ?? '[]';
            $items = json_decode($items_json, true);

            if (empty($id_colaborador) || empty($items)) {
                $_SESSION['error_message'] = 'Selecione um colaborador e adicione pelo menos um EPI à entrega.';
                header('Location: ' . $_ENV['APP_URL'] . '/entregas/create');
                exit;
            }

            $db = getDbConnection();
            $entregaModel = new Entrega($db);
            $erros = 0;
            $sucessos = 0;

            foreach ($items as $item) {
                if ($entregaModel->create($id_empresa, $id_colaborador, $item['epi_id'], $item['quantidade'], $id_usuario_entrega)) {
                    registrarAcao("Registou uma entrega de {$item['quantidade']}x '{$item['nome']}' para o Colaborador ID {$id_colaborador}.");
                    $sucessos++;
                } else {
                    $erros++;
                }
            }

            if ($sucessos > 0) {
                $_SESSION['success_message'] = "{$sucessos} entrega(s) registada(s) com sucesso!";
            }
            if ($erros > 0) {
                $_SESSION['error_message'] = "Falha ao registar {$erros} item(ns). Verifique se há estoque suficiente.";
            }

            header('Location: ' . $_ENV['APP_URL'] . '/entregas');
            exit;
        }
    }

    public function ajaxGetEpis()
    {
        header('Content-Type: application/json');
        $id_funcao = filter_input(INPUT_GET, 'funcao_id', FILTER_VALIDATE_INT);
        $id_empresa = $_SESSION['id_empresa_ativa'];

        if (!$id_funcao) {
            echo json_encode([]);
            exit;
        }

        $db = getDbConnection();
        $epiModel = new EPI($db);
        $epis = $epiModel->getEpisByFuncaoId($id_funcao, $id_empresa);

        echo json_encode($epis);
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];

            if (!$id) {
                header('Location: ' . $_ENV['APP_URL'] . '/entregas');
                exit;
            }

            $db = getDbConnection();
            $entregaModel = new Entrega($db);

            if ($entregaModel->delete($id, $id_empresa)) {
                registrarAcao("Excluiu a entrega ID {$id}.");
                $_SESSION['success_message'] = 'Entrega excluída e estoque devolvido com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao excluir a entrega.';
            }
            header('Location: ' . $_ENV['APP_URL'] . '/entregas');
            exit;
        }
    }
}
