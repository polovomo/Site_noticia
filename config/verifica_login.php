<?php
/**
 * Verifica se o usuário está logado
 * Protege páginas que exigem autenticação
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redirecionar para login com mensagem
    $_SESSION['erro'] = "Você precisa estar logado para acessar esta página.";
    header("Location: login.php");
    exit();
}

// Opcional: Verificar se usuário ainda existe no banco
require_once 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT id FROM usuarios WHERE id = ?";
$stmt = $conexao->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Usuário não existe mais no banco
        session_destroy();
        $_SESSION['erro'] = "Sua sessão expirou. Faça login novamente.";
        header("Location: login.php");
        exit();
    }
    $stmt->close();
}
?>