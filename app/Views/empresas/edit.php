<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $appUrl ?>/empresas/update" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $empresa['id'] ?>">
    <input type="hidden" name="foto_atual" value="<?= $empresa['foto_empresa'] ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dados da Empresa</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome_empresa" class="form-label">Nome da Empresa</label>
                        <input type="text" class="form-control" id="nome_empresa" name="nome_empresa"
                            value="<?= htmlspecialchars($empresa['nome_empresa']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj"
                                value="<?= htmlspecialchars($empresa['cnpj']) ?>" required maxlength="18">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="contato" class="form-label">Nome do Contato</label>
                        <input type="text" class="form-control" id="contato" name="contato"
                            value="<?= htmlspecialchars($empresa['contato'] ?? '') ?>">
                    </div>
                    <hr class="my-4">
                    <h6 class="font-weight-bold text-primary mb-3">Endereço Principal</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep"
                                value="<?= htmlspecialchars($empresa['cep'] ?? '') ?>" maxlength="9">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="logradouro" class="form-label">Logradouro</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro"
                                value="<?= htmlspecialchars($empresa['logradouro'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero"
                                value="<?= htmlspecialchars($empresa['numero'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro"
                                value="<?= htmlspecialchars($empresa['bairro'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento"
                                value="<?= htmlspecialchars($empresa['complemento'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade"
                                value="<?= htmlspecialchars($empresa['cidade'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado"
                                value="<?= htmlspecialchars($empresa['estado'] ?? '') ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Logo da Empresa</h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-preview-box mb-2">
                        <img src="<?= $empresa['foto_empresa'] ? $appUrl . '/' . $empresa['foto_empresa'] : 'https://placehold.co/180x180/e9ecef/6c757d?text=Logo' ?>"
                            class="image-preview" alt="Preview" id="logoPreview">
                    </div>
                    <input type="file" class="form-control" name="foto_empresa" id="foto_empresa" accept="image/*">
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Unidades de Operação</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="add-unidade-btn">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="unidades-list">
                        <!-- Unidades serão adicionadas aqui via JS -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-2 text-end">
        <a href="<?= $appUrl ?>/empresas" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Atualizar Empresa</button>
    </div>
</form>

<!-- Modal para Adicionar/Editar Unidade -->
<div class="modal fade" id="unidadeModal" tabindex="-1" aria-labelledby="unidadeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unidadeModalLabel">Adicionar Unidade de Operação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="unidade-index">
                <div class="mb-3">
                    <label for="unidade-nome" class="form-label">Nome da Unidade</label>
                    <input type="text" class="form-control" id="unidade-nome">
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="unidade-cep" class="form-label">CEP</label>
                        <input type="text" class="form-control" id="unidade-cep" maxlength="9">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3"><label for="unidade-logradouro">Logradouro</label><input type="text"
                            class="form-control" id="unidade-logradouro" readonly></div>
                    <div class="col-md-4 mb-3"><label for="unidade-numero">Número</label><input type="text"
                            class="form-control" id="unidade-numero"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="unidade-bairro">Bairro</label><input type="text"
                            class="form-control" id="unidade-bairro" readonly></div>
                    <div class="col-md-6 mb-3"><label for="unidade-complemento">Complemento</label><input type="text"
                            class="form-control" id="unidade-complemento"></div>
                </div>
                <div class="row">
                    <div class="col-md-8 mb-3"><label for="unidade-cidade">Cidade</label><input type="text"
                            class="form-control" id="unidade-cidade" readonly></div>
                    <div class="col-md-4 mb-3"><label for="unidade-estado">Estado</label><input type="text"
                            class="form-control" id="unidade-estado" readonly></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="save-unidade-btn">Salvar</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cnpjInput = document.getElementById('cnpj');
        const cepInput = document.getElementById('cep');
        const telefoneInput = document.getElementById('telefone');
        const logoInput = document.getElementById('foto_empresa');
        const logoPreview = document.getElementById('logoPreview');
        const unidadesList = document.getElementById('unidades-list');
        const addUnidadeBtn = document.getElementById('add-unidade-btn');
        const saveUnidadeBtn = document.getElementById('save-unidade-btn');
        const unidadeModalEl = document.getElementById('unidadeModal');
        const unidadeModal = new bootstrap.Modal(unidadeModalEl);
        let unidades = <?= json_encode($unidades ?? []) ?>;

        // Máscara de CNPJ
        cnpjInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        });

        // Máscara de Telefone
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            if (value.length > 10) {
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value.substring(0, 15);
        });

        // Preview do Logo
        logoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    logoPreview.src = event.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Função genérica para busca de CEP
        function setupCepSearch(cepField, logradouroField, bairroField, cidadeField, estadoField, numeroField) {
            cepField.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });

            cepField.addEventListener('blur', function (e) {
                const cep = e.target.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                logradouroField.value = data.logradouro;
                                bairroField.value = data.bairro;
                                cidadeField.value = data.localidade;
                                estadoField.value = data.uf;
                                numeroField.focus();
                            } else {
                                alert('CEP não encontrado.');
                            }
                        }).catch(error => console.error('Erro ao buscar CEP:', error));
                }
            });
        }

        setupCepSearch(cepInput, document.getElementById('logradouro'), document.getElementById('bairro'), document.getElementById('cidade'), document.getElementById('estado'), document.getElementById('numero'));
        setupCepSearch(document.getElementById('unidade-cep'), document.getElementById('unidade-logradouro'), document.getElementById('unidade-bairro'), document.getElementById('unidade-cidade'), document.getElementById('unidade-estado'), document.getElementById('unidade-numero'));

        // Lógica para gestão de Unidades
        function renderUnidades() {
            unidadesList.innerHTML = '';
            if (unidades.length === 0) {
                unidadesList.innerHTML = '<li class="list-group-item text-center">Nenhuma unidade cadastrada.</li>';
                return;
            }
            unidades.forEach((unidade, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                <div>
                    <strong>${unidade.nome_unidade}</strong><br>
                    <small>${unidade.cep} - ${unidade.logradouro}, ${unidade.numero}</small>
                </div>
                <div>
                    <input type="hidden" name="unidades[${index}][nome]" value="${unidade.nome_unidade}">
                    <input type="hidden" name="unidades[${index}][cep]" value="${unidade.cep}">
                    <input type="hidden" name="unidades[${index}][logradouro]" value="${unidade.logradouro}">
                    <input type="hidden" name="unidades[${index}][numero]" value="${unidade.numero}">
                    <input type="hidden" name="unidades[${index}][complemento]" value="${unidade.complemento}">
                    <input type="hidden" name="unidades[${index}][bairro]" value="${unidade.bairro}">
                    <input type="hidden" name="unidades[${index}][cidade]" value="${unidade.cidade}">
                    <input type="hidden" name="unidades[${index}][estado]" value="${unidade.estado}">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-unidade" data-index="${index}"><i class="fas fa-trash"></i></button>
                </div>
            `;
                unidadesList.appendChild(li);
            });
        }

        addUnidadeBtn.addEventListener('click', function () {
            document.getElementById('unidadeModalLabel').textContent = 'Adicionar Unidade de Operação';
            unidadeModal.show();
        });

        saveUnidadeBtn.addEventListener('click', function () {
            const nome = document.getElementById('unidade-nome').value;
            if (nome) {
                unidades.push({
                    nome_unidade: nome,
                    cep: document.getElementById('unidade-cep').value,
                    logradouro: document.getElementById('unidade-logradouro').value,
                    numero: document.getElementById('unidade-numero').value,
                    complemento: document.getElementById('unidade-complemento').value,
                    bairro: document.getElementById('unidade-bairro').value,
                    cidade: document.getElementById('unidade-cidade').value,
                    estado: document.getElementById('unidade-estado').value
                });
                renderUnidades();
                unidadeModal.hide();
            }
        });

        unidadeModalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('unidade-nome').value = '';
            document.getElementById('unidade-cep').value = '';
            document.getElementById('unidade-logradouro').value = '';
            document.getElementById('unidade-numero').value = '';
            document.getElementById('unidade-complemento').value = '';
            document.getElementById('unidade-bairro').value = '';
            document.getElementById('unidade-cidade').value = '';
            document.getElementById('unidade-estado').value = '';
        });

        unidadesList.addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-unidade')) {
                const indexToRemove = e.target.closest('.btn-remove-unidade').dataset.index;
                unidades.splice(indexToRemove, 1);
                renderUnidades();
            }
        });

        renderUnidades();
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>