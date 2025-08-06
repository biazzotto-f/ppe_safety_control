<?php
namespace App\Models;

class Log
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllLogs()
    {
        $result = $this->conn->query("SELECT * FROM log_acoes ORDER BY timestamp DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
