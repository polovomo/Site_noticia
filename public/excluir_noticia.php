<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';
require_once '../config/verifica_login.php';

// VERIFICAR SE É EDITOR/ADMIN
if (!podePublicar()) {
    redirect('minha_conta.php', 'Acesso restrito a editores.');
}

$usuario = usuarioAtual();

// Verificar se foi passado um ID
if (!isset($_GET['id'])) {
    redirect('dashboard.php', 'Notícia não especificada.');
}

$noticia_id = intval($_GET['id']);

// Buscar a notícia - ADMIN pode ver qualquer notícia, EDITOR só as próprias
if (isAdmin()) {
    // Admin pode ver qualquer notícia
    $sql = "SELECT n.*, u.nome as autor_nome 
            FROM noticias n 
            JOIN usuarios u ON n.autor = u.id 
            WHERE n.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $noticia_id);
} else {
    // Editor só pode ver suas próprias notícias
    $sql = "SELECT n.*, u.nome as autor_nome 
            FROM noticias n 
            JOIN usuarios u ON n.autor = u.id 
            WHERE n.id = ? AND n.autor = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $noticia_id, $usuario['id']);
}

$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    redirect('dashboard.php', 'Notícia não encontrada ou você não tem permissão para excluí-la.');
}
$stmt->close();

// Processar a exclusão se confirmada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'sim') {
        
        // Verificar permissão para excluir
        if (isAdmin() || $noticia['autor'] == $usuario['id']) {
            
            // ✅✅✅ CORREÇÃO: Excluir a imagem física se existir
            if (!empty($noticia['imagem']) && file_exists('../' . $noticia['imagem'])) {
                if (unlink('../' . $noticia['imagem'])) {
                    error_log("Imagem excluída: " . $noticia['imagem']);
                } else {
                    error_log("Erro ao excluir imagem: " . $noticia['imagem']);
                }
            }
            
            // Excluir a notícia do banco
            $sql = "DELETE FROM noticias WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $noticia_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                redirect('dashboard.php', 'Notícia excluída com sucesso!');
            } else {
                redirect('dashboard.php', 'Erro ao excluir notícia.');
            }
        } else {
            redirect('dashboard.php', 'Você não tem permissão para excluir esta notícia.');
        }
    } else {
        // Usuário cancelou a exclusão
        redirect('dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Notícia - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="nova_noticia.php">Nova Notícia</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container" style="max-width: 600px; text-align: center;">
            <h2>Excluir Notícia</h2>
            
            <div class="mensagem erro" style="text-align: left;">
                <h3>⚠️ Confirmação de Exclusão</h3>
                <p>Você está prestes a excluir a notícia:</p>
                <p><strong>"<?php echo htmlspecialchars($noticia['titulo']); ?>"</strong></p>
                
                <?php if (!empty($noticia['imagem'])): ?>
                <p><strong>📷 Imagem:</strong> A imagem associada também será excluída permanentemente.</p>
                <?php endif; ?>
                
                <?php if (isAdmin() && $noticia['autor'] != $usuario['id']): ?>
                <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <strong>🔧 Ação de Administrador:</strong><br>
                    Esta notícia pertence a <strong><?php echo htmlspecialchars($noticia['autor_nome']); ?></strong><br>
                    Como administrador, você pode excluir notícias de qualquer usuário.
                </div>
                <?php endif; ?>
                
                <p>Esta ação <strong>não pode ser desfeita</strong>. Tem certeza que deseja continuar?</p>
            </div>

            <form method="POST">
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                    <button type="submit" name="confirmar" value="sim" class="btn btn-danger" 
                            onclick="return confirm('Tem certeza absoluta? Esta ação é irreversível!')">
                        Sim, Excluir Notícia
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </div>
                
                <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                    <strong>Atenção:</strong> Todas as informações desta notícia serão permanentemente removidas.
                </p>
            </form>
        </div>
    </main>
</body>
</html>
<?php
// Fechar conexão
if (isset($conexao)) {
    $conexao->close();
}
?>