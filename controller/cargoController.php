<?php
// Controller que recebe requisições e chama o model de cargos.
    require_once __DIR__ . '/../model/cargoModel.php';
    class CargoController {
        public function salvar() {
            // Lê o formulário e cria um cargo no banco.
            if($_SERVER['REQUEST_METHOD'] == "POST") {
                $salario = $this->normalizarSalario($_POST['salario'] ?? '');
                if ($salario === false) {
                    $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Salário inválido', 'text' => 'Informe um valor válido, como R$ 1.250,00.'];
                    header('Location: index.php?action=formCargo');
                    exit;
                }
                $model = new Cargo();
                if ($model->inserir($_POST['nome'], $salario)) {
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
                                text: 'Cargo salvo com sucesso!',
                                icon: 'success',
                                draggable: true,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                window.location.href = 'index.php?action=listaCargo';
                            });
                        </script>
                    </body>
                    </html>
                    ";
                    exit;               
                }
            }
        }
        public function listar() {
            // Busca os cargos e envia os dados para a tabela.
            $model = new Cargo();
            $listaCargos = $model->listarTodos();
            include __DIR__."/../view/cargoLista.php";
        }
        public function editar() {
            // Busca um cargo e abre o formulário preenchido.
            $cargo = (new Cargo())->buscarPorId($_GET['id'] ?? null);
            if (!$cargo) { header('Location: index.php?action=listaCargo'); exit; }
            include __DIR__."/../view/cargoEditar.php";
        }
        public function atualizar() {
            // Grava os dados editados e retorna à listagem.
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $salario = $this->normalizarSalario($_POST['salario'] ?? '');
                if ($salario === false) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Salário inválido',
                        'text' => 'O salário não pode ser negativo.'
                    ];

                    header('Location: index.php?action=listaCargo');
                    exit;
                }
                $sucesso = (new Cargo())->atualizar($_POST['id'], $_POST['nome'], $salario);
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Cargo atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o cargo.'
                ];
            }
            header('Location: index.php?action=listaCargo'); exit;
        }
        public function excluir() {
            // Remove o cargo, tratando vínculos existentes.
            try {
                (new Cargo())->deletar($_GET['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Cargo excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                // A chave estrangeira impede a exclusão de cargos utilizados.
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Não foi possível excluir', 'text' => 'Existem funcionários vinculados a este cargo.'];
            }
            header('Location: index.php?action=listaCargo'); exit;
        }

        private function normalizarSalario($valor) {
            $valor = preg_replace('/[^0-9,.-]/', '', (string) $valor);
            if (strpos($valor, ',') !== false) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            }
            if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valor)) {
                return false;
            }
            return $valor;
        }
    }
?>