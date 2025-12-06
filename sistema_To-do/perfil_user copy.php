<?php
session_start();
include('conexao.php'); // Assumindo que este arquivo contém a variável $conexao

// 1. VERIFICAÇÃO E CARREGAMENTO DE DADOS
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$usuario = [];

// Busca os dados atuais do usuário no banco de dados
$sql = "SELECT nome, nome_social, email, idade, nome_mae, cpf, estado_civil, genero, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
} else {
    // Caso raro, mas trata se o usuário estiver logado mas não existir no BD
    session_destroy();
    header("Location: login.php?erro=usuario_nao_encontrado");
    exit;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    
    <style>
        /*
        * ARQUIVO CSS REORGANIZADO E CORRIGIDO
        */

        /* ============================
        PERFIL DO USUÁRIO (PADRÃO)
        ============================ */

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        #perfilcard-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        #perfilcard-card {
            width: 900px;
            display: flex;
            gap: 25px;
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        /* FOTO DO USUÁRIO (ALTERADA) */
        #perfilcard-left {
            /* AUMENTADO: De 30% para 35% para melhor centralização e espaço */
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Adicionado padding para centralizar visualmente */
            padding-top: 10px; 
        }

        #perfilcard-preview {
            /* AUMENTADO: De 180px para 220px */
            margin-top: 30px;
            width: 350px;
            height: 350px;
            border-radius: 15%;
            object-fit: cover;
            margin-bottom: 20px; /* Aumentado ligeiramente */
            border: 3px solid #007bff;
        }

        #perfilcard-btn-foto {
            background: #007bff;
            color: #fff;
            padding: 10px 18px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            display: block;
            text-align: center;
        }

        #perfilcard-btn-foto:hover {
            background: #0056b3;
        }

        /* O input de arquivo deve estar sempre fora da vista, mas funcional */
        #perfilcard-fotoInput {
            display: none;
        }


        /* FORMULÁRIO DO PERFIL (ALTERADO) */
        #perfilcard-form {
            /* AJUSTADO: De 70% para 65% para compensar o aumento de #perfilcard-left */
            width: 65%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        #perfilcard-titulo {
            font-size: 26px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        /* Inputs padrão do site */
        #perfilcard-form input,
        #perfilcard-form select {
            width: 100%;
            border: 1px solid #ccc;
            background: #f8f8f8;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            transition: 0.2s;
            box-sizing: border-box;
        }

        #perfilcard-form input[readonly],
        #perfilcard-form select:disabled {
            background-color: #eee;
            cursor: default;
        }

        #perfilcard-form input:focus,
        #perfilcard-form select:focus {
            border-color: #007bff;
            background: #fff;
        }
        
        /* Adiciona um estilo visual para campos required (opcional) */
        #perfilcard-form input:required:invalid:not([readonly]),
        #perfilcard-form select:required:invalid:not(:disabled) {
            border-color: #dc3545; /* Borda vermelha para campos vazios e required */
        }


        /* Labels */
        #perfilcard-form label {
            font-size: 15px;
            font-weight: 600;
            color: #444;
            margin-top: 5px;
        }

        /* --- ESTILOS PARA LINHAS HORIZONTAIS --- */
        .perfilcard-linha {
            display: flex;
            gap: 20px; /* Espaço entre as colunas */
            margin-bottom: 8px; /* Espaço abaixo da linha */
        }

        .perfilcard-linha > div {
            flex: 1; /* Garante que as duas colunas tenham o mesmo tamanho */
            display: flex;
            flex-direction: column; /* Coloca Label e Input verticalmente dentro da coluna */
        }

        /* Os inputs dentro das linhas precisam da largura completa do seu contêiner */
        .perfilcard-linha input,
        .perfilcard-linha select {
            width: 100%;
        }
        /* --- FIM: ESTILOS PARA LINHAS HORIZONTAIS --- */

        /* BOTÕES */
        #perfilcard-botoes {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 15px;
        }

        /* Botão Salvar */
        #perfilcard-btn-salvar {
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            flex: 1;
        }

        #perfilcard-btn-salvar:hover {
            background: #218838;
        }

        /* Botão Trocar Senha */
        #perfilcard-btn-senha {
            background: #ffc107;
            color: #333;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
            flex: 1;
            text-align: center;
        }

        #perfilcard-btn-senha:hover {
            background: #e0a800;
            color: #000;
        }

        /* Mensagem de Feedback (mantida no CSS, mas não usada no HTML) */
        .mensagem {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            text-align: center;
        }


        /* RESPONSIVIDADE */
        @media (max-width: 900px) {

            #perfilcard-card {
                flex-direction: column;
                width: 95%;
            }

            #perfilcard-left {
                width: 100%;
                /* Garantir que a foto fique centralizada no topo da tela */
                align-items: center; 
            }

            #perfilcard-form {
                width: 100%;
            }

            #perfilcard-botoes {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Ajuste de responsividade para as novas linhas horizontais */
        @media (max-width: 600px) {
            .perfilcard-linha {
                flex-direction: column; /* Volta para a coluna vertical em telas pequenas */
                gap: 0;
            }
        }
    </style>
</head>
<body>

    <div id="perfilcard-container">
        <div id="perfilcard-card">
            
            <?php
            // **IMPORTANTE**: O bloco de exibição da mensagem via PHP foi removido.
            // A mensagem agora será exibida via JavaScript 'alert'.
            
            // 2. Define o caminho da foto de perfil
            $caminho_foto = 'img/perfil/' . ($usuario['foto_perfil'] ?? 'default.png');
            if (empty($usuario['foto_perfil']) || !file_exists($caminho_foto)) {
                $caminho_foto = 'img/perfil/default.png'; // Garante uma imagem padrão
            }
            ?>

            <div id="perfilcard-left">
                <img src="<?= htmlspecialchars($caminho_foto) ?>" alt="Foto de Perfil" id="perfilcard-preview">

                <input type="file" name="foto_perfil" accept="image/*" id="perfilcard-fotoInput">

                <label for="perfilcard-fotoInput" id="perfilcard-btn-foto" style="display: none;">
                    Clique para Alterar Foto
                </label>
            </div>

            <form id="perfilcard-form" action="salvar_perfil.php" method="POST" enctype="multipart/form-data">
                
                <h2 id="perfilcard-titulo">Meu Perfil</h2>
                
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id_usuario) ?>">
                <input type="hidden" name="foto_perfil_atual" value="<?= htmlspecialchars($usuario['foto_perfil'] ?? '') ?>">
                
                <label for="perfilcard-nome">Nome Completo:</label>
                <input type="text" name="nome" id="perfilcard-nome"
                        value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" readonly> 
                
                <div class="perfilcard-linha">
                    <div>
                        <label for="perfilcard-email">Email:</label>
                        <input type="email" name="email" id="perfilcard-email"
                                value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" readonly>
                    </div>
                    <div>
                        <label for="perfilcard-nome-social">Nome Social:</label>
                        <input type="text" name="nome_social" id="perfilcard-nome-social"
                                value="<?= htmlspecialchars($usuario['nome_social'] ?? '') ?>" readonly>
                    </div>
                </div>

                <label for="perfilcard-nome-mae">Nome da Mãe:</label>
                <input type="text" name="nome_mae" id="perfilcard-nome-mae"
                        value="<?= htmlspecialchars($usuario['nome_mae'] ?? '') ?>" readonly>
                
                <div class="perfilcard-linha">
                    <div>
                        <label for="perfilcard-idade">Data de Nascimento:</label>
                        <input type="date" name="idade" id="perfilcard-idade"
                                value="<?= htmlspecialchars($usuario['idade'] ?? '') ?>" readonly>
                    </div>
                    <div>
                        <label for="perfilcard-cpf">CPF:</label>
                        <input type="text" name="cpf" id="perfilcard-cpf"
                                value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>" readonly oninput="
                        this.value = this.value
                            .replace(/\D/g,'')
                            .replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d{2})$/, '$1-$2');">
                        <?php if (!empty($erroCPF)): ?>
                        <p><?= $erroCPF ?></p><?php endif; ?>  
                    </div>
                </div>

                <div class="perfilcard-linha">
                    <div>
                        <label for="perfilcard-estado-civil">Estado Civil:</label>
                        <select name="estado_civil" id="perfilcard-estado-civil" disabled>
                            <option value="">Selecione</option>
                            <option value="solteiro" <?= ($usuario['estado_civil'] ?? '') == 'solteiro' ? 'selected' : '' ?>>Solteiro(a)</option>
                            <option value="casado" <?= ($usuario['estado_civil'] ?? '') == 'casado' ? 'selected' : '' ?>>Casado(a)</option>
                            <option value="divorciado" <?= ($usuario['estado_civil'] ?? '') == 'divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                            <option value="viuvo" <?= ($usuario['estado_civil'] ?? '') == 'viuvo' ? 'selected' : '' ?>>Viúvo(a)</option>
                        </select>
                    </div>
                    <div>
                        <label for="perfilcard-genero">Gênero:</label>
                        <select name="genero" id="perfilcard-genero" disabled>
                            <option value="">Selecione</option>
                            <option value="masculino" <?= ($usuario['genero'] ?? '') == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="feminino" <?= ($usuario['genero'] ?? '') == 'feminino' ? 'selected' : '' ?>>Feminino</option>
                            <option value="outro" <?= ($usuario['genero'] ?? '') == 'outro' ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>
                </div>
                
                <div id="perfilcard-botoes">
                    <button type="button" id="perfilcard-btn-salvar" onclick="alternarModoEdicao()">Alterar Dados</button> 
                    <a href="trocar_senha.php" id="perfilcard-btn-senha">Trocar Senha</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        let modoEdicao = false; // Estado inicial: Visualização (false)

        function alternarModoEdicao() {
            const form = document.getElementById('perfilcard-form');
            const btnSalvar = document.getElementById('perfilcard-btn-salvar');
            const btnFoto = document.getElementById('perfilcard-btn-foto');
            const fotoInput = document.getElementById('perfilcard-fotoInput');
            
            // Elementos para alternar (inputs e selects)
            const inputsParaAlternar = form.querySelectorAll(
                'input[type="text"], input[type="email"], input[type="date"], select, input[name="cpf"]'
            );

            if (modoEdicao) {
                // MODO SALVAR (Ação: Enviar dados)
                
                // 1. GARANTE QUE TODOS OS CAMPOS ESTEJAM COM O ATRIBUTO 'required'
                inputsParaAlternar.forEach(element => {
                    // Impede que o campo de EMAIL seja editado E seja required
                    if (element.id !== 'perfilcard-email') { 
                         element.setAttribute('required', '');
                    }
                });
                
                // 2. Mudar o tipo do botão para 'submit' para acionar a validação HTML5
                btnSalvar.type = 'submit'; 
                
            } else {
                // MODO ALTERAR DADOS (Ação: Habilitar edição)

                btnSalvar.textContent = 'Salvar Dados';
                btnFoto.style.display = 'block'; // Mostra o botão de alteração de foto
                
                // CRÍTICO: Move o input de arquivo (que estava fora) para DENTRO do FORMULÁRIO
                form.prepend(fotoInput); 
                
                // Habilita e remove readonly/disabled de todos os campos
                inputsParaAlternar.forEach(element => {
                    // Impede que o campo de EMAIL seja editado
                    if (element.id === 'perfilcard-email') {
                         return; // Mantemos o email readonly/não editável
                    }
                    
                    if (element.tagName === 'SELECT') {
                        element.disabled = false;
                    } else {
                        element.removeAttribute('readonly');
                    }
                    
                    // Remove o 'required' neste modo (ele só é necessário no modo SALVAR)
                    element.removeAttribute('required');
                });

                modoEdicao = true; // Define o estado para Edição
                
                // O tipo é 'button' para que o próximo clique acione o MODO SALVAR
                btnSalvar.type = 'button'; 
            }
        }
        
        // Pré-visualização da imagem ao selecionar um arquivo
        document.getElementById('perfilcard-fotoInput').addEventListener('change', function(e) {
            const fotoDisplay = document.getElementById('perfilcard-preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    fotoDisplay.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // 🚨 NOVO CÓDIGO: Verifica o parâmetro de URL e exibe o alerta
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('salvo') && urlParams.get('salvo') === 'sucesso') {
                // Exibe o alerta de sucesso
                alert("✅ Dados do perfil atualizados com sucesso!"); 
                
                // Limpa o parâmetro da URL usando History API
                history.pushState({}, document.title, window.location.pathname);
            }
        });

    </script>
</body>
</html>