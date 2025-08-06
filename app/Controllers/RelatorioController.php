<?php
namespace App\Controllers;

use App\Models\Relatorio;
use App\Models\Colaborador;
use App\Models\Empresa;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RelatorioController extends BaseController
{

    public function __construct()
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['nivel_acesso'] !== 'admin') {
            header('Location: ' . $_ENV['APP_URL'] . '/dashboard');
            exit;
        }
    }

    public function entregas()
    {
        $db = getDbConnection();
        $relatorioModel = new Relatorio($db);
        $empresaModel = new Empresa($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $empresa_ativa = $empresaModel->findById($id_empresa);

        $data_inicio = $_POST['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_POST['data_fim'] ?? date('Y-m-d');

        $entregas = $relatorioModel->getEntregasPorPeriodo($id_empresa, $data_inicio, $data_fim);

        $this->view('relatorios/entregas', [
            'pageTitle' => 'Relatório de Entregas por Período',
            'entregas' => $entregas,
            'empresa_ativa' => $empresa_ativa,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
    }

    public function porColaborador()
    {
        $db = getDbConnection();
        $relatorioModel = new Relatorio($db);
        $colaboradorModel = new Colaborador($db);
        $empresaModel = new Empresa($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $colaboradores = $colaboradorModel->getActiveByEmpresaId($id_empresa);
        $empresa_ativa = $empresaModel->findById($id_empresa);

        $data_inicio = $_POST['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_POST['data_fim'] ?? date('Y-m-d');
        $colaborador_id_selecionado = $_POST['id_colaborador'] ?? null;
        $dados = [];
        $colaborador_selecionado = null;

        if ($colaborador_id_selecionado) {
            $dados = $relatorioModel->getEntregasPorColaboradorEPeriodo($id_empresa, $colaborador_id_selecionado, $data_inicio, $data_fim);
            $colaborador_selecionado = $colaboradorModel->getDetailsById($colaborador_id_selecionado);
        }

        $this->view('relatorios/por_funcionario', [
            'pageTitle' => 'Relatório de Entregas por Funcionário',
            'colaboradores' => $colaboradores,
            'colaborador_id_selecionado' => $colaborador_id_selecionado,
            'colaborador_selecionado' => $colaborador_selecionado,
            'empresa_ativa' => $empresa_ativa,
            'dados' => $dados,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
    }

    public function porFuncao()
    {
        $db = getDbConnection();
        $relatorioModel = new Relatorio($db);
        $empresaModel = new Empresa($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $dados = $relatorioModel->getRelatorioFuncao($id_empresa);
        $empresa_ativa = $empresaModel->findById($id_empresa);

        $this->view('relatorios/por_funcao', [
            'pageTitle' => 'Relatório de EPIs por Função',
            'dados' => $dados,
            'empresa_ativa' => $empresa_ativa
        ]);
    }

    public function exportarEntregas()
    {
        $db = getDbConnection();
        $relatorioModel = new Relatorio($db);

        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');

        $entregas = $relatorioModel->getEntregasPorPeriodo($_SESSION['id_empresa_ativa'], $data_inicio, $data_fim);
        registrarAcao("Exportou o relatório de entregas por período.");

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório de Entregas');

        $sheet->setCellValue('A1', 'Colaborador');
        $sheet->setCellValue('B1', 'Matrícula');
        $sheet->setCellValue('C1', 'EPI');
        $sheet->setCellValue('D1', 'C.A.');
        $sheet->setCellValue('E1', 'Data da Entrega');
        $sheet->setCellValue('F1', 'Status');

        $row = 2;
        foreach ($entregas as $entrega) {
            $sheet->setCellValue('A' . $row, $entrega['colaborador_nome']);
            $sheet->setCellValue('B' . $row, $entrega['matricula']);
            $sheet->setCellValue('C' . $row, $entrega['nome_epi']);
            $sheet->setCellValue('D' . $row, $entrega['ca']);
            $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($entrega['data_entrega'])));
            $sheet->setCellValue('F' . $row, $entrega['assinatura_digital'] ? 'Assinado' : 'Pendente');
            $row++;
        }

        $filename = 'relatorio_entregas_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportarPorColaborador()
    {
        $db = getDbConnection();
        $relatorioModel = new Relatorio($db);

        $id_empresa = $_SESSION['id_empresa_ativa'];
        $id_colaborador = $_GET['id_colaborador'] ?? null;
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');

        if (!$id_colaborador) {
            die("Erro: Nenhum funcionário selecionado para exportação.");
        }

        $dados = $relatorioModel->getEntregasPorColaboradorEPeriodo($id_empresa, $id_colaborador, $data_inicio, $data_fim);
        registrarAcao("Exportou o relatório de entregas para o colaborador ID {$id_colaborador}.");

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Entregas por Funcionário');

        $sheet->setCellValue('A1', 'Colaborador');
        $sheet->setCellValue('B1', 'Matrícula');
        $sheet->setCellValue('C1', 'EPI');
        $sheet->setCellValue('D1', 'C.A.');
        $sheet->setCellValue('E1', 'Data da Entrega');
        $sheet->setCellValue('F1', 'Status');

        $row = 2;
        foreach ($dados as $dado) {
            $sheet->setCellValue('A' . $row, $dado['colaborador_nome']);
            $sheet->setCellValue('B' . $row, $dado['matricula']);
            $sheet->setCellValue('C' . $row, $dado['nome_epi']);
            $sheet->setCellValue('D' . $row, $dado['ca']);
            $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($dado['data_entrega'])));
            $sheet->setCellValue('F' . $row, $dado['assinatura_digital'] ? 'Assinado' : 'Pendente');
            $row++;
        }

        $filename = 'relatorio_funcionario_' . ($dados[0]['matricula'] ?? 'ID' . $id_colaborador) . '_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
