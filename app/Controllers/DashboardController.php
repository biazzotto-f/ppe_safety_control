<?php

namespace App\Controllers;

use App\Models\Dashboard;
use App\Models\Empresa;

class DashboardController extends BaseController
{

    public function index()
    {
        if ($_SESSION['nivel_acesso'] == 'funcionario') {
            header('Location: ' . $_ENV['APP_URL'] . '/minhas_entregas');
            exit;
        }

        $db = getDbConnection();
        $dashboardModel = new Dashboard($db);
        $stats = [];
        $top_epis = [];
        $top_colaboradores = [];
        $epis_estoque_baixo = [];
        $proximas_entregas = [];
        $empresas = [];
        $id_empresa_selecionada = null;
        $nome_empresa_selecionada = "Visão Geral de Todas as Empresas";

        if ($_SESSION['nivel_acesso'] == 'admin') {
            $id_empresa = $_SESSION['id_empresa_ativa'];
            $stats = $dashboardModel->getAdminStats($id_empresa);
            $top_epis = $dashboardModel->getTopEpisUtilizados($id_empresa);
            $top_colaboradores = $dashboardModel->getTopColaboradoresCusto($id_empresa);
            $epis_estoque_baixo = $dashboardModel->getEpisEstoqueBaixo($id_empresa);
            $proximas_entregas = $dashboardModel->getProximasEntregasProgramadas($id_empresa);
        } elseif ($_SESSION['nivel_acesso'] == 'superadmin') {
            $empresaModel = new Empresa($db);
            $empresas = $empresaModel->getAll();

            // Lógica de filtro persistente para o Superadmin
            if (isset($_GET['empresa_id'])) {
                $_SESSION['superadmin_filtro_empresa'] = $_GET['empresa_id'];
                header('Location: ' . $_ENV['APP_URL'] . '/dashboard');
                exit;
            }

            $id_empresa_selecionada = $_SESSION['superadmin_filtro_empresa'] ?? 'all';

            if ($id_empresa_selecionada !== 'all' && is_numeric($id_empresa_selecionada)) {
                $id_filtrar = (int)$id_empresa_selecionada;
                $stats = $dashboardModel->getAdminStats($id_filtrar);
                $top_epis = $dashboardModel->getTopEpisUtilizados($id_filtrar);
                $top_colaboradores = $dashboardModel->getTopColaboradoresCusto($id_filtrar);
                $epis_estoque_baixo = $dashboardModel->getEpisEstoqueBaixo($id_filtrar);
                $proximas_entregas = $dashboardModel->getProximasEntregasProgramadas($id_filtrar);

                foreach ($empresas as $empresa) {
                    if ($empresa['id'] == $id_filtrar) {
                        $nome_empresa_selecionada = $empresa['nome_empresa'];
                        break;
                    }
                }
            } else {
                $stats = $dashboardModel->getSuperAdminStats();
                $top_epis = $dashboardModel->getGlobalTopEpisUtilizados();
                $top_colaboradores = $dashboardModel->getGlobalTopColaboradoresCusto();
                $epis_estoque_baixo = $dashboardModel->getGlobalEpisEstoqueBaixo();
                $proximas_entregas = $dashboardModel->getGlobalProximasEntregas();
            }
        }

        $data = [
            'pageTitle' => 'Dashboard',
            'nomeUsuario' => $_SESSION['nome_usuario'],
            'stats' => $stats,
            'top_epis' => $top_epis,
            'top_colaboradores' => $top_colaboradores,
            'epis_estoque_baixo' => $epis_estoque_baixo,
            'proximas_entregas' => $proximas_entregas,
            'empresas' => $empresas,
            'id_empresa_selecionada' => $id_empresa_selecionada,
            'nome_empresa_selecionada' => $nome_empresa_selecionada
        ];

        $this->view('dashboard/index', $data);
    }
}
