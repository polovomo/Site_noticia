<?php
require_once '../config/conexao.php';
require_once '../config/funcoes.php';

// Destruir sessão
session_destroy();

// Redirecionar para página inicial
redirect('index.php', 'Logout realizado com sucesso!');
?>