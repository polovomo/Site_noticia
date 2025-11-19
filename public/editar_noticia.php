<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';
require_once '../config/verifica_login.php';

// VERIFICAR SE É EDITOR/ADMIN
if (!podePublicar()) {
    redirect('minha_conta.php', 'Acesso restrito a editores.');
}

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

// Verificar se foi passado um ID
if (!isset($_GET['id'])) {
    redirect('dashboard.php', 'Notícia não especificada.');
}

$noticia_id = intval($_GET['id']);

// ✅✅✅ CORREÇÃO: Admin pode editar QUALQUER notícia, Editor só as próprias
if (isAdmin()) {
    // Admin pode editar qualquer notícia
    $sql = "SELECT * FROM noticias WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $noticia_id);
} else {
    // Editor só pode editar suas próprias notícias
    $sql = "SELECT * FROM noticias WHERE id = ? AND autor = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $noticia_id, $usuario['id']);
}

$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    redirect('dashboard.php', 'Notícia não encontrada ou você não tem permissão para editá-la.');
}
$stmt->close();

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $resumo = trim($_POST['resumo']);
    $noticia_texto = trim($_POST['noticia']);
    $categoria = $_POST['categoria'];
    $status = $_POST['status'];
    
    // Validações
    if (empty($titulo) || strlen($titulo) < 5) {
        $erros[] = "Título deve ter pelo menos 5 caracteres.";
    }
    
    if (empty($noticia_texto) || strlen($noticia_texto) < 50) {
        $erros[] = "A notícia deve ter pelo menos 50 caracteres.";
    }
    
    // Processar upload de nova imagem
    $imagem_path = $noticia['imagem']; // Manter a imagem atual
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nova_imagem = uploadImagem($_FILES['imagem']);
        if ($nova_imagem) {
            // Se fez upload de nova imagem, excluir a antiga
            if ($noticia['imagem'] && file_exists('../' . $noticia['imagem'])) {
                unlink('../' . $noticia['imagem']);
            }
            $imagem_path = $nova_imagem;
        }
    }
    
    // Se marcar para remover imagem
    if (isset($_POST['remover_imagem']) && $_POST['remover_imagem'] == '1') {
        // Excluir imagem física se existir
        if ($noticia['imagem'] && file_exists('../' . $noticia['imagem'])) {
            unlink('../' . $noticia['imagem']);
        }
        $imagem_path = NULL;
    }
    
    // Atualizar notícia se não houver erros
    if (empty($erros)) {
        // ✅✅✅ CORREÇÃO: Admin pode atualizar qualquer notícia
        if (isAdmin()) {
            $sql = "UPDATE noticias SET titulo = ?, resumo = ?, noticia = ?, categoria = ?, status = ?, imagem = ? WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssssi", $titulo, $resumo, $noticia_texto, $categoria, $status, $imagem_path, $noticia_id);
        } else {
            $sql = "UPDATE noticias SET titulo = ?, resumo = ?, noticia = ?, categoria = ?, status = ?, imagem = ? WHERE id = ? AND autor = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssssii", $titulo, $resumo, $noticia_texto, $categoria, $status, $imagem_path, $noticia_id, $usuario['id']);
        }
        
        if ($stmt === false) {
            $erros[] = "Erro no sistema. Tente novamente mais tarde.";
        } else {
            $resumo_valor = empty($resumo) ? NULL : $resumo;
            $imagem_valor = empty($imagem_path) ? NULL : $imagem_path;
            
            if ($stmt->execute()) {
                $mensagem = "Notícia atualizada com sucesso!";
                $stmt->close();
                redirect('dashboard.php', $mensagem);
            } else {
                $erros[] = "Erro ao atualizar notícia: " . $stmt->error;
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
    <title>Editar Notícia - <?php echo SITE_NOME; ?></title>
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
        .imagem-atual {
            max-width: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 5px 0;
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
                <li><a href="nova_noticia.php">Nova Notícia</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container" style="max-width: 800px;">
            <h2>Editar Notícia</h2>
            
            <?php if (isAdmin() && $noticia['autor'] != $usuario['id']): ?>
            <div class="mensagem info">
                <strong>🔧 Modo Administrador:</strong> Editando notícia de outro usuário.
            </div>
            <?php endif; ?>
            
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
                           value="<?php echo htmlspecialchars($noticia['titulo']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="resumo">Resumo (opcional):</label>
                    <textarea id="resumo" name="resumo" class="form-control" rows="3" maxlength="500"><?php echo htmlspecialchars($noticia['resumo'] ?? ''); ?></textarea>
                    <small>Breve descrição que aparecerá na listagem (máx. 500 caracteres).</small>
                </div>

                <div class="form-group">
                    <label for="noticia">Notícia:</label>
                    <textarea id="noticia" name="noticia" class="form-control" rows="10" required><?php echo htmlspecialchars($noticia['noticia']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria:</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($noticia['categoria'] == $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Imagem atual:</label>
                    <?php if (!empty($noticia['imagem']) && file_exists('../' . $noticia['imagem'])): ?>
                        <div style="margin: 10px 0;">
                            <img src="../<?php echo $noticia['imagem']; ?>" alt="Imagem atual" class="imagem-atual">
                            <br>
                            <label style="display: inline-flex; align-items: center; margin-top: 5px;">
                                <input type="checkbox" name="remover_imagem" value="1"> 
                                Remover imagem atual
                            </label>
                        </div>
                    <?php else: ?>
                        <p style="color: #666;">Nenhuma imagem definida</p>
                    <?php endif; ?>
                    
                    <label for="imagem">Nova imagem (opcional):</label>
                    <input type="file" id="imagem" name="imagem" class="form-control" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small>Formatos: JPG, PNG, GIF, WebP (máx. 5MB)</small>
                    
                    <!-- Preview da nova imagem -->
                    <div id="imagem-preview" class="imagem-preview">
                        <p><strong>Preview da nova imagem:</strong></p>
                        <img src="" alt="Preview da imagem">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="rascunho" <?php echo ($noticia['status'] == 'rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                        <option value="publicada" <?php echo ($noticia['status'] == 'publicada') ? 'selected' : ''; ?>>Publicada</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">Atualizar Notícia</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                    <a href="noticia.php?id=<?php echo $noticia_id; ?>" class="btn">Ver Notícia</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Preview da nova imagem antes do upload
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