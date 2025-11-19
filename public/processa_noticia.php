<?php
require_once 'verifica_login.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    $imagem = $_FILES['imagem'] ?? null;
    
    // Validações básicas
    if (empty($titulo) || empty($conteudo)) {
        $_SESSION['erro'] = "Título e conteúdo são obrigatórios!";
        header('Location: nova_noticia.php');
        exit;
    }
    
    $caminhoImagem = null;
    
    // Processar upload da imagem se existir
    if ($imagem && $imagem['error'] === 0) {
        $pastaUploads = 'public/uploads/';
        
        // Garantir que a pasta existe
        if (!is_dir($pastaUploads)) {
            mkdir($pastaUploads, 0755, true);
        }
        
        // Validar tipo de arquivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArquivo = mime_content_type($imagem['tmp_name']);
        
        if (!in_array($tipoArquivo, $tiposPermitidos)) {
            $_SESSION['erro'] = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP.";
            header('Location: nova_noticia.php');
            exit;
        }
        
        // Validar tamanho do arquivo (máximo 5MB)
        if ($imagem['size'] > 5 * 1024 * 1024) {
            $_SESSION['erro'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
            header('Location: nova_noticia.php');
            exit;
        }
        
        // Gerar nome único para o arquivo
        $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '_' . date('Y-m-d') . '.' . $extensao;
        $caminhoCompleto = $pastaUploads . $nomeArquivo;
        
        // Mover arquivo para a pasta de uploads
        if (move_uploaded_file($imagem['tmp_name'], $caminhoCompleto)) {
            $caminhoImagem = $caminhoCompleto;
        } else {
            $_SESSION['erro'] = "Erro ao fazer upload da imagem.";
            header('Location: nova_noticia.php');
            exit;
        }
    }
    
    try {
        // Inserir no banco de dados
        $sql = "INSERT INTO noticias (titulo, conteudo, imagem, usuario_id) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $conteudo, $caminhoImagem, $_SESSION['usuario_id']]);
        
        $_SESSION['sucesso'] = "Notícia publicada com sucesso!";
        header('Location: dashboard.php');
        
    } catch (PDOException $e) {
        // Se houve erro, excluir a imagem que foi upada
        if ($caminhoImagem && file_exists($caminhoImagem)) {
            unlink($caminhoImagem);
        }
        
        $_SESSION['erro'] = "Erro ao salvar notícia: " . $e->getMessage();
        header('Location: nova_noticia.php');
    }
    
} else {
    header('Location: nova_noticia.php');
}