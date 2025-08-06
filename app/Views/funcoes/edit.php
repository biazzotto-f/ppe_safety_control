<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $_ENV['APP_URL'] ?>/funcoes/update" method="POST">
    <input type="hidden" name="id" value="<?= $funcao['id'] ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dados da Função</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome_funcao" class="form-label">Nome da Função</label>
                            <input type="text" class="form-control" id="nome_funcao" name="nome_funcao"
                                value="<?= htmlspecialchars($funcao['nome_funcao']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_setor" class="form-label">Setor</label>
                            <select class="form-select" id="id_setor" name="id_setor" required>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id'] ?>" <?= $setor['id'] == $funcao['id_setor'] ? 'selected' : '' ?>><?= htmlspecialchars($setor['nome_setor']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Associar Categorias de EPIs Padrão</h6>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <select class="form-select" id="classificacao-filtro">
                            <option value="">Filtrar por Classificação...</option>
                            <?php foreach ($classificacoes as $classificacao): ?>
                                <option value="<?= $classificacao['id'] ?>">
                                    (<?= $classificacao['tipo'] ?>)
                                    <?= htmlspecialchars($classificacao['nome_classificacao']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-select" id="categoria-select" disabled>
                            <option selected>Selecione uma classificação</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" id="add-categoria-btn">Adicionar</button>
                    </div>
                    <ul class="list-group" id="categorias-list">
                        <!-- Categorias selecionadas serão adicionadas aqui -->
                    </ul>
                    <input type="hidden" name="categoria_ids" id="categoria_ids_hidden">
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tipos de Risco</h6>
                </div>
                <div class="card-body">
                    <?php $riscos_selecionados = explode(',', $funcao['riscos'] ?? ''); ?>
                    <p>Selecione os riscos associados a esta função:</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="riscos[]" value="Fisico" id="riscoFisico"
                            <?= in_array('Fisico', $riscos_selecionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="riscoFisico">Físico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="riscos[]" value="Quimico"
                            id="riscoQuimico" <?= in_array('Quimico', $riscos_selecionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="riscoQuimico">Químico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="riscos[]" value="Biologico"
                            id="riscoBiologico" <?= in_array('Biologico', $riscos_selecionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="riscoBiologico">Biológico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="riscos[]" value="Ergonomico"
                            id="riscoErgonomico" <?= in_array('Ergonomico', $riscos_selecionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="riscoErgonomico">Ergonômico</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="riscos[]" value="Acidentes"
                            id="riscoAcidentes" <?= in_array('Acidentes', $riscos_selecionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="riscoAcidentes">Acidentes</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-end">
            <a href="<?= $_ENV['APP_URL'] ?>/funcoes" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const classificacaoSelect = document.getElementById('classificacao-filtro');
        const categoriaSelect = document.getElementById('categoria-select');
        const addBtn = document.getElementById('add-categoria-btn');
        const categoriaList = document.getElementById('categorias-list');
        const hiddenInput = document.getElementById('categoria_ids_hidden');
        const todasCategorias = <?= json_encode($categorias) ?>;
        let selectedCategoriaIds = [];

        function updateHiddenInput() {
            hiddenInput.value = selectedCategoriaIds.join(',');
        }

        function addCategoriaToList(id, nome) {
            id = String(id);
            if (selectedCategoriaIds.includes(id)) {
                alert('Esta categoria já foi adicionada.');
                return;
            }
            selectedCategoriaIds.push(id);

            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.dataset.id = id;
            listItem.textContent = nome;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-close';
            removeBtn.addEventListener('click', function () {
                const idToRemove = this.parentElement.dataset.id;
                selectedCategoriaIds = selectedCategoriaIds.filter(catId => catId !== idToRemove);
                this.parentElement.remove();
                updateHiddenInput();
            });

            listItem.appendChild(removeBtn);
            categoriaList.appendChild(listItem);
            updateHiddenInput();
        }

        classificacaoSelect.addEventListener('change', function () {
            const classificacaoId = this.value;
            categoriaSelect.innerHTML = '<option value="" selected disabled>Selecione uma categoria</option>';
            categoriaSelect.disabled = true;

            if (classificacaoId) {
                const categoriasFiltradas = todasCategorias.filter(cat => cat.id_classificacao == classificacaoId);
                if (categoriasFiltradas.length > 0) {
                    categoriasFiltradas.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nome_categoria;
                        categoriaSelect.appendChild(option);
                    });
                    categoriaSelect.disabled = false;
                } else {
                    categoriaSelect.innerHTML = '<option value="" selected disabled>Nenhuma categoria encontrada</option>';
                }
            }
        });

        addBtn.addEventListener('click', function () {
            const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
            const categoriaId = selectedOption.value;
            const categoriaNome = selectedOption.textContent;

            if (categoriaId) {
                addCategoriaToList(categoriaId, categoriaNome);
                categoriaSelect.selectedIndex = 0;
            }
        });

        const categoriasAssociadas = <?= json_encode($categorias_associadas) ?>;
        categoriasAssociadas.forEach(cat => {
            addCategoriaToList(String(cat.id), cat.nome_categoria);
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>