<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <div>
        <a href="<?= $_ENV['APP_URL'] ?>/configuracoes" class="btn btn-secondary">Voltar</a>
        <a href="<?= $_ENV['APP_URL'] ?>/setores/create" class="btn btn-primary">
            <i class="fas fa-plus fa-sm"></i> Novo Setor
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-modern">
        <thead>
            <tr>
                <th>Nome do Setor</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($setores)): ?>
                <tr>
                    <td colspan="3" class="text-center">Nenhum setor cadastrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($setores as $setor): ?>
                    <tr class="<?= $setor['status'] == 'inativo' ? 'tr-inactive' : '' ?>">
                        <td><?= htmlspecialchars($setor['nome_setor']) ?></td>
                        <td>
                            <span class="badge bg-<?= $setor['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($setor['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="<?= $_ENV['APP_URL'] ?>/setores/toggle-status" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $setor['id'] ?>">
                                <?php if ($setor['status'] == 'ativo'): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Desativar"><i
                                            class="fas fa-toggle-on text-success"></i></button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ativar"><i
                                            class="fas fa-toggle-off text-danger"></i></button>
                                <?php endif; ?>
                            </form>
                            <a href="<?= $_ENV['APP_URL'] ?>/setores/edit?id=<?= $setor['id'] ?>" class="btn btn-sm btn-warning"
                                title="Editar"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal" data-delete-url="<?= $_ENV['APP_URL'] ?>/setores/delete"
                                data-delete-id="<?= $setor['id'] ?>">
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
                Tem certeza de que deseja excluir este setor? Funções associadas também poderão ser afetadas.
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