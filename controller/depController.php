<?php
// Controller responsável pelo fluxo de departamentos.
    require_once __DIR__ . '/../model/departamentoModel.php';
    class DepController {
        public function salvar() {
            // Valida o método e envia o novo nome ao model.
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $model = new Departamento();
                if ($model->inserir($_POST['nome'])) {
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
                                    text: 'Departamento salvo com sucesso!',
                                    icon: 'success',
                                    draggable: true,
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    window.location.href = 'index.php?action=listaDep';
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
            // Busca departamentos e carrega a view de relatório.
            $model = new Departamento();
            $listaDepartamentos = $model->listarTodos();
            include __DIR__ . "/../view/depLista.php";
        }
        public function editar() {
            // Carrega um departamento no formulário de edição.
            $departamento = (new Departamento())->buscarPorId($_GET['id'] ?? null);
            if (!$departamento) { header('Location: index.php?action=listaDep'); exit; }
            include __DIR__ . "/../view/depEditar.php";
        }
        public function atualizar() {
            // Persiste o novo nome do departamento.
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $sucesso = (new Departamento())->atualizar($_POST['id'], $_POST['nome']);
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Departamento atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o departamento.'
                ];
            }
            header('Location: index.php?action=listaDep'); exit;
        }
        public function excluir() {
            // Remove o departamento quando não há dependências.
            try {
                (new Departamento())->deletar($_GET['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Departamento excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                // A chave estrangeira impede a exclusão de departamentos utilizados.
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Não foi possível excluir', 'text' => 'Existem funcionários vinculados a este departamento.'];
            }
            header('Location: index.php?action=listaDep'); exit;
        }
    }
?>