<?php
namespace App\Models;

class Setor
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByEmpresaId($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM setores WHERE id_empresa = ? ORDER BY status ASC, nome_setor ASC");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM setores WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($id_empresa, $nome_setor)
    {
        $stmt = $this->conn->prepare("INSERT INTO setores (id_empresa, nome_setor) VALUES (?, ?)");
        $stmt->bind_param("is", $id_empresa, $nome_setor);
        return $stmt->execute();
    }

    public function update($id, $id_empresa, $nome_setor)
    {
        $stmt = $this->conn->prepare("UPDATE setores SET nome_setor = ? WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("sii", $nome_setor, $id, $id_empresa);
        return $stmt->execute();
    }

    public function delete($id, $id_empresa)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM setores WHERE id = ? AND id_empresa = ?");
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
        $stmt = $this->conn->prepare("UPDATE setores SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }
}
