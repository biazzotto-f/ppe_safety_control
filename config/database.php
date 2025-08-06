<?php

/**
 * Estabelece e retorna uma conexão com a base de dados utilizando
 * as credenciais definidas no arquivo .env.
 *
 * @return mysqli Uma instância do objeto mysqli em caso de sucesso.
 * Termina a execução com die() em caso de falha.
 */
function getDbConnection()
{
    static $conn = null;

    if ($conn === null) {
        $host = $_ENV['DB_HOST'];
        $db   = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        try {
            // Tenta estabelecer a conexão
            $conn = new mysqli($host, $user, $pass, $db);

            // Checar conexão
            if ($conn->connect_error) {
                error_log("Falha na conexão: " . $conn->connect_error);
                die("Ocorreu um erro ao conectar-se ao banco de dados. Por favor, tente novamente mais tarde.");
            }

            // Define o conjunto de caracteres para UTF-8 (utf8mb4)
            if (!$conn->set_charset("utf8mb4")) {
                error_log("Erro ao definir o charset utf8mb4: " . $conn->error);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Ocorreu um erro inesperado. Por favor, tente novamenet mais tarde.");
        }
    }

    return $conn;
}