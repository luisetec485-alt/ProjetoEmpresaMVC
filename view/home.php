<?php
include __DIR__ . '/include/cabecalho.php';
$nomeUsuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$ehAdmin = ($_SESSION['usuario_perfil'] ?? '') === 'admin';
?>

<main class="conteudo-home">
    <p class="home-kicker">SISTEMA EMPRESA</p>
    <h2 class="titulo-pagina">Olá, <?= $nomeUsuario ?></h2>
    <section class="boas-vindas">
        <p class="texto-destaque">
            <?= $ehAdmin
                ? 'Acompanhe e analise os pedidos de férias e ausências da equipe.'
                : 'Precisa se ausentar ou planejar suas férias? Envie um pedido e acompanhe a resposta por aqui.' ?>
        </p>
        <div class="home-solicitacao-acoes">
            <a class="btn-save" href="index.php?action=solicitacoes">
                <?= $ehAdmin ? 'Analisar solicitações' : 'Minhas solicitações' ?>
            </a>
            <?php if (!$ehAdmin): ?>
                <a class="home-solicitacao-link" href="index.php?action=novaSolicitacao">Criar novo pedido</a>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/include/rodape.php'; ?>