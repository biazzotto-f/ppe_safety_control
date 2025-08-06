<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <a href="<?= $_ENV['APP_URL'] ?>/fornecedores/create" class="btn btn-primary">
        <i class="fas fa-plus fa-sm"></i> Novo Fornecedor
    </a>
</div>

<div class="table-responsive">
    <table class="table table-hover table-modern">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Fornecedor</th>
                <th>Contato</th>
                <th>Telefone</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($fornecedores)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum fornecedor cadastrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($fornecedores as $fornecedor): ?>
                    <tr class="<?= $fornecedor['status'] == 'inativo' ? 'tr-inactive' : '' ?>">
                        <td>
                            <div class="table-logo">
                                <?php if ($fornecedor['foto_fornecedor']): ?>
                                    <img src="<?= $_ENV['APP_URL'] . '/' . $fornecedor['foto_fornecedor'] ?>" alt="Logo"
                                        class="logo-img">
                                <?php else: ?>
                                    <i class="fas fa-truck"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($fornecedor['nome_fornecedor']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($fornecedor['cnpj']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($fornecedor['contato']) ?></td>
                        <td><?= htmlspecialchars($fornecedor['telefone']) ?></td>
                        <td>
                            <span class="badge bg-<?= $fornecedor['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($fornecedor['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="<?= $_ENV['APP_URL'] ?>/fornecedores/toggle-status" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $fornecedor['id'] ?>">
                                <?php if ($fornecedor['status'] == 'ativo'): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Desativar"><i
                                            class="fas fa-toggle-on text-success"></i></button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ativar"><i
                                            class="fas fa-toggle-off text-danger"></i></button>
                                <?php endif; ?>
                            </form>
                            <a href="<?= $_ENV['APP_URL'] ?>/fornecedores/edit?id=<?= $fornecedor['id'] ?>"
                                class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal"
                                data-delete-url="<?= $_ENV['APP_URL'] ?>/fornecedores/delete"
                                data-delete-id="<?= $fornecedor['id'] ?>">
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
                Tem certeza de que deseja excluir este fornecedor?
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