<?php
// Lista contas de acesso para administração.
include __DIR__ . '/include/cabecalho.php'; ?>
<?php $temOutroAdministrador = $temOutroAdministrador ?? false; ?>

<h2>Relatório de Contas de Usuários</h2>

    <!-- Cada linha permite editar ou remover a conta correspondente. -->
    <table class="tabela-dados">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Funcional</th>
            <th>Funcionário</th>
            <th>Login</th>
            <th>Nível de acesso</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($listaContas) && count($listaContas) > 0): ?>
            <?php foreach ($listaContas as $conta): ?>
                <tr>
                    <td style="text-align: center;">
                        <?php
                            $nomeFoto = $conta['foto'] ?? '';
                            $foto = __DIR__ . '/img/foto de perfil/' . $nomeFoto;
                            $caminhoFoto = file_exists($foto)
                                ? 'view/img/foto%20de%20perfil/' . rawurlencode($nomeFoto)
                                : 'view/img/foto%20de%20perfil/default.jpg';
                        ?>
                        <img src="/ProjetoEmpresaMVC/<?= $caminhoFoto ?>" 
                            alt="Perfil" 
                            class="foto-perfil-lista">
                    </td>
                    <td>#<?= $conta['funcional'] ?></td>
                    <td><?= htmlspecialchars($conta['nome']) ?></td>
                    <td><?= htmlspecialchars($conta['login']) ?></td>
                    <td><?= $conta['nivelAcesso'] == 0 ? 'Administrador' : 'Funcionário' ?></td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarConta&id=<?=$conta['funcional']?>">Editar</a>
                        <?php if ((int) $conta['funcional'] === (int) ($_SESSION['usuario_funcional'] ?? 0)
                            && (int) $conta['nivelAcesso'] === 0 && !$temOutroAdministrador): ?>
                            <span class="conta-admin-protegido" title="Cadastre outro administrador para poder excluir esta conta.">Último administrador</span>
                        <?php else: ?>
                            <a class="btn-acao btn-excluir" href="index.php?action=excluirConta&amp;id=<?= (int) $conta['funcional'] ?>" onclick="return confirmarExclusao(event, this.href, 'conta');">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;" class="msg-vazia">Nenhuma conta cadastrada no sistema.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/include/rodape.php'; ?>