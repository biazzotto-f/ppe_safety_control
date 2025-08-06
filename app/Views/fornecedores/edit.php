<?php ob_start(); ?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<form action="<?= $_ENV['APP_URL'] ?>/fornecedores/update" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $fornecedor['id'] ?>">
    <input type="hidden" name="foto_atual" value="<?= $fornecedor['foto_fornecedor'] ?>">
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Logo do Fornecedor</h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-preview-box mb-2">
                        <img src="<?= $fornecedor['foto_fornecedor'] ? $_ENV['APP_URL'] . '/' . $fornecedor['foto_fornecedor'] : 'https://placehold.co/180x180/e9ecef/6c757d?text=Logo' ?>"
                            class="image-preview" alt="Preview" id="logoPreview">
                    </div>
                    <input type="file" class="form-control" name="foto_fornecedor" id="foto_fornecedor"
                        accept="image/*">
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dados do Fornecedor</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nome_fornecedor" class="form-label">Nome do Fornecedor</label>
                            <input type="text" class="form-control" id="nome_fornecedor" name="nome_fornecedor"
                                value="<?= htmlspecialchars($fornecedor['nome_fornecedor']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj"
                                value="<?= htmlspecialchars($fornecedor['cnpj']) ?>" maxlength="18">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                value="<?= htmlspecialchars($fornecedor['telefone']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($fornecedor['email']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contato" class="form-label">Nome do Contato</label>
                            <input type="text" class="form-control" id="contato" name="contato"
                                value="<?= htmlspecialchars($fornecedor['contato']) ?>">
                        </div>
                    </div>
                    <hr class="my-4">
                    <h6 class="font-weight-bold text-primary mb-3">Endereço</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep"
                                value="<?= htmlspecialchars($fornecedor['cep']) ?>" maxlength="9">
                        </div>
                        <div class="col-md-7 mb-3">
                            <label for="logradouro" class="form-label">Logradouro</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro"
                                value="<?= htmlspecialchars($fornecedor['logradouro']) ?>" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero"
                                value="<?= htmlspecialchars($fornecedor['numero']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro"
                                value="<?= htmlspecialchars($fornecedor['bairro']) ?>" readonly>
                        </div>
                        <div class="col-md-7 mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento"
                                value="<?= htmlspecialchars($fornecedor['complemento']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade"
                                value="<?= htmlspecialchars($fornecedor['cidade']) ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado"
                                value="<?= htmlspecialchars($fornecedor['estado']) ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-2 text-end">
        <a href="<?= $_ENV['APP_URL'] ?>/fornecedores" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cnpjInput = document.getElementById('cnpj');
        const cepInput = document.getElementById('cep');
        const telefoneInput = document.getElementById('telefone');
        const logoInput = document.getElementById('foto_fornecedor');
        const logoPreview = document.getElementById('logoPreview');

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

        // Máscara de CEP e Busca de Endereço
        cepInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        cepInput.addEventListener('blur', function (e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                            document.getElementById('numero').focus();
                        } else {
                            alert('CEP não encontrado.');
                        }
                    }).catch(error => console.error('Erro ao buscar CEP:', error));
            }
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
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>