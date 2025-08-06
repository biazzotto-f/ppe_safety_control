<?php
namespace App\Models;

class Empresa
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $result = $this->conn->query("SELECT id, nome_empresa, cnpj, data_cadastro, foto_empresa, status FROM empresas ORDER BY status ASC, nome_empresa ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUnidadesByEmpresaId($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM empresa_unidades WHERE id_empresa = ? ORDER BY nome_unidade");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($dados)
    {
        $sql = "INSERT INTO empresas (nome_empresa, cnpj, foto_empresa, cep, logradouro, numero, complemento, bairro, cidade, estado, telefone, contato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssss",
            $dados['nome_empresa'],
            $dados['cnpj'],
            $dados['foto_empresa'],
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['telefone'],
            $dados['contato']
        );
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function update($id, $dados)
    {
        $sql = "UPDATE empresas SET nome_empresa = ?, cnpj = ?, foto_empresa = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, telefone = ?, contato = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssi",
            $dados['nome_empresa'],
            $dados['cnpj'],
            $dados['foto_empresa'],
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['telefone'],
            $dados['contato'],
            $id
        );
        return $stmt->execute();
    }

    public function syncUnidades($id_empresa, $unidades)
    {
        $this->conn->query("DELETE FROM empresa_unidades WHERE id_empresa = $id_empresa");
        if (!empty($unidades)) {
            $stmt_insert = $this->conn->prepare("INSERT INTO empresa_unidades (id_empresa, nome_unidade, cep, logradouro, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($unidades as $unidade) {
                if (!empty($unidade['nome'])) {
                    $stmt_insert->bind_param("issssssss", $id_empresa, $unidade['nome'], $unidade['cep'], $unidade['logradouro'], $unidade['numero'], $unidade['complemento'], $unidade['bairro'], $unidade['cidade'], $unidade['estado']);
                    $stmt_insert->execute();
                }
            }
        }
        return true;
    }

    public function delete($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM empresas WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (\mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                return false;
            }
            error_log($e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id)
    {
        $stmt = $this->conn->prepare("UPDATE empresas SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
