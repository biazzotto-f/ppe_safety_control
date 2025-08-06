<?php
$nivel_acesso = $_SESSION['nivel_acesso'];
$current_route = explode('/', $_GET['route'] ?? '')[0];
$is_reports_active = strpos($current_route, 'relatorios') === 0;
$is_config_active = in_array($current_route, ['configuracoes', 'setores', 'funcoes', 'categorias']);
?>
<div class="sidebar">
        <!-- Seletor de Empresa para Superadmin -->
        <?php if ($nivel_acesso == 'superadmin'):
                $id_empresa_selecionada = $_GET['empresa_id'] ?? 'all';
                $nome_empresa_selecionada = 'Visão Geral (Todas)';
                $logo_empresa_selecionada = null;

                if ($id_empresa_selecionada !== 'all' && isset($_SESSION['todas_empresas'])) {
                        foreach ($_SESSION['todas_empresas'] as $empresa) {
                                if ($empresa['id'] == $id_empresa_selecionada) {
                                        $nome_empresa_selecionada = $empresa['nome_empresa'];
                                        $logo_empresa_selecionada = $empresa['foto_empresa'];
                                        break;
                                }
                        }
                }
                ?>
                <div class="company-selector dropdown">
                        <a href="#" class="btn dropdown-toggle" role="button" id="empresaSidebarDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="company-logo-wrapper">
                                        <?php if ($logo_empresa_selecionada): ?>
                                                <img src="<?= $appUrl . '/' . $logo_empresa_selecionada ?>" alt="Logo"
                                                        class="company-logo-img">
                                        <?php else: ?>
                                                <i class="fa-solid fa-globe"></i>
                                        <?php endif; ?>
                                </div>
                                <div class="company-selector-text">
                                        <small>Visualizando Empresa</small>
                                        <span class="company-name"><?= htmlspecialchars($nome_empresa_selecionada) ?></span>
                                </div>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="empresaSidebarDropdown">
                                <li>
                                        <a class="dropdown-item <?= $id_empresa_selecionada == 'all' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/dashboard?empresa_id=all">
                                                Visão Geral (Todas)
                                        </a>
                                </li>
                                <?php foreach ($_SESSION['todas_empresas'] as $empresa): ?>
                                        <li>
                                                <a class="dropdown-item <?= $id_empresa_selecionada == $empresa['id'] ? 'active' : '' ?>"
                                                        href="<?= $appUrl ?>/dashboard?empresa_id=<?= $empresa['id'] ?>">
                                                        <?= htmlspecialchars($empresa['nome_empresa']) ?>
                                                </a>
                                        </li>
                                <?php endforeach; ?>
                        </ul>
                </div>
        <?php endif; ?>

        <!-- Seletor de Empresa para Admins -->
        <?php if ($nivel_acesso == 'admin' && isset($_SESSION['empresas_acesso'])): ?>
                <?php if (count($_SESSION['empresas_acesso']) > 1): ?>
                        <div class="company-selector dropdown">
                                <a href="#" class="btn dropdown-toggle" role="button" id="empresaSidebarDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="company-logo-wrapper">
                                                <?php if (isset($_SESSION['empresa_ativa_logo']) && !empty($_SESSION['empresa_ativa_logo'])): ?>
                                                        <img src="<?= $appUrl . '/' . $_SESSION['empresa_ativa_logo'] ?>" alt="Logo"
                                                                class="company-logo-img">
                                                <?php else: ?>
                                                        <i class="fa-solid fa-building"></i>
                                                <?php endif; ?>
                                        </div>
                                        <div class="company-selector-text">
                                                <small>Empresa Ativa</small><br>
                                                <span
                                                        class="company-name"><?= htmlspecialchars($_SESSION['empresa_ativa_nome']) ?></span>
                                        </div>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="empresaSidebarDropdown">
                                        <?php foreach ($_SESSION['empresas_acesso'] as $empresa): ?>
                                                <li>
                                                        <a class="dropdown-item <?= $empresa['id'] == $_SESSION['id_empresa_ativa'] ? 'active' : '' ?>"
                                                                href="<?= $appUrl ?>/switch-empresa?id=<?= $empresa['id'] ?>&redirect=<?= urlencode($current_route) ?>">
                                                                <?= htmlspecialchars($empresa['nome_empresa']) ?>
                                                        </a>
                                                </li>
                                        <?php endforeach; ?>
                                </ul>
                        </div>
                <?php elseif (count($_SESSION['empresas_acesso']) == 1): ?>
                        <div class="company-selector">
                                <div class="btn dropdown-toggle">
                                        <div class="company-logo-wrapper">
                                                <?php if (isset($_SESSION['empresa_ativa_logo']) && !empty($_SESSION['empresa_ativa_logo'])): ?>
                                                        <img src="<?= $appUrl . '/' . $_SESSION['empresa_ativa_logo'] ?>" alt="Logo"
                                                                class="company-logo-img">
                                                <?php else: ?>
                                                        <i class="fa-solid fa-building"></i>
                                                <?php endif; ?>
                                        </div>
                                        <div class="company-selector-text">
                                                <small>Empresa Ativa</small><br>
                                                <span
                                                        class="company-name"><?= htmlspecialchars($_SESSION['empresa_ativa_nome']) ?></span>
                                        </div>
                                </div>
                        </div>
                <?php endif; ?>
        <?php endif; ?>

        <div class="sidebar-nav-wrapper">
                <ul class="nav flex-column">
                        <?php if ($nivel_acesso == 'superadmin'): ?>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'dashboard' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/dashboard"><i class="fa-solid fa-chart-line fa-fw"></i>
                                                Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'empresas' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/empresas"><i class="fa-solid fa-building fa-fw"></i>
                                                Empresas</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'usuarios' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/usuarios"><i class="fa-solid fa-users-cog fa-fw"></i>
                                                Usuários</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'logs' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/logs"><i class="fa-solid fa-history fa-fw"></i> Log de
                                                Ações</a></li>
                        <?php elseif ($nivel_acesso == 'admin'): ?>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'dashboard' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/dashboard"><i class="fa-solid fa-chart-line fa-fw"></i>
                                                Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'fornecedores' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/fornecedores"><i class="fa-solid fa-truck fa-fw"></i>
                                                Fornecedores</a></li>

                                <li class="nav-item"><a class="nav-link <?= $current_route == 'compras' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/compras"><i class="fa-solid fa-shopping-cart fa-fw"></i>
                                                Compras</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'entregas' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/entregas"><i class="fa-solid fa-right-left fa-fw"></i>
                                                Entregas</a></li>
                                <li class="nav-item"><a
                                                class="nav-link <?= $current_route == 'colaboradores' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/colaboradores"><i class="fa-solid fa-users fa-fw"></i>
                                                Colaboradores</a></li>
                                <li class="nav-item"><a class="nav-link <?= $current_route == 'epis' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/epis"><i class="fa-solid fa-hard-hat fa-fw"></i> Catálogo
                                                de EPIs</a></li>
                                <li class="nav-item">
                                        <a class="nav-link <?= $is_reports_active ? 'active' : '' ?>" href="#submenu-reports"
                                                data-bs-toggle="collapse" role="button"
                                                aria-expanded="<?= $is_reports_active ? 'true' : 'false' ?>">
                                                <i class="fa-solid fa-file-alt fa-fw"></i> Relatórios
                                        </a>
                                        <div class="collapse <?= $is_reports_active ? 'show' : '' ?>" id="submenu-reports">
                                                <ul class="nav flex-column ps-3">
                                                        <li><a class="nav-link sub-link <?= $current_route == 'relatorios/entregas' ? 'active' : '' ?>"
                                                                        href="<?= $appUrl ?>/relatorios/entregas">Entregas por
                                                                        Período</a></li>
                                                        <li><a class="nav-link sub-link <?= $current_route == 'relatorios/colaboradores' ? 'active' : '' ?>"
                                                                        href="<?= $appUrl ?>/relatorios/colaboradores">Entregas
                                                                        por Colaborador</a></li>
                                                        <li><a class="nav-link sub-link <?= $current_route == 'relatorios/funcoes' ? 'active' : '' ?>"
                                                                        href="<?= $appUrl ?>/relatorios/funcoes">EPIs por
                                                                        Função</a></li>
                                                </ul>
                                        </div>
                                </li>
                                <li class="nav-item"><a
                                                class="nav-link <?= $current_route == 'configuracoes' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/configuracoes"><i class="fa-solid fa-cogs fa-fw"></i>
                                                Configurações</a></li>
                        <?php elseif ($nivel_acesso == 'funcionario'): ?>
                                <li class="nav-item"><a
                                                class="nav-link <?= $current_route == 'minhas_entregas' ? 'active' : '' ?>"
                                                href="<?= $appUrl ?>/minhas_entregas"><i
                                                        class="fa-solid fa-signature fa-fw"></i> Minhas Entregas</a></li>
                        <?php endif; ?>
                </ul>
        </div>

        <div class="sidebar-footer">
                <a href="<?= $appUrl ?>/logout" class="nav-link"><i class="fa-solid fa-sign-out-alt fa-fw"></i> Sair</a>
        </div>
</div>