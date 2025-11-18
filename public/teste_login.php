<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

$email = 'editor@culturaarte.com';
$senha = '123456';

echo "<h2>Teste Final de Login</h2>";

// Buscar usuário
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario) {
    echo "✅ Usuário encontrado!<br>";
    echo "Nome: " . $usuario['nome'] . "<br>";
    echo "Tipo: " . $usuario['tipo'] . "<br>";
    echo "Hash no banco: " . ($usuario['senha_hash'] ? 'PREENCHIDO (' . strlen($usuario['senha_hash']) . ' chars)' : 'VAZIO') . "<br>";
    
    if ($usuario['senha_hash'] && password_verify($senha, $usuario['senha_hash'])) {
        echo "🎉 LOGIN FUNCIONA! Redirecionando...";
        echo "<script>setTimeout(() => window.location.href = 'login.php', 2000);</script>";
    } else {
        echo "❌ Senha não confere<br>";
        echo "Senha testada: $senha<br>";
    }
} else {
    echo "❌ Usuário não existe";
}

$conexao->close();
?>