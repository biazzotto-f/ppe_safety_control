<?php
namespace App\Models;

class Fornecedor
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByEmpresaId($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM fornecedores WHERE id_empresa = ? ORDER BY status ASC, nome_fornecedor ASC");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIdAndEmpresaId($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM fornecedores WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($dados)
    {
        $sql = "INSERT INTO fornecedores (id_empresa, nome_fornecedor, cnpj, telefone, email, contato, cep, logradouro, numero, complemento, bairro, cidade, estado, foto_fornecedor) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "isssssssssssss",
            $dados['id_empresa'],
            $dados['nome_fornecedor'],
            $dados['cnpj'],
            $dados['telefone'],
            $dados['email'],
            $dados['contato'],
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['foto_fornecedor']
        );
        return $stmt->execute();
    }

    public function update($id, $dados)
    {
        $sql = "UPDATE fornecedores SET nome_fornecedor = ?, cnpj = ?, telefone = ?, email = ?, contato = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, foto_fornecedor = ?
                WHERE id = ? AND id_empresa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssii",
            $dados['nome_fornecedor'],
            $dados['cnpj'],
            $dados['telefone'],
            $dados['email'],
            $dados['contato'],
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['foto_fornecedor'],
            $id,
            $dados['id_empresa']
        );
        return $stmt->execute();
    }

    public function delete($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("DELETE FROM fornecedores WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }

    public function toggleStatus($id, $id_empresa)
    {
        $stmt = $this->conn->prepare("UPDATE fornecedores SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ? AND id_empresa = ?");
        $stmt->bind_param("ii", $id, $id_empresa);
        return $stmt->execute();
    }
}
