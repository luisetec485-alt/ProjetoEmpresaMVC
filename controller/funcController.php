<?php
// Controller do cadastro, consulta e manutenção de funcionários.
    require_once __DIR__ . '/../model/funcionarioModel.php';
    require_once __DIR__ . '/../model/cargoModel.php';
    require_once __DIR__ . '/../model/departamentoModel.php';
    class FuncController {
        public function salvar() {
            // Recebe dados do formulário e cria o funcionário.
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
                $telefone = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
                $endereco = $this->validarEndereco($_POST['endereco'] ?? []);
                if (strlen($cpf) !== 11 || ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) || $endereco === false) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Confira os dados',
                        'text' => 'Informe CPF, telefone e endereço nos formatos válidos.'
                    ];
                    header('Location: index.php?action=formFunc');
                    exit;
                }

                $model = new Funcionario();
                if ($model->inserir(
                    trim((string) ($_POST['nome'] ?? '')),
                    $cpf,
                    $telefone,
                    $endereco,
                    $_POST['codDepartamento'] ?? null,
                    $_POST['codCargo'] ?? null
                )) {
                    echo "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    </head>
                    <body>
                        <script>
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Funcionário salvo com sucesso!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'index.php?action=listaFunc';
                            });
                        </script>
                    </body>
                    </html>
                    ";
                    exit;
                } else {
                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao salvar',
                            text: 'Não foi possível cadastrar o funcionário.'
                        }).then(() => window.history.back());
                    </script>";
                }
            }
        }
        public function listarTodos() {
            // Busca funcionários com cargo e departamento relacionados.
            $model = new Funcionario();
            $listaFuncionarios = $model->listarTodos();
            include __DIR__ . "/../view/funcLista.php";
        }
        public function editar() {
            // Prepara o funcionário e as opções dos selects para edição.
            $funcionario = (new Funcionario())->buscarPorId($_GET['id'] ?? null);
            if (!$funcionario) { header('Location: index.php?action=listaFunc'); exit; }
            $listaCargo = (new Cargo())->listarTodos();
            $listaDep = (new Departamento())->listarTodos();
            include __DIR__ . "/../view/funcEditar.php";
        }
        public function atualizar() {
            // Salva os dados administrativos alterados.
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
                $telefone = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
                $endereco = $this->validarEndereco($_POST['endereco'] ?? []);
                if (strlen($cpf) !== 11 || ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) || $endereco === false) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Confira os dados',
                        'text' => 'Informe CPF, telefone e endereço nos formatos válidos.'
                    ];
                    header('Location: index.php?action=editarFunc&id=' . rawurlencode((string) ($_POST['funcional'] ?? '')));
                    exit;
                }

                $sucesso = (new Funcionario())->atualizar(
                    $_POST['funcional'],
                    trim((string) ($_POST['nome'] ?? '')),
                    $cpf,
                    $telefone,
                    $endereco,
                    $_POST['codDepartamento'] ?? null,
                    $_POST['codCargo'] ?? null
                );
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Funcionário atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o funcionário.'
                ];
            }
            header('Location: index.php?action=listaFunc'); exit;
        }

        private function validarEndereco($dados) {
            if (!is_array($dados)) {
                return false;
            }

            $endereco = [
                'cep' => preg_replace('/\D/', '', (string) ($dados['cep'] ?? '')),
                'logradouro' => trim((string) ($dados['logradouro'] ?? '')),
                'numero' => trim((string) ($dados['numero'] ?? '')),
                'complemento' => trim((string) ($dados['complemento'] ?? '')),
                'bairro' => trim((string) ($dados['bairro'] ?? '')),
                'cidade' => trim((string) ($dados['cidade'] ?? '')),
                'uf' => strtoupper(trim((string) ($dados['uf'] ?? ''))),
            ];

            if (strlen($endereco['cep']) !== 8
                || $endereco['logradouro'] === '' || strlen($endereco['logradouro']) > 150
                || $endereco['numero'] === '' || strlen($endereco['numero']) > 20
                || strlen($endereco['complemento']) > 100
                || $endereco['bairro'] === '' || strlen($endereco['bairro']) > 100
                || $endereco['cidade'] === '' || strlen($endereco['cidade']) > 100
                || !preg_match('/^[A-Z]{2}$/', $endereco['uf'])) {
                return false;
            }

            return $endereco;
        }
        public function excluir() {
            // Exclui apenas funcionários sem conta vinculada.
            try {
                (new Funcionario())->deletar($_GET['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Funcionário excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                // A conta vinculada precisa ser removida antes do funcionário.
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Não foi possível excluir', 'text' => 'Exclua primeiro a conta de acesso vinculada.'];
            }
            header('Location: index.php?action=listaFunc'); exit;
        }
    }
?>