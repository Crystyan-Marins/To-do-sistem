<?php
// Arquivo: salvar_perfil.php
session_start();
include('conexao.php'); 

// 1. VERIFICAÇÃO DE SESSÃO E ID
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. RECEBIMENTO E SANITIZAÇÃO DOS DADOS DO FORMULÁRIO
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$nome_social = filter_input(INPUT_POST, 'nome_social', FILTER_SANITIZE_STRING);
// O email não é alterado, mas é bom sanitizá-lo por precaução.
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL); 
$idade = filter_input(INPUT_POST, 'idade', FILTER_SANITIZE_STRING); // Data
$nome_mae = filter_input(INPUT_POST, 'nome_mae', FILTER_SANITIZE_STRING);
$cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
$estado_civil = filter_input(INPUT_POST, 'estado_civil', FILTER_SANITIZE_STRING);
$genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING);
$foto_perfil_atual = filter_input(INPUT_POST, 'foto_perfil_atual', FILTER_SANITIZE_STRING);

$mensagem_erro = '';
$upload_sucesso = false;
$novo_nome_foto = $foto_perfil_atual; // Mantém a foto atual por padrão

// 3. VALIDAÇÃO BÁSICA
if (empty($nome) || empty($email) || empty($idade) || empty($cpf)) {
    $_SESSION['mensagem'] = "erro|Todos os campos obrigatórios (Nome, Email, Data de Nascimento, CPF) devem ser preenchidos.";
    header("Location: perfil_user.php");
    exit;
}

// Limpeza e validação do CPF (apenas dígitos)
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
if (strlen($cpf_limpo) !== 11) {
    $_SESSION['mensagem'] = "erro|O CPF informado é inválido.";
    header("Location: perfil_user.php");
    exit;
}

// 4. PROCESSAMENTO DO UPLOAD DE IMAGEM
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $diretorio_destino = 'img/perfil/';
    $arquivo_temporario = $_FILES['foto_perfil']['tmp_name'];
    $tipo_arquivo = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));

    // Verifica o tipo de arquivo
    if (!in_array($tipo_arquivo, ['jpg', 'jpeg', 'png', 'gif'])) {
        $mensagem_erro = "Formato de arquivo não suportado. Use JPG, JPEG, PNG ou GIF.";
    } else {
        // Gera um nome único para o novo arquivo (segurança)
        $novo_nome_foto = "perfil_" . $id_usuario . "_" . time() . "." . $tipo_arquivo;
        $caminho_destino = $diretorio_destino . $novo_nome_foto;

        if (move_uploaded_file($arquivo_temporario, $caminho_destino)) {
            $upload_sucesso = true;
            
            // Opcional: Excluir a foto antiga, se não for a padrão 'default.png'
            if (!empty($foto_perfil_atual) && $foto_perfil_atual !== 'default.png') {
                $caminho_antigo = $diretorio_destino . $foto_perfil_atual;
                if (file_exists($caminho_antigo)) {
                    unlink($caminho_antigo);
                }
            }
        } else {
            $mensagem_erro = "Erro ao mover o arquivo de upload.";
        }
    }
}

// 5. PREPARAÇÃO E EXECUÇÃO DO UPDATE NO BANCO DE DADOS
if (empty($mensagem_erro)) {
    $sql_update = "UPDATE usuarios SET 
                    nome = ?, nome_social = ?, idade = ?, nome_mae = ?, 
                    cpf = ?, estado_civil = ?, genero = ?, foto_perfil = ?
                    WHERE id = ?";

    $stmt_update = $conexao->prepare($sql_update);

    if ($stmt_update) {
        $stmt_update->bind_param(
            "ssssssssi", 
            $nome, $nome_social, $idade, $nome_mae, 
            $cpf_limpo, $estado_civil, $genero, $novo_nome_foto, $id_usuario
        );

        if ($stmt_update->execute()) {
            $_SESSION['mensagem'] = "sucesso|Perfil atualizado com sucesso!";
            // Atualiza a sessão com a nova foto, caso tenha sido alterada
            $_SESSION['foto_perfil'] = $novo_nome_foto; 
        } else {
            $_SESSION['mensagem'] = "erro|Erro ao atualizar o perfil: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $_SESSION['mensagem'] = "erro|Erro na preparação da consulta: " . $conexao->error;
    }
} else {
    // Se houve erro no upload, notifica e não tenta atualizar o BD
    $_SESSION['mensagem'] = "erro|Erro no upload da foto. " . $mensagem_erro;
}

$conexao->close();
header("Location: perfil_user.php");
exit;
?>