<?php
namespace App\Controllers;

use App\Models\Setor;

class SetorController extends BaseController
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
        $setorModel = new Setor($db);
        $setores = $setorModel->getByEmpresaId($_SESSION['id_empresa_ativa']);

        $this->view('setores/index', [
            'pageTitle' => 'Gerir Setores',
            'setores' => $setores
        ]);
    }

    public function create()
    {
        $this->view('setores/create', [
            'pageTitle' => 'Novo Setor'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $setorModel = new Setor($db);

            $id_empresa = $_SESSION['id_empresa_ativa'];
            $nome_setor = $_POST['nome_setor'];

            if ($setorModel->create($id_empresa, $nome_setor)) {
                registrarAcao("Criou o setor '{$nome_setor}'.");
                $_SESSION['success_message'] = 'Setor criado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao criar o setor.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }

        $db = getDbConnection();
        $setorModel = new Setor($db);
        $setor = $setorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

        if (!$setor) {
            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }

        $this->view('setores/edit', [
            'pageTitle' => 'Editar Setor',
            'setor' => $setor
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = getDbConnection();
            $setorModel = new Setor($db);

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id_empresa = $_SESSION['id_empresa_ativa'];
            $nome_setor = $_POST['nome_setor'];

            if ($setorModel->update($id, $id_empresa, $nome_setor)) {
                registrarAcao("Atualizou o setor '{$nome_setor}' (ID: {$id}).");
                $_SESSION['success_message'] = 'Setor atualizado com sucesso!';
            } else {
                $_SESSION['error_message'] = 'Erro ao atualizar o setor.';
            }

            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $setorModel = new Setor($db);
                $setor = $setorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);

                if ($setorModel->delete($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Excluiu o setor '{$setor['nome_setor']}' (ID: {$id}).");
                    $_SESSION['success_message'] = 'Setor excluído com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao excluir o setor. Verifique se existem funções associadas a ele.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $db = getDbConnection();
                $setorModel = new Setor($db);
                $setor = $setorModel->findByIdAndEmpresaId($id, $_SESSION['id_empresa_ativa']);
                $novo_status = $setor['status'] == 'ativo' ? 'inativo' : 'ativo';

                if ($setorModel->toggleStatus($id, $_SESSION['id_empresa_ativa'])) {
                    registrarAcao("Alterou o status do setor '{$setor['nome_setor']}' para '{$novo_status}'.");
                    $_SESSION['success_message'] = 'Status do setor alterado com sucesso!';
                } else {
                    $_SESSION['error_message'] = 'Erro ao alterar o status do setor.';
                }
            }
            header('Location: ' . $_ENV['APP_URL'] . '/setores');
            exit;
        }
    }
}
