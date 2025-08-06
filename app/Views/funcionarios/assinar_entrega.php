<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .signature-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .signature-header {
            padding: 1rem;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-size: 0.9rem;
            text-align: center;
            flex-shrink: 0;
            border-bottom: 1px solid #dee2e6;
        }
        .signature-pad-wrapper {
            flex-grow: 1;
            position: relative;
            background-color: #fff;
            min-height: 0;
        }
        #signature-pad {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
        .signature-footer {
            padding: 1rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <div class="signature-container">
        <div class="signature-header">
            Eu, <strong><?= htmlspecialchars($entrega['nome_completo']) ?></strong>, confirmo o recebimento do(s) equipamento(s) de proteção individual <strong>(<?= htmlspecialchars($entrega['nome_epi']) ?>)</strong> na quantidade de <strong><?= $entrega['quantidade_entregue'] ?></strong> unidade(s), referente(s) à entrega <strong>#<?= $entrega['id'] ?></strong>, e declaro ter recebido treinamento para o uso correto do mesmo.
        </div>

        <div class="signature-pad-wrapper">
            <canvas id="signature-pad"></canvas>
        </div>

        <div class="signature-footer">
            <button id="clear" class="btn btn-warning"><i class="fas fa-eraser me-2"></i> Limpar</button>
            <a href="<?= $_ENV['APP_URL'] ?>/minhas_entregas" class="btn btn-secondary">Cancelar</a>
            <button id="save" class="btn btn-primary"><i class="fas fa-check me-2"></i> Enviar Assinatura</button>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        function resizeCanvas() {
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener("resize", resizeCanvas);
        
        // CORREÇÃO: Atrasar a primeira chamada de resizeCanvas para garantir que o layout está renderizado.
        setTimeout(resizeCanvas, 100);

        document.getElementById('clear').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('save').addEventListener('click', function () {
            if (signaturePad.isEmpty()) {
                return alert("Por favor, forneça a sua assinatura.");
            }

            var dataURL = signaturePad.toDataURL('image/png');
            
            fetch('<?= $_ENV['APP_URL'] ?>/ajax/salvar_assinatura', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id_entrega: <?= $entrega['id'] ?>, 
                    assinatura: dataURL 
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('A resposta da rede não foi OK');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    alert('Assinatura guardada com sucesso!');
                    window.location.href = '<?= $_ENV['APP_URL'] ?>/minhas_entregas';
                } else {
                    alert(`Erro ao guardar: ${data.error}`);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro de comunicação. Verifique a consola para mais detalhes.');
            });
        });
    });
    </script>
</body>
</html>
