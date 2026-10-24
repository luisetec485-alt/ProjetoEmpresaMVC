<?php
require_once __DIR__ . '/../config/conexao.php';

class Solicitacao
{
    public function listarDoFuncionario($funcional)
    {
        $stmt = Conexao::getConn()->prepare(
            'SELECT * FROM dboSolicitacaoAusencia '
            . 'WHERE funcional = :funcional ORDER BY solicitadoEm DESC, id DESC'
        );
        $stmt->execute([':funcional' => $funcional]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodas()
    {
        $sql = 'SELECT * FROM dboSolicitacaoAusencia '
            . 'ORDER BY id ASC LIMIT 300';
        return Conexao::getConn()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar($funcional, $tipo, $dataInicio, $dataFim, $motivo)
    {
        $conexao = Conexao::getConn();
        $conexao->beginTransaction();

        try {
            $consultaFuncionario = $conexao->prepare(
                'SELECT nome FROM dboFuncionario WHERE funcional = :funcional FOR UPDATE'
            );
            $consultaFuncionario->execute([':funcional' => $funcional]);
            $nomeFuncionario = $consultaFuncionario->fetchColumn();
            if ($nomeFuncionario === false) {
                throw new RuntimeException('O cadastro do funcionário não foi encontrado.');
            }

            $consultaConflito = $conexao->prepare(
                "SELECT COUNT(*) FROM dboSolicitacaoAusencia
                 WHERE funcional = :funcional
                   AND status IN ('pendente', 'aprovada')
                   AND dataInicio <= :dataFim
                   AND dataFim >= :dataInicio"
            );
            $consultaConflito->execute([
                ':funcional' => $funcional,
                ':dataInicio' => $dataInicio,
                ':dataFim' => $dataFim,
            ]);
            if ((int) $consultaConflito->fetchColumn() > 0) {
                throw new RuntimeException('Já existe um pedido pendente ou aprovado nesse período.');
            }

            $inserir = $conexao->prepare(
                "INSERT INTO dboSolicitacaoAusencia
                    (funcional, solicitanteNome, tipo, dataInicio, dataFim, motivo)
                 VALUES (:funcional, :nome, :tipo, :dataInicio, :dataFim, :motivo)"
            );
            $inserir->execute([
                ':funcional' => $funcional,
                ':nome' => $nomeFuncionario,
                ':tipo' => $tipo,
                ':dataInicio' => $dataInicio,
                ':dataFim' => $dataFim,
                ':motivo' => $motivo,
            ]);

            $id = (int) $conexao->lastInsertId();
            $conexao->commit();
            return $id;
        } catch (Throwable $erro) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw $erro;
        }
    }

    public function avaliar($id, $status, $resposta, $avaliadoPor, $avaliadorNome)
    {
        $stmt = Conexao::getConn()->prepare(
            "UPDATE dboSolicitacaoAusencia
             SET status = :status, respostaAdmin = :resposta,
                 avaliadoPor = :avaliadoPor, avaliadorNome = :avaliadorNome,
                 avaliadoEm = CURRENT_TIMESTAMP
             WHERE id = :id AND status = 'pendente'"
        );
        $stmt->execute([
            ':status' => $status,
            ':resposta' => $resposta !== '' ? $resposta : null,
            ':avaliadoPor' => $avaliadoPor,
            ':avaliadorNome' => $avaliadorNome,
            ':id' => $id,
        ]);
        return $stmt->rowCount() === 1;
    }

    public function cancelar($id, $funcional)
    {
        $stmt = Conexao::getConn()->prepare(
            "UPDATE dboSolicitacaoAusencia SET status = 'cancelada'
             WHERE id = :id AND funcional = :funcional AND status = 'pendente'"
        );
        $stmt->execute([
            ':id' => $id,
            ':funcional' => $funcional,
        ]);
        return $stmt->rowCount() === 1;
    }
}