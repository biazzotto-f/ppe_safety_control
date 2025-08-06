<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Dados da Compra - <?= htmlspecialchars($lote['nome_epi']) ?></h6>
    </div>
    <div class="card-body">
        <form action="<?= $appUrl ?>/compras/update" method="POST">
            <input type="hidden" name="id" value="<?= $lote['id'] ?>">
            <div class="alert alert-info">
                <strong>Atenção:</strong> A quantidade inicial deste lote (<?= $lote['quantidade_inicial'] ?>) e o EPI
                não podem ser alterados para manter a integridade do estoque FIFO.
            </div>
            <div class="mb-3">
                <label for="id_fornecedor" class="form-label">Fornecedor (Opcional)</label>
                <select class="form-select" id="id_fornecedor" name="id_fornecedor">
                    <option value="">Selecione um fornecedor</option>
                    <?php foreach ($fornecedores as $fornecedor): ?>
                        <option value="<?= $fornecedor['id'] ?>" <?= ($lote['id_fornecedor'] == $fornecedor['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fornecedor['nome_fornecedor']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="custo_unitario" class="form-label">Custo Unitário (R$)</label>
                    <input type="number" class="form-control" id="custo_unitario" name="custo_unitario"
                        value="<?= htmlspecialchars($lote['custo_unitario']) ?>" step="0.01" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="custo_total" class="form-label">Custo Total (R$)</label>
                    <input type="number" class="form-control" id="custo_total" name="custo_total"
                        value="<?= htmlspecialchars($lote['custo_total']) ?>" step="0.01" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="data_compra" class="form-label">Data da Compra</label>
                    <input type="date" class="form-control" id="data_compra" name="data_compra"
                        value="<?= htmlspecialchars($lote['data_compra']) ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="data_vencimento" class="form-label">Data de Vencimento (Opcional)</label>
                    <input type="date" class="form-control" id="data_vencimento" name="data_vencimento"
                        value="<?= htmlspecialchars($lote['data_vencimento']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nota_fiscal" class="form-label">Nota Fiscal (Opcional)</label>
                    <input type="text" class="form-control" id="nota_fiscal" name="nota_fiscal"
                        value="<?= htmlspecialchars($lote['nota_fiscal']) ?>">
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="<?= $appUrl ?>/compras" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantidadeInput = <?= $lote['quantidade_inicial'] ?>;
        const unitarioInput = document.getElementById('custo_unitario');
        const totalInput = document.getElementById('custo_total');

        function calcularTotal() {
            const unitario = parseFloat(unitarioInput.value) || 0;
            if (quantidadeInput > 0 && unitario > 0) {
                totalInput.value = (quantidadeInput * unitario).toFixed(2);
            }
        }

        function calcularUnitario() {
            const total = parseFloat(totalInput.value) || 0;
            if (quantidadeInput > 0 && total > 0) {
                unitarioInput.value = (total / quantidadeInput).toFixed(2);
            }
        }

        unitarioInput.addEventListener('input', calcularTotal);
        totalInput.addEventListener('input', calcularUnitario);
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>