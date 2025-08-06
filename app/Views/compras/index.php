<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <a href="<?= $_ENV['APP_URL'] ?>/compras/create" class="btn btn-primary">
        <i class="fas fa-plus fa-sm"></i> Registar Compra
    </a>
</div>

<div class="table-responsive">
    <table class="table table-hover table-modern">
        <thead>
            <tr>
                <th>Foto</th>
                <th>EPI</th>
                <th>Fornecedor</th>
                <th>Qtd. Atual</th>
                <th>Vencimento</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lotes)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma compra registada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($lotes as $lote):
                    $is_expiring = $lote['data_vencimento'] && (strtotime($lote['data_vencimento']) < strtotime('+30 days'));
                    $row_class = $lote['quantidade_atual'] == 0 ? 'tr-inactive' : ($is_expiring ? 'tr-expired' : '');
                    ?>
                    <tr class="<?= $row_class ?>">
                        <td>
                            <div class="table-logo">
                                <?php if ($lote['foto_epi']): ?>
                                    <img src="<?= $_ENV['APP_URL'] . '/' . $lote['foto_epi'] ?>" alt="Foto EPI" class="logo-img">
                                <?php else: ?>
                                    <i class="fas fa-hard-hat"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($lote['nome_epi']) ?></div>
                            <small class="text-muted">C.A: <?= htmlspecialchars($lote['ca']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($lote['nome_fornecedor'] ?? 'Não especificado') ?></td>
                        <td><?= $lote['quantidade_atual'] ?> / <?= $lote['quantidade_inicial'] ?></td>
                        <td>
                            <?= $lote['data_vencimento'] ? date('d/m/Y', strtotime($lote['data_vencimento'])) : 'N/A' ?>
                            <?php if ($is_expiring && $lote['quantidade_atual'] > 0): ?>
                                <span class="badge bg-danger ms-2">Expira em breve</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= $_ENV['APP_URL'] ?>/compras/edit?id=<?= $lote['id'] ?>" class="btn btn-sm btn-warning"
                                title="Editar"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal" data-delete-url="<?= $_ENV['APP_URL'] ?>/compras/delete"
                                data-delete-id="<?= $lote['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem a certeza de que deseja excluir este item? Esta ação só é permitida se nenhuma unidade deste lote
                tiver sido entregue.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" action="">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        if (confirmDeleteModal) {
            confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var deleteUrl = button.getAttribute('data-delete-url');
                var deleteId = button.getAttribute('data-delete-id');

                var deleteForm = document.getElementById('deleteForm');
                deleteForm.action = deleteUrl;

                var deleteIdInput = document.getElementById('deleteId');
                deleteIdInput.value = deleteId;
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>