<?php
// Controller de login, contas administrativas e perfil do usuário.
require_once __DIR__ . '/../model/contaModel.php';
require_once __DIR__ . '/../model/funcionarioModel.php';

class ContaController {

    public function abrirLogin() {
        // Exibe o formulário de autenticação.
        include __DIR__ . "/../view/login.php";
    }

    public function autenticar() {
        // Valida credenciais e cria as variáveis da sessão.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }

        $login = trim($_POST['login'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if ($login === '' || $senha === '') {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Campos obrigatórios',
                'text' => 'Preencha login e senha.'
            ];
            header('Location: index.php?action=login');
            exit;
        }

        $modelConta = new Conta();
        try {
            $conta = $modelConta->buscarPorLogin($login);
        } catch (PDOException $e) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Erro ao acessar o sistema',
                'text' => 'Não foi possível validar o login. Tente novamente.'
            ];
            header('Location: index.php?action=login');
            exit;
        }
        $senhaArmazenada = $conta['senha'] ?? '';
        $senhaValida = false;

        if ($conta) {
            if (password_verify($senha, $senhaArmazenada)) {
                $senhaValida = true;
            } elseif ($senhaArmazenada !== '' && $senha === $senhaArmazenada) {
                $senhaValida = true;
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $modelConta->atualizarSenhaHash($conta['funcional'], $senhaHash);
            }
        }

        if ($conta && $senhaValida) {
            $_SESSION['usuario_funcional'] = $conta['funcional'];
            $_SESSION['usuario_nome'] = $conta['nome'];
            $_SESSION['usuario_perfil'] = isset($conta['nivelAcesso']) && $conta['nivelAcesso'] == 0 ? 'admin' : 'user';

            header('Location: index.php?action=home');
            exit;
        }

        $_SESSION['alerta'] = [
            'icon' => 'error',
            'title' => 'Login inválido',
            'text' => 'Login ou senha inválidos!'
        ];
        header('Location: index.php?action=login');
        exit;
    }

    public function salvarConta() {
        // Valida o funcionário e cria sua conta de acesso.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $funcional = $_POST['funcional'];
            $cpf = preg_replace('/\D/', '', $_POST['cpf']);
            $login = $_POST['login'];
            $senha = $_POST['senha'];

            $modelFunc = new Funcionario();
            $funcionarioValido = $modelFunc->verificarDadosIniciais($funcional, $cpf);
            if (!$funcionarioValido) {
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Funcionário não encontrado',
                    'text' => 'Confira o Registro Funcional e o CPF.'
                ];
                header('Location: index.php?action=formConta');
                exit;
            }

            $modelConta = new Conta();
            $nomeFoto = "default.jpg";

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $extensoesPermitidas = ['jpg', 'jpeg', 'png'];

                if (in_array($extensao, $extensoesPermitidas)) {
                    $nomeFoto = uniqid() . "." . $extensao;

                    $diretorioFotos = __DIR__ . '/../view/img/foto de perfil/';
                    if (!is_dir($diretorioFotos)) {
                        mkdir($diretorioFotos, 0775, true);
                    }
                    $diretorioDestino = $diretorioFotos . $nomeFoto;

                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $diretorioDestino)) {
                    } else {
                        $_SESSION['alerta'] = [
                            'icon' => 'error',
                            'title' => 'Erro no upload',
                            'text' => 'Não foi possível salvar a foto.'
                        ];
                        header('Location: index.php?action=formConta');
                        exit;
                    }
                } else {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Formato inválido',
                        'text' => 'Envie uma imagem JPG, JPEG ou PNG.'
                    ];
                    header('Location: index.php?action=formConta');
                    exit;
                }
            }
            try {
                $sucesso = $modelConta->inserir($funcional, $login, $senha, $nomeFoto);
            } catch (PDOException $e) {
                $sucesso = false;
            }
            if ($sucesso) {
                $_SESSION['alerta'] = [
                    'icon' => 'success',
                    'title' => 'Conta criada!',
                    'text' => 'Conta criada com sucesso para ' . $funcionarioValido['nome'] . '!'
                ];
                $destino = ($_SESSION['usuario_perfil'] ?? '') === 'admin' ? 'listaConta' : 'login';
                header('Location: index.php?action=' . $destino);
            } else {
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Não foi possível criar a conta',
                    'text' => 'O login ou a conta já pode estar cadastrado.'
                ];
                header('Location: index.php?action=formConta');
            }
            exit;
        }
    }   
    
    public function listarConta() {
        // Envia as contas cadastradas para a tabela administrativa.
        $model = new Conta();
        $listaContas = $model->listarConta();
        $temOutroAdministrador = $model->existeAdministradorDiferente($_SESSION['usuario_funcional'] ?? 0);

        include __DIR__ . "/../view/contaLista.php";
    }

    public function editar() {
        // Carrega uma conta para edição pelo administrador.
        $conta = (new Conta())->buscarPorId($_GET['id'] ?? null);
        if (!$conta) { header('Location: index.php?action=listaConta'); exit; }
        $editandoPropriaConta = (int) $conta['funcional'] === (int) ($_SESSION['usuario_funcional'] ?? 0);
        include __DIR__ . "/../view/contaEditar.php";
    }

    public function atualizar() {
        // Atualiza login, nível e senha opcionalmente.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $funcional = filter_var($_POST['funcional'] ?? null, FILTER_VALIDATE_INT);
            $nivelAcesso = filter_var($_POST['nivelAcesso'] ?? null, FILTER_VALIDATE_INT);
            if ($funcional === false || $funcional < 1 || !in_array($nivelAcesso, [0, 1], true)) {
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Dados inválidos',
                    'text' => 'Confira a conta e o nível de acesso selecionados.'
                ];
                header('Location: index.php?action=listaConta'); exit;
            }

            $modelConta = new Conta();
            $ehPropriaConta = (int) $funcional === (int) ($_SESSION['usuario_funcional'] ?? 0);
            if ($ehPropriaConta && $nivelAcesso === 1
                && !$modelConta->existeAdministradorDiferente($funcional)) {
                $_SESSION['alerta'] = [
                    'icon' => 'warning',
                    'title' => 'Ação não permitida',
                    'text' => 'Promova outro funcionário a administrador antes de rebaixar esta conta.'
                ];
                header('Location: index.php?action=listaConta'); exit;
            }

            $senha = $ehPropriaConta
                ? null
                : ($_POST['senha'] ?? null);
            $sucesso = $modelConta->atualizar(
                $funcional,
                trim((string) ($_POST['login'] ?? '')),
                $nivelAcesso,
                $senha
            );
            // Guarda o resultado para o cabeçalho exibir após o redirect.
            $_SESSION['alerta'] = [
                'icon' => $sucesso ? 'success' : 'error',
                'title' => $sucesso ? 'Conta atualizada!' : 'Erro ao atualizar',
                'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar a conta.'
            ];
        }
        header('Location: index.php?action=listaConta'); exit;
    }

    public function excluir() {
        // Impede que o sistema fique sem administrador após uma autoexclusão.
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $funcionalAtual = (int) ($_SESSION['usuario_funcional'] ?? 0);
        $ehPropriaConta = $id !== false && $id === $funcionalAtual;
        $model = new Conta();

        if ($id === false || $id < 1) {
            $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Conta inválida', 'text' => 'Não foi possível identificar a conta.'];
            header('Location: index.php?action=listaConta');
            exit;
        }

        if ($ehPropriaConta && !$model->existeAdministradorDiferente($funcionalAtual)) {
            $_SESSION['alerta'] = ['icon' => 'warning', 'title' => 'Ação não permitida', 'text' => 'Cadastre ou promova outro administrador antes de excluir esta conta.'];
            header('Location: index.php?action=listaConta');
            exit;
        }

        if ($model->deletar($id)) {
            if ($ehPropriaConta) {
                session_unset();
                session_destroy();
                header('Location: index.php?action=apresentacao');
                exit;
            }
            // Informa à lista que a exclusão foi concluída.
            $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Conta excluída!', 'text' => 'O registro foi removido.'];
        } else {
            $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Erro ao excluir', 'text' => 'Não foi possível excluir a conta.'];
        }
        header('Location: index.php?action=listaConta'); exit;
    }

    public function relatorioPerfil() {
        // Consulta e exibe os dados do perfil autenticado.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_funcional'])) {
            header("Location: index.php?action=login");
            exit;
        }

        try {
            $modelFunc = new Funcionario();
            $funcionario = $modelFunc->buscarPorId($_SESSION['usuario_funcional']);
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            http_response_code(500);
            echo '<h2>Erro ao carregar o perfil</h2>';
            echo '<p>Não foi possível consultar os dados do perfil.</p>';
            exit;
        }

        if (!$funcionario) {
            http_response_code(404);
            echo '<h2>Perfil não encontrado</h2>';
            echo '<p>Não existem dados cadastrais para este usuário.</p>';
            exit;
        }

        include __DIR__ . "/../view/meuPerfil.php";
    }


    public function atualizarPerfil() {
        // Atualiza telefone, foto e senha do próprio usuário.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->relatorioPerfil();
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $funcional = $_SESSION['usuario_funcional'];
        $telefone  = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
        if ($telefone === '' || !in_array(strlen($telefone), [10, 11], true)) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Telefone inválido',
                'text' => 'Informe um telefone com DDD.'
            ];
            header('Location: index.php?action=relatorioPerfil');
            exit;
        }
        $novaSenha = $_POST['senha'] ?? '';
        $nomeFoto  = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png'];

            if (in_array($extensao, $extensoesPermitidas)) {
                $nomeFoto = uniqid() . "." . $extensao;
                $diretorioFotos = __DIR__ . '/../view/img/foto de perfil/';
                if (!is_dir($diretorioFotos)) {
                    mkdir($diretorioFotos, 0775, true);
                }
                $diretorioDestino = $diretorioFotos . $nomeFoto;
                move_uploaded_file($_FILES['foto']['tmp_name'], $diretorioDestino);
            }
        }

        $modelFunc = new Funcionario();
        $modelFunc->atualizarDadosProprios($funcional, $telefone, $nomeFoto);

        if (!empty($novaSenha)) {
            $modelConta = new Conta();
            $modelConta->atualizarSenha($funcional, $novaSenha);
        }

        // O cabeçalho exibirá o sucesso após voltar ao perfil.
        $_SESSION['alerta'] = [
            'icon' => 'success',
            'title' => 'Perfil atualizado!',
            'text' => 'Suas alterações foram salvas.'
        ];
        header('Location: index.php?action=relatorioPerfil');
        exit;
    }
}
?>