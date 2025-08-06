<?php
namespace App\Models;

class Funcao
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByEmpresaId($id_empresa)
    {
        // CORREÇÃO: A consulta agora busca as classificações através das categorias associadas.
        $sql = "SELECT 
                    f.id, 
                    f.nome_funcao, 
                    s.nome_setor, 
                    f.riscos, 
                    f.status,
                    GROUP_CONCAT(DISTINCT cl.nome_classificacao ORDER BY cl.tipo SEPARATOR ', ') as classificacoes_padrao,
                    GROUP_CONCAT(DISTINCT cat.nome_categoria ORDER BY cat.nome_categoria SEPARATOR ', ') as categorias_padrao
                FROM funcoes f
                JOIN setores s ON f.id_setor = s.id
                LEFT JOIN funcao_categorias fcat ON f.id = fcat.id_funcao
                LEFT JOIN epi_categorias cat ON fcat.id_categoria = cat.id
                LEFT JOIN epi_classificacoes cl ON cat.id_classificacao = cl.id
                WHERE f.id_empresa = ?
                GROUP BY f.id
                ORDER BY f.status ASC, s.nome_setor ASC, f.nome_funcao ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM funcoes WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAssociatedCategorias($id_funcao)
    {
        $stmt = $this->conn->prepare("SELECT c.id, c.nome_categoria FROM funcao_categorias fc JOIN epi_categorias c ON fc.id_categoria = c.id WHERE fc.id_funcao = ?");
        $stmt->bind_param("i", $id_funcao);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAssociatedClassificacaoIds($id_funcao)
    {
        $stmt = $this->conn->prepare("SELECT id_classificacao FROM funcao_classificacoes WHERE id_funcao = ?");
        $stmt->bind_param("i", $id_funcao);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_classificacao'];
        }
        return $ids;
    }

    public function getAllByEmpresaId($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT id, id_setor, nome_funcao FROM funcoes WHERE id_empresa = ? AND status = 'ativo' ORDER BY nome_funcao");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($id_empresa, $id_setor, $nome_funcao, $riscos, $classificacao_ids, $categoria_ids)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO funcoes (id_empresa, id_setor, nome_funcao, riscos) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $id_empresa, $id_setor, $nome_funcao, $riscos);
            $stmt->execute();
            $id_nova_funcao = $this->conn->insert_id;

            if (!empty($classificacao_ids)) {
                $stmt_link = $this->conn->prepare("INSERT INTO funcao_classificacoes (id_funcao, id_classificacao) VALUES (?, ?)");
                foreach ($classificacao_ids as $id) {
                    $stmt_link->bind_param("ii", $id_nova_funcao, $id);
                    $stmt_link->execute();
                }
            }
            if (!empty($categoria_ids)) {
                $stmt_link = $this->conn->prepare("INSERT INTO funcao_categorias (id_funcao, id_categoria) VALUES (?, ?)");
                foreach ($categoria_ids as $id) {
                    $stmt_link->bind_param("ii", $id_nova_funcao, $id);
                    $stmt_link->execute();
                }
            }
            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function update($id, $id_setor, $nome_funcao, $riscos, $classificacao_ids, $categoria_ids)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE funcoes SET id_setor = ?, nome_funcao = ?, riscos = ? WHERE id = ?");
            $stmt->bind_param("issi", $id_setor, $nome_funcao, $riscos, $id);
            $stmt->execute();

            $this->conn->query("DELETE FROM funcao_classificacoes WHERE id_funcao = $id");
            if (!empty($classificacao_ids)) {
                $stmt_link = $this->conn->prepare("INSERT INTO funcao_classificacoes (id_funcao, id_classificacao) VALUES (?, ?)");
                foreach ($classificacao_ids as $id_class) {
                    $stmt_link->bind_param("ii", $id, $id_class);
                    $stmt_link->execute();
                }
            }

            $this->conn->query("DELETE FROM funcao_categorias WHERE id_funcao = $id");
            if (!empty($categoria_ids)) {
                $stmt_link = $this->conn->prepare("INSERT INTO funcao_categorias (id_funcao, id_categoria) VALUES (?, ?)");
                foreach ($categoria_ids as $id_cat) {
                    $stmt_link->bind_param("ii", $id, $id_cat);
                    $stmt_link->execute();
                }
            }
            $this->conn->commit();
            return true;
        } catch (\mysqli_sql_exception $e) {
            $this->conn->rollback();
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete($id, $id_empresa)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM funcoes WHERE id = ? AND id_empresa = ?");
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
        $stmt = $this->conn->prepare("UPDATE funcoes SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }
}
