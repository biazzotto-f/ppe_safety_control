<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <i class="fas fa-sitemap fa-3x text-info mb-3"></i>
                <h5 class="card-title">Gerir Setores</h5>
                <p class="card-text">Crie e administre os setores da sua empresa.</p>
                <a href="<?= $_ENV['APP_URL'] ?>/setores" class="btn btn-info text-white">Aceder</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                <h5 class="card-title">Gerir Funções</h5>
                <p class="card-text">Defina as funções e associe os EPIs padrão.</p>
                <a href="<?= $_ENV['APP_URL'] ?>/funcoes" class="btn btn-success">Aceder</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <i class="fas fa-tags fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Gerir Categorias de EPIs</h5>
                <p class="card-text">Organize os seus equipamentos por tipo e categoria.</p>
                <a href="<?= $_ENV['APP_URL'] ?>/categorias" class="btn btn-warning text-dark">Aceder</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>