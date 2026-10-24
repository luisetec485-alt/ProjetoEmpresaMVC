<?php

// Centraliza a criação da conexão PDO usada pelos models.
class Conexao
{
    // Abre uma conexão MySQL com tratamento de exceções.
    public static function getConn()
    {
        $host = "127.0.0.1";
        $port = "3307";
        $dbname = "dboEmpresaMvc";
        $user = "root";
        $pass = "";

        try {
            $conexao = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
                $user,
                $pass
            );

            $conexao->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $conexao;

        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }
}