<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Carrega o autoload do Composer e helpers
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/log_helper.php';

// Carrega as variáveis de ambiente do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Roteador simples
$route = $_GET['route'] ?? '';

// Roteamento para a página de login se não houver rota
if ($route === '') {
    $route = 'login';
}

// Proteção de rotas
$authRequiredRoutes = ['dashboard', 'empresas', 'colaboradores', 'epis', 'entregas', 'minhas_entregas', 'switch-empresa', 'perfil', 'relatorios', 'usuarios', 'compras', 'configuracoes', 'setores', 'funcoes', 'categorias', 'logs', 'fornecedores'];
if (in_array(explode('/', $route)[0], $authRequiredRoutes) && !isset($_SESSION['loggedin'])) {
    header('Location: ' . $_ENV['APP_URL'] . '/login');
    exit;
}

// Instanciação dos controllers
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EmpresaController;
use App\Controllers\ColaboradorController;
use App\Controllers\EPIController;
use App\Controllers\EntregaController;
use App\Controllers\FuncionarioController;
use App\Controllers\PerfilController;
use App\Controllers\RelatorioController;
use App\Controllers\UsuarioController; // Alterado
use App\Controllers\CompraController;
use App\Controllers\ConfiguracaoController;
use App\Controllers\SetorController;
use App\Controllers\FuncaoController;
use App\Controllers\CategoriaController;
use App\Controllers\LogController;
use App\Controllers\FornecedorController;

// Mapeamento de rotas para controllers
switch ($route) {
    // Auth & Perfil
    case 'login':
        (new AuthController())->showLoginForm();
        break;
    case 'auth/login':
        (new AuthController())->login();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'switch-empresa':
        (new AuthController())->switchEmpresa();
        break;
    case 'perfil':
        (new PerfilController())->index();
        break;
    case 'perfil/update':
        (new PerfilController())->update();
        break;

    // Dashboard
    case 'dashboard':
        (new DashboardController())->index();
        break;

    // Relatórios (Admin)
    case 'relatorios':
        (new RelatorioController())->entregas();
        break;
    case 'relatorios/entregas':
        (new RelatorioController())->entregas();
        break;
    case 'relatorios/colaboradores':
        (new RelatorioController())->porColaborador();
        break;
    case 'relatorios/funcoes':
        (new RelatorioController())->porFuncao();
        break;
    case 'relatorios/exportar_entregas':
        (new RelatorioController())->exportarEntregas();
        break;
    case 'relatorios/exportar_colaboradores':
        (new RelatorioController())->exportarPorColaborador();
        break;

    // Compras (Admin)
    case 'compras':
        (new CompraController())->index();
        break;
    case 'compras/create':
        (new CompraController())->create();
        break;
    case 'compras/store':
        (new CompraController())->store();
        break;
    case 'compras/edit':
        (new CompraController())->edit();
        break;
    case 'compras/update':
        (new CompraController())->update();
        break;
    case 'compras/delete':
        (new CompraController())->delete();
        break;

    // Configurações (Admin)
    case 'configuracoes':
        (new ConfiguracaoController())->index();
        break;
    case 'setores':
        (new SetorController())->index();
        break;
    case 'setores/create':
        (new SetorController())->create();
        break;
    case 'setores/store':
        (new SetorController())->store();
        break;
    case 'setores/edit':
        (new SetorController())->edit();
        break;
    case 'setores/update':
        (new SetorController())->update();
        break;
    case 'setores/delete':
        (new SetorController())->delete();
        break;
    case 'setores/toggle-status':
        (new SetorController())->toggleStatus();
        break;
    case 'funcoes':
        (new FuncaoController())->index();
        break;
    case 'funcoes/create':
        (new FuncaoController())->create();
        break;
    case 'funcoes/store':
        (new FuncaoController())->store();
        break;
    case 'funcoes/edit':
        (new FuncaoController())->edit();
        break;
    case 'funcoes/update':
        (new FuncaoController())->update();
        break;
    case 'funcoes/delete':
        (new FuncaoController())->delete();
        break;
    case 'funcoes/toggle-status':
        (new FuncaoController())->toggleStatus();
        break;
    case 'categorias':
        (new CategoriaController())->index();
        break;
    case 'categorias/create':
        (new CategoriaController())->create();
        break;
    case 'categorias/store':
        (new CategoriaController())->store();
        break;
    case 'categorias/edit':
        (new CategoriaController())->edit();
        break;
    case 'categorias/update':
        (new CategoriaController())->update();
        break;
    case 'categorias/delete':
        (new CategoriaController())->delete();
        break;

    // Empresas (Superadmin)
    case 'empresas':
        (new EmpresaController())->index();
        break;
    case 'empresas/create':
        (new EmpresaController())->create();
        break;
    case 'empresas/store':
        (new EmpresaController())->store();
        break;
    case 'empresas/edit':
        (new EmpresaController())->edit();
        break;
    case 'empresas/update':
        (new EmpresaController())->update();
        break;
    case 'empresas/delete':
        (new EmpresaController())->delete();
        break;
    case 'empresas/toggle-status':
        (new EmpresaController())->toggleStatus();
        break;

    // Usuários (Superadmin)
    case 'usuarios':
        (new UsuarioController())->index();
        break;
    case 'usuarios/create':
        (new UsuarioController())->create();
        break;
    case 'usuarios/store':
        (new UsuarioController())->store();
        break;
    case 'usuarios/edit':
        (new UsuarioController())->edit();
        break;
    case 'usuarios/update':
        (new UsuarioController())->update();
        break;
    case 'usuarios/delete':
        (new UsuarioController())->delete();
        break;
    case 'usuarios/toggle-status':
        (new UsuarioController())->toggleStatus();
        break;

    // Colaboradores (Admin)
    case 'colaboradores':
        (new ColaboradorController())->index();
        break;
    case 'colaboradores/create':
        (new ColaboradorController())->create();
        break;
    case 'colaboradores/store':
        (new ColaboradorController())->store();
        break;
    case 'colaboradores/edit':
        (new ColaboradorController())->edit();
        break;
    case 'colaboradores/update':
        (new ColaboradorController())->update();
        break;
    case 'colaboradores/delete':
        (new ColaboradorController())->delete();
        break;
    case 'colaboradores/toggle-status':
        (new ColaboradorController())->toggleStatus();
        break;

    // EPIs (Admin)
    case 'epis':
        (new EPIController())->index();
        break;
    case 'epis/create':
        (new EPIController())->create();
        break;
    case 'epis/store':
        (new EPIController())->store();
        break;
    case 'epis/edit':
        (new EPIController())->edit();
        break;
    case 'epis/update':
        (new EPIController())->update();
        break;
    case 'epis/delete':
        (new EPIController())->delete();
        break;
    case 'epis/toggle-status':
        (new EPIController())->toggleStatus();
        break;

    // Entregas (Admin)
    case 'entregas':
        (new EntregaController())->index();
        break;
    case 'entregas/create':
        (new EntregaController())->create();
        break;
    case 'entregas/store':
        (new EntregaController())->store();
        break;
    case 'entregas/delete':
        (new EntregaController())->delete();
        break;
    case 'entregas/ajax_get_epis':
        (new EntregaController())->ajaxGetEpis();
        break;


    // Funcionário
    case 'minhas_entregas':
        (new FuncionarioController())->minhasEntregas();
        break;
    case 'assinar_entrega':
        (new FuncionarioController())->showAssinaturaForm();
        break;
    case 'ajax/salvar_assinatura':
        (new FuncionarioController())->salvarAssinatura();
        break;

    // Fornecedores (Admin)
    case 'fornecedores':
        (new FornecedorController())->index();
        break;
    case 'fornecedores/create':
        (new FornecedorController())->create();
        break;
    case 'fornecedores/store':
        (new FornecedorController())->store();
        break;
    case 'fornecedores/edit':
        (new FornecedorController())->edit();
        break;
    case 'fornecedores/update':
        (new FornecedorController())->update();
        break;
    case 'fornecedores/delete':
        (new FornecedorController())->delete();
        break;
    case 'fornecedores/toggle-status':
        (new FornecedorController())->toggleStatus();
        break;

    // Log de Ações
    case 'logs':
        (new LogController())->index();
        break;

    default:
        http_response_code(404);
        echo "Página não encontrada";
        break;
}
