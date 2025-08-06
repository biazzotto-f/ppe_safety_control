<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Dados da Categoria</h6>
    </div>
    <div class="card-body">
        <form action="<?= $_ENV['APP_URL'] ?>/categorias/store" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome_categoria" class="form-label">Nome da Categoria</label>
                    <input type="text" class="form-control" id="nome_categoria" name="nome_categoria" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="id_classificacao" class="form-label">Classificação</label>
                    <select class="form-select" id="id_classificacao" name="id_classificacao" required>
                        <option value="" selected disabled>Selecione uma classificação</option>
                        <?php foreach ($classificacoes as $classificacao): ?>
                            <option value="<?= $classificacao['id'] ?>">(<?= $classificacao['tipo'] ?>)
                                <?= htmlspecialchars($classificacao['nome_classificacao']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="<?= $_ENV['APP_URL'] ?>/categorias" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>