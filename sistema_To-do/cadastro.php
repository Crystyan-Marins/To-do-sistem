<?php

include('conexao.php');

// Função para validar CPF
function validarCPF($cpf) {
// Limpa o CPF de qualquer máscara antes da validação e do uso
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);

    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
        if ($cpf[$t] != $digito) return false;
    }
    return true;
}
// Função para calcular a idade exata com base no dia e mês
function calcularIdade($data_nascimento) {
    // Calcula a diferença em carimbos de data/hora (timestamps)
    $data_timestamp = strtotime($data_nascimento);
    $hoje_timestamp = time();

    // Obtém a diferença em anos
    $idade = date('Y', $hoje_timestamp) - date('Y', $data_timestamp);

    // Verifica se o aniversário já ocorreu este ano
    // Se o dia/mês atual for anterior ao dia/mês de nascimento, subtrai 1 ano.
    if (
        date('m', $hoje_timestamp) < date('m', $data_timestamp) ||
        (date('m', $hoje_timestamp) == date('m', $data_timestamp) && 
         date('d', $hoje_timestamp) < date('d', $data_timestamp))
    ) {
        $idade--;
    }
    
    return $idade;
}

// ...
// Recebe o campo de data de nascimento
$nascimento = $_POST['nascimento']; 

// 1. Inclua a função calcularIdade (pode ser no topo do seu arquivo PHP)

// 2. Chame a função para validar a MAIORIDADE (18 anos)
$idade_calculada = calcularIdade($nascimento);

if ($idade_calculada < 18) {
    echo "<script>
              alert('Você deve ter 18 anos ou mais para se cadastrar. Sua idade calculada é: {$idade_calculada} anos.');
              window.location.href = 'cadastro.php'; 
          </script>";
    exit;
}

// PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $nome_social = $_POST['nome_social'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $senha_confirm = $_POST['senha_confirm'];
    $nome_mae = $_POST['nome_mae'];
    $nascimento = $_POST['nascimento'];
    $cpf = $_POST['cpf'];
    $estado_civil = $_POST['estado_civil'];
    $genero = $_POST['genero'];

    // 1️⃣ Valida senha
    if ($senha !== $senha_confirm) {
        echo "<script>
                alert('As senhas não coincidem!');
                window.location.href = 'cadastro.php';
              </script>";
        exit;
    }

    // 2️⃣ Valida CPF
    if (!validarCPF($cpf)) {
        echo "<script>
                alert('CPF inválido!');
                window.location.href = 'cadastro.php';
              </script>";
        exit;
    }

    // 3️⃣ Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // 4️⃣ Insere no banco
    $stmt = $conexao->prepare("
        INSERT INTO usuarios 
        (nome, nome_social, email, senha, nome_mae, nascimento, cpf, estado_civil, genero) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssssssss", 
        $nome, $nome_social, $email, $senha_hash, $nome_mae, $nascimento, $cpf, $estado_civil, $genero
    );

if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        // Correção do tratamento de erro: Checa se é erro de chave duplicada (MySQL 1062)
        if ($stmt->errno == 1062) {
            $msg = "Erro: E-mail ou CPF já cadastrado no sistema!";
        } else {
            // Em ambiente de produção, não mostre o erro SQL ao usuário
            $msg = "Erro interno ao cadastrar. Tente novamente."; 
            // Para debug, descomente: $msg .= " SQL Error: " . $stmt->error;
        }
        
        echo "<script>
                  alert('{$msg}');
                  window.location.href = 'cadastro.php'; // Redireciona para o CADASTRO, não login
              </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html">
    <meta charset="UTF-8">
    <title id="user-page-title-cadastro">Cadastro</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
    <link rel="stylesheet" id="user-theme-style" href="css/style.css">
    <?php
    // Inclui o cabeçalho padrão da página de usuário.
    include 'header.php';
    ?>
<body id="user-registration-page"> <div id="user-main-container-cadastro" class="user-container user-registration__wrapper">
        <h2 class="user-registration__heading">Cadastro de Usuário</h2>
        <form id="user-registration-form" method="POST" class="user-registration__form">
            <input type="text" name="nome" placeholder="Nome completo" required 
                   class="user-form__input user-form__input--nome" id="user-input-nome">
            <input type="text" name="nome_social" placeholder="Nome Social" required 
                   class="user-form__input user-form__input--nome-social" id="user-input-nome-social">
            <input type="email" name="email" placeholder="E-mail" required 
                   class="user-form__input user-form__input--email" id="user-input-email">
            
            <div class="div--senha">

            <input type="password" name="senha" placeholder="Senha" required 
                   class="user-form__input user-form__input--senha" id="uesr-input-senha">
            <input type="password" name="senha_confirm" placeholder="Repetir senha" required 
                   class="user-form__input user-form__input--senha-confirm" id="user-input-senha-confirm">
            </div>
            <div class="div--cpf--nasc">
            <input type="date" name="nascimento" placeholder="nascimento" required 
                   class="user-form__input user-form__input--nascimento" id="user-input-nascimento">

            <input type="text" id="user-input-cpf" class="user-form__input user-form__input--cpf" placeholder="CPF (Somente número)" required  name="cpf" maxlength="14" oninput="
            this.value = this.value
                .replace(/\D/g,'')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{2})$/, '$1-$2');">
            <?php if (!empty($erroCPF)): ?>
            <p><?= $erroCPF ?></p><?php endif; ?>
            </div>
            <input type="text" name="nome_mae" placeholder="Nome da mãe" required 
                   class="user-form__input user-form__input--nome-mae" id="user-input-nome-mae">

            <div class="div-select">
            <div class="user-form-group user-form-group--genero" id="user-group-genero">
                
                <select name="genero" required class="user-form-group__select" id="user-select-genero">
                    <option value="" class="user-form-group__option user-form-group__option--placeholder">Gênero:</option>
                    <option value="masculino" class="user-form-group__option user-form-group__option--masculino">Masculino</option>
                    <option value="feminino" class="user-form-group__option user-form-group__option--feminino">Feminino</option>
                    <option value="outro" class="user-form-group__option user-form-group__option--outro">Outro</option>
                </select>
            </div>

            <div class="user-form-group user-form-group--estado-civil" id="user-group-estado-civil">

                <select name="estado_civil" required class="user-form-group__select" id="user-select-estado-civil">
                    <option value="" class="user-form-group__option user-form-group__option--placeholder">Estado civil:</option>
                    <option value="solteiro" class="user-form-group__option user-form-group__option--solteiro">Solteiro(a)</option>
                    <option value="casado" class="user-form-group__option user-form-group__option--casado">Casado(a)</option>
                    <option value="divorciado" class="user-form-group__option user-form-group__option--divorciado">Divorciado(a)</option>
                    <option value="viuvo" class="user-form-group__option user-form-group__option--viuvo">Viúvo(a)</option>
                </select>
            </div>
            </div>
            <div class="div--button">
            <button id="user-btn-limpar-cadastro" type="button" onclick="limparFormulario('user-registration-form')" 
                        class="user-button user-button--secondary user-registration__btn--limpar">Limpar</button>

            <button id="user-btn-submit-cadastro" type="submit" 
                        class="user-button user-button--primary user-registration__btn--cadastrar">Cadastrar</button>
            </div>

            <p class="user-registration__login-link-text">
                Já tem conta? 
                <a href="login.php" class="user-registration__login-link" id="user-link-login-page">Faça login</a>
            </p>
        </form>
    </div>
</body>
<?php
// Inclui o rodapé padrão da página de usuário.
include 'footer.php';
?>
</html>

<script>
// Comentário: Função JavaScript para limpar todos os campos de um formulário específico.
function limparFormulario(formId) {
    // Comentário: Busca o elemento formulário pelo ID fornecido.
    const form = document.getElementById(formId);
    // Comentário: Sai da função se o formulário não for encontrado.
    if (!form) return;

    // Comentário: Converte a coleção de elementos do formulário em um Array para iteração.
    Array.from(form.elements).forEach(element => {
        // Comentário: Usa switch para tratar diferentes tipos de campos de formulário.
        switch(element.type) {
            // Comentário: Limpa o valor de campos de texto, e-mail, senha e números.
            case 'text':
            case 'email':
            case 'password':
            case 'number':
            case 'date':
            case 'time':
                element.value = '';
                break;
            // Comentário: Redefine o índice selecionado para a primeira opção (geralmente "Selecione").
            case 'select-one':
            case 'select-multiple':
                element.selectedIndex = 0;
                break;
            // Comentário: Desmarca checkboxes e radio buttons.
            case 'checkbox':
            case 'radio':
                element.checked = false;
                break;
        }
    });
}
// NOTA: A chamada da função no botão 'Limpar' foi ajustada para usar o novo ID do formulário ('user-registration-form').
</script>

