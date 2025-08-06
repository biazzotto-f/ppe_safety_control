<?php

namespace App\Models;

class Entrega
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByEmpresaId($id_empresa)
    {
        $sql = "SELECT 
                    e.id, 
                    c.nome_completo as colaborador_nome, 
                    u.foto_perfil,
                    epi.nome_epi, 
                    e.quantidade_entregue, 
                    e.data_entrega, 
                    e.assinatura_digital,
                    e.data_proxima_troca
                FROM entregas e
                JOIN colaboradores c ON e.id_colaborador = c.id
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                JOIN epis epi ON e.id_epi = epi.id
                WHERE e.id_empresa = ? 
                ORDER BY e.data_entrega DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($id_empresa, $id_colaborador, $id_epi, $quantidade_total, $id_usuario_entrega)
    {
        $this->conn->begin_transaction();

        try {
            // 1. Busca a programação de troca do EPI
            $stmt_epi = $this->conn->prepare("SELECT frequencia_troca, unidade_frequencia FROM epis WHERE id = ?");
            $stmt_epi->bind_param("i", $id_epi);
            $stmt_epi->execute();
            $epi_programacao = $stmt_epi->get_result()->fetch_assoc();

            // 2. Calcula a data da próxima troca
            $data_proxima_troca = null;
            if ($epi_programacao && !empty($epi_programacao['frequencia_troca'])) {
                $frequencia = (int)$epi_programacao['frequencia_troca'];
                $unidade = $epi_programacao['unidade_frequencia'];
                $intervalo = $unidade === 'meses' ? "P{$frequencia}M" : "P{$frequencia}D";

                $data_proxima_troca = (new \DateTime())
                    ->add(new \DateInterval($intervalo))
                    ->format('Y-m-d');
            }

            // 3. Lógica de FIFO para buscar lotes
            $sql_lotes = "SELECT id, quantidade_atual FROM epi_lotes 
                          WHERE id_epi = ? AND id_empresa = ? AND quantidade_atual > 0
                          ORDER BY CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END, data_vencimento ASC, data_compra ASC";

            $stmt_lotes = $this->conn->prepare($sql_lotes);
            $stmt_lotes->bind_param("ii", $id_epi, $id_empresa);
            $stmt_lotes->execute();
            $lotes_disponiveis = $stmt_lotes->get_result()->fetch_all(MYSQLI_ASSOC);

            // 4. Verificar se há estoque suficiente
            $estoque_total = array_sum(array_column($lotes_disponiveis, 'quantidade_atual'));
            if ($estoque_total < $quantidade_total) {
                $this->conn->rollback();
                return false; // Estoque insuficiente
            }

            // 5. Criar o registo principal da entrega com a nova data
            $stmt_entrega = $this->conn->prepare(
                "INSERT INTO entregas (id_empresa, id_colaborador, id_epi, quantidade_entregue, id_usuario_entrega, data_proxima_troca) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt_entrega->bind_param("iiisis", $id_empresa, $id_colaborador, $id_epi, $quantidade_total, $id_usuario_entrega, $data_proxima_troca);
            $stmt_entrega->execute();
            $id_nova_entrega = $this->conn->insert_id;

            // 6. Debitar dos lotes e registar na tabela de ligação
            $quantidade_restante = $quantidade_total;
            foreach ($lotes_disponiveis as $lote) {
                if ($quantidade_restante <= 0) break;

                $id_lote_atual = $lote['id'];
                $quantidade_no_lote = $lote['quantidade_atual'];
                $quantidade_a_retirar = min($quantidade_restante, $quantidade_no_lote);

                $stmt_update_lote = $this->conn->prepare("UPDATE epi_lotes SET quantidade_atual = quantidade_atual - ? WHERE id = ?");
                $stmt_update_lote->bind_param("ii", $quantidade_a_retirar, $id_lote_atual);
                $stmt_update_lote->execute();

                $stmt_link = $this->conn->prepare("INSERT INTO entrega_lotes (id_entrega, id_lote, quantidade_retirada) VALUES (?, ?, ?)");
                $stmt_link->bind_param("iii", $id_nova_entrega, $id_lote_atual, $quantidade_a_retirar);
                $stmt_link->execute();

                $quantidade_restante -= $quantidade_a_retirar;
            }

            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $exception) {
            $this->conn->rollback();
            error_log($exception->getMessage());
            return false;
        }
    }

    public function delete($id, $id_empresa)
    {
        $this->conn->begin_transaction();

        try {
            $stmt_get_entrega = $this->conn->prepare("SELECT assinatura_digital FROM entregas WHERE id = ? AND id_empresa = ?");
            $stmt_get_entrega->bind_param("ii", $id, $id_empresa);
            $stmt_get_entrega->execute();
            $entrega = $stmt_get_entrega->get_result()->fetch_assoc();

            if ($entrega && !empty($entrega['assinatura_digital'])) {
                if (file_exists($entrega['assinatura_digital'])) {
                    unlink($entrega['assinatura_digital']);
                }
            }

            $stmt_get_lotes = $this->conn->prepare("SELECT id_lote, quantidade_retirada FROM entrega_lotes WHERE id_entrega = ?");
            $stmt_get_lotes->bind_param("i", $id);
            $stmt_get_lotes->execute();
            $lotes_afetados = $stmt_get_lotes->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($lotes_afetados as $lote) {
                $stmt_update_stock = $this->conn->prepare("UPDATE epi_lotes SET quantidade_atual = quantidade_atual + ? WHERE id = ?");
                $stmt_update_stock->bind_param("ii", $lote['quantidade_retirada'], $lote['id_lote']);
                $stmt_update_stock->execute();
            }

            $stmt_delete_link = $this->conn->prepare("DELETE FROM entrega_lotes WHERE id_entrega = ?");
            $stmt_delete_link->bind_param("i", $id);
            $stmt_delete_link->execute();

            $stmt_delete_entrega = $this->conn->prepare("DELETE FROM entregas WHERE id = ? AND id_empresa = ?");
            $stmt_delete_entrega->bind_param("ii", $id, $id_empresa);
            $stmt_delete_entrega->execute();

            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $exception) {
            $this->conn->rollback();
            return false;
        }
    }
}
