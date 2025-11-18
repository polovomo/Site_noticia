<?php
require_once '../config/conexao.php';

// APAGAR usuários problemáticos
$conexao->query("DELETE FROM usuarios WHERE email LIKE '%culturaarte.com%'");

// Dados do editor
$nome = "Editor Principal";
$email = "editor@culturaarte.com";
$senha = "123456";
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

echo "<h2>Corrigindo Editor</h2>";
echo "Gerando hash para: $senha<br>";
echo "Hash: $senha_hash<br>";

// INSERIR diretamente
$sql = "INSERT INTO usuarios (nome, email, senha_hash, tipo, ativo, data_criacao) 
        VALUES ('$nome', '$email', '$senha_hash', 'editor', 1, NOW())";

if ($conexao->query($sql)) {
    echo "✅ EDITOR CRIADO!<br>";
    
    // Verificar
    $result = $conexao->query("SELECT * FROM usuarios WHERE email = '$email'");
    $usuario = $result->fetch_assoc();
    
    echo "Nome: " . $usuario['nome'] . "<br>";
    echo "Hash no banco: " . ($usuario['senha_hash'] ? '✅ PREENCHIDO' : '❌ VAZIO') . "<br>";
    echo "Tamanho do hash: " . strlen($usuario['senha_hash']) . "<br>";
    
    // Testar login
    if (password_verify($senha, $usuario['senha_hash'])) {
        echo "🎉 LOGIN FUNCIONA!<br>";
        echo "<a href='login.php'>Fazer Login Agora</a>";
    }
} else {
    echo "❌ Erro: " . $conexao->error;
}

$conexao->close();
?>