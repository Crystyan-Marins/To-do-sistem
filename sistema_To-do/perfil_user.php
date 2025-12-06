<?php
// Arquivo: perfil_user.php
session_start();
include('conexao.php'); 

// 1. VERIFICAÇÃO E CARREGAMENTO DE DADOS DO USUÁRIO
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$usuario = [];
// $total_criadas = 0; // COMENTADO: Variável de Gráfico
// $total_concluidas = 0; // COMENTADO: Variável de Gráfico

// Busca os dados atuais do usuário no banco de dados (usando Prepared Statements)
$sql_perfil = "SELECT nome, nome_social, email, idade, nome_mae, cpf, estado_civil, genero, foto_perfil FROM usuarios WHERE id = ?";
$stmt_perfil = $conexao->prepare($sql_perfil);
$stmt_perfil->bind_param("i", $id_usuario);
$stmt_perfil->execute();
$resultado_perfil = $stmt_perfil->get_result();

if ($resultado_perfil->num_rows > 0) {
    $usuario = $resultado_perfil->fetch_assoc();
} else {
    // Trata se o usuário estiver logado mas não existir no BD
    session_destroy();
    header("Location: login.php?erro=usuario_nao_encontrado");
    exit;
}
$stmt_perfil->close();

// 2. CARREGAMENTO DOS DADOS DO GRÁFICO (Tarefas)
/* COMENTADO: Bloco inteiro de SQL e processamento de dados para o gráfico.
$sql_tarefas = "
    SELECT
        COUNT(id) AS TotalCriadas,
        SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) AS TotalConcluidas
    FROM
        tarefas
    WHERE
        id_usuario = ?
";

$stmt_tarefas = $conexao->prepare($sql_tarefas);

if ($stmt_tarefas) {
    $stmt_tarefas->bind_param("i", $id_usuario);
    $stmt_tarefas->execute();
    $resultado_tarefas = $stmt_tarefas->get_result();

    if ($resultado_tarefas->num_rows > 0) {
        $dados_relatorio = $resultado_tarefas->fetch_assoc();
        $total_criadas = (int)$dados_relatorio['TotalCriadas'];
        $total_concluidas = (int)$dados_relatorio['TotalConcluidas'];
    }
    $stmt_tarefas->close();
}
*/

// 3. TRATAMENTO DE MENSAGENS DA SESSÃO (Para exibir alerta após salvar)
$mensagem_sucesso = '';
if (isset($_SESSION['mensagem'])) {
    if (strpos($_SESSION['mensagem'], 'sucesso') !== false) {
        $mensagem_sucesso = $_SESSION['mensagem'];
    }
    unset($_SESSION['mensagem']); 
}
// --------------------------------------------------------------------------
// INCLUSÃO DO HEADER
// --------------------------------------------------------------------------
// NOTA: header.php e footer.php são placeholders. 
// Certifique-se de que eles existam ou comente as linhas se não os usar.
include 'header.php'; 

// Define o caminho da foto de perfil para uso no HTML
$caminho_foto = 'img/perfil/' . ($usuario['foto_perfil'] ?? 'default.png');
if (empty($usuario['foto_perfil']) || !file_exists($caminho_foto)) {
    $caminho_foto = 'img/perfil/default.png'; // Garante uma imagem padrão
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    
    <style>
        /* Estilos CSS (inalterados, apenas para visualização) */
        body { font-family: Arial, sans-serif; background-color: #f3f1ff; padding-top: 20px; }
        #perfilcard-container { width: 100%; display: flex; justify-content: center; margin-top: 40px; }
        #perfilcard-card { width: 900px; display: flex; gap: 25px; background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        #perfilcard-left { width: 50%; display: flex; flex-direction: column; align-items: center; padding-top: 10px; }
        #perfilcard-preview { margin-top: 30px; width: 350px; height: 350px; border-radius: 15%; object-fit: cover; margin-bottom: 20px; border: 3px solid #6a0dad; }
        #perfilcard-btn-foto { background: #6a0dad; color: #fff; padding: 10px 18px; border-radius: 7px; cursor: pointer; font-weight: bold; transition: 0.3s; display: block; text-align: center; }
        #perfilcard-btn-foto:hover { background: #580ea1; }
        #perfilcard-fotoInput { display: none; }
        #perfilcard-form { width: 65%; display: flex; flex-direction: column; gap: 8px; }
        #perfilcard-titulo { font-size: 26px; font-weight: 700; color: #4b0082; margin-bottom: 15px; }
        #perfilcard-form input, #perfilcard-form select { width: 100%; border: 1px solid #ccc; background: #f8f8f8; padding: 10px; font-size: 16px; border-radius: 8px; transition: 0.2s; box-sizing: border-box; }
        #perfilcard-form input[readonly], #perfilcard-form select:disabled { background-color: #eee; cursor: default; }
        #perfilcard-form input:focus, #perfilcard-form select:focus { border-color: #6a0dad; background: #fff; }
        #perfilcard-form label { font-size: 15px; font-weight: 600; color: #444; margin-top: 5px; }
        .perfilcard-linha { display: flex; gap: 20px; margin-bottom: 8px; }
        .perfilcard-linha > div { flex: 1; display: flex; flex-direction: column; }
        #perfilcard-botoes { display: flex; justify-content: space-between; margin-top: 20px; gap: 15px; }
        #perfilcard-btn-salvar { background: #6a0dad; color: white; padding: 12px 20px; border-radius: 8px; border: none; font-size: 16px; cursor: pointer; font-weight: bold; transition: 0.3s; flex: 1; }
        #perfilcard-btn-salvar:hover { background: #580ea1; }
        #perfilcard-btn-senha { background: #f8f4ff; color: #4b0082; padding: 12px 20px; border-radius: 8px; font-weight: bold; text-decoration: none; transition: 0.3s; flex: 1; text-align: center; border: 1px solid #6a0dad; }
        #perfilcard-btn-senha:hover { background: #f3f1ff; color: #4b0082; }
        /* COMENTADO: Estilos CSS do Container do Gráfico */
        /*
        #grafico-container { width: 900px; margin: 25px auto 50px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); padding: 25px; display: flex; flex-direction: column; }
        #grafico-titulo { font-size: 24px; font-weight: 700; color: #4b0082; margin-bottom: 20px; text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        #canvas-wrapper { display: flex; justify-content: center; align-items: center; height: 400px; }
        #graficoTarefas { max-width: 400px; max-height: 400px; }
        */
        @media (max-width: 900px) { #perfilcard-card { flex-direction: column; width: 95%; } /* #grafico-container { width: 95%; } */ #perfilcard-left { width: 100%; align-items: center; } #perfilcard-form { width: 100%; } #perfilcard-botoes { flex-direction: column; gap: 15px; } }
        @media (max-width: 600px) { .perfilcard-linha { flex-direction: column; gap: 0; } }
    </style>
</head>
<body>

    <div id="perfilcard-container">
        <div id="perfilcard-card">
            
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
                    <a href="grafico_tarefas.php" id="perfilcard-btn-senha">Gerar Gráfico</a>
                </div>

            </form>
        </div>
    </div>
    
    <script>
        let modoEdicao = false; 

        function alternarModoEdicao() {
            const form = document.getElementById('perfilcard-form');
            const btnSalvar = document.getElementById('perfilcard-btn-salvar');
            const btnFoto = document.getElementById('perfilcard-btn-foto');
            const fotoInput = document.getElementById('perfilcard-fotoInput');
            
            const inputsParaAlternar = form.querySelectorAll(
                'input[type="text"], input[type="email"], input[type="date"], select, input[name="cpf"]'
            );

            if (modoEdicao) {
                // MODO SALVAR
                inputsParaAlternar.forEach(element => {
                    if (element.id !== 'perfilcard-email') { 
                         element.setAttribute('required', '');
                    }
                });
                btnSalvar.type = 'submit'; 
                
            } else {
                // MODO ALTERAR DADOS (Habilitar edição)

                btnSalvar.textContent = 'Salvar Dados';
                btnFoto.style.display = 'block'; 
                form.prepend(fotoInput); 
                
                inputsParaAlternar.forEach(element => {
                    if (element.id === 'perfilcard-email') {
                         return;
                    }
                    
                    if (element.tagName === 'SELECT') {
                        element.disabled = false;
                    } else {
                        element.removeAttribute('readonly');
                    }
                    
                    element.removeAttribute('required');
                });

                modoEdicao = true;
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

        // Alerta de Sucesso e Renderização do Gráfico
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Alerta de Sucesso (USANDO VARIÁVEL DE SESSÃO) ---
            const mensagemSucesso = "<?= addslashes($mensagem_sucesso) ?>";

            if (mensagemSucesso.length > 0) {
                alert(`✅ ${mensagemSucesso}`); 
            }
            // ---------------------------------------------------------
            
            // --- COMENTADO: Bloco de Código JavaScript que Renderiza o Gráfico de Pizza ---
            /* 
            const criadas = <//?= $total_criadas ?//>;
            const concluidas = <//?= $total_concluidas ?//>;
            
            const pendentes = criadas - concluidas;

            const ctx = document.getElementById('graficoTarefas').getContext('2d');
            new Chart(ctx, {
                type: 'pie', 
                data: {
                    labels: ['Concluídas', 'Pendentes'], 
                    datasets: [{
                        data: [concluidas, pendentes],
                        backgroundColor: [
                            '#27ae60',  // Verde (Concluídas)
                            '#f39c12'  // Laranja (Pendentes)
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed;
                                    const percentage = ((value / total) * 100).toFixed(1) + '%';
                                    return label + value + ' (' + percentage + ')';
                                }
                            }
                        },
                        title: {
                            display: false 
                        }
                    }
                }
            }); 
            */
            // ---------------------------------------------------------------------------------
        });

    </script>
</body>
<?php
// Inclui o rodapé padrão da página.
include 'footer.php';
?>
</html>