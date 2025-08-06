<?php
ob_start();
// Define a variável $appUrl no início para que esteja disponível em todo o ficheiro.
$appUrl = $_ENV['APP_URL'];
?>

<h1 class="h3 mb-4 text-gray-800"><?= $pageTitle ?></h1>

<!-- Formulário de Filtro -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtrar por Período e Funcionário</h6>
    </div>
    <div class="card-body">
        <form action="<?= $appUrl ?>/relatorios/colaboradores" method="POST">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="id_colaborador" class="form-label">Funcionário</label>
                    <select class="form-select" id="id_colaborador" name="id_colaborador" required>
                        <option value="" selected disabled>Selecione um funcionário</option>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?= $colaborador['id'] ?>" <?= (isset($colaborador_id_selecionado) && $colaborador_id_selecionado == $colaborador['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($colaborador['nome_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                        value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="col-md-3">
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
        <h6 class="m-0 font-weight-bold text-primary">Entregas do Funcionário Selecionado</h6>
        <div>
            <?php if (isset($colaborador_id_selecionado) && !empty($dados)): ?>
                <button id="printPdfBtn" class="btn btn-sm btn-info"><i class="fas fa-print me-2"></i> Imprimir</button>
                <a href="<?= $appUrl ?>/relatorios/exportar_colaboradores?id_colaborador=<?= $colaborador_id_selecionado ?>&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>"
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
                    <?php if (!isset($colaborador_id_selecionado)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Por favor, selecione um funcionário para ver os resultados.
                            </td>
                        </tr>
                    <?php elseif (empty($dados)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum resultado encontrado para o funcionário e período
                                selecionado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados as $dado): ?>
                            <tr>
                                <td><?= htmlspecialchars($dado['colaborador_nome']) ?></td>
                                <td><?= htmlspecialchars($dado['matricula']) ?></td>
                                <td><?= htmlspecialchars($dado['nome_epi']) ?></td>
                                <td><?= htmlspecialchars($dado['ca']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($dado['data_entrega'])) ?></td>
                                <td>
                                    <?php if ($dado['assinatura_digital']): ?>
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

<?php if (isset($colaborador_id_selecionado) && !empty($dados)): ?>
    <script>
        document.getElementById('printPdfBtn')?.addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');

            // --- DADOS PARA O PDF (obtidos do PHP de forma segura) ---
            const appUrl = <?= json_encode($appUrl) ?>;
            const logoUrl = <?= json_encode(isset($empresa_ativa) && $empresa_ativa['foto_empresa'] ? $appUrl . '/' . $empresa_ativa['foto_empresa'] : null) ?>;
            const colaboradorFotoUrl = <?= json_encode(isset($colaborador_selecionado) && $colaborador_selecionado['foto_perfil'] ? $appUrl . '/' . $colaborador_selecionado['foto_perfil'] : null) ?>;
            const nomeEmpresa = <?= json_encode($empresa_ativa['nome_empresa']) ?>;
            const cnpjEmpresa = <?= json_encode($empresa_ativa['cnpj']) ?>;
            const responsavel = <?= json_encode($_SESSION['nome_usuario']) ?>;
            const nomeColaborador = <?= json_encode($colaborador_selecionado['nome_completo']) ?>;
            const matriculaColaborador = <?= json_encode($colaborador_selecionado['matricula']) ?>;
            const setorColaborador = <?= json_encode($colaborador_selecionado['nome_setor']) ?>;
            const funcaoColaborador = <?= json_encode($colaborador_selecionado['nome_funcao']) ?>;

            function getDataPorExtenso() {
                const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
                const data = new Date();
                const dia = data.getDate();
                const mes = meses[data.getMonth()];
                const ano = data.getFullYear();
                return `São Caetano do Sul, ${dia} de ${mes} de ${ano}`;
            }
            const dataExtenso = getDataPorExtenso();

            // --- FUNÇÃO PARA GERAR O CONTEÚDO DO PDF ---
            function generatePdfContent(doc, logoImage, colaboradorImage) {
                doc.setFont('Helvetica', 'normal');
                doc.setTextColor(0, 0, 0);

                const pageHeight = doc.internal.pageSize.getHeight();
                const pageWidth = doc.internal.pageSize.getWidth();

                // CABEÇALHO
                const headerY = 40;
                const headerHeight = 80;
                const headerCenterY = headerY + headerHeight / 2;

                if (logoImage) {
                    doc.addImage(logoImage, 'PNG', 40, 40, 80, 80);
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

                // TABELA DE DADOS DO COLABORADOR
                doc.autoTable({
                    startY: 130,
                    theme: 'plain',
                    styles: { fontSize: 10, font: 'Helvetica', valign: 'middle' },
                    body: [
                        [
                            { content: '', styles: { cellWidth: 80, minCellHeight: 65 } },
                            { content: `Colaborador: ${nomeColaborador}\nMatrícula: ${matriculaColaborador}` },
                            { content: `Setor: ${setorColaborador}\nFunção: ${funcaoColaborador}` },
                        ]
                    ],
                    didDrawCell: function (data) {
                        if (data.section === 'body' && data.column.index === 0) {
                            if (colaboradorImage) {
                                doc.addImage(colaboradorImage, 'PNG', data.cell.x + 10, data.cell.y + 5, 60, 60);
                            }
                        }
                    }
                });

                // TABELA PRINCIPAL DE ENTREGAS
                doc.autoTable({
                    html: '#reportTable',
                    startY: doc.lastAutoTable.finalY + 20,
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

                // TEXTO DE DECLARAÇÃO E ASSINATURA
                let finalY = doc.lastAutoTable.finalY;
                const remainingSpace = pageHeight - finalY - 40;
                const signatureBlockHeight = 90;

                if (remainingSpace < signatureBlockHeight) {
                    doc.addPage();
                    finalY = 40;
                } else {
                    finalY = pageHeight - 110;
                }

                const declaracao = `Eu, ${nomeColaborador}, declaro que recebi os equipamentos de proteção individual listados acima, em perfeitas condições, e que fui treinado quanto ao seu uso, guarda e conservação.`;
                const splitDeclaracao = doc.splitTextToSize(declaracao, pageWidth - 80);
                doc.setFontSize(9);
                doc.text(splitDeclaracao, pageWidth / 2, finalY, { align: 'center' });

                finalY += 50;
                doc.line(150, finalY, pageWidth - 150, finalY);
                doc.setFontSize(10);
                doc.text(nomeColaborador, pageWidth / 2, finalY + 15, { align: 'center' });

                finalY += 25;
                doc.text(dataExtenso, pageWidth / 2, finalY, { align: 'center' });

                doc.save('relatorio-funcionario.pdf');
            }

            // --- LÓGICA DE CARREGAMENTO DAS IMAGENS (MAIS ROBUSTA) ---
            function loadImage(url) {
                return new Promise((resolve) => {
                    if (!url) {
                        resolve(null); // Resolve com null se não houver URL
                        return;
                    }
                    const img = new Image();
                    img.crossOrigin = "Anonymous";
                    img.onload = () => resolve(img);
                    img.onerror = () => {
                        console.error(`Falha ao carregar imagem: ${url}`);
                        resolve(null); // Resolve com null em caso de erro
                    };
                    img.src = url;
                });
            }

            Promise.all([
                loadImage(logoUrl),
                loadImage(colaboradorFotoUrl)
            ]).then(([logoImage, colaboradorImage]) => {
                generatePdfContent(doc, logoImage, colaboradorImage);
            });
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>