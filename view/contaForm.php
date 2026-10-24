<?php 
    // Formulário de primeiro acesso para criação de conta.
    include __DIR__."/INCLUDE/cabecalho.php";
?>

    <h2>Primeiro Acesso - Criar Conta de Usuário</h2>
    <!-- O controller confirma funcional e CPF antes de criar a conta. -->
    <form action="index.php?action=salvarConta" method="POST" enctype="multipart/form-data" class="form-grupo">
        <label class="form-label">Registro Funcional:</label><br>
        <input 
            type="number" 
            name="funcional" 
            class="form-input" 
            required 
        > <br>
        <label class="form-label">CPF:</label><br>
        <input 
            type="text" 
            name="cpf" 
            class="form-input" 
            required 
            placeholder="000.000.000-00"
            maxlength="14"
            inputmode="numeric"
            data-mask="cpf"
            title="Digite um CPF com 11 números"
        > <br>
        <label class="form-label">Login de Acesso desejado:</label><br>
        <input 
            type="text"  
            name="login" 
            class="form-input" 
            required
            placeholder="Ex: nome.sobrenome"
        > <br>
        <label class="form-label">Senha do Sistema:</label><br>
        <input 
            type="password"  
            name="senha" 
            class="form-input" 
            required 
            placeholder="Define sua senha de acesso"
        > <br>
        <label class="form-label">Foto de Perfil:</label><br>
        <input 
            type="file"  
            name="foto" 
            class="form-input" 
            accept="image/png, image/jpg, image/jpeg"
        > <br>
        <button type="submit" class="btn-save">Validar e Criar Conta</button>
    </form>

<?php 
    include __DIR__."/INCLUDE/rodape.php";
?>