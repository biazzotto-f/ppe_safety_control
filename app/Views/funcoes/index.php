<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <div>
        <a href="<?= $_ENV['APP_URL'] ?>/configuracoes" class="btn btn-secondary">Voltar</a>
        <a href="<?= $_ENV['APP_URL'] ?>/funcoes/create" class="btn btn-primary">
            <i class="fas fa-plus fa-sm"></i> Nova Função
        </a>
    </div>
</div>

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover table-modern">
            <thead>
                <tr>
                    <th>Função</th>
                    <th>Setor</th>
                    <th>Riscos</th>
                    <th>Categorias Padrão</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $riscoMap = [
                    'Fisico' => ['letra' => 'F', 'cor' => 'f'],
                    'Quimico' => ['letra' => 'Q', 'cor' => 'q'],
                    'Biologico' => ['letra' => 'B', 'cor' => 'b'],
                    'Acidentes' => ['letra' => 'A', 'cor' => 'a'],
                    'Ergonomico' => ['letra' => 'E', 'cor' => 'e'],
                ];
                ?>
                <?php if (empty($funcoes)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhuma função cadastrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($funcoes as $funcao): ?>
                        <tr class="<?= $funcao['status'] == 'inativo' ? 'tr-inactive' : '' ?>">
                            <td><?= htmlspecialchars($funcao['nome_funcao']) ?></td>
                            <td><?= htmlspecialchars($funcao['nome_setor']) ?></td>
                            <td>
                                <?php
                                if (!empty($funcao['riscos'])) {
                                    $riscos = explode(',', $funcao['riscos']);
                                    foreach ($riscos as $risco) {
                                        if (isset($riscoMap[$risco])) {
                                            echo '<span class="badge-risco badge-risco-' . $riscoMap[$risco]['cor'] . '" title="' . $risco . '">' . $riscoMap[$risco]['letra'] . '</span>';
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td><?= str_replace(',', '<br>', htmlspecialchars($funcao['categorias_padrao'] ?? 'Nenhuma')) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $funcao['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($funcao['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="<?= $_ENV['APP_URL'] ?>/funcoes/toggle-status" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $funcao['id'] ?>">
                                    <?php if ($funcao['status'] == 'ativo'): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Desativar"><i
                                                class="fas fa-toggle-on text-success"></i></button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Ativar"><i
                                                class="fas fa-toggle-off text-danger"></i></button>
                                    <?php endif; ?>
                                </form>
                                <a href="<?= $_ENV['APP_URL'] ?>/funcoes/edit?id=<?= $funcao['id'] ?>"
                                    class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-danger" title="Excluir" data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal"
                                    data-delete-url="<?= $_ENV['APP_URL'] ?>/funcoes/delete"
                                    data-delete-id="<?= $funcao['id'] ?>">
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
                    Tem certeza de que deseja excluir esta função?
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