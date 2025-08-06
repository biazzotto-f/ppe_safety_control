<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <a href="<?= $_ENV['APP_URL'] ?>/entregas/create" class="btn btn-primary">
        <i class="fas fa-plus fa-sm"></i> Registrar Entrega
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Histórico de Entregas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-modern">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Colaborador</th>
                        <th>EPI</th>
                        <th>Qtd.</th>
                        <th>Data da Entrega</th>
                        <th>Próxima Troca</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entregas)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Nenhuma entrega registrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entregas as $entrega): ?>
                            <tr>
                                <td>
                                    <div class="user-avatar">
                                        <?php if ($entrega['foto_perfil']): ?>
                                            <img src="<?= $_ENV['APP_URL'] . '/' . $entrega['foto_perfil'] ?>" alt="Avatar"
                                                class="avatar-img">
                                        <?php else: ?>
                                            <?= strtoupper(substr($entrega['colaborador_nome'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($entrega['colaborador_nome']) ?></td>
                                <td><?= htmlspecialchars($entrega['nome_epi']) ?></td>
                                <td><?= $entrega['quantidade_entregue'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($entrega['data_entrega'])) ?></td>
                                <td><?= $entrega['data_proxima_troca'] ? date('d/m/Y', strtotime($entrega['data_proxima_troca'])) : 'N/A' ?>
                                </td>
                                <td>
                                    <?php if ($entrega['assinatura_digital']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Assinado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal"
                                        data-delete-url="<?= $_ENV['APP_URL'] ?>/entregas/delete"
                                        data-delete-id="<?= $entrega['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
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
                Tem certeza de que deseja excluir este item? Esta ação não pode ser desfeita e o estoque será devolvido.
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
        const confirmDeleteModalElement = document.getElementById('confirmDeleteModal');
        if (confirmDeleteModalElement) {
            const confirmDeleteModal = bootstrap.Modal.getOrCreateInstance(confirmDeleteModalElement);

            confirmDeleteModalElement.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const deleteUrl = button.getAttribute('data-delete-url');
                const deleteId = button.getAttribute('data-delete-id');

                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = deleteUrl;

                const deleteIdInput = document.getElementById('deleteId');
                deleteIdInput.value = deleteId;
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>