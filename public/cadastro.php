<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

// Se já estiver logado, redireciona
if (usuarioLogado()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validar dados
    $erros = [];
    
    if (empty($nome) || strlen($nome) < 2) {
        $erros[] = "Nome deve ter pelo menos 2 caracteres.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }
    
    if (empty($senha) || strlen($senha) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres.";
    }
    
    if ($senha !== $confirmar_senha) {
        $erros[] = "As senhas não coincidem.";
    }

    // Verificar se email já existe
    if (empty($erros)) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        
        // VERIFICAÇÃO CRÍTICA - se prepare() falhou
        if ($stmt === false) {
            $erros[] = "Erro no sistema. Tente novamente mais tarde.";
            error_log("Erro no prepare (verificação email): " . $conexao->error);
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $erros[] = "Este e-mail já está cadastrado.";
            }
            $stmt->close();
        }
    }

    // Cadastrar usuário
    if (empty($erros)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // ✅✅✅ LINHA CORRIGIDA - usar senha_hash e definir tipo como 'leitor'
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, 'leitor')";
        $stmt = $conexao->prepare($sql);
        
        // VERIFICAÇÃO CRÍTICA - se prepare() falhou
        if ($stmt === false) {
            $erros[] = "Erro no sistema. Tente novamente mais tarde.";
            error_log("Erro no prepare (insert): " . $conexao->error);
        } else {
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            
            if ($stmt->execute()) {
                // Login automático após cadastro
                $usuario_id = $stmt->insert_id;
                $_SESSION['usuario_id'] = $usuario_id;
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['usuario_tipo'] = 'leitor';
                
                $stmt->close();
                
                // ✅✅✅ Redirecionar leitores para "minha_conta.php"
                redirect('minha_conta.php', 'Cadastro realizado com sucesso! Bem-vindo!');
            } else {
                $erros[] = "Erro ao cadastrar. Tente novamente.";
                error_log("Erro no execute: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container">
            <h2>Criar Conta</h2>
            
            <?php if (!empty($erros)): ?>
                <div class="mensagem erro">
                    <?php foreach ($erros as $erro): ?>
                        <p><?php echo $erro; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" class="form-control" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" class="form-control" 
                           minlength="6" required>
                    <small>A senha deve ter pelo menos 6 caracteres.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" 
                           class="form-control" required>
                </div>

                <button type="submit" class="btn">Cadastrar</button>
            </form>

            <p style="margin-top: 1rem; text-align: center;">
                Já tem conta? <a href="login.php">Faça login aqui</a>
            </p>
        </div>
    </main>
</body>
</html>
<?php
// Fechar conexão
if (isset($conexao)) {
    $conexao->close();
}