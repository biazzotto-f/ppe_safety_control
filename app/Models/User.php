<?php
namespace App\Models;

class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT id, nome, password, nivel_acesso, foto_perfil FROM usuarios WHERE username = ? AND status = 'ativo'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, nome, username, nivel_acesso, foto_perfil, status FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEmpresasAcesso($id_usuario)
    {
        $sql = "SELECT e.id, e.nome_empresa, e.foto_empresa 
                FROM usuario_empresa_acesso uea
                JOIN empresas e ON uea.id_empresa = e.id
                WHERE uea.id_usuario = ? AND e.status = 'ativo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getEmpresaIdsForUser($id_usuario)
    {
        $stmt = $this->conn->prepare("SELECT id_empresa FROM usuario_empresa_acesso WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_empresa'];
        }
        return $ids;
    }

    public function getAllAdmins()
    {
        $sql = "SELECT 
                    u.id, u.nome, u.username, u.created_at, u.foto_perfil, u.status,
                    GROUP_CONCAT(e.nome_empresa SEPARATOR ', ') as empresas_associadas
                FROM usuarios u
                LEFT JOIN usuario_empresa_acesso uea ON u.id = uea.id_usuario
                LEFT JOIN empresas e ON uea.id_empresa = e.id
                WHERE u.nivel_acesso = 'admin'
                GROUP BY u.id
                ORDER BY u.status ASC, u.nome ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createUser($nome, $username, $password, $nivel_acesso, $foto_path = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, username, password, nivel_acesso, foto_perfil) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $username, $password, $nivel_acesso, $foto_path);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function updateProfile($id, $nome, $username, $foto_path)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET nome = ?, username = ?, foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $username, $foto_path, $id);
        return $stmt->execute();
    }

    public function updateProfilePicture($id, $foto_path)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("si", $foto_path, $id);
        return $stmt->execute();
    }

    public function updatePassword($id, $password)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $id);
        return $stmt->execute();
    }

    public function associateEmpresas($id_usuario, $empresa_ids)
    {
        $stmt_delete = $this->conn->prepare("DELETE FROM usuario_empresa_acesso WHERE id_usuario = ?");
        $stmt_delete->bind_param("i", $id_usuario);
        $stmt_delete->execute();

        if (!empty($empresa_ids)) {
            $stmt_insert = $this->conn->prepare("INSERT INTO usuario_empresa_acesso (id_usuario, id_empresa) VALUES (?, ?)");
            foreach ($empresa_ids as $id_empresa) {
                $stmt_insert->bind_param("ii", $id_usuario, $id_empresa);
                $stmt_insert->execute();
            }
        }
        return true;
    }

    public function deleteUser($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function toggleStatus($id)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET status = IF(status='ativo', 'inativo', 'ativo') WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
