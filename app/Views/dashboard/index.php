<?php
ob_start();

function calcularDiferenca($atual, $anterior)
{
    if ($anterior > 0) {
        return (($atual - $anterior) / $anterior) * 100;
    }
    if ($atual > 0) {
        return 100.0;
    }
    return 0.0;
}

$diferencaInvestimento = calcularDiferenca($stats['investimento_mes_atual'], $stats['investimento_mes_anterior']);
$diferencaUtilizado = calcularDiferenca($stats['utilizado_mes_atual'], $stats['utilizado_mes_anterior']);

// Agrupa as próximas entregas por colaborador
$entregas_por_colaborador = [];
foreach ($proximas_entregas as $entrega) {
    $entregas_por_colaborador[$entrega['id_colaborador']]['detalhes'] = [
        'nome' => $entrega['nome_completo'],
        'foto' => $entrega['foto_perfil']
    ];
    $entregas_por_colaborador[$entrega['id_colaborador']]['entregas'][] = $entrega;
}
?>

<div class="container-fluid">
    <?php if ($_SESSION['nivel_acesso'] == 'superadmin'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($nome_empresa_selecionada) ?></h1>
        </div>
    <?php else: ?>
        <h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>
    <?php endif; ?>

    <!-- Linha 1: Métricas Principais -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Colaboradores Ativos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['total_colaboradores'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-success"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Entregas Pendentes</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['entregas_pendentes'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-signature fa-2x text-info"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Custo Total do Estoque</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">R$ <?= number_format($stats['custo_total_estoque'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-primary"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha 2: Métricas Financeiras e Estoque Baixo -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-secondary text-uppercase mb-1">Investimento Mensal (<?= date('m/Y') ?>)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">R$ <?= number_format($stats['investimento_mes_atual'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-money-bill-wave fa-2x text-secondary"></i></div>
                    </div>
                    <hr class="my-2">
                    <div class="text-xs text-muted">Mês Anterior (<?= date('m/Y', strtotime('first day of last month')) ?>): R$ <?= number_format($stats['investimento_mes_anterior'] ?? 0, 2, ',', '.') ?></div>
                    <div class="text-xs d-flex align-items-center mt-1">
                        <?php if ($diferencaInvestimento >= 0): ?>
                            <span class="text-danger me-1"><i class="fas fa-arrow-up"></i> <?= number_format($diferencaInvestimento, 1, ',') ?>%</span>
                        <?php else: ?>
                            <span class="text-success me-1"><i class="fas fa-arrow-down"></i> <?= number_format(abs($diferencaInvestimento), 1, ',') ?>%</span>
                        <?php endif; ?>
                        <small class="ms-1">vs Mês Anterior</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Estoque Utilizado (<?= date('m/Y') ?>)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">R$ <?= number_format($stats['utilizado_mes_atual'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-helmet-safety fa-2x text-warning"></i></div>
                    </div>
                    <hr class="my-2">
                    <div class="text-xs text-muted">Mês Anterior (<?= date('m/Y', strtotime('first day of last month')) ?>): R$ <?= number_format($stats['utilizado_mes_anterior'] ?? 0, 2, ',', '.') ?></div>
                    <div class="text-xs d-flex align-items-center mt-1">
                        <?php if ($diferencaUtilizado >= 0): ?>
                            <span class="text-danger me-1"><i class="fas fa-arrow-up"></i> <?= number_format($diferencaUtilizado, 1, ',') ?>%</span>
                        <?php else: ?>
                            <span class="text-success me-1"><i class="fas fa-arrow-down"></i> <?= number_format(abs($diferencaUtilizado), 1, ',') ?>%</span>
                        <?php endif; ?>
                        <small class="ms-1">vs Mês Anterior</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-danger">EPIs com Estoque Baixo</h6>
                </div>
                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                    <?php if (empty($epis_estoque_baixo)): ?>
                        <p class="text-center text-muted small mt-3">Nenhum item com estoque baixo.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($epis_estoque_baixo as $epi): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <span class="small"><?= htmlspecialchars($epi['nome_epi']) ?></span>
                                    <span class="badge bg-danger rounded-pill"><?= $epi['total_estoque'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha 3: Top 5 -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Top 5 EPIs Mais Utilizados</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($top_epis)): ?>
                        <p class="text-center">Não há dados de utilização de EPIs.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($top_epis as $epi): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($epi['nome_epi']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $epi['total_entregue'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Top 5 Colaboradores (Custo de Utilização)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($top_colaboradores)): ?>
                        <p class="text-center">Não há dados de custo de utilização.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($top_colaboradores as $colaborador): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($colaborador['nome_completo']) ?>
                                    <span class="badge bg-success rounded-pill">R$ <?= number_format($colaborador['custo_total'], 2, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha 4: Próximas Entregas Programadas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Próximas Entregas Programadas (Próximos 5 dias úteis)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (empty($entregas_por_colaborador)): ?>
                            <p class="text-center text-muted">Nenhuma entrega programada para os próximos dias.</p>
                        <?php else: ?>
                            <?php foreach ($entregas_por_colaborador as $colaborador_id => $dados_colaborador): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="user-avatar me-3">
                                                    <?php if ($dados_colaborador['detalhes']['foto']): ?>
                                                        <img src="<?= $_ENV['APP_URL'] . '/' . $dados_colaborador['detalhes']['foto'] ?>" alt="Avatar" class="avatar-img">
                                                    <?php else: ?>
                                                        <?= strtoupper(substr($dados_colaborador['detalhes']['nome'], 0, 1)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($dados_colaborador['detalhes']['nome']) ?></h6>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($dados_colaborador['entregas'] as $entrega): ?>
                                                    <?php
                                                    $duracao_total = $entrega['quantidade_total_recebida'] * $entrega['frequencia_troca'];
                                                    $unidade = $entrega['unidade_frequencia'] == 'dias' ? 'day' : 'month';
                                                    $data_esgotamento = date_create($entrega['ultima_entrega'])->modify("+{$duracao_total} {$unidade}")->format('d/m/Y');
                                                    ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <div>
                                                            <p class="mb-0 small"><?= htmlspecialchars($entrega['nome_epi']) ?></p>
                                                            <small class="text-muted">Stock esgota em: <strong class="text-danger"><?= $data_esgotamento ?></strong></small>
                                                        </div>
                                                        <a href="<?= $_ENV['APP_URL'] ?>/entregas/create?colaborador_id=<?= $colaborador_id ?>&epi_id=<?= $entrega['id_epi'] ?>" class="btn btn-sm btn-success">Entregar</a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>