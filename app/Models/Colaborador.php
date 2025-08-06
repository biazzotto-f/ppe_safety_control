<?php

namespace App\Models;

class Colaborador
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByEmpresaId($id_empresa)
    {
        $sql = "SELECT c.id, c.nome_completo, c.matricula, f.nome_funcao, s.nome_setor, c.status, u.foto_perfil, un.nome_unidade
                FROM colaboradores c
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                JOIN funcoes f ON c.id_funcao = f.id
                JOIN setores s ON c.id_setor = s.id
                LEFT JOIN empresa_unidades un ON c.id_unidade_operacao = un.id
                WHERE c.id_empresa = ? ORDER BY c.status ASC, c.nome_completo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT c.*, u.username 
                                      FROM colaboradores c 
                                      JOIN usuarios u ON c.id_usuario = u.id
                                      WHERE c.id = ? AND c.id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($id_empresa, $id_usuario, $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $foto_path)
    {
        $stmt = $this->conn->prepare("INSERT INTO colaboradores (id_empresa, id_usuario, nome_completo, matricula, id_funcao, id_setor, id_unidade_operacao, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssiis", $id_empresa, $id_usuario, $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $foto_path);
        return $stmt->execute();
    }

    public function update($id, $id_empresa, $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $status, $foto_path)
    {
        $stmt = $this->conn->prepare("UPDATE colaboradores SET nome_completo = ?, matricula = ?, id_funcao = ?, id_setor = ?, id_unidade_operacao = ?, status = ?, foto_perfil = ? WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ssiiissii", $nome, $matricula, $id_funcao, $id_setor, $id_unidade, $status, $foto_path, $id, $id_empresa);
        return $stmt->execute();
    }

    public function delete($id, $id_empresa)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM colaboradores WHERE id = ? AND id_empresa = ?");
            $stmt->bind_param("ii", $id, $id_empresa);
            return $stmt->execute();
        } catch (\mysqli_sql_exception $e) {
            // Captura a exceção de chave estrangeira e retorna 'false'.
            if ($e->getCode() == 1451) { // Código de erro para violação de chave estrangeira
                return false;
            }
            // Para outros erros, pode optar por registá-los ou lançar a exceção.
            error_log($e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("UPDATE colaboradores SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }

    public function getActiveByEmpresaId($id_empresa)
    {
        $sql = "SELECT c.id, c.nome_completo, c.matricula, c.id_funcao
                FROM colaboradores c
                WHERE c.id_empresa = ? AND c.status = 'ativo'
                ORDER BY c.nome_completo ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getDetailsById($id_colaborador)
    {
        $sql = "SELECT c.nome_completo, c.matricula, s.nome_setor, f.nome_funcao, u.foto_perfil
                FROM colaboradores c
                JOIN setores s ON c.id_setor = s.id
                JOIN funcoes f ON c.id_funcao = f.id
                LEFT JOIN usuarios u ON c.id_usuario = u.id
                WHERE c.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_colaborador);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
