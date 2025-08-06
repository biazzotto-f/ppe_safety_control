<?php 
ob_start(); 
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $appUrl ?>/epis/update" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $epi['id'] ?>">
    <input type="hidden" name="foto_atual" value="<?= $epi['foto_epi'] ?>">

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Foto do EPI</h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-preview-box mb-2">
                        <img src="<?= $epi['foto_epi'] ? $appUrl . '/' . $epi['foto_epi'] : 'https://placehold.co/180x180/e9ecef/6c757d?text=Foto+EPI' ?>" class="image-preview" alt="Preview" id="photoPreview">
                    </div>
                    <input type="file" class="form-control" id="foto_epi" name="foto_epi" accept="image/*">
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dados do EPI</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome_epi" class="form-label">Nome do EPI</label>
                        <input type="text" class="form-control" id="nome_epi" name="nome_epi" value="<?= htmlspecialchars($epi['nome_epi']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_classificacao" class="form-label">Classificação</label>
                            <select class="form-select" id="id_classificacao" required>
                                <option value="">Selecione uma classificação</option>
                                <?php foreach($classificacoes as $classificacao): ?>
                                    <option value="<?= $classificacao['id'] ?>" <?= (isset($epi['id_classificacao']) && $classificacao['id'] == $epi['id_classificacao']) ? 'selected' : '' ?>>
                                        (<?= $classificacao['tipo'] ?>) <?= htmlspecialchars($classificacao['nome_classificacao']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_categoria" class="form-label">Categoria</label>
                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                <option value="">Selecione uma classificação primeiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ca" class="form-label">Número do C.A.</label>
                            <input type="text" class="form-control" id="ca" name="ca" value="<?= htmlspecialchars($epi['ca']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validade_ca" class="form-label">Validade do C.A. (Opcional)</label>
                            <input type="date" class="form-control" id="validade_ca" name="validade_ca" value="<?= $epi['validade_ca'] ?>">
                        </div>
                    </div>
                    <hr class="my-4">
                    <h6 class="font-weight-bold text-primary mb-3">Programação de Troca</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="frequencia_troca" class="form-label">Trocar a cada</label>
                            <input type="number" class="form-control" id="frequencia_troca" name="frequencia_troca" value="<?= htmlspecialchars($epi['frequencia_troca'] ?? '') ?>" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unidade_frequencia" class="form-label">Unidade</label>
                            <select class="form-select" name="unidade_frequencia" id="unidade_frequencia">
                                <option value="dias" <?= ($epi['unidade_frequencia'] ?? 'dias') == 'dias' ? 'selected' : '' ?>>Dias</option>
                                <option value="meses" <?= ($epi['unidade_frequencia'] ?? '') == 'meses' ? 'selected' : '' ?>>Meses</option>
                            </select>
                        </div>
                    </div>
                    <small class="form-text text-muted">Deixe em branco se este EPI não necessitar de troca programada.</small>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label for="fornecedor-select" class="form-label">Fornecedores (Opcional)</label>
                        <div class="input-group">
                            <select class="form-select" id="fornecedor-select">
                                <option selected>Selecione um fornecedor para adicionar...</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= $fornecedor['id'] ?>" data-nome="<?= htmlspecialchars($fornecedor['nome_fornecedor']) ?>">
                                        <?= htmlspecialchars($fornecedor['nome_fornecedor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-outline-primary" type="button" id="add-fornecedor-btn">Adicionar</button>
                        </div>
                    </div>
                    <ul class="list-group" id="fornecedores-list">
                        <!-- Fornecedores selecionados serão adicionados aqui -->
                    </ul>
                    <input type="hidden" name="fornecedor_ids" id="fornecedor_ids_hidden">
                </div>
            </div>
        </div>
    </div>
    <div class="mt-2 text-end">
        <a href="<?= $appUrl ?>/epis" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('foto_epi');
    const photoPreview = document.getElementById('photoPreview');
    const classificacaoSelect = document.getElementById('id_classificacao');
    const categoriaSelect = document.getElementById('id_categoria');
    const todasCategorias = <?= json_encode($categorias) ?>;
    const idCategoriaInicial = <?= $epi['id_categoria'] ?>;

    photoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                photoPreview.src = event.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    function popularCategorias() {
        const classificacaoId = classificacaoSelect.value;
        categoriaSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
        categoriaSelect.disabled = true;

        if (classificacaoId) {
            const categoriasFiltradas = todasCategorias.filter(cat => cat.id_classificacao == classificacaoId);
            
            if (categoriasFiltradas.length > 0) {
                categoriasFiltradas.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.nome_categoria;
                    if (cat.id == idCategoriaInicial) {
                        option.selected = true;
                    }
                    categoriaSelect.appendChild(option);
                });
                categoriaSelect.disabled = false;
            } else {
                categoriaSelect.innerHTML = '<option value="">Nenhuma categoria encontrada</option>';
            }
        } else {
            categoriaSelect.innerHTML = '<option value="">Selecione uma classificação primeiro</option>';
        }
    }

    classificacaoSelect.addEventListener('change', popularCategorias);
    popularCategorias();

    // Lógica para adicionar/remover fornecedores
    const fornecedorSelect = document.getElementById('fornecedor-select');
    const addFornecedorBtn = document.getElementById('add-fornecedor-btn');
    const fornecedoresList = document.getElementById('fornecedores-list');
    const hiddenFornecedorInput = document.getElementById('fornecedor_ids_hidden');
    let selectedFornecedorIds = [];

    function updateHiddenFornecedorInput() {
        hiddenFornecedorInput.value = selectedFornecedorIds.join(',');
    }

    function addFornecedorToList(id, nome) {
        id = String(id);
        if (selectedFornecedorIds.includes(id)) {
            alert('Este fornecedor já foi adicionado.');
            return;
        }
        selectedFornecedorIds.push(id);

        const listItem = document.createElement('li');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
        listItem.dataset.id = id;
        listItem.textContent = nome;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-close';
        removeBtn.addEventListener('click', function() {
            const idToRemove = this.parentElement.dataset.id;
            selectedFornecedorIds = selectedFornecedorIds.filter(fornecedorId => fornecedorId !== idToRemove);
            this.parentElement.remove();
            updateHiddenFornecedorInput();
        });

        listItem.appendChild(removeBtn);
        fornecedoresList.appendChild(listItem);
        updateHiddenFornecedorInput();
    }

    addFornecedorBtn.addEventListener('click', function() {
        const selectedOption = fornecedorSelect.options[fornecedorSelect.selectedIndex];
        const fornecedorId = selectedOption.value;
        const fornecedorNome = selectedOption.dataset.nome;

        if (fornecedorId && fornecedorNome) {
            addFornecedorToList(fornecedorId, fornecedorNome);
            fornecedorSelect.selectedIndex = 0;
        }
    });

    // Pré-popula a lista com os fornecedores já associados
    const fornecedoresAssociadosIds = <?= json_encode($fornecedores_associados_ids) ?>;
    const todosFornecedores = <?= json_encode($fornecedores) ?>;
    fornecedoresAssociadosIds.forEach(fornecedorId => {
        const fornecedor = todosFornecedores.find(f => f.id == fornecedorId);
        if (fornecedor) {
            addFornecedorToList(String(fornecedor.id), fornecedor.nome_fornecedor);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
