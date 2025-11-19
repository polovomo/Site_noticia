<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';
require_once '../config/verifica_login.php';

$usuario = usuarioAtual();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container">
            <h2>Minha Conta</h2>
            
            <div class="mensagem info">
                <h3>👋 Olá, <?php echo htmlspecialchars($usuario['nome']); ?>!</h3>
                <p>Você é um <strong>leitor</strong> do nosso portal.</p>
                <p>Como leitor, você pode:</p>
                <ul style="text-align: left; margin: 1rem 0;">
                    <li>📖 Ler todas as notícias</li>
                    <li>🔔 Fazer login para acesso personalizado</li>
                    <li>💾 Salvar suas preferências</li>
                </ul>
                <p>Para publicar conteúdo, entre em contato com a administração.</p>
            </div>

            <div class="user-info" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin: 1rem 0;">
                <h4>Suas Informações:</h4>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p><strong>Tipo de Conta:</strong> Leitor</p>
                <p><strong>Data de Cadastro:</strong> <?php echo formatarData($usuario['data_criacao']); ?></p>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="index.php" class="btn">Voltar às Notícias</a>
            </div>
        </div>
    </main>
</body>
</html>