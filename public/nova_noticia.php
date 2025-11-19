<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';
require_once '../config/verifica_login.php';

$usuario = usuarioAtual();
$erros = [];

// Categorias disponíveis
$categorias = [
    'Artes Visuais',
    'Teatro', 
    'Música',
    'Dança',
    'Cinema',
    'Literatura',
    'Cultura Popular',
    'Patrimônio Histórico',
    'Exposições',
    'Festivais'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $resumo = trim($_POST['resumo']);
    $noticia = trim($_POST['noticia']);
    $categoria = $_POST['categoria'];
    $status = $_POST['status'];
    
    // Validações
    if (empty($titulo) || strlen($titulo) < 5) {
        $erros[] = "Título deve ter pelo menos 5 caracteres.";
    }
    
    if (empty($noticia) || strlen($noticia) < 50) {
        $erros[] = "A notícia deve ter pelo menos 50 caracteres.";
    }
    
    // ✅✅✅ CORREÇÃO: Processar upload de imagem ANTES de salvar
    $imagem_path = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem_path = uploadImagem($_FILES['imagem']);
        if (!$imagem_path) {
            $erros[] = "Erro no upload da imagem. Verifique o formato (JPG, PNG, GIF, WebP) e tamanho (máx. 5MB).";
        }
    } elseif (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Se houve erro no upload que não seja "arquivo não selecionado"
        $erros[] = "Erro no upload da imagem: " . obterMensagemErroUpload($_FILES['imagem']['error']);
    }
    
    // Salvar notícia se não houver erros
    if (empty($erros)) {
        $sql = "INSERT INTO noticias (titulo, resumo, noticia, autor, categoria, status, imagem) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexao->prepare($sql);
        
        if ($stmt === false) {
            $erros[] = "Erro no sistema. Tente novamente mais tarde.";
            error_log("Erro no prepare (nova_noticia): " . $conexao->error);
        } else {
            // Se resumo estiver vazio, usar NULL
            $resumo_valor = empty($resumo) ? NULL : $resumo;
            $imagem_valor = empty($imagem_path) ? NULL : $imagem_path;
            
            $stmt->bind_param("sssisss", $titulo, $resumo_valor, $noticia, $usuario['id'], $categoria, $status, $imagem_valor);
            
            if ($stmt->execute()) {
                $mensagem = $status == 'publicada' ? 
                    "Notícia publicada com sucesso!" : 
                    "Rascunho salvo com sucesso!";
                
                $stmt->close();
                redirect('dashboard.php', $mensagem);
            } else {
                $erros[] = "Erro ao salvar notícia: " . $stmt->error;
                error_log("Erro no execute (nova_noticia): " . $stmt->error);
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
    <title>Nova Notícia - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .imagem-preview {
            max-width: 300px;
            margin: 10px 0;
            display: none;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .imagem-preview.visible {
            display: block;
        }
        .imagem-preview img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">Cultura<span>&</span>Arte</a>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container" style="max-width: 800px;">
            <h2>Nova Notícia</h2>
            
            <?php if (!empty($erros)): ?>
                <div class="mensagem erro">
                    <?php foreach ($erros as $erro): ?>
                        <p><?php echo $erro; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" 
                           value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="resumo">Resumo (opcional):</label>
                    <textarea id="resumo" name="resumo" class="form-control" rows="3" maxlength="500"><?php echo isset($_POST['resumo']) ? htmlspecialchars($_POST['resumo']) : ''; ?></textarea>
                    <small>Breve descrição que aparecerá na listagem (máx. 500 caracteres).</small>
                </div>

                <div class="form-group">
                    <label for="noticia">Notícia:</label>
                    <textarea id="noticia" name="noticia" class="form-control" rows="10" required><?php echo isset($_POST['noticia']) ? htmlspecialchars($_POST['noticia']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria:</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="imagem">Imagem (opcional):</label>
                    <input type="file" id="imagem" name="imagem" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small>Formatos: JPG, PNG, GIF, WebP (máx. 5MB)</small>
                    
                    <!-- Preview da imagem -->
                    <div id="imagem-preview" class="imagem-preview">
                        <p><strong>Preview:</strong></p>
                        <img src="" alt="Preview da imagem">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="rascunho">Salvar como Rascunho</option>
                        <option value="publicada" <?php echo (isset($_POST['status']) && $_POST['status'] == 'publicada') ? 'selected' : ''; ?>>Publicar Agora</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">Salvar Notícia</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Preview da imagem antes do upload
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagem-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.add('visible');
                }
                reader.readAsDataURL(file);
            } else {
                preview.classList.remove('visible');
            }
        });
    </script>
</body>
</html>
<?php
// Fechar conexão
if (isset($conexao)) {
    $conexao->close();
}
?>