<?php 
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "To_do";

$conexao = new mysqli($servidor, $usuario, $senha);

if ($conexao->connect_error) {
    die("Erro na conexão com o servidor MySQL: " . $conexao->connect_error);
}

$conexao->query("CREATE DATABASE IF NOT EXISTS $banco");
$conexao->select_db($banco);


// Tabela de usuários atualizada
$conexao->query("CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  nome_social VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nascimento DATE NOT NULL,
  nome_mae VARCHAR(100) NOT NULL,
  cpf VARCHAR(20) NOT NULL,
  estado_civil VARCHAR(20) NOT NULL,
  genero VARCHAR(30) NOT NULL,
  foto_perfil VARCHAR(255) NULL,
  tipo ENUM('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Tabela de tarefas permanece igual
$conexao->query("CREATE TABLE IF NOT EXISTS tarefas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  titulo VARCHAR(100) NOT NULL,
  descricao VARCHAR(255),
  data_tarefa DATE NOT NULL,
  hora_atividade TIME NOT NULL,
  status ENUM('pendente', 'concluida') DEFAULT 'pendente',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Dados do admin
$email_admin = 'admin@jarvis.com';
$senha_admin = password_hash('admin123', PASSWORD_DEFAULT); // Senha padrão

// Verifica se já existe admin
$result = $conexao->query("SELECT id FROM usuarios WHERE email='$email_admin'");
if ($result->num_rows == 0) {
    $conexao->query("
        INSERT INTO usuarios (
            nome, nome_social, email, senha, nascimento, nome_mae, cpf, estado_civil, genero, foto_perfil, tipo
        ) VALUES (
            'Administrador', 'Administrador', '$email_admin', '$senha_admin', '2000-01-01', 'N/A', 'N/A', 'N/A', 'N/A', NULL, 'admin'
        )
    ");
}