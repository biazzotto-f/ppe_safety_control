<?php
ob_start();
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<!-- Formulário de Filtro -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtrar por Período</h6>
    </div>
    <div class="card-body">
        <form action="<?= $appUrl ?>/relatorios/entregas" method="POST">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                        value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-5">
                    <label for="data_fim" class="form-label">Data de Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim"
                        value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Resultados -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Resultados</h6>
        <div>
            <?php if (!empty($entregas)): ?>
                <button id="printPdfBtn" class="btn btn-sm btn-info"><i class="fas fa-print me-2"></i> Imprimir</button>
                <a href="<?= $appUrl ?>/relatorios/exportar_entregas?data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>"
                    class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-2"></i> Exportar para Excel
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-info disabled"><i class="fas fa-print me-2"></i> Imprimir</button>
                <button class="btn btn-sm btn-success disabled"><i class="fas fa-file-excel me-2"></i> Exportar para
                    Excel</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-modern" id="reportTable">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Matrícula</th>
                        <th>EPI</th>
                        <th>C.A.</th>
                        <th>Data da Entrega</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entregas)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum resultado encontrado para o período selecionado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entregas as $entrega): ?>
                            <tr>
                                <td><?= htmlspecialchars($entrega['colaborador_nome']) ?></td>
                                <td><?= htmlspecialchars($entrega['matricula']) ?></td>
                                <td><?= htmlspecialchars($entrega['nome_epi']) ?></td>
                                <td><?= htmlspecialchars($entrega['ca']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($entrega['data_entrega'])) ?></td>
                                <td>
                                    <?php if ($entrega['assinatura_digital']): ?>
                                        <span class="badge bg-success">Assinado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>
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

<?php if (!empty($entregas)): ?>
    <script>
        document.getElementById('printPdfBtn')?.addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');

            // --- DADOS PARA O PDF ---
            const appUrl = <?= json_encode($appUrl) ?>;
            const logoUrl = <?= json_encode($empresa_ativa['foto_empresa'] ? $appUrl . '/' . $empresa_ativa['foto_empresa'] : '') ?>;
            const nomeEmpresa = <?= json_encode($empresa_ativa['nome_empresa']) ?>;
            const cnpjEmpresa = <?= json_encode($empresa_ativa['cnpj']) ?>;
            const responsavel = <?= json_encode($_SESSION['nome_usuario']) ?>;
            const dataInicio = <?= json_encode(date('d/m/Y', strtotime($data_inicio))) ?>;
            const dataFim = <?= json_encode(date('d/m/Y', strtotime($data_fim))) ?>;

            // --- FUNÇÃO PARA GERAR O CONTEÚDO DO PDF ---
            function generatePdfContent(doc, logoImage) {
                doc.setFont('Helvetica', 'normal');
                doc.setTextColor(0, 0, 0);

                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();

                // CABEÇALHO
                const headerY = 40;
                const headerHeight = 80;
                const headerCenterY = headerY + headerHeight / 2;

                if (logoImage) {
                    const maxWidth = 80;
                    const maxHeight = 80;
                    let imgWidth = logoImage.width;
                    let imgHeight = logoImage.height;
                    if (imgWidth > maxWidth || imgHeight > maxHeight) {
                        const ratio = Math.min(maxWidth / imgWidth, maxHeight / imgHeight);
                        imgWidth *= ratio;
                        imgHeight *= ratio;
                    }
                    const x = 40 + (maxWidth - imgWidth) / 2;
                    const y = headerY + (maxHeight - imgHeight) / 2;
                    doc.addImage(logoImage, 'PNG', x, y, imgWidth, imgHeight);
                }

                doc.setFont('Helvetica', 'bold');
                doc.setFontSize(13);
                doc.text(nomeEmpresa, pageWidth / 2, headerCenterY - 5, { align: 'center' });
                doc.setFont('Helvetica', 'normal');
                doc.setFontSize(11);
                doc.text(`CNPJ: ${cnpjEmpresa}`, pageWidth / 2, headerCenterY + 10, { align: 'center' });

                doc.setFontSize(10);
                doc.text(`Data de Impressão: ${new Date().toLocaleDateString('pt-BR')}`, pageWidth - 40, headerCenterY - 5, { align: 'right' });
                doc.text(`Responsável: ${responsavel}`, pageWidth - 40, headerCenterY + 10, { align: 'right' });

                // PERÍODO SELECIONADO
                const startYTable = headerY + headerHeight + 20;
                doc.setFont('Helvetica', 'normal');
                doc.setFontSize(10);
                const periodoText = `Período Selecionado: ${dataInicio} a ${dataFim}`;
                doc.text(periodoText, pageWidth / 2, startYTable, { align: 'center' });

                // TABELA PRINCIPAL DE ENTREGAS
                doc.autoTable({
                    html: '#reportTable',
                    startY: startYTable + 15, // Espaçamento após a linha de período
                    theme: 'grid',
                    headStyles: {
                        fillColor: [217, 217, 217],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold',
                        fontSize: 10, // Tamanho do título da tabela
                        font: 'Helvetica'
                    },
                    bodyStyles: {
                        fontSize: 9, // Tamanho do conteúdo da tabela
                        textColor: [0, 0, 0],
                        font: 'Helvetica',
                        fontStyle: 'normal'
                    },
                    didDrawPage: function (data) {
                        // RODAPÉ
                        doc.setFontSize(8);
                        doc.text("PPE's Safety Control VER: 1.0", 40, pageHeight - 20);
                        doc.text(`Página ${data.pageNumber}`, pageWidth - 40, pageHeight - 20, { align: 'right' });
                    }
                });

                doc.save('relatorio-periodo.pdf');
            }

            // --- LÓGICA DE CARREGAMENTO DA IMAGEM ---
            if (logoUrl) {
                const img = new Image();
                img.crossOrigin = "Anonymous";
                img.src = logoUrl;
                img.onload = function () {
                    generatePdfContent(doc, img);
                };
                img.onerror = function () {
                    console.error("Não foi possível carregar o logo da empresa.");
                    generatePdfContent(doc, null);
                };
            } else {
                generatePdfContent(doc, null);
            }
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>