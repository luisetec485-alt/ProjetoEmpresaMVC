<?php
// Exibe os dados do usuário logado e permite atualizar seu próprio perfil.
include __DIR__ . '/include/cabecalho.php'; ?>

<h2>Meu Relatório Cadastral</h2>
<p class="subtitulo">Dados extraídos diretamente do servidor para verificação de dados e atualização de contato.</p>

<div class="perfil-card">
    
    <!-- A imagem atual é validada antes de ser exibida. -->
    <div class="perfil-item">
        <?php 
            $nomeFoto = $funcionario['foto'] ?? '';
            $caminhoFoto = __DIR__ . '/img/foto de perfil/' . $nomeFoto;
            if (!empty($nomeFoto) && file_exists($caminhoFoto)) {
                $fotoExibicao = 'view/img/foto%20de%20perfil/' . rawurlencode($nomeFoto);
            } else {
                $fotoExibicao = 'view/img/foto%20de%20perfil/default.jpg';
            }
        ?>
        <img src="<?= htmlspecialchars($fotoExibicao) ?>" alt="Foto do Colaborador" class="foto-perfil">
    </div>

    <div class="perfil-item">
        <strong>Registro Funcional:</strong> <span>#<?= htmlspecialchars($funcionario['funcional'] ?? '') ?></span>
    </div>

    <div class="perfil-item">
        <strong>Nome do Funcionário:</strong> <span><?= htmlspecialchars($funcionario['nome'] ?? '') ?></span>
    </div>

    <div class="perfil-item">
        <strong>CPF Cadastrado:</strong> <span><?= htmlspecialchars($funcionario['cpf'] ?? '') ?></span>
    </div>

    <div class="perfil-item">
        <strong>Endereço:</strong>
        <span><?= htmlspecialchars(implode(', ', array_filter([
            $funcionario['logradouro'] ?? $funcionario['endereco'] ?? null,
            $funcionario['numero'] ?? null,
            $funcionario['complemento'] ?? null,
            $funcionario['bairro'] ?? null,
            $funcionario['cidade'] ?? null,
            $funcionario['uf'] ?? null,
            !empty($funcionario['cep']) ? 'CEP ' . $funcionario['cep'] : null,
        ], static function ($parte) { return $parte !== null && $parte !== ''; }))) ?></span>
    </div>

    <div class="perfil-item">
        <strong>Cargo:</strong> <span><?= htmlspecialchars($funcionario['nomeCargo'] ?? '') ?></span>
    </div>

    <div class="perfil-item">
        <strong>Departamento:</strong> <span><?= htmlspecialchars($funcionario['nomeDepartamento'] ?? '') ?></span>
    </div>

    <!-- O formulário atualiza somente dados permitidos ao próprio usuário. -->
    <form action="index.php?action=atualizarPerfil" method="POST" enctype="multipart/form-data" class="perfil-form-interno" onsubmit="return confirmarAlteracoes(event, this, 'do perfil')">
        
        <label class="form-label">Substituir Imagem de Perfil:</label>
        <input type="file" name="foto" accept="image/jpeg, image/png" class="form-input">

        <label class="form-label">Telefone de Contato:</label>
        <input type="text" name="telefone" class="form-input input-perfil-ajustado" value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>" required autocomplete="off" maxlength="15" inputmode="tel" data-mask="telefone">

        <label class="form-label">Alterar Senha do Sistema:</label>
        <input type="password" name="senha" class="form-input input-perfil-ajustado" placeholder="Deixe em branco para preservar a senha atual" autocomplete="new-password">

        <button type="submit" class="btn-save">Confirmar Atualizações</button>
    </form>
</div>

<?php include __DIR__ . '/include/rodape.php'; ?>