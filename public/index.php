<?php
// Incluir arquivas de configuração
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

// Verificar conexão
if ($conexao->connect_error) {
    die("Erro de conexão com o banco de dados.");
}

// API Cotação do Dólar
$url_dolar = "https://economia.awesomeapi.com.br/last/USD-BRL";
$json_dolar = file_get_contents($url_dolar);
$dados_dolar = json_decode($json_dolar, true);
$cotacao_dolar = isset($dados_dolar['USDBRL']['bid']) ? "R$ " . $dados_dolar['USDBRL']['bid'] : "Indisponível";

// API Clima - Porto Alegre/RS (Exemplo)
$url_clima = "https://api.open-meteo.com/v1/forecast?latitude=-30.03&longitude=-51.23&current=temperature_2m";
$json_clima = file_get_contents($url_clima);
$dados_clima = json_decode($json_clima, true);
$temperatura = isset($dados_clima['current']['temperature_2m']) ? $dados_clima['current']['temperature_2m'] . "°C" : "Indisponível";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NOME; ?> - Página Inicial</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            

            
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="#categorias">Categorias</a></li>
                <?php if (usuarioLogado()) { ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="nova_noticia.php">Nova Notícia</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php } else { ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="cadastro.php">Cadastrar</a></li>
                <?php } ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <section class="hero">
            <h1>Portal Cultura & Arte</h1>
            <p>Descubra as mais vibrantes expressões culturais e artísticas do momento</p>
            
            <!-- Informações adicionais no hero -->
            <div class="hero-info">
                <small>🌡️ Temperatura atual: <?php echo $temperatura; ?> | 💵 Cotação do dólar: <?php echo $cotacao_dolar; ?></small>
            </div>
        </section>

        <?php 
        if (isset($_SESSION['mensagem'])) { 
            echo '<div class="mensagem sucesso">' . $_SESSION['mensagem'] . '</div>';
            unset($_SESSION['mensagem']);
        } 
        
        if (isset($_SESSION['erro'])) { 
            echo '<div class="mensagem erro">' . $_SESSION['erro'] . '</div>';
            unset($_SESSION['erro']);
        } 
        ?>

        <section class="noticias-destaque">
            <h2>Últimas Notícias</h2>
            
            <?php
            // Buscar notícias publicadas
            $sql = "SELECT n.*, u.nome as autor_nome 
                    FROM noticias n 
                    JOIN usuarios u ON n.autor = u.id 
                    WHERE n.status = 'publicada' 
                    ORDER BY n.data_publicacao DESC 
                    LIMIT 12";
            
            $result = $conexao->query($sql);
            
            if ($result && $result->num_rows > 0) {
                echo '<div class="noticias-grid">';
                
                while ($noticia = $result->fetch_assoc()) {
                    echo '<article class="noticia-card">';
                    
                    if (!empty($noticia['imagem']) && file_exists('../' . $noticia['imagem'])) {
                        echo '<img src="../' . $noticia['imagem'] . '" alt="' . $noticia['titulo'] . '" class="noticia-imagem">';
                    } else {
                        echo '<div class="noticia-imagem" style="background: #8B4513; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">';
                        echo 'Cultura & Arte';
                        echo '</div>';
                    }
                    
                    echo '<div class="noticia-conteudo">';
                    echo '<span class="categoria">' . $noticia['categoria'] . '</span>';
                    echo '<h3>' . htmlspecialchars($noticia['titulo']) . '</h3>';
                    
                    $resumo = !empty($noticia['resumo']) ? $noticia['resumo'] : gerarResumo($noticia['noticia']);
                    echo '<p>' . htmlspecialchars($resumo) . '</p>';
                    
                    echo '<div class="noticia-meta">';
                    echo '<span>Por: ' . htmlspecialchars($noticia['autor_nome']) . '</span>';
                    echo '<span>' . formatarData($noticia['data_publicacao']) . '</span>';
                    echo '</div>';
                    
                    echo '<a href="noticia.php?id=' . $noticia['id'] . '" class="btn" style="margin-top: 1rem; display: inline-block;">Ler mais</a>';
                    echo '</div>';
                    echo '</article>';
                }
                
                echo '</div>';
            } else {
                echo '<div class="mensagem info">';
                echo '<p>Nenhuma notícia publicada ainda. Seja o primeiro a compartilhar!</p>';
                if (!usuarioLogado()) {
                    echo '<p><a href="cadastro.php">Cadastre-se</a> ou <a href="login.php">faça login</a> para publicar.</p>';
                }
                echo '</div>';
            }
            
            // Fechar conexão
            $conexao->close();
            ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NOME; ?>. Todos os direitos reservados.</p>
        <p>Desenvolvido com ❤️ para amantes da cultura e arte</p>
        <!-- Informações no footer também -->
        <div class="footer-info">
            <small>Dados atualizados: Clima <?php echo $temperatura; ?> | Dólar <?php echo $cotacao_dolar; ?></small>
        </div>
    </footer>
</body>
</html>