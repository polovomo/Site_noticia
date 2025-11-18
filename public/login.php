<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

// Se já estiver logado, redireciona
if (usuarioLogado()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validar dados
    if (empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        // Buscar usuário
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        // ✅✅✅ LINHA CORRIGIDA - mudou 'senha' para 'senha_hash' ✅✅✅
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            
            redirect('dashboard.php', 'Login realizado com sucesso!');
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NOME; ?></title>
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
            <h2>Login</h2>
            
            <?php if (isset($erro)): ?>
                <div class="mensagem erro"><?php echo $erro; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="mensagem sucesso"><?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>

                <button type="submit" class="btn">Entrar</button>
            </form>

            <p style="margin-top: 1rem; text-align: center;">
                Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>
            </p>
        </div>
    </main>
</body>
</html>