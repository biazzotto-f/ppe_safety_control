<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
    <div>
        <?php if (!empty($dados)): ?>
            <button id="printPdfBtn" class="btn btn-info"><i class="fas fa-print me-2"></i> Imprimir Relatório</button>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-modern" id="reportTable">
                <thead>
                    <tr>
                        <th>Função</th>
                        <th>Setor</th>
                        <th>Riscos</th>
                        <th>Classificações Padrão</th>
                        <th>Categorias Padrão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $riscoMap = [
                        'Fisico' => ['letra' => 'F', 'cor' => 'f'],
                        'Quimico' => ['letra' => 'Q', 'cor' => 'q'],
                        'Biologico' => ['letra' => 'B', 'cor' => 'b'],
                        'Acidentes' => ['letra' => 'A', 'cor' => 'a'],
                        'Ergonomico' => ['letra' => 'E', 'cor' => 'e'],
                    ];
                    ?>
                    <?php if (empty($dados)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Não foram encontradas funções ativas com EPIs associados
                                para gerar o relatório.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados as $dado): ?>
                            <tr>
                                <td><?= htmlspecialchars($dado['nome_funcao']) ?></td>
                                <td><?= htmlspecialchars($dado['nome_setor']) ?></td>
                                <td>
                                    <?php
                                    if (!empty($dado['riscos'])) {
                                        $riscos = explode(',', $dado['riscos']);
                                        foreach ($riscos as $risco) {
                                            if (isset($riscoMap[$risco])) {
                                                echo '<span class="badge-risco badge-risco-' . $riscoMap[$risco]['cor'] . '" title="' . $risco . '">' . $riscoMap[$risco]['letra'] . '</span>';
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?= str_replace(',', '<br>', htmlspecialchars($dado['classificacoes'] ?? 'Nenhuma')) ?></td>
                                <td><?= str_replace(',', '<br>', htmlspecialchars($dado['categorias'] ?? 'Nenhuma')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bibliotecas para Geração de PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<?php if (!empty($dados)): ?>
    <script>
        document.getElementById('printPdfBtn')?.addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');

            const logoUrl = <?= json_encode(isset($empresa_ativa) && $empresa_ativa['foto_empresa'] ? $appUrl . '/' . $empresa_ativa['foto_empresa'] : null) ?>;
            const nomeEmpresa = <?= json_encode($empresa_ativa['nome_empresa']) ?>;
            const cnpjEmpresa = <?= json_encode($empresa_ativa['cnpj']) ?>;
            const responsavel = <?= json_encode($_SESSION['nome_usuario']) ?>;

            function generatePdfContent(doc, logoImage) {
                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();

                // CABEÇALHO
                if (logoImage) {
                    doc.addImage(logoImage, 'PNG', 40, 40, 80, 80);
                }
                doc.setFont('Helvetica', 'bold');
                doc.setFontSize(13);
                doc.text(nomeEmpresa, pageWidth / 2, 60, { align: 'center' });
                doc.setFont('Helvetica', 'normal');
                doc.setFontSize(11);
                doc.text(`CNPJ: ${cnpjEmpresa}`, pageWidth / 2, 75, { align: 'center' });
                doc.setFontSize(10);
                doc.text(`Data de Impressão: ${new Date().toLocaleDateString('pt-BR')}`, pageWidth - 40, 60, { align: 'right' });
                doc.text(`Responsável: ${responsavel}`, pageWidth - 40, 75, { align: 'right' });

                // TÍTULO DO RELATÓRIO
                doc.setFont('Helvetica', 'bold');
                doc.setFontSize(14);
                doc.text("Relatório de EPIs por Função", pageWidth / 2, 140, { align: 'center' });

                // TABELA
                doc.autoTable({
                    html: '#reportTable',
                    startY: 160,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [217, 217, 217],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold',
                        fontSize: 10,
                        font: 'Helvetica'
                    },
                    bodyStyles: {
                        fontSize: 9,
                        textColor: [0, 0, 0],
                        font: 'Helvetica'
                    },
                    didDrawPage: function (data) {
                        // RODAPÉ
                        doc.setFontSize(8);
                        doc.text("PPE's Safety Control VER: 1.0", 40, pageHeight - 20);
                        doc.text(`Página ${data.pageNumber}`, pageWidth - 40, pageHeight - 20, { align: 'right' });
                    }
                });

                doc.save('relatorio-epis-por-funcao.pdf');
            }

            function loadImage(url) {
                return new Promise((resolve) => {
                    if (!url) {
                        resolve(null);
                        return;
                    }
                    const img = new Image();
                    img.crossOrigin = "Anonymous";
                    img.onload = () => resolve(img);
                    img.onerror = () => {
                        console.error(`Falha ao carregar imagem: ${url}`);
                        resolve(null);
                    };
                    img.src = url;
                });
            }

            loadImage(logoUrl).then(logoImage => {
                generatePdfContent(doc, logoImage);
            });
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>