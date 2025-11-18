<?php
require_once '../config/conexao.php';

echo "<h2>🔍 Debug do Sistema de Cadastro</h2>";

// Simular um cadastro
$nome = "Usuário Teste";
$email = "teste@culturaarte.com";
$senha = "123456";

echo "Dados de teste:<br>";
echo "Nome: $nome<br>";
echo "Email: $email<br>";
echo "Senha: $senha<br>";

// Gerar hash
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Hash gerado: $senha_hash<br>";
echo "Tamanho do hash: " . strlen($senha_hash) . " caracteres<br>";

// Testar se o hash funciona
$teste_senha = password_verify($senha, $senha_hash);
echo "Teste password_verify: " . ($teste_senha ? '✅ FUNCIONA' : '❌ FALHA') . "<br>";

// Verificar usuários no banco
echo "<h3>📊 Usuários no Banco:</h3>";
$result = $conexao->query("SELECT id, nome, email, tipo, LENGTH(senha_hash) as hash_len FROM usuarios");
while ($usuario = $result->fetch_assoc()) {
    echo "ID: {$usuario['id']} | ";
    echo "Nome: {$usuario['nome']} | ";
    echo "Email: {$usuario['email']} | ";
    echo "Tipo: {$usuario['tipo']} | ";
    echo "Hash: {$usuario['hash_len']} chars<br>";
}

$conexao->close();
?>