<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <div>
        <a href="<?= $_ENV['APP_URL'] ?>/configuracoes" class="btn btn-secondary">Voltar</a>
        <a href="<?= $_ENV['APP_URL'] ?>/categorias/create" class="btn btn-primary">
            <i class="fas fa-plus fa-sm"></i> Nova Categoria
        </a>
    </div>
</div>


<div class="table-responsive">
    <table class="table table-hover table-modern">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Classificação</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
                <tr>
                    <td colspan="3" class="text-center">Nenhuma categoria cadastrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= htmlspecialchars($categoria['nome_categoria']) ?></td>
                        <td>(<?= $categoria['tipo'] ?>) <?= htmlspecialchars($categoria['nome_classificacao']) ?></td>
                        <td class="text-end">
                            <a href="<?= $_ENV['APP_URL'] ?>/categorias/edit?id=<?= $categoria['id'] ?>"
                                class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal" data-delete-url="<?= $_ENV['APP_URL'] ?>/categorias/delete"
                                data-delete-id="<?= $categoria['id'] ?>">
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
                Tem certeza de que deseja excluir esta categoria? Esta ação não pode ser desfeita.
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