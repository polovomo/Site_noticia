<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$noticia_id = intval($_GET['id']);

// Buscar notícia
$sql = "SELECT n.*, u.nome as autor_nome 
        FROM noticias n 
        JOIN usuarios u ON n.autor = u.id 
        WHERE n.id = ? AND n.status = 'publicada'";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $noticia_id);
$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> - <?= SITE_NOME ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="index.php#categorias">Categorias</a></li>
                <?php if (usuarioLogado()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="nova_noticia.php">Nova Notícia</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="cadastro.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <article class="noticia-detalhe">
            <span class="categoria"><?= $noticia['categoria'] ?></span>
            <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
            
            <div class="noticia-meta-detalhe">
                <span><strong>Autor:</strong> <?= htmlspecialchars($noticia['autor_nome']) ?></span>
                <span><strong>Publicado em:</strong> <?= formatarData($noticia['data_publicacao']) ?></span>
            </div>

            <?php if ($noticia['imagem']): ?>
                <?php
                // ✅✅✅ CORREÇÃO DEFINITIVA - usar public/ + caminho da imagem
                $caminhoImagem = 'public/' . $noticia['imagem'];
                $caminhoAbsoluto = $_SERVER['DOCUMENT_ROOT'] . '/site/' . $caminhoImagem;
                
                if (file_exists($caminhoAbsoluto)): ?>
                    <img src="<?= $caminhoImagem ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" class="noticia-imagem-detalhe">
                <?php else: ?>
                    <!-- Fallback: tentar caminho antigo -->
                    <img src="../<?= $noticia['imagem'] ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" class="noticia-imagem-detalhe">
                <?php endif; ?>
            <?php endif; ?>

            <div class="noticia-conteudo">
                <?= nl2br(htmlspecialchars($noticia['noticia'])) ?>
            </div>

            <?php 
            // Verificar se o usuário é o autor ou admin para mostrar opções de edição
            $usuario = usuarioAtual();
            if ($usuario && ($usuario['id'] == $noticia['autor'] || isAdmin())): 
            ?>
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
                <a href="editar_noticia.php?id=<?= $noticia['id'] ?>" class="btn btn-secondary">Editar</a>
                <a href="excluir_noticia.php?id=<?= $noticia['id'] ?>" class="btn btn-danger" 
                   onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">Excluir</a>
            </div>
            <?php endif; ?>
        </article>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> <?= SITE_NOME ?>. Todos os direitos reservados.</p>
    </footer>
</body>
</html>