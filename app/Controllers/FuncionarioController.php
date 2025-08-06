<?php
namespace App\Controllers;

use App\Models\Funcionario;

class FuncionarioController extends BaseController {

    public function __construct() {
        if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] !== 'funcionario') {
            header('Location: ' . $_ENV['APP_URL'] . '/dashboard');
            exit;
        }
    }

    public function minhasEntregas() {
        $db = getDbConnection();
        $funcionarioModel = new Funcionario($db);
        $entregas = $funcionarioModel->getEntregasByUserId($_SESSION['id_usuario']);

        $this->view('funcionarios/minhas_entregas', [
            'pageTitle' => 'Minhas Entregas de EPIs',
            'entregas' => $entregas
        ]);
    }

    public function showAssinaturaForm() {
        $id_entrega = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id_entrega) {
            header('Location: ' . $_ENV['APP_URL'] . '/minhas_entregas');
            exit;
        }

        $db = getDbConnection();
        $funcionarioModel = new Funcionario($db);
        $entrega = $funcionarioModel->getEntregaDetailsById($id_entrega, $_SESSION['id_usuario']);

        if (!$entrega) {
            // Segurança: não mostra a página se a entrega não pertence ao usuário
            header('Location: ' . $_ENV['APP_URL'] . '/minhas_entregas');
            exit;
        }

        // Passa os dados para a view
        $pageTitle = 'Assinar Recebimento';
        
        // Carrega a view de assinatura diretamente, pois ela é uma página completa
        // e não precisa do layout principal.
        require_once __DIR__ . '/../Views/funcionarios/assinar_entrega.php';
    }

    public function salvarAssinatura() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id_entrega = isset($data['id_entrega']) ? intval($data['id_entrega']) : 0;
        $assinatura_base64 = isset($data['assinatura']) ? $data['assinatura'] : '';
        $id_usuario = $_SESSION['id_usuario'];

        if ($id_entrega > 0 && !empty($assinatura_base64)) {
            $db = getDbConnection();
            $funcionarioModel = new Funcionario($db);

            if ($funcionarioModel->saveSignature($id_entrega, $id_usuario, $assinatura_base64)) {
                registrarAcao("Assinou o recebimento da entrega ID: {$id_entrega}.");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Falha ao guardar na base de dados.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Dados de assinatura inválidos.']);
        }
        exit;
    }
}
