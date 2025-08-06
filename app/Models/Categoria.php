<?php
namespace App\Models;

class Categoria
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getClassificacoes()
    {
        $result = $this->conn->query("SELECT * FROM epi_classificacoes ORDER BY tipo");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByEmpresaId($id_empresa)
    {
        $sql = "SELECT c.id, c.nome_categoria, cl.nome_classificacao, cl.tipo, c.id_classificacao
                FROM epi_categorias c
                JOIN epi_classificacoes cl ON c.id_classificacao = cl.id
                WHERE c.id_empresa = ? 
                ORDER BY cl.tipo, c.nome_categoria";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM epi_categorias WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($id_empresa, $id_classificacao, $nome_categoria)
    {
        $stmt = $this->conn->prepare("INSERT INTO epi_categorias (id_empresa, id_classificacao, nome_categoria) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_empresa, $id_classificacao, $nome_categoria);
        return $stmt->execute();
    }

    public function update($id, $id_empresa, $id_classificacao, $nome_categoria)
    {
        $stmt = $this->conn->prepare("UPDATE epi_categorias SET id_classificacao = ?, nome_categoria = ? WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("isii", $id_classificacao, $nome_categoria, $id, $id_empresa);
        return $stmt->execute();
    }

    public function delete($id, $id_empresa)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM epi_categorias WHERE id = ? AND id_empresa = ?");
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
}
