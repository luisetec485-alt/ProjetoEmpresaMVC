<?php
// Model responsável pelas operações da tabela de cargos.
    require_once __DIR__ . '/../config/conexao.php';
    class Cargo {
        // Cria um novo cargo.
        public function inserir($nome, $salario) {
            $sql = "insert into dboCargo (nomeCargo, salario) values (:nome, :salario)";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':salario', $salario);
            return $stmt->execute();
        }
        // Retorna todos os cargos ordenados pelo código.
        public function listarTodos() {
            $sql = "select * from dboCargo order by codCargo asc";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // Busca um cargo para preencher a tela de edição.
        public function buscarPorId($id) {
            $stmt = Conexao::getConn()->prepare("select * from dboCargo where codCargo = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        // Atualiza nome e salário do cargo.
        public function atualizar($id, $nome, $salario) {
            $stmt = Conexao::getConn()->prepare("update dboCargo set nomeCargo = :nome, salario = :salario where codCargo = :id");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':salario', $salario);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        // Exclui o cargo; o banco impede a exclusão se houver funcionários vinculados.
        public function deletar($id) {
            $stmt = Conexao::getConn()->prepare("delete from dboCargo where codCargo = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
?>