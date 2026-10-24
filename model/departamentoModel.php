<?php
// Model responsável pelas operações da tabela de departamentos.
    require_once __DIR__ . '/../config/conexao.php';
    class Departamento {
        // Cria um departamento.
        public function inserir($nome) {
            $sql = "insert into dboDepartamento (nomeDepartamento) values (:nome)";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            return $stmt->execute();
        }
        // Lista os departamentos ordenados pelo código.
        public function listarTodos() {
            $sql = "select * from dboDepartamento order by codDepartamento asc";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // Busca um departamento pelo código.
        public function buscarPorId($id) {
            $stmt = Conexao::getConn()->prepare("select * from dboDepartamento where codDepartamento = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        // Atualiza o nome do departamento.
        public function atualizar($id, $nome) {
            $stmt = Conexao::getConn()->prepare("update dboDepartamento set nomeDepartamento = :nome where codDepartamento = :id");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        // Exclui o departamento quando não há funcionários dependentes.
        public function deletar($id) {
            $stmt = Conexao::getConn()->prepare("delete from dboDepartamento where codDepartamento = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
?>