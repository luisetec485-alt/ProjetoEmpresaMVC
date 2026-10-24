<?php 
    // Lista cargos e oferece as ações administrativas do CRUD.
    include __DIR__."/INCLUDE/cabecalho.php";
?>

    <h2>Relatório de Cargos</h2>
    <p class="subtitulo">Dados processados pelo Controller e exibidos pela View</p>

    <!-- Os dados desta tabela vêm do CargoController. -->
    <table class="tabela-dados">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th class="col-valor">Salário</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($listaCargos) && count($listaCargos) > 0): ?>
                <?php foreach($listaCargos as $c): ?>
                <tr>
                    <td>#<?=$c['codCargo']?></td>
                    <td><?= htmlspecialchars($c['nomeCargo']) ?></td>
                    <td class="col-valor">
                        R$ <?= number_format($c['salario'], 2, ',', '.') ?>
                    </td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarCargo&id=<?=$c['codCargo']?>">Editar</a>
                        <a class="btn-acao btn-excluir" href="index.php?action=excluirCargo&id=<?=$c['codCargo']?>" onclick="return confirmarExclusao(event, this.href, 'cargo');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
        <?php else: ?>
                <tr>
                    <td colspan="4" class="msg-vazia">
                        Nenhum registro para exibir.
                    </td>
                </tr>
        <?php endif; ?>

        </tbody>
    </table>

<?php 
    include __DIR__."/INCLUDE/rodape.php";
?>