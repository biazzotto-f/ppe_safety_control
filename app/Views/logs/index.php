<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Histórico de Atividades do Sistema</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-modern">
                <thead>
                    <tr>
                        <th>Data e Hora</th>
                        <th>Usuário</th>
                        <th>Ação Realizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhum registo no log.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['timestamp'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <!-- Assumindo que a foto do usuário pode ser buscada ou um placeholder é usado -->
                                            <?= strtoupper(substr($log['nome_usuario'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($log['nome_usuario']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($log['acao']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>