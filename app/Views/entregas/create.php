<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $appUrl ?>/entregas/store" method="POST">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registrar Entrega de EPIs</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="id_colaborador" class="form-label">1. Selecione o Colaborador</label>
                <select class="form-select" id="id_colaborador" name="id_colaborador" required>
                    <option value="" selected disabled>Selecione um colaborador</option>
                    <?php foreach ($colaboradores as $colaborador): ?>
                        <option value="<?= $colaborador['id'] ?>" data-funcao-id="<?= $colaborador['id_funcao'] ?>">
                            <?= htmlspecialchars($colaborador['nome_completo']) ?> (Mat: <?= htmlspecialchars($colaborador['matricula']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr class="my-4">

            <div class="mb-3">
                <label for="epi-select" class="form-label">2. Adicione os EPIs Padrão da Função</label>
                <div class="input-group">
                    <select class="form-select" id="epi-select" disabled>
                        <option selected>Selecione um colaborador primeiro...</option>
                    </select>
                    <input type="number" class="form-control" id="epi-quantidade" value="1" min="1" placeholder="Qtd." style="max-width: 100px;">
                    <button class="btn btn-outline-primary" type="button" id="add-epi-btn">Adicionar</button>
                </div>
            </div>

            <div id="epi-list-container" class="mt-3">
                <!-- EPIs selecionados serão adicionados aqui como cards -->
            </div>

            <input type="hidden" name="entrega_items" id="entrega_items_hidden">
        </div>
    </div>

    <div class="mt-3 text-end">
        <a href="<?= $appUrl ?>/entregas" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar Entrega</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colaboradorSelect = document.getElementById('id_colaborador');
        const epiSelect = document.getElementById('epi-select');
        const quantidadeInput = document.getElementById('epi-quantidade');
        const addBtn = document.getElementById('add-epi-btn');
        const epiListContainer = document.getElementById('epi-list-container');
        const hiddenInput = document.getElementById('entrega_items_hidden');
        let entregaItems = [];

        colaboradorSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const funcaoId = selectedOption.dataset.funcaoId;

            epiSelect.innerHTML = '<option>A carregar EPIs...</option>';
            epiSelect.disabled = true;

            if (!funcaoId) {
                epiSelect.innerHTML = '<option>Colaborador sem função definida.</option>';
                return;
            }

            fetch(`<?= $appUrl ?>/entregas/ajax_get_epis?funcao_id=${funcaoId}`)
                .then(response => response.json())
                .then(data => {
                    epiSelect.innerHTML = '';
                    if (data.length > 0) {
                        epiSelect.innerHTML = '<option selected disabled>Selecione um EPI para adicionar...</option>';
                        data.forEach(epi => {
                            const option = document.createElement('option');
                            option.value = epi.id;
                            option.dataset.nome = epi.nome_epi;
                            option.dataset.foto = epi.foto_epi ? `<?= $appUrl ?>/${epi.foto_epi}` : 'https://placehold.co/60x60/e9ecef/6c757d?text=EPI';
                            option.textContent = `${epi.nome_epi} (Estoque: ${epi.estoque_total})`;
                            epiSelect.appendChild(option);
                        });
                        epiSelect.disabled = false;
                    } else {
                        epiSelect.innerHTML = '<option selected disabled>Nenhum EPI padrão encontrado para esta função.</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar EPIs:', error);
                    epiSelect.innerHTML = '<option selected disabled>Erro ao carregar EPIs.</option>';
                });
        });

        function updateHiddenInput() {
            hiddenInput.value = JSON.stringify(entregaItems);
        }

        function addEpiToList(id, nome, quantidade, fotoUrl) {
            const existingItem = entregaItems.find(item => item.epi_id === id);
            if (existingItem) {
                alert('Este EPI já foi adicionado. Remova-o para alterar a quantidade.');
                return;
            }

            entregaItems.push({
                epi_id: id,
                nome: nome,
                quantidade: quantidade
            });

            const card = document.createElement('div');
            card.className = 'epi-delivery-item card mb-2';
            card.dataset.id = id;

            card.innerHTML = `
            <div class="card-body p-2">
                <div class="d-flex align-items-center">
                    <div class="epi-delivery-photo me-3">
                        <img src="${fotoUrl}" alt="${nome}">
                    </div>
                    <div class="flex-grow-1">
                        <strong class="epi-delivery-name">${nome}</strong>
                    </div>
                    <span class="badge bg-primary rounded-pill me-3">${quantidade}</span>
                    <button type="button" class="btn-close"></button>
                </div>
            </div>
        `;

            card.querySelector('.btn-close').addEventListener('click', function() {
                const idToRemove = this.closest('.epi-delivery-item').dataset.id;
                entregaItems = entregaItems.filter(item => item.epi_id !== idToRemove);
                this.closest('.epi-delivery-item').remove();
                updateHiddenInput();
            });

            epiListContainer.appendChild(card);
            updateHiddenInput();
        }

        addBtn.addEventListener('click', function() {
            const selectedOption = epiSelect.options[epiSelect.selectedIndex];
            const epiId = selectedOption.value;
            const epiNome = selectedOption.dataset.nome;
            const epiFoto = selectedOption.dataset.foto;
            const quantidade = parseInt(quantidadeInput.value, 10);

            if (epiId && epiNome && quantidade > 0) {
                addEpiToList(epiId, epiNome, quantidade, epiFoto);
                epiSelect.selectedIndex = 0;
                quantidadeInput.value = 1;
            } else {
                alert('Selecione um EPI e defina uma quantidade válida.');
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>