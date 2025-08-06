<?php
namespace App\Models;

class Compra
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getLotesByEmpresa($id_empresa)
    {
        $sql = "SELECT l.*, e.nome_epi, e.ca, e.foto_epi, f.nome_fornecedor
                FROM epi_lotes l
                JOIN epis e ON l.id_epi = e.id
                LEFT JOIN fornecedores f ON l.id_fornecedor = f.id
                WHERE l.id_empresa = ? 
                ORDER BY CASE WHEN l.quantidade_atual = 0 THEN 1 ELSE 0 END ASC, l.data_compra DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findLoteById($id_empresa, $id_lote)
    {
        $sql = "SELECT l.*, e.nome_epi, e.ca FROM epi_lotes l JOIN epis e ON l.id_epi = e.id WHERE l.id_empresa = ? AND l.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_empresa, $id_lote);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createLote($id_empresa, $id_epi, $id_fornecedor, $quantidade, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO epi_lotes (id_empresa, id_epi, id_fornecedor, quantidade_inicial, quantidade_atual, data_compra, data_vencimento, nota_fiscal, custo_unitario, custo_total) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // CORREÇÃO: A string de tipos agora tem 10 caracteres para 10 variáveis.
        $stmt->bind_param("iiiisssddd", $id_empresa, $id_epi, $id_fornecedor, $quantidade, $quantidade, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function updateLote($id_lote, $id_empresa, $id_fornecedor, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total)
    {
        $stmt = $this->conn->prepare(
            "UPDATE epi_lotes SET id_fornecedor = ?, data_compra = ?, data_vencimento = ?, nota_fiscal = ?, custo_unitario = ?, custo_total = ? 
             WHERE id = ? AND id_empresa = ?"
        );
        $stmt->bind_param("isssddii", $id_fornecedor, $data_compra, $data_vencimento, $nota_fiscal, $custo_unitario, $custo_total, $id_lote, $id_empresa);
        return $stmt->execute();
    }

    public function deleteLote($id_lote, $id_empresa)
    {
        // Apenas permite apagar se nenhuma unidade do lote foi utilizada
        $stmt = $this->conn->prepare(
            "DELETE FROM epi_lotes 
             WHERE id = ? AND id_empresa = ? AND quantidade_atual = quantidade_inicial"
        );
        $stmt->bind_param("ii", $id_lote, $id_empresa);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
