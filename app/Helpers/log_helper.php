<?php

/**
 * Regista uma ação na tabela de logs.
 *
 * @param string $acao A descrição da ação a ser registada.
 */
function registrarAcao($acao)
{
    // Garante que a sessão está ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $id_usuario = $_SESSION['id_usuario'];
        $nome_usuario = $_SESSION['nome_usuario'];

        // Obtém a ligação à base de dados
        $db = getDbConnection();

        $stmt = $db->prepare("INSERT INTO log_acoes (id_usuario, nome_usuario, acao) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $nome_usuario, $acao);
        $stmt->execute();
        $stmt->close();
    }
}
