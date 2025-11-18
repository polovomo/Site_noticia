<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';
require_once '../config/verifica_login.php';

// VERIFICAR SE É EDITOR/ADMIN
if (!podePublicar()) {
    redirect('minha_conta.php', 'Acesso restrito a editores.');
}

$usuario = usuarioAtual();

// Inicializar variáveis
$total_noticias = 0;
$noticias_publicadas = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="nova_noticia.php">Nova Notícia</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Olá, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
            <a href="nova_noticia.php" class="btn">Nova Notícia</a>
        </div>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="mensagem sucesso"><?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></div>
        <?php endif; ?>

        <?php
        // TENTAR carregar estatísticas - COM TRATAMENTO DE ERRO
        try {
            // Estatísticas
            $sql_noticias = "SELECT COUNT(*) as total FROM noticias WHERE autor = ?";
            $stmt = $conexao->prepare($sql_noticias);
            
            if ($stmt) {
                $stmt->bind_param("i", $usuario['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $total_noticias = $result->fetch_assoc()['total'];
                $stmt->close();
            }

            // Notícias publicadas
            $sql_publicadas = "SELECT COUNT(*) as total FROM noticias WHERE autor = ? AND status = 'publicada'";
            $stmt = $conexao->prepare($sql_publicadas);
            
            if ($stmt) {
                $stmt->bind_param("i", $usuario['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $noticias_publicadas = $result->fetch_assoc()['total'];
                $stmt->close();
            }
        } catch (Exception $e) {
            echo "<div class='mensagem erro'>Atenção: Configure a tabela de notícias primeiro.</div>";
        }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_noticias; ?></div>
                <div class="stat-label">Total de Notícias</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $noticias_publicadas; ?></div>
                <div class="stat-label">Notícias Publicadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_noticias - $noticias_publicadas; ?></div>
                <div class="stat-label">Rascunhos</div>
            </div>
        </div>

        <section>
            <h2>Minhas Notícias</h2>
            
            <?php
            // TENTAR carregar notícias do usuário
            try {
                $sql = "SELECT * FROM noticias WHERE autor = ? ORDER BY data_publicacao DESC";
                $stmt = $conexao->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("i", $usuario['id']);
                    $stmt->execute();
                    $noticias_result = $stmt->get_result();
                    
                    if ($noticias_result->num_rows > 0): ?>
                    <div class="noticias-grid">
                        <?php while ($noticia = $noticias_result->fetch_assoc()): ?>
                        <article class="noticia-card">
                            <?php if (!empty($noticia['imagem']) && file_exists('../' . $noticia['imagem'])): ?>
                            <img src="../<?php echo $noticia['imagem']; ?>" alt="<?php echo $noticia['titulo']; ?>" class="noticia-imagem">
                            <?php else: ?>
                            <div class="noticia-imagem" style="background: #8B4513; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                Cultura & Arte
                            </div>
                            <?php endif; ?>
                            
                            <div class="noticia-conteudo">
                                <span class="categoria"><?php echo $noticia['categoria']; ?></span>
                                <span style="background: <?php echo $noticia['status'] == 'publicada' ? '#10B981' : '#6B7280'; ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                    <?php echo $noticia['status'] == 'publicada' ? 'Publicada' : 'Rascunho'; ?>
                                </span>
                                
                                <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                                <p><?php echo htmlspecialchars($noticia['resumo'] ?: gerarResumo($noticia['noticia'])); ?></p>
                                
                                <div class="noticia-meta">
                                    <span><?php echo formatarData($noticia['data_publicacao']); ?></span>
                                </div>
                                
<div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
    <a href="noticia.php?id=<?php echo $noticia['id']; ?>" class="btn">Ver</a>
    <a href="editar_noticia.php?id=<?php echo $noticia['id']; ?>" class="btn btn-secondary">Editar</a>
    <a href="excluir_noticia.php?id=<?php echo $noticia['id']; ?>" class="btn btn-danger" 
       onclick="return confirm('Tem certeza?')">Excluir</a>
</div>  
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="mensagem info">
                        <p>Você ainda não criou nenhuma notícia. <a href="nova_noticia.php">Comece agora</a>!</p>
                    </div>
                    <?php endif;
                    
                    $stmt->close();
                } else {
                    throw new Exception("Não foi possível preparar a consulta");
                }
            } catch (Exception $e) {
                echo "<div class='mensagem info'>";
                echo "<h3>📝 Bem-vindo ao seu Dashboard!</h3>";
                echo "<p>Para começar a usar o sistema, você precisa:</p>";
                echo "<ol>";
                echo "<li><strong>Configurar a tabela de notícias</strong> no banco de dados</li>";
                echo "<li><a href='nova_noticia.php'>Criar sua primeira notícia</a></li>";
                echo "</ol>";
                echo "<p><small>Erro técnico: A coluna 'autor' não existe na tabela 'noticias'</small></p>";
                echo "</div>";
            }
            ?>
        </section>
    </main>
</body>
</html>
<?php
// Fechar conexão
if (isset($conexao)) {
    $conexao->close();
}
?>