<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $_ENV['APP_URL'] ?>/usuarios/store" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Perfil do Usuário</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="image-preview-box mb-3 mx-auto">
                            <img src="https://placehold.co/180x180/e9ecef/6c757d?text=Foto" class="image-preview"
                                alt="Preview" id="photoPreview">
                        </div>
                        <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" accept="image/*">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Nome de Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Associar Empresas</h6>
                </div>
                <div class="card-body">
                    <p>Selecione as empresas que este usuário poderá gerir:</p>
                    <div class="row">
                        <?php foreach ($empresas as $empresa): ?>
                            <div class="col-md-6 mb-3">
                                <label class="company-card-selector">
                                    <img src="<?= $empresa['foto_empresa'] ? $_ENV['APP_URL'] . '/' . $empresa['foto_empresa'] : 'https://placehold.co/80x80/e9ecef/6c757d?text=Logo' ?>"
                                        alt="Logo" class="card-logo">
                                    <div class="card-company-name"><?= htmlspecialchars($empresa['nome_empresa']) ?></div>
                                    <input class="form-check-input" type="checkbox" name="empresas[]"
                                        value="<?= $empresa['id'] ?>">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-end">
            <a href="<?= $_ENV['APP_URL'] ?>/usuarios" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const photoInput = document.getElementById('foto_perfil');
        const photoPreview = document.getElementById('photoPreview');

        photoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    photoPreview.src = event.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.querySelectorAll('.company-card-selector input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                this.closest('.company-card-selector').classList.toggle('selected', this.checked);
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>