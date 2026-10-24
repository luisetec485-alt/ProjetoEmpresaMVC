<?php
include __DIR__ . '/include/cabecalho.php';
$pedidos = $pedidos ?? [];
$csrfToken = $csrfToken ?? '';
$ehAdmin = $ehAdmin ?? false;
$escapar = static function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
$statusTexto = [
    'pendente' => 'Aguardando análise',
    'aprovada' => 'Aprovada',
    'recusada' => 'Recusada',
    'cancelada' => 'Cancelada',
];
$tipoTexto = [
    'ferias' => 'Férias',
    'ausencia' => 'Ausência',
];
?>

<p class="home-kicker"><?= $ehAdmin ? 'GESTÃO DE PESSOAS' : 'SERVIÇOS DO FUNCIONÁRIO' ?></p>
<div class="solicitacao-titulo">
    <div>
        <h2><?= $ehAdmin ? 'Solicitações de férias e ausências' : 'Minhas solicitações' ?></h2>
        <p><?= $ehAdmin ? 'Analise pedidos pendentes e consulte o histórico.' : 'Acompanhe o andamento dos seus pedidos.' ?></p>
    </div>
    <?php if (!$ehAdmin): ?>
        <a class="btn-save solicitacao-botao-link" href="index.php?action=novaSolicitacao">Nova solicitação</a>
    <?php endif; ?>
</div>

<?php if ($pedidos): ?>
    <div class="solicitacao-tabela-wrap">
        <table class="tabela-dados solicitacao-tabela">
            <thead>
                <tr>
                    <?php if ($ehAdmin): ?><th>Funcionário</th><?php endif; ?>
                    <th>Tipo</th><th>Período</th><th>Motivo</th><th>Status</th><th>Solicitado</th><th>Resposta</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <?php if ($ehAdmin): ?>
                            <td><?= $escapar($pedido['solicitanteNome']) ?> <span class="solicitacao-funcional">#<?= (int) $pedido['funcional'] ?></span></td>
                        <?php endif; ?>
                        <td><?= $escapar($tipoTexto[$pedido['tipo']] ?? $pedido['tipo']) ?></td>
                        <td><?= $escapar(date('d/m/Y', strtotime($pedido['dataInicio']))) ?> a<br><?= $escapar(date('d/m/Y', strtotime($pedido['dataFim']))) ?></td>
                        <td class="solicitacao-celula-motivo"><?= nl2br($escapar($pedido['motivo'])) ?></td>
                        <td><span class="solicitacao-status status-<?= $escapar($pedido['status']) ?>"><?= $escapar($statusTexto[$pedido['status']] ?? $pedido['status']) ?></span></td>
                        <td><?= $escapar(date('d/m/Y H:i', strtotime($pedido['solicitadoEm']))) ?></td>
                        <td class="solicitacao-celula-resposta">
                            <?php if (!empty($pedido['respostaAdmin'])): ?>
                                <?= nl2br($escapar($pedido['respostaAdmin'])) ?>
                                <?php if (!empty($pedido['avaliadorNome'])): ?>
                                    <small>Por <?= $escapar($pedido['avaliadorNome']) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="solicitacao-sem-resposta">Sem resposta</span>
                            <?php endif; ?>
                        </td>
                        <td class="solicitacao-celula-acao">
                            <?php if ($ehAdmin && $pedido['status'] === 'pendente'): ?>
                                <form action="index.php?action=avaliarSolicitacao" method="POST" class="solicitacao-form-analise">
                                    <input type="hidden" name="csrf_token" value="<?= $escapar($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                    <label class="sr-only" for="resposta-<?= (int) $pedido['id'] ?>">Resposta ao funcionário</label>
                                    <textarea id="resposta-<?= (int) $pedido['id'] ?>" name="respostaAdmin" maxlength="500" placeholder="Resposta; obrigatória ao recusar"></textarea>
                                    <div class="solicitacao-botoes-analise">
                                        <button type="submit" name="status" value="aprovada" class="btn-solicitacao-aprovar">Aprovar</button>
                                        <button type="submit" name="status" value="recusada" class="btn-solicitacao-recusar">Recusar</button>
                                    </div>
                                </form>
                            <?php elseif (!$ehAdmin && $pedido['status'] === 'pendente'): ?>
                                <form action="index.php?action=cancelarSolicitacao" method="POST" onsubmit="return confirm('Cancelar esta solicitação?');">
                                    <input type="hidden" name="csrf_token" value="<?= $escapar($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                    <button type="submit" class="btn-solicitacao-recusar">Cancelar pedido</button>
                                </form>
                            <?php else: ?>
                                <span class="solicitacao-sem-resposta">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="solicitacao-vazio">
        <h3>Nenhuma solicitação por aqui</h3>
        <p><?= $ehAdmin ? 'Quando um funcionário enviar um pedido, ele aparecerá nesta lista.' : 'Se precisar de férias ou registrar uma ausência, envie uma solicitação.' ?></p>
        <?php if (!$ehAdmin): ?>
            <a href="index.php?action=novaSolicitacao">Criar solicitação</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/include/rodape.php'; ?>