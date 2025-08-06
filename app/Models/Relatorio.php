<?php
namespace App\Models;

class Relatorio
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getEntregasPorPeriodo($id_empresa, $data_inicio, $data_fim)
    {
        $data_fim_ajustada = $data_fim . ' 23:59:59';
        $sql = "SELECT e.id, c.nome_completo as colaborador_nome, c.matricula, epi.nome_epi, epi.ca, e.quantidade_entregue, e.data_entrega, e.assinatura_digital 
                FROM entregas e
                JOIN colaboradores c ON e.id_colaborador = c.id
                JOIN epis epi ON e.id_epi = epi.id
                WHERE e.id_empresa = ? AND e.data_entrega BETWEEN ? AND ?
                ORDER BY e.data_entrega DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $id_empresa, $data_inicio, $data_fim_ajustada);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEntregasPorColaboradorEPeriodo($id_empresa, $id_colaborador, $data_inicio, $data_fim)
    {
        $data_fim_ajustada = $data_fim . ' 23:59:59';
        $sql = "SELECT e.id, c.nome_completo as colaborador_nome, c.matricula, epi.nome_epi, epi.ca, e.quantidade_entregue, e.data_entrega, e.assinatura_digital
                FROM entregas e
                JOIN colaboradores c ON e.id_colaborador = c.id
                JOIN epis epi ON e.id_epi = epi.id
                WHERE e.id_empresa = ? AND e.id_colaborador = ? AND e.data_entrega BETWEEN ? AND ?
                ORDER BY e.data_entrega DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $id_empresa, $id_colaborador, $data_inicio, $data_fim_ajustada);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRelatorioFuncao($id_empresa)
    {
        // CORREÇÃO: A consulta agora busca as classificações através das categorias associadas.
        $sql = "SELECT
                    f.nome_funcao,
                    s.nome_setor,
                    f.riscos,
                    GROUP_CONCAT(DISTINCT cl.nome_classificacao ORDER BY cl.tipo SEPARATOR ', ') as classificacoes,
                    GROUP_CONCAT(DISTINCT cat.nome_categoria ORDER BY cat.nome_categoria SEPARATOR ', ') as categorias
                FROM funcoes f
                JOIN setores s ON f.id_setor = s.id
                LEFT JOIN funcao_categorias fcat ON f.id = fcat.id_funcao
                LEFT JOIN epi_categorias cat ON fcat.id_categoria = cat.id
                LEFT JOIN epi_classificacoes cl ON cat.id_classificacao = cl.id
                WHERE f.id_empresa = ? AND f.status = 'ativo'
                GROUP BY f.id
                ORDER BY f.nome_funcao";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEntregasAgrupadasPorFuncionario($id_empresa, $data_inicio, $data_fim)
    {
        $data_fim_ajustada = $data_fim . ' 23:59:59';
        $sql = "SELECT c.nome_completo, c.matricula,
                       COUNT(e.id) as total_entregas,
                       COALESCE(SUM(el.quantidade_retirada * lote.custo_unitario), 0) as custo_total
                FROM entregas e
                JOIN colaboradores c ON e.id_colaborador = c.id
                LEFT JOIN entrega_lotes el ON e.id = el.id_entrega
                LEFT JOIN epi_lotes lote ON el.id_lote = lote.id
                WHERE e.id_empresa = ? AND e.data_entrega BETWEEN ? AND ?
                GROUP BY c.id, c.nome_completo, c.matricula
                ORDER BY custo_total DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $id_empresa, $data_inicio, $data_fim_ajustada);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
