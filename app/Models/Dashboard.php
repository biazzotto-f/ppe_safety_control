<?php

namespace App\Models;

class Dashboard
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAdminStats($id_empresa)
    {
        $stats = [];

        // --- MÉTRICAS OPERACIONAIS ---
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM colaboradores WHERE id_empresa = ? AND status = 'ativo'");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['total_colaboradores'] = $stmt->get_result()->fetch_assoc()['total'];

        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entregas WHERE id_empresa = ? AND assinatura_digital IS NULL");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['entregas_pendentes'] = $stmt->get_result()->fetch_assoc()['total'];

        // --- MÉTRICAS FINANCEIRAS ---
        $stmt = $this->conn->prepare("SELECT SUM(quantidade_atual * custo_unitario) as total FROM epi_lotes WHERE id_empresa = ?");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['custo_total_estoque'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt = $this->conn->prepare("SELECT SUM(custo_total) as total FROM epi_lotes WHERE id_empresa = ? AND YEAR(data_compra) = YEAR(CURDATE()) AND MONTH(data_compra) = MONTH(CURDATE())");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['investimento_mes_atual'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt = $this->conn->prepare("SELECT SUM(custo_total) as total FROM epi_lotes WHERE id_empresa = ? AND YEAR(data_compra) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(data_compra) = MONTH(CURDATE() - INTERVAL 1 MONTH)");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['investimento_mes_anterior'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $sql_utilizado_atual = "SELECT SUM(el.quantidade_retirada * lote.custo_unitario) as total FROM entregas e JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id WHERE e.id_empresa = ? AND YEAR(e.data_entrega) = YEAR(CURDATE()) AND MONTH(e.data_entrega) = MONTH(CURDATE())";
        $stmt = $this->conn->prepare($sql_utilizado_atual);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['utilizado_mes_atual'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $sql_utilizado_anterior = "SELECT SUM(el.quantidade_retirada * lote.custo_unitario) as total FROM entregas e JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id WHERE e.id_empresa = ? AND YEAR(e.data_entrega) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(e.data_entrega) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($sql_utilizado_anterior);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $stats['utilizado_mes_anterior'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        return $stats;
    }

    public function getSuperAdminStats()
    {
        $stats = [];
        $stats['total_colaboradores'] = $this->conn->query("SELECT COUNT(*) as total FROM colaboradores WHERE status = 'ativo'")->fetch_assoc()['total'];
        $stats['entregas_pendentes'] = $this->conn->query("SELECT COUNT(*) as total FROM entregas WHERE assinatura_digital IS NULL")->fetch_assoc()['total'];
        $stats['custo_total_estoque'] = $this->conn->query("SELECT SUM(quantidade_atual * custo_unitario) as total FROM epi_lotes")->fetch_assoc()['total'] ?? 0;
        $stats['investimento_mes_atual'] = $this->conn->query("SELECT SUM(custo_total) as total FROM epi_lotes WHERE YEAR(data_compra) = YEAR(CURDATE()) AND MONTH(data_compra) = MONTH(CURDATE())")->fetch_assoc()['total'] ?? 0;
        $stats['investimento_mes_anterior'] = $this->conn->query("SELECT SUM(custo_total) as total FROM epi_lotes WHERE YEAR(data_compra) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(data_compra) = MONTH(CURDATE() - INTERVAL 1 MONTH)")->fetch_assoc()['total'] ?? 0;
        $stats['utilizado_mes_atual'] = $this->conn->query("SELECT SUM(el.quantidade_retirada * lote.custo_unitario) as total FROM entregas e JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id WHERE YEAR(e.data_entrega) = YEAR(CURDATE()) AND MONTH(e.data_entrega) = MONTH(CURDATE())")->fetch_assoc()['total'] ?? 0;
        $stats['utilizado_mes_anterior'] = $this->conn->query("SELECT SUM(el.quantidade_retirada * lote.custo_unitario) as total FROM entregas e JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id WHERE YEAR(e.data_entrega) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(e.data_entrega) = MONTH(CURDATE() - INTERVAL 1 MONTH)")->fetch_assoc()['total'] ?? 0;
        return $stats;
    }

    public function getProximasEntregasProgramadas($id_empresa)
    {
        $sql = "
            SELECT
                c.id as id_colaborador,
                c.nome_completo,
                c.foto_perfil,
                e.id as id_epi,
                e.nome_epi,
                (SELECT SUM(ent_inner.quantidade_entregue) 
                 FROM entregas ent_inner 
                 WHERE ent_inner.id_colaborador = c.id AND ent_inner.id_epi = e.id) as quantidade_total_recebida,
                MAX(ent.data_entrega) as ultima_entrega,
                e.frequencia_troca,
                e.unidade_frequencia
            FROM entregas ent
            JOIN colaboradores c ON ent.id_colaborador = c.id
            JOIN epis e ON ent.id_epi = e.id
            WHERE ent.id_empresa = ?
              AND e.frequencia_troca IS NOT NULL AND e.frequencia_troca > 0
            GROUP BY c.id, e.id
            HAVING 
                CASE
                    WHEN e.unidade_frequencia = 'dias' THEN
                        DATE_ADD(MAX(ent.data_entrega), INTERVAL ( (SELECT SUM(ent_inner.quantidade_entregue) FROM entregas ent_inner WHERE ent_inner.id_colaborador = c.id AND ent_inner.id_epi = e.id) * e.frequencia_troca) DAY)
                    WHEN e.unidade_frequencia = 'meses' THEN
                        DATE_ADD(MAX(ent.data_entrega), INTERVAL ( (SELECT SUM(ent_inner.quantidade_entregue) FROM entregas ent_inner WHERE ent_inner.id_colaborador = c.id AND ent_inner.id_epi = e.id) * e.frequencia_troca) MONTH)
                END <= DATE_ADD(CURDATE(), INTERVAL 5 DAY)
            ORDER BY ultima_entrega ASC, c.nome_completo ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGlobalProximasEntregas()
    {
        $sql = "SELECT 
                    c.id as id_colaborador,
                    c.nome_completo,
                    COALESCE(u.foto_perfil, c.foto_perfil) as foto_perfil,
                    e.id as id_epi,
                    e.nome_epi,
                    ent.quantidade_entregue,
                    ent.data_proxima_troca
                FROM entregas ent
                JOIN colaboradores c ON ent.id_colaborador = c.id
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                JOIN epis e ON ent.id_epi = e.id
                WHERE ent.data_proxima_troca IS NOT NULL
                AND ent.data_proxima_troca BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
                ORDER BY ent.data_proxima_troca ASC, c.nome_completo ASC";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getTopEpisUtilizados($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT epi.nome_epi, SUM(e.quantidade_entregue) as total_entregue FROM entregas e JOIN epis epi ON e.id_epi = epi.id WHERE e.id_empresa = ? GROUP BY e.id_epi ORDER BY total_entregue DESC LIMIT 5");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGlobalTopEpisUtilizados()
    {
        return $this->conn->query("SELECT epi.nome_epi, SUM(e.quantidade_entregue) as total_entregue FROM entregas e JOIN epis epi ON e.id_epi = epi.id GROUP BY e.id_epi ORDER BY total_entregue DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    }

    public function getTopColaboradoresCusto($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT c.nome_completo, SUM(el.quantidade_retirada * lote.custo_unitario) as custo_total FROM entregas e JOIN colaboradores c ON e.id_colaborador = c.id JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id WHERE e.id_empresa = ? GROUP BY e.id_colaborador ORDER BY custo_total DESC LIMIT 5");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGlobalTopColaboradoresCusto()
    {
        return $this->conn->query("SELECT c.nome_completo, SUM(el.quantidade_retirada * lote.custo_unitario) as custo_total FROM entregas e JOIN colaboradores c ON e.id_colaborador = c.id JOIN entrega_lotes el ON e.id = el.id_entrega JOIN epi_lotes lote ON el.id_lote = lote.id GROUP BY e.id_colaborador ORDER BY custo_total DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    }

    public function getEpisEstoqueBaixo($id_empresa)
    {
        $sql = "SELECT e.nome_epi, SUM(l.quantidade_atual) as total_estoque FROM epi_lotes l JOIN epis e ON l.id_epi = e.id WHERE l.id_empresa = ? GROUP BY e.id, e.nome_epi HAVING total_estoque < 10 AND total_estoque > 0 ORDER BY total_estoque ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGlobalEpisEstoqueBaixo()
    {
        return $this->conn->query("SELECT e.nome_epi, SUM(l.quantidade_atual) as total_estoque FROM epi_lotes l JOIN epis e ON l.id_epi = e.id GROUP BY e.id, e.nome_epi HAVING total_estoque < 10 AND total_estoque > 0 ORDER BY total_estoque ASC")->fetch_all(MYSQLI_ASSOC);
    }
}
