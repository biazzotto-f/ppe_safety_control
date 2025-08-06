<?php
namespace App\Models;

class Funcionario
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Busca todas as entregas associadas a um ID de usuário (funcionário).
     */
    public function getEntregasByUserId($id_usuario)
    {
        $sql = "SELECT 
                    e.id,
                    epi.nome_epi,
                    e.quantidade_entregue,
                    e.data_entrega,
                    e.assinatura_digital
                FROM entregas e
                JOIN epis epi ON e.id_epi = epi.id
                JOIN colaboradores c ON e.id_colaborador = c.id
                WHERE c.id_usuario = ?
                ORDER BY e.data_entrega DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Busca os detalhes de uma entrega específica para um usuário.
     */
    public function getEntregaDetailsById($id_entrega, $id_usuario)
    {
        $sql = "SELECT 
                    e.id,
                    epi.nome_epi,
                    e.quantidade_entregue,
                    c.nome_completo
                FROM entregas e
                JOIN epis epi ON e.id_epi = epi.id
                JOIN colaboradores c ON e.id_colaborador = c.id
                WHERE e.id = ? AND c.id_usuario = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_entrega, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Guarda a assinatura como um ficheiro de imagem e armazena o caminho na base de dados.
     */
    public function saveSignature($id_entrega, $id_usuario, $assinatura_base64)
    {
        $target_dir = "uploads/assinaturas/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        list($type, $data) = explode(';', $assinatura_base64);
        list(, $data) = explode(',', $data);
        $decoded_data = base64_decode($data);

        $filename = uniqid('sig_', true) . '.png';
        $filepath = $target_dir . $filename;

        if (file_put_contents($filepath, $decoded_data)) {
            $sql = "UPDATE entregas e
                    JOIN colaboradores c ON e.id_colaborador = c.id
                    SET e.assinatura_digital = ?
                    WHERE e.id = ? AND c.id_usuario = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sii", $filepath, $id_entrega, $id_usuario);
            return $stmt->execute();
        }

        return false;
    }
}
