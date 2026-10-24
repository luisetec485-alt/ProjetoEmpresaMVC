<?php
// Roteador principal: transforma a action da URL em uma operação do sistema.
    require_once __DIR__ . '/cargoController.php';
    require_once __DIR__ . '/depController.php';
    require_once __DIR__ . '/funcController.php';
    require_once __DIR__ . '/contaController.php';
    require_once __DIR__ . '/solicitacaoController.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // $action = strtolower(trim((string)($_GET['action'] ?? 'apresentacao')));
    $action = trim((string)($_GET['action'] ?? 'apresentacao'));

    // Reservado para futuros nomes alternativos de actions.
    $aliasesAcoes = [];
    if (isset($aliasesAcoes[$action])) {
        $action = $aliasesAcoes[$action];
    }

    $controller = new CargoController();
    $controller2 = new DepController();
    $controller3 = new FuncController();
    $controller4 = new ContaController();
    $controllerSolicitacao = new SolicitacaoController();

    function verificarAdmin() {
        // Restringe operações administrativas a usuários com nível 0.
        if(!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin'){
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Acesso negado',
                    text: 'Esta funcionalidade é restrita a administradores.'
                }).then(() => {
                    window.location.href = 'index.php?action=home';
                });
                </script>";
            exit;
        }
    }
    
    function verificarLogado() {
        // Redireciona visitantes não autenticados para o login.
        if (!isset($_SESSION['usuario_funcional'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    switch ($action) {
        // APRESENTAÇÃO DO PROJETO
        case 'apresentacao':
            include __DIR__."/../view/apresentacao.php";
            break;

        // HOME
        case 'home':
            verificarLogado();
            include __DIR__."/../view/home.php";
            break;

        // CARGOS
        case 'formCargo':
            verificarAdmin();
            include __DIR__."/../view/cargoForm.php";
            break;
        case 'listaCargo':
            verificarAdmin();
            $controller->listar();
            break;
        case 'salvarCargo':
            verificarAdmin();
            $controller->salvar();
            break;
        // Abre, salva ou exclui um cargo existente.
        case 'editarCargo': verificarAdmin(); $controller->editar(); break;
        case 'atualizarCargo': verificarAdmin(); $controller->atualizar(); break;
        case 'excluirCargo': verificarAdmin(); $controller->excluir(); break;

        // DEPARTAMENTOS
        case 'formDep':
            verificarAdmin();
            include __DIR__."/../view/depForm.php";
            break;
        case 'listaDep':
            verificarAdmin();
            $controller2->listar();
            break;
        case 'salvarDep':
            verificarAdmin();
            $controller2->salvar();
            break;
        // Abre, salva ou exclui um departamento existente.
        case 'editarDep': verificarAdmin(); $controller2->editar(); break;
        case 'atualizarDep': verificarAdmin(); $controller2->atualizar(); break;
        case 'excluirDep': verificarAdmin(); $controller2->excluir(); break;

        // FUNCIONÁRIOS
        case 'formFunc': 
            verificarAdmin();
            // carregar models
            require_once __DIR__.'/../model/cargoModel.php';
            require_once __DIR__.'/../model/departamentoModel.php';
            
            // pegar listas
            $cargoModel = new Cargo();
            $listaCargo = $cargoModel->listarTodos();
            
            $depModel = new Departamento();
            $listaDep = $depModel->listarTodos();
            
            // incluir a view
            include __DIR__."/../view/funcForm.php";
            break;
            case 'listaFunc':
                verificarAdmin();
                $controller3->listarTodos();
                break;
            case 'salvarFunc':
                verificarAdmin();
                $controller3->salvar();
                break;
            // Abre, salva ou exclui um funcionário existente.
            case 'editarFunc': verificarAdmin(); $controller3->editar(); break;
            case 'atualizarFunc': verificarAdmin(); $controller3->atualizar(); break;
            case 'excluirFunc': verificarAdmin(); $controller3->excluir(); break;

        // CONTAS
        case 'formConta':
            include __DIR__."/../view/contaForm.php";
            break;
        case 'listaConta':
            verificarAdmin();
            $controller4->listarConta();
            break;
        case 'salvarConta':
            $controller4->salvarConta();
            break;
        // Permite ao administrador editar ou excluir uma conta de acesso.
        case 'editarConta': verificarAdmin(); $controller4->editar(); break;
        case 'atualizarConta': verificarAdmin(); $controller4->atualizar(); break;
        case 'excluirConta': verificarAdmin(); $controller4->excluir(); break;

        // LOGIN
        case 'login':
            $controller4->abrirLogin();
            break;

        // AUTENTICAÇÃO
        case 'autenticar':
            $controller4->autenticar();
            break;


        // PERFIL DO USUÁRIO
        case 'relatorioPerfil':
            verificarLogado();
            $controller4->relatorioPerfil();
            break;

        case 'atualizarPerfil':
            verificarLogado();
            $controller4->atualizarPerfil();
            break;

        // FÉRIAS E AUSÊNCIAS
        case 'solicitacoes':
            verificarLogado();
            $controllerSolicitacao->listar();
            break;
        case 'novaSolicitacao':
            verificarLogado();
            $controllerSolicitacao->formulario();
            break;
        case 'salvarSolicitacao':
            verificarLogado();
            $controllerSolicitacao->salvar();
            break;
        case 'cancelarSolicitacao':
            verificarLogado();
            $controllerSolicitacao->cancelar();
            break;
        case 'avaliarSolicitacao':
            verificarAdmin();
            $controllerSolicitacao->avaliar();
            break;


        // SAIR DO SISTEMA
        case 'logout':
            session_destroy();
            header("Location: index.php?action=apresentacao");
            exit;
            break;

        default:
            include __DIR__ . "/../view/apresentacao.php";
            break;
    }
?>