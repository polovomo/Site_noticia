<?php
// Funções auxiliares do sistema

/**
 * Verifica se o usuário está logado
 */
function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Retorna os dados do usuário logado
 */
function usuarioAtual() {
    if (usuarioLogado()) {
        global $conexao;
        $usuario_id = $_SESSION['usuario_id'];
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    return null;
}

/**
 * Verifica se o usuário é admin
 */
function isAdmin() {
    $usuario = usuarioAtual();
    return $usuario && $usuario['tipo'] === 'admin';
}

/**
 * Verifica se o usuário é editor ou admin
 */
function isEditor() {
    $usuario = usuarioAtual();
    return $usuario && ($usuario['tipo'] === 'editor' || $usuario['tipo'] === 'admin');
}

/**
 * Verifica se o usuário pode publicar conteúdo
 */
function podePublicar() {
    return isEditor();
}

/**
 * Verifica se o usuário é apenas leitor
 */
function isLeitor() {
    $usuario = usuarioAtual();
    return $usuario && $usuario['tipo'] === 'leitor';
}

/**
 * Redireciona com mensagem flash
 */
function redirect($url, $mensagem = null) {
    if ($mensagem) {
        $_SESSION['mensagem'] = $mensagem;
    }
    header("Location: $url");
    exit();
}

/**
 * Faz upload de imagem - VERSÃO CORRIGIDA ✅
 */
function uploadImagem($arquivo) {
    // ✅ CAMINHO CORRETO para sua estrutura de pastas
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/site/public/uploads/imagens/';
    
    // Verificar se há algum erro no upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        error_log("Erro no upload: " . $arquivo['error']);
        return null;
    }

    // Verificar se o arquivo é realmente uma imagem
    $info_imagem = @getimagesize($arquivo['tmp_name']);
    if ($info_imagem === false) {
        error_log("Arquivo não é uma imagem válida");
        return null;
    }

    // Verificar tipo de arquivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($info_imagem['mime'], $tiposPermitidos)) {
        error_log("Tipo de arquivo não permitido: " . $info_imagem['mime']);
        return null;
    }

    // Verificar tamanho (máximo 5MB)
    if ($arquivo['size'] > 5 * 1024 * 1024) {
        error_log("Arquivo muito grande: " . $arquivo['size'] . " bytes");
        return null;
    }

    // Gerar nome único
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid() . '_' . date('Ymd') . '.' . $extensao;
    $caminhoCompleto = $uploadDir . $nomeArquivo;

    // Criar diretório se não existir
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Falha ao criar diretório: " . $uploadDir);
            return null;
        }
    }

    // Tentar mover o arquivo
    if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        error_log("Upload bem-sucedido: " . $caminhoCompleto);
        // ✅ CORREÇÃO: Retornar caminho RELATIVO para salvar no banco
        return 'uploads/imagens/' . $nomeArquivo;
    } else {
        error_log("Falha ao mover arquivo: " . $arquivo['tmp_name'] . " para " . $caminhoCompleto);
        return null;
    }
}

/**
 * Formata data para exibição
 */
function formatarData($data) {
    return date('d/m/Y \à\s H:i', strtotime($data));
}

/**
 * Gera resumo do texto
 */
function gerarResumo($texto, $limite = 200) {
    $texto = strip_tags($texto);
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    return substr($texto, 0, $limite) . '...';
}

/**
 * Retorna mensagem amigável para erro de upload
 */
function obterMensagemErroUpload($erro_code) {
    switch ($erro_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "Arquivo muito grande. Tamanho máximo: 5MB.";
        case UPLOAD_ERR_PARTIAL:
            return "Upload parcialmente feito. Tente novamente.";
        case UPLOAD_ERR_NO_FILE:
            return "Nenhum arquivo selecionado.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Pasta temporária não encontrada.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Não foi possível salvar o arquivo.";
        case UPLOAD_ERR_EXTENSION:
            return "Extensão do arquivo não permitida.";
        default:
            return "Erro desconhecido no upload.";
    }
}
?>  