CREATE DATABASE dboEmpresaMvc;
USE dboEmpresaMvc;

CREATE TABLE dboCargo (
    codCargo INT AUTO_INCREMENT PRIMARY KEY,
    nomeCargo VARCHAR(100) NOT NULL UNIQUE,
    salario DECIMAL(10, 2) NOT NULL
);

CREATE TABLE dboDepartamento (
    codDepartamento INT AUTO_INCREMENT PRIMARY KEY,
    nomeDepartamento VARCHAR(50) NOT NULL
);

CREATE TABLE dboFuncionario (
    funcional INT AUTO_INCREMENT PRIMARY KEY,
    cpf CHAR(11) NOT NULL UNIQUE,
    nome VARCHAR(40) NOT NULL,
    telefone CHAR(15),
    endereco VARCHAR(500) NOT NULL,
    codDepartamento INT NOT NULL,
    codCargo INT NOT NULL,
    CONSTRAINT fkCodFun
        FOREIGN KEY (codCargo)
        REFERENCES dboCargo(codCargo),
    CONSTRAINT fkCodDepto
        FOREIGN KEY (codDepartamento)
        REFERENCES dboDepartamento(codDepartamento)
);

CREATE TABLE dboEndereco (
    funcional INT PRIMARY KEY,
    cep CHAR(8) NULL,
    logradouro VARCHAR(150) NOT NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    uf CHAR(2) NULL,
    CONSTRAINT fkEnderecoFuncionario
        FOREIGN KEY (funcional)
        REFERENCES dboFuncionario(funcional)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dboConta (
    login VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NULL,
    nivelAcesso TINYINT NOT NULL DEFAULT 1
        COMMENT '0=ADM, 1=FUNCIONARIO',
    funcional INT PRIMARY KEY,
    CONSTRAINT fkFunc
        FOREIGN KEY (funcional)
        REFERENCES dboFuncionario(funcional)
);

CREATE TABLE dboSolicitacaoAusencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcional INT NOT NULL,
    solicitanteNome VARCHAR(40) NOT NULL,
    tipo ENUM('ferias', 'ausencia') NOT NULL,
    dataInicio DATE NOT NULL,
    dataFim DATE NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    status ENUM('pendente', 'aprovada', 'recusada', 'cancelada') NOT NULL DEFAULT 'pendente',
    respostaAdmin VARCHAR(500) NULL,
    solicitadoEm TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    avaliadoPor INT NULL,
    avaliadorNome VARCHAR(40) NULL,
    avaliadoEm TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_solicitacao_funcionario (funcional, solicitadoEm),
    INDEX idx_solicitacao_status_data (status, dataInicio, dataFim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('Administrativo');
INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('Contabilidade');
INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('TI');
INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('RH');
INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('Marketing');
INSERT INTO dboDepartamento (nomeDepartamento) VALUES ('Atendimento');
-- SELECT * FROM dboDepartamento;

INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Gerente', 8500.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Coordenador', 6500.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Analista', 4200.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Assistente', 2800.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Estagiário', 1500.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Auxiliar Administrativo', 2200.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Desenvolvedor Junior', 3500.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Desenvolvedor Pleno', 6000.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Desenvolvedor Senior', 9000.00);
INSERT INTO dboCargo (nomeCargo, salario) VALUES ('Analista de RH', 4000.00);
-- SELECT * FROM dboCargo;

INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('João Silva', '11122233344', '11987654321', 'Rua das Flores, 123 – SP', 1, 1);
INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('Maria Oliveira', '22233344455', '21998765432', 'Av. Paulista, 1000 – SP', 2, 3);
INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('Lucas Ferreira', '33344455566', '31987658765', 'Rua Minas, 456 – MG', 3, 4);
INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('Ana Costa', '44455566677', '41996543210', 'Rua Paraná, 789 – PR', 4, 2);
INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('Pedro Santos', '55566677788', '51987651122', 'Av. Brasil, 321 – RS', 6, 5);
INSERT INTO dboFuncionario (nome, cpf, telefone, endereco, codDepartamento, codCargo) VALUES ('Carla Mendes', '66677788899', '61999887766', 'Rua Goiás, 654 – DF', 5, 3);
INSERT INTO dboConta (funcional, login, senha, foto, nivelAcesso)
VALUES (1, 'admin', '$2y$10$KRTkr9wQY9qd.lnOTbfKfuP2mLzpaGck8Cu.BP5/GoCpSeXl9SIv2', 'default.jpg', 0);
INSERT INTO dboEndereco (funcional, logradouro)
SELECT funcional, endereco FROM dboFuncionario;
-- SELECT * FROM dboFuncionario;

-- DROP DATABASE dboEmpresaMvc;
-- UPDATE dboConta SET nivelAcesso = 0 WHERE funcional = 1;