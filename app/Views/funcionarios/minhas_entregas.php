<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="table-responsive">
    <table class="table table-hover table-modern">
        <thead>
            <tr>
                <th>ID</th>
                <th>EPI Recebido</th>
                <th>Quantidade</th>
                <th>Data da Entrega</th>
                <th>Assinatura</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entregas)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhuma entrega encontrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($entregas as $entrega): ?>
                    <tr>
                        <td><?= $entrega['id'] ?></td>
                        <td><?= htmlspecialchars($entrega['nome_epi']) ?></td>
                        <td><?= $entrega['quantidade_entregue'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($entrega['data_entrega'])) ?></td>
                        <td class="text-center">
                            <?php if ($entrega['assinatura_digital']): ?>
                                <img src="<?= $_ENV['APP_URL'] . '/' . $entrega['assinatura_digital'] ?>" alt="Assinatura"
                                    style="height: 40px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                            <?php else: ?>
                                <a href="<?= $_ENV['APP_URL'] ?>/assinar_entrega?id=<?= $entrega['id'] ?>"
                                    class="btn btn-sm btn-success">
                                    <i class="fas fa-signature"></i> Assinar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>