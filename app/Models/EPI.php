<?php

namespace App\Models;

class EPI
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllByEmpresaId($id_empresa)
    {
        $sql = "SELECT e.*, cat.nome_categoria,
                       (SELECT GROUP_CONCAT(f.nome_fornecedor SEPARATOR ', ') FROM epi_fornecedores ef JOIN fornecedores f ON ef.id_fornecedor = f.id WHERE ef.id_epi = e.id) as fornecedores,
                       COALESCE((SELECT SUM(l.quantidade_atual) FROM epi_lotes l WHERE l.id_epi = e.id), 0) as estoque_total
                FROM epis e
                JOIN epi_categorias cat ON e.id_categoria = cat.id
                WHERE e.id_empresa = ?
                ORDER BY e.status ASC, e.nome_epi ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailableForDropdown($id_empresa)
    {
        $sql = "SELECT e.id, e.nome_epi, e.ca, e.foto_epi,
                       COALESCE((SELECT SUM(l.quantidade_atual) FROM epi_lotes l WHERE l.id_epi = e.id), 0) as estoque_total
                FROM epis e
                WHERE e.id_empresa = ? AND e.status = 'ativo'
                GROUP BY e.id, e.nome_epi, e.ca, e.foto_epi
                HAVING estoque_total > 0
                ORDER BY e.nome_epi ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $sql = "SELECT e.*, c.id_classificacao 
                FROM epis e
                JOIN epi_categorias c ON e.id_categoria = c.id
                WHERE e.id = ? AND e.id_empresa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAssociatedFornecedorIds($id_epi)
    {
        $stmt = $this->conn->prepare("SELECT id_fornecedor FROM epi_fornecedores WHERE id_epi = ?");
        $stmt->bind_param("i", $id_epi);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_fornecedor'];
        }
        return $ids;
    }
                      
    public function getEpisByFuncaoId($id_funcao, $id_empresa)
    {
        $sql = "SELECT e.id, e.nome_epi, e.ca, e.foto_epi,
                       COALESCE((SELECT SUM(l.quantidade_atual) FROM epi_lotes l WHERE l.id_epi = e.id), 0) as estoque_total
                FROM epis e
                JOIN epi_categorias cat ON e.id_categoria = cat.id
                JOIN funcao_categorias fc ON cat.id = fc.id_categoria
                WHERE fc.id_funcao = ? AND e.id_empresa = ? AND e.status = 'ativo'
                GROUP BY e.id
                HAVING estoque_total > 0
                ORDER BY e.nome_epi ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_funcao, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($dados)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO epis (id_empresa, id_categoria, nome_epi, ca, validade_ca, foto_epi, frequencia_troca, unidade_frequencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssss", $dados['id_empresa'], $dados['id_categoria'], $dados['nome_epi'], $dados['ca'], $dados['validade_ca'], $dados['foto_epi'], $dados['frequencia_troca'], $dados['unidade_frequencia']);
            $stmt->execute();
            $id_novo_epi = $this->conn->insert_id;

            $this->syncFornecedores($id_novo_epi, $dados['fornecedor_ids']);

            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function update($id, $dados)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE epis SET id_categoria = ?, nome_epi = ?, ca = ?, validade_ca = ?, foto_epi = ?, frequencia_troca = ?, unidade_frequencia = ? WHERE id = ? AND id_empresa = ?");
            $stmt->bind_param("issssssii", $dados['id_categoria'], $dados['nome_epi'], $dados['ca'], $dados['validade_ca'], $dados['foto_epi'], $dados['frequencia_troca'], $dados['unidade_frequencia'], $id, $dados['id_empresa']);
            $stmt->execute();

            $this->syncFornecedores($id, $dados['fornecedor_ids']);

            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    private function syncFornecedores($id_epi, $fornecedor_ids)
    {
        $this->conn->query("DELETE FROM epi_fornecedores WHERE id_epi = $id_epi");
        if (!empty($fornecedor_ids)) {
            $stmt = $this->conn->prepare("INSERT INTO epi_fornecedores (id_epi, id_fornecedor) VALUES (?, ?)");
            foreach ($fornecedor_ids as $id_fornecedor) {
                $stmt->bind_param("ii", $id_epi, $id_fornecedor);
                $stmt->execute();
            }
        }
    }

    public function delete($id, $id_empresa)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM epis WHERE id = ? AND id_empresa = ?");
            $stmt->bind_param("ii", $id, $id_empresa);
            return $stmt->execute();
        } catch (\mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                return false;
            }
            error_log($e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("UPDATE epis SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }
}
