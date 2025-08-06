<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $appUrl ?>/colaboradores/store" method="POST" enctype="multipart/form-data">
    <div class="row">
        <!-- Coluna Esquerda: Foto de Perfil -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Foto de Perfil</h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-preview-box mb-3 mx-auto">
                        <img src="https://placehold.co/180x180/e9ecef/6c757d?text=Foto" class="image-preview"
                            alt="Preview" id="photoPreview">
                    </div>
                    <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" accept="image/*">
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Dados do Colaborador -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dados Profissionais e Pessoais</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary mb-3">Dados Profissionais</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="matricula" class="form-label">Matrícula</label>
                            <input type="text" class="form-control" id="matricula" name="matricula" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_unidade" class="form-label">Unidade de Operação</label>
                            <select class="form-select" id="id_unidade" name="id_unidade">
                                <option value="">Endereço Principal da Empresa</option>
                                <?php foreach ($unidades as $unidade): ?>
                                    <option value="<?= $unidade['id'] ?>"><?= htmlspecialchars($unidade['nome_unidade']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_setor" class="form-label">Setor</label>
                            <select class="form-select" id="id_setor" name="id_setor" required>
                                <option value="" selected disabled>Selecione um setor</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id'] ?>"><?= htmlspecialchars($setor['nome_setor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_funcao" class="form-label">Função</label>
                            <select class="form-select" id="id_funcao" name="id_funcao" required disabled>
                                <option value="" selected disabled>Selecione um setor primeiro</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="font-weight-bold text-primary mb-3">Dados Pessoais e de Acesso</h6>
                    <div class="mb-3">
                        <label for="nome_completo" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome_completo" name="nome_completo" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="text-end">
                <a href="<?= $appUrl ?>/colaboradores" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const photoInput = document.getElementById('foto_perfil');
        const photoPreview = document.getElementById('photoPreview');
        const funcoes = <?= json_encode($funcoes) ?>;
        const setorSelect = document.getElementById('id_setor');
        const funcaoSelect = document.getElementById('id_funcao');

        photoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    photoPreview.src = event.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        setorSelect.addEventListener('change', function () {
            const setorId = this.value;
            funcaoSelect.innerHTML = '<option value="" selected disabled>Selecione uma função</option>';
            funcaoSelect.disabled = true;

            if (setorId) {
                const funcoesFiltradas = funcoes.filter(funcao => funcao.id_setor == setorId);
                if (funcoesFiltradas.length > 0) {
                    funcoesFiltradas.forEach(funcao => {
                        const option = document.createElement('option');
                        option.value = funcao.id;
                        option.textContent = funcao.nome_funcao;
                        funcaoSelect.appendChild(option);
                    });
                    funcaoSelect.disabled = false;
                } else {
                    funcaoSelect.innerHTML = '<option value="" selected disabled>Nenhuma função para este setor</option>';
                }
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>