<?php
// Model que concentra consultas e alterações de funcionários.
    require_once __DIR__ . '/../config/conexao.php';
    class Funcionario {
        // Insere o funcionário e seus vínculos com cargo e departamento.
        public function inserir($nome, $cpf, $telefone, array $endereco, $codDep, $codCargo) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                $sql = "INSERT INTO dboFuncionario
                        (nome, cpf, telefone, endereco, codDepartamento, codCargo)
                        VALUES
                        (:nome, :cpf, :telefone, :endereco, :codDepartamento, :codCargo)";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':telefone' => $telefone,
                    ':endereco' => $this->formatarEndereco($endereco),
                    ':codDepartamento' => $codDep,
                    ':codCargo' => $codCargo,
                ]);

                $funcional = (int) $conexao->lastInsertId();
                $this->salvarEndereco($conexao, $funcional, $endereco);
                $conexao->commit();
                return true;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }
        // Lista funcionários trazendo também os nomes dos relacionamentos.
        public function listarTodos(){
            $sql = "SELECT f.*, d.nomeDepartamento, c.nomeCargo,
                        e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf
                FROM dboFuncionario f
                INNER JOIN dboDepartamento d ON f.codDepartamento = d.codDepartamento
                INNER JOIN dboCargo c ON f.codCargo = c.codCargo
                LEFT JOIN dboEndereco e ON f.funcional = e.funcional
                ORDER BY f.funcional ASC";
            $stmt= Conexao::getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // Atualiza os dados administrativos do funcionário.
        public function atualizar($id, $nome, $cpf, $telefone, array $endereco, $codDep, $codCargo) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                $sql = "UPDATE dboFuncionario SET nome = :nome, cpf = :cpf, telefone = :telefone,
                        endereco = :endereco, codDepartamento = :codDepartamento, codCargo = :codCargo
                        WHERE funcional = :id";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':telefone' => $telefone,
                    ':endereco' => $this->formatarEndereco($endereco),
                    ':codDepartamento' => $codDep,
                    ':codCargo' => $codCargo,
                    ':id' => $id,
                ]);

                $this->salvarEndereco($conexao, $id, $endereco);
                $conexao->commit();
                return true;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }
        // Exclui o funcionário quando não existe conta vinculada.
        public function deletar($id) {
            $stmt = Conexao::getConn()->prepare("delete from dboFuncionario where funcional = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        // Confere funcional e CPF durante a criação de uma conta.
        public function verificarDadosIniciais($funcional, $cpf) {
            $sql = "SELECT funcional, nome FROM dboFuncionario WHERE funcional = :funcional AND cpf = :cpf";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        // Busca os dados do perfil e seus relacionamentos.
        public function buscarPorId($funcional) {
                $sql = "SELECT f.*, c.nomeCargo, d.nomeDepartamento AS nomeDepartamento, co.foto,
                    e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf
                    FROM dboFuncionario f
                    INNER JOIN dboCargo c ON f.codCargo = c.codCargo
                    INNER JOIN dboDepartamento d ON f.codDepartamento = d.codDepartamento
                    LEFT JOIN dboConta co ON f.funcional = co.funcional
                    LEFT JOIN dboEndereco e ON f.funcional = e.funcional
                    WHERE f.funcional = :funcional";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        private function salvarEndereco(PDO $conexao, $funcional, array $endereco) {
            $sql = "INSERT INTO dboEndereco
                    (funcional, cep, logradouro, numero, complemento, bairro, cidade, uf)
                    VALUES (:funcional, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf)
                    ON DUPLICATE KEY UPDATE cep = VALUES(cep), logradouro = VALUES(logradouro),
                    numero = VALUES(numero), complemento = VALUES(complemento), bairro = VALUES(bairro),
                    cidade = VALUES(cidade), uf = VALUES(uf)";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                ':funcional' => $funcional,
                ':cep' => $endereco['cep'],
                ':logradouro' => $endereco['logradouro'],
                ':numero' => $endereco['numero'],
                ':complemento' => $endereco['complemento'] !== '' ? $endereco['complemento'] : null,
                ':bairro' => $endereco['bairro'],
                ':cidade' => $endereco['cidade'],
                ':uf' => $endereco['uf'],
            ]);
        }

        private function formatarEndereco(array $endereco) {
            $partes = [
                $endereco['logradouro'],
                $endereco['numero'],
                $endereco['complemento'],
                $endereco['bairro'],
                $endereco['cidade'] . ' - ' . $endereco['uf'],
                $endereco['cep'],
            ];
            return implode(', ', array_filter($partes, static function ($parte) {
                return $parte !== '';
            }));
        }

        // Permite alterar apenas telefone e foto no próprio perfil.
        public function atualizarDadosProprios($funcional, $telefone, $foto = null) {
            if ($foto !== null) {
                $sql = "UPDATE dboFuncionario f
                        INNER JOIN dboConta c ON c.funcional = f.funcional
                        SET f.telefone = :telefone, c.foto = :foto
                        WHERE f.funcional = :funcional";
            } else {
                $sql = "UPDATE dboFuncionario
                        SET telefone = :telefone
                        WHERE funcional = :funcional";
            }

            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);

            if ($foto !== null) {
                $stmt->bindParam(':foto', $foto, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

    }
?>