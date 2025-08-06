<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Dados do Setor</h6>
    </div>
    <div class="card-body">
        <form action="<?= $_ENV['APP_URL'] ?>/setores/update" method="POST">
            <input type="hidden" name="id" value="<?= $setor['id'] ?>">
            <div class="mb-3">
                <label for="nome_setor" class="form-label">Nome do Setor</label>
                <input type="text" class="form-control" id="nome_setor" name="nome_setor"
                    value="<?= htmlspecialchars($setor['nome_setor']) ?>" required>
            </div>

            <div class="mt-3 text-end">
                <a href="<?= $_ENV['APP_URL'] ?>/setores" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>