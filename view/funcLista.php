<?php 
    // Relatório de funcionários com cargo, departamento e ações do CRUD.
    include __DIR__."/INCLUDE/cabecalho.php";
?>

    <h2>Relatório de Funcionários</h2>
    <p class="subtitulo">Dados processados pelo Controller e exibidos pela View</p>
    <!-- Os nomes dos relacionamentos já vêm preparados pelo model. -->
    <table class="tabela-dados">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Endereço</th>
                <th>Departamento</th>
                <th>Cargo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($listaFuncionarios) && count($listaFuncionarios) > 0): ?>
                <?php foreach($listaFuncionarios as $f): ?>
                <?php
                    $cpf = preg_replace('/\D/', '', (string) $f['cpf']);
                    $cpfFormatado = strlen($cpf) === 11
                        ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2)
                        : $f['cpf'];
                    $telefone = preg_replace('/\D/', '', (string) ($f['telefone'] ?? ''));
                    $telefoneFormatado = strlen($telefone) === 11
                        ? '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4)
                        : (strlen($telefone) === 10 ? '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4) : $f['telefone']);
                    $partesEndereco = array_filter([
                        $f['logradouro'] ?? $f['endereco'], $f['numero'] ?? null, $f['complemento'] ?? null,
                        $f['bairro'] ?? null, $f['cidade'] ?? null, $f['uf'] ?? null,
                        !empty($f['cep']) ? 'CEP ' . $f['cep'] : null,
                    ], static function ($parte) { return $parte !== null && $parte !== ''; });
                ?>
                <tr>
                    <td>#<?= $f['funcional'] ?></td>
                    <td><?= htmlspecialchars($f['nome']) ?></td>
                    <td><?= htmlspecialchars($cpfFormatado) ?></td>
                    <td><?= htmlspecialchars($telefoneFormatado ?? '') ?></td>
                    <td><?= htmlspecialchars(implode(', ', $partesEndereco)) ?></td>
                    <td><?= $f['nomeDepartamento'] ?? '---' ?></td>
                    <td><?= $f['nomeCargo'] ?? '---' ?></td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarFunc&id=<?=$f['funcional']?>">Editar</a>
                        <a class="btn-acao btn-excluir" href="index.php?action=excluirFunc&id=<?=$f['funcional']?>" onclick="return confirmarExclusao(event, this.href, 'funcionário');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="msg-vazia">
                        Nenhum funcionário cadastrado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php 
    include __DIR__."/INCLUDE/rodape.php";
?>