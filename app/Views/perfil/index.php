<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4 profile-pic-card">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Foto de Perfil</h6>
            </div>
            <div class="card-body">
                <div class="profile-pic-wrapper">
                    <img src="<?= $usuario['foto_perfil'] ? $_ENV['APP_URL'] . '/' . $usuario['foto_perfil'] : 'https://placehold.co/150x150/e9ecef/6c757d?text=Foto' ?>"
                        alt="Foto de Perfil" class="profile-pic" id="profilePicPreview">
                </div>
                <form action="<?= $_ENV['APP_URL'] ?>/perfil/update" method="POST" enctype="multipart/form-data"
                    id="formProfilePic">
                    <label for="foto_perfil" class="btn btn-primary">
                        <i class="fas fa-upload fa-sm"></i> Alterar Foto
                    </label>
                    <input type="file" id="foto_perfil" name="foto_perfil" class="d-none"
                        onchange="document.getElementById('formProfilePic').submit();">
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Informações do Perfil</h6>
            </div>
            <div class="card-body">
                <form action="<?= $_ENV['APP_URL'] ?>/perfil/update" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome"
                            value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Nome de Usuário</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?= htmlspecialchars($usuario['username']) ?>" required>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3">Alterar Senha</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nova_senha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                        </div>
                    </div>
                    <small class="form-text text-muted">Deixe os campos de senha em branco se não desejar
                        alterá-la.</small>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>