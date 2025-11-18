<?php
// Iniciar sessão apenas se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do banco
$servidor = "localhost";
$usuario = "root";
$senha = "";  
$banco = "portal_cultura_arte";

// Tentar conexão
try {
    $conexao = new mysqli($servidor, $usuario, $senha, $banco);
    
    // Verificar conexão
    if ($conexao->connect_error) {
        throw new Exception("Erro de conexão: " . $conexao->connect_error);
    }
    
    // Definir charset
    if (!$conexao->set_charset("utf8mb4")) {
        throw new Exception("Erro ao definir charset: " . $conexao->error);
    }
    
} catch (Exception $e) {
    // Mensagem amigável
    die("<div style='padding: 20px; margin: 20px; border: 1px solid #f00; background: #fee;'>
        <h3>Erro do Sistema</h3>
        <p>Desculpe, estamos com problemas técnicos. Tente novamente mais tarde.</p>
        <small>Erro: " . $e->getMessage() . "</small>
    </div>");
}

// Configurações do site
define('SITE_NOME', 'Portal Cultura & Arte');
define('SITE_DESCRICAO', 'Seu portal de notícias sobre cultura e arte');
define('UPLOAD_DIR', 'uploads/imagens/');
?>