USE dboEmpresaMvc;

ALTER TABLE dboFuncionario MODIFY endereco VARCHAR(500) NOT NULL;

CREATE TABLE IF NOT EXISTS dboEndereco (
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

INSERT IGNORE INTO dboEndereco (funcional, logradouro)
SELECT funcional, endereco
FROM dboFuncionario
WHERE endereco <> '';

INSERT INTO dboConta (funcional, login, senha, foto, nivelAcesso)
SELECT funcionario.funcional, 'admin', '$2y$10$KRTkr9wQY9qd.lnOTbfKfuP2mLzpaGck8Cu.BP5/GoCpSeXl9SIv2', 'default.jpg', 0
FROM dboFuncionario AS funcionario
WHERE funcionario.funcional = 1
    AND NOT EXISTS (SELECT 1 FROM dboConta);