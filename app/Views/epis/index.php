<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <a href="<?= $appUrl ?>/epis/create" class="btn btn-primary">
        <i class="fas fa-plus fa-sm"></i> Novo EPI
    </a>
</div>


<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-modern">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nome do EPI</th>
                    <th>Fornecedores</th>
                    <th>Estoque Total</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($epis)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum EPI cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($epis as $epi):
                        $is_expired = $epi['validade_ca'] && (strtotime($epi['validade_ca']) < time());
                        $row_class = $epi['status'] == 'inativo' ? 'tr-inactive' : ($is_expired ? 'tr-expired' : '');
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td>
                                <div class="table-logo">
                                    <?php if ($epi['foto_epi']): ?>
                                        <img src="<?= $appUrl . '/' . $epi['foto_epi'] ?>" alt="Foto EPI" class="logo-img">
                                    <?php else: ?>
                                        <i class="fas fa-hard-hat"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($epi['nome_epi']) ?></div>
                                <small class="text-muted">C.A: <?= htmlspecialchars($epi['ca']) ?></small>
                                <?php if ($is_expired): ?>
                                    <span class="badge bg-danger ms-2">C.A. Vencido</span>
                                <?php endif; ?>
                            </td>
                            <td><?= str_replace(',', '<br>', htmlspecialchars($epi['fornecedores'] ?? 'Nenhum')) ?></td>
                            <td><?= $epi['estoque_total'] ?></td>
                            <td>
                                <span class="badge bg-<?= $epi['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($epi['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="<?= $appUrl ?>/epis/toggle-status" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $epi['id'] ?>">
                                    <?php if ($epi['status'] == 'ativo'): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Desativar"><i
                                                class="fas fa-toggle-on text-success"></i></button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ativar"><i
                                                class="fas fa-toggle-off text-danger"></i></button>
                                    <?php endif; ?>
                                </form>
                                <a href="<?= $appUrl ?>/epis/edit?id=<?= $epi['id'] ?>" class="btn btn-sm btn-warning"
                                    title="Editar"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal" data-delete-url="<?= $appUrl ?>/epis/delete"
                                    data-delete-id="<?= $epi['id'] ?>">
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
                    Tem certeza de que deseja excluir este item? Esta ação não pode ser desfeita.
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