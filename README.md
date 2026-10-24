## Solicitações de férias e ausências

Execute `model/banco.sql` para criar o banco `dboEmpresaMvc`, as tabelas da empresa, endereços e solicitações. Em uma instalação que já possui o banco, execute `model/migrar_endereco.sql` para criar a tabela de endereços, preservar os endereços atuais como dados legados e criar um administrador inicial caso ainda não existam contas.

### Primeiro acesso administrativo

O script cria a conta inicial de administrador vinculada ao primeiro funcionário de exemplo. Use `admin` como login e `Empresa@2026` como senha. A senha é inserida no banco como hash;

Funcionários autenticados podem enviar férias ou registrar uma ausência, acompanhar o status e cancelar pedidos ainda pendentes. Administradores acessam a mesma opção no menu para aprovar ou recusar pedidos pendentes; recusas exigem uma resposta. Períodos sobrepostos com pedidos pendentes ou aprovados são bloqueados.
